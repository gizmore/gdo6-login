<?php
namespace GDO\Login\Method;

use GDO\Captcha\GDT_Captcha;
use GDO\Core\GDT_Hook;
use GDO\Core\GDO;
use GDO\Date\Time;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Login\GDO_LoginAttempt;
use GDO\Login\Module_Login;
use GDO\Mail\Mail;
use GDO\Net\GDT_IP;
use GDO\DB\GDT_Checkbox;
use GDO\User\GDT_Password;
use GDO\UI\GDT_Button;
use GDO\User\GDT_Username;
use GDO\User\GDO_Session;
use GDO\User\GDO_User;
use GDO\Core\GDT_Success;
use GDO\DB\GDT_String;
/**
 * Login via GWFv5 credentials form and method.
 * @author gizmore
 * @since 1.0
 */
final class Form extends MethodForm
{
	public function isUserRequired() { return false; }
	
	public function getUserType() { return 'ghost'; }
	
	public function createForm(GDT_Form $form)
	{
		$form->action(href('Login', 'Form'));
		$form->addField(GDT_String::make('login')->tooltip('tt_login')->notNull());
		$form->addField(GDT_Password::make('password')->notNull());
		$form->addField(GDT_Checkbox::make('bind_ip')->initial('0'));
		if (Module_Login::instance()->cfgCaptcha())
		{
			$form->addField(GDT_Captcha::make());
		}
		$form->addField(GDT_Submit::make()->label('btn_login'));
		$form->addField(GDT_AntiCSRF::make());
		$form->addField(GDT_Button::make('btn_recovery')->href(href('Recovery', 'Form')));
	
		GDT_Hook::callHook('LoginForm', $form);
	}
	
	public function formValidated(GDT_Form $form)
	{
		return $this->onLogin($form->getFormVar('login'), $form->getFormVar('password'), $form->getFormValue('bind_ip'));
	}
	
	public function onLogin($login, $password, $bindIP=false)
	{
		if ($response = $this->banCheck())
		{
			return $response->add($this->renderPage());
		}
		if ( (!($user = GDO_User::getByLogin($login))) ||
			 (!($user->getValue('user_password')->validate($password))) )
		{
			return $this->loginFailed($user)->addField($this->getForm());
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
		$session->setValue('sess_user', $user);
		GDO_User::$CURRENT = $user;
		$session->setValue('sess_data', null);
		$ip = $bindIP ? GDT_IP::current() : null;
		$session->setValue('sess_ip', $ip);
		$session->save();
// 		$user->tempReset();
		GDT_Hook::callWithIPC('UserAuthenticated', $user);
		return $this->message('msg_authenticated', [$user->displayNameLabel()]);
	}

	################
	### Security ###
	################
	private function banCut() { return time() - $this->banTimeout(); }
	private function banTimeout() { return Module_Login::instance()->cfgFailureTimeout(); }
	private function maxAttempts() { return Module_Login::instance()->cfgFailureAttempts(); }
	
	public function loginFailed($user)
	{
		# Insert attempt
		$ip = GDT_IP::current();
		$userid = $user ? $user->getID() : null;
		$attempt = GDO_LoginAttempt::blank(["la_ip"=>$ip, 'la_user_id'=>$userid])->insert();
		
		# Count victim attack. If only 1, we got a new threat and mail it.
		if ($user)
		{
			$this->checkSecurityThreat($user);
		}
		
		# Count attacker attempts
		list($mintime, $attempts) = $this->banData();
		$bannedFor = $mintime - $this->banCut();
		$attemptsLeft = $this->maxAttempts() - $attempts;
		return $this->error('err_login_failed', [$attemptsLeft, Time::humanDuration($bannedFor)]);
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
		if (1 === ($attempts = $table->countWhere($condition)))
		{
			$this->mailSecurityThreat($user);
		}
	}
	
	private function mailSecurityThreat(GDO_User $user)
	{
		$mail = new Mail();
		$mail->setSender(GWF_BOT_EMAIL);
		$mail->setSubject(t('mail_subj_login_threat', [sitename()]));
		$args = [$user->displayName(), sitename(), GDT_IP::current()];
		$mail->setBody(t('mail_body_login_threat', $args));
		$mail->sendToUser($user);
	}
}
