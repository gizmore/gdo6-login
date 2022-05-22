<?php
namespace GDO\Login;

use GDO\Core\GDO_Module;
use GDO\Date\GDT_Duration;
use GDO\DB\GDT_Checkbox;
use GDO\DB\GDT_Int;
use GDO\UI\GDT_Link;
use GDO\User\GDO_User;
use GDO\UI\GDT_Page;

/**
 * Login module for GDO6.
 * - Optional captcha
 * - Warnings on failed logins
 * - Login History
 * 
 * @author gizmore@wechall.net
 * @version 6.11.0
 * @since 3.0.0
 */
final class Module_Login extends GDO_Module
{
    public $module_priority = 70;
    
	##############
	### Module ###
	##############
	public function getDependencies() { return ['Captcha']; }
	public function getClasses() { return [GDO_LoginAttempt::class, GDO_LoginHistory::class]; }
	public function onLoadLanguage() { $this->loadLanguage('lang/login'); }
	
	##############
	### Config ###
	##############
	public function getConfig()
	{
		return [
			GDT_Checkbox::make('login_captcha')->initial('0'),
			GDT_Checkbox::make('login_history')->initial('1'),
			GDT_Duration::make('login_timeout')->initial('10m')->min(10)->max(72600),
			GDT_Int::make('login_tries')->initial('3')->min(1)->max(100),
			GDT_Checkbox::make('login_warning_ip_reveal')->initial('1'), # Do not censor IP in alert mails
			GDT_Checkbox::make('login_right_bar')->initial('1'),
		];
	}
	public function cfgCaptcha() { return module_enabled('Captcha') && $this->getConfigValue('login_captcha'); }
	public function cfgHistory() { return $this->getConfigValue('login_history'); }
	public function cfgFailureTimeout() { return $this->getConfigValue('login_timeout'); }
	public function cfgFailureAttempts() { return $this->getConfigValue('login_tries'); }
	public function cfgFailureIPReveal() { return $this->getConfigValue('login_warning_ip_reveal'); }
	public function cfgRightBar() { return $this->getConfigValue('login_right_bar'); }
	
	##############
	### Navbar ###
	##############
	public function onInitSidebar()
	{
	    if ($this->cfgRightBar())
	    {
    		$user = GDO_User::current();
    		$navbar = GDT_Page::$INSTANCE->rightNav;
    		if (!$user->isUser())
    		{
    			$navbar->addField(GDT_Link::make('signin')->label('btn_login')->href(href('Login', 'Form')));
    		}
    		else
    		{
    			$navbar->addField(GDT_Link::make('signout')->label('btn_logout', [$user->displayNameLabel()])->href(href('Login', 'Logout')));
    		}
	    }
	}
	
}
