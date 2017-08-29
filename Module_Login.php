<?php
namespace GDO\Login;

use GDO\Core\Module;
use GDO\Date\GDT_Duration;
use GDO\Template\GDT_Bar;
use GDO\Type\GDT_Checkbox;
use GDO\Type\GDT_Int;
use GDO\UI\GDT_Link;
use GDO\User\User;

final class Module_Login extends Module
{
	##############
	### Module ###
	##############
	public function isCoreModule() { return true; }
	public function getClasses() { return ['GDO\Login\LoginAttempt', 'GDO\Login\LoginHistory']; }
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
		);
	}
	public function cfgCaptcha() { return $this->getConfigValue('login_captcha'); }
	public function cfgHistory() { return $this->getConfigValue('login_history'); }
	public function cfgFailureTimeout() { return $this->getConfigValue('login_timeout'); }
	public function cfgFailureAttempts() { return $this->getConfigValue('login_tries'); }
	
	##############
	### Navbar ###
	##############
	public function hookRightBar(GDT_Bar $navbar)
	{
		$user = User::current();
		if ($user->isGhost())
		{
			$navbar->addField(GDT_Link::make('signin')->label('btn_login')->href($this->getMethodHREF('Form')));
		}
		else
		{
			$navbar->addField(GDT_Link::make('signout')->label('btn_logout', [$user->displayName()])->href($this->getMethodHREF('Logout')));
		}
	}
}
