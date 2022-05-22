<?php
namespace GDO\Login\Method;

use GDO\Captcha\GDT_Captcha;
use GDO\Core\Application;
use GDO\Core\GDT;
use GDO\Core\GDT_Hook;
use GDO\Core\GDO;
use GDO\Date\Time;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Login\GDO_LoginAttempt;
use GDO\Login\Module_Login;
use GDO\Mail\Mail;
use GDO\Net\GDT_IP;
use GDO\DB\GDT_Checkbox;
use GDO\User\GDT_Password;
use GDO\Session\GDO_Session;
use GDO\User\GDO_User;
use GDO\Core\GDT_Success;
use GDO\DB\GDT_String;
use GDO\Form\GDT_Validator;

/**
 * Login via GDOv6.11 credentials form and method.
 * 
 * @author gizmore
 * @version 6.11.1
 * @since 1.0.0
 */
final class Form extends MethodForm
{
	public function isUserRequired() { return false; }
	
	public function getUserType() { return ['ghost', 'guest']; }
	
	public function renderPage()
	{
		return $this->templatePHP('form.php', ['form'=>$this->getForm()]);
	}
	
	public function createForm(GDT_Form $form)
	{
		$form->action(href('Login', 'Form'));
		$form->addField(GDT_String::make('login')->icon('face')->tooltip('tt_login')->notNull());
		$form->addField(GDT_Validator::make('validateDeleted')->validator('login', [$this, 'validateDeleted']));
		$form->addField(GDT_Validator::make('validateType')->validator('login', [$this, 'validateType']));
		$form->addField(GDT_Password::make('password')->notNull());
		$form->addField(GDT_Checkbox::make('bind_ip')->tooltip('tt_bind_ip')->initial('1'));
		if (Module_Login::instance()->cfgCaptcha())
		{
			$form->addField(GDT_Captcha::make());
		}
		$form->actions()->addField(GDT_Submit::make()->label('btn_login'));
		GDT_Hook::callHook('LoginForm', $form);
	}
	
	/**
	 * 
	 * @param GDT_Form $form
	 * @param GDT $field
	 * @param string $value
	 */
	public function validateDeleted(GDT_Form $form, GDT $field, $value)
	{
	    if ($user = GDO_User::getByLogin($value))
	    {
	        if ($user->isDeleted())
	        {
	            return $field->error('err_user_deleted');
	        }
	    }
	    return true;
	}
	
	/**
	 * Disallow system or bot users.
	 * @param GDT_Form $form
	 * @param GDT $field
	 * @param string $value
	 */
	public function validateType(GDT_Form $form, GDT $field, $value)
	{
	    if ($user = GDO_User::getByLogin($value))
	    {
	        if (!$user->isMember())
	        {
    	        return $field->error('err_user_type', [t('enum_member')]);
	        }
	    }
	    return true;
	}
	
	public function formValidated(GDT_Form $form)
	{
		return $this->onLogin($form->getFormVar('login'), $form->getFormVar('password'), $form->getFormValue('bind_ip'));
	}
	
	public function onLogin($login, $password, $bindIP=false)
	{
		if ($response = $this->banCheck())
		{
			return $response->addField($this->renderPage());
		}
		
		$user = GDO_User::getByLogin($login);
		$hash = $user ? $user->getValue('user_password') : null;
		
		if ( (!$user) ||
		     (!$hash) ||
		     (!$hash->validate($password)) )
		{
			return $this->loginFailed($user)->addField($this->renderPage());
		}
		return $this->loginSuccess($user, $bindIP);
	}
	
	/**
	 * @param GDO_User $user
	 * @param bool $bindIP
	 * @return GDT_Success
	 */
	public function loginSuccess(GDO_User $user, $bindIP=false)
	{
		if (!($session = GDO_Session::instance()))
		{
			return $this->error('err_session_required');
		}
		$session->setVar('sess_user', $user->getID());
		GDO_User::setCurrent($user);
        if ($bindIP)
        {
    		$session->setVar('sess_ip', GDT_IP::current());
        }
		GDT_Hook::callWithIPC('UserAuthenticated', $user);
		return $this->message('msg_authenticated', [$user->displayNameLabel()]);
	}

	################
	### Security ###
	################
	private function banCut() { return Application::$TIME - $this->banTimeout(); }
	private function banTimeout() { return Module_Login::instance()->cfgFailureTimeout(); }
	private function maxAttempts() { return Module_Login::instance()->cfgFailureAttempts(); }
	
	public function loginFailed($user)
	{
		# Insert attempt
		$ip = GDT_IP::current();
		$userid = $user ? $user->getID() : null;
		GDO_LoginAttempt::blank([
			"la_ip" => $ip,
			'la_user_id' => $userid,
		])->insert();
		
		# Count victim attack. If only 1, we got a new threat and mail it.
		if ($user)
		{
			$this->checkSecurityThreat($user);
		}
		
		# Count attacker attempts
		list($mintime, $attempts) = $this->banData();
		$bannedFor = $mintime - $this->banCut();
		$attemptsLeft = $this->maxAttempts() - $attempts;
		
		return $this->error('err_login_failed', [$attemptsLeft, Time::humanDuration($bannedFor)], 200);
	}
	
	private function banCheck()
	{
		list($mintime, $count) = $this->banData();
		if ($count >= $this->maxAttempts())
		{
			$bannedFor = $mintime - $this->banCut();
			return $this->error('err_login_ban', [Time::humanDuration($bannedFor)]);
		}
	}
	
	private function banData()
	{
		$table = GDO_LoginAttempt::table();
		$condition = sprintf('la_ip=%s AND la_time > FROM_UNIXTIME(%d)', GDO::quoteS(GDT_IP::current()), $this->banCut());
		return $table->select('UNIX_TIMESTAMP(MIN(la_time)), COUNT(*)')->where($condition)->exec()->fetchRow();
	}
	
	private function checkSecurityThreat(GDO_User $user)
	{
		$table = GDO_LoginAttempt::table();
		$condition = sprintf('la_user_id=%s AND la_time > FROM_UNIXTIME(%d)', $user->getID(), $this->banCut());
		if (1 === $table->countWhere($condition))
		{
			$this->mailSecurityThreat($user);
		}
	}
	
	private function mailSecurityThreat(GDO_User $user)
	{
		$mail = new Mail();
		$mail->setSender(GDO_BOT_EMAIL);
		$mail->setSubject(t('mail_subj_login_threat', [sitename()]));
		$revealIP = Module_Login::instance()->cfgFailureIPReveal();
		$ip = $revealIP ? GDT_IP::current() : 'xx.xx.xx.xx';
		$args = [$user->displayName(), sitename(), $ip];
		$mail->setBody(t('mail_body_login_threat', $args));
		$mail->sendToUser($user);
	}
	
}
