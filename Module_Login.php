<?php
namespace GDO\Login;

use GDO\Core\GDO_Module;
use GDO\Date\GDT_Duration;
use GDO\UI\GDT_Bar;
use GDO\DB\GDT_Checkbox;
use GDO\DB\GDT_Int;
use GDO\UI\GDT_Link;
use GDO\User\GDO_User;

/**
 * Login module for GDO6.
 * - Optional captcha
 * - Warnings on failed logins
 * - Login History
 * @author gizmore@wechall.net
 * @version 6.10
 * @since 3.00
 */
final class Module_Login extends GDO_Module
{
    public $module_priority = 100;
    
	##############
	### Module ###
	##############
	public function getDependencies() { return ['Captcha']; }
	public function getClasses() { return ['GDO\Login\GDO_LoginAttempt', 'GDO\Login\GDO_LoginHistory']; }
	public function onLoadLanguage() { $this->loadLanguage('lang/login'); }
	
	##############
	### Config ###
	##############
	public function getConfig()
	{
		return array(
			GDT_Checkbox::make('login_captcha')->initial('0'),
			GDT_Checkbox::make('login_history')->initial('1'),
			GDT_Duration::make('login_timeout')->initial('600')->min(10)->max(72600),
			GDT_Int::make('login_tries')->initial('3')->min(1)->max(100),
			GDT_Checkbox::make('login_warning_ip_reveal')->initial('1'), # Do not censor IP in alert mails
		);
	}
	public function cfgCaptcha() { return $this->getConfigValue('login_captcha'); }
	public function cfgHistory() { return $this->getConfigValue('login_history'); }
	public function cfgFailureTimeout() { return $this->getConfigValue('login_timeout'); }
	public function cfgFailureAttempts() { return $this->getConfigValue('login_tries'); }
	public function cfgFailureIPReveal() { return $this->getConfigValue('login_warning_ip_reveal'); }
	
	##############
	### Navbar ###
	##############
	public function hookRightBar(GDT_Bar $navbar)
	{
		$user = GDO_User::current();
		if ($user->isGhost())
		{
			$navbar->addField(GDT_Link::make('signin')->label('btn_login')->href(href('Login', 'Form')));
		}
		else
		{
			$navbar->addField(GDT_Link::make('signout')->label('btn_logout', [$user->displayNameLabel()])->href(href('Login', 'Logout')));
		}
	}
}
