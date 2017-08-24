<?php
namespace GDO\Login;

use GDO\Core\Module;
use GDO\Date\GDO_Duration;
use GDO\Template\GDO_Bar;
use GDO\Type\GDO_Checkbox;
use GDO\Type\GDO_Int;
use GDO\UI\GDO_Link;
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
			GDO_Checkbox::make('login_captcha')->initial('0'),
			GDO_Checkbox::make('login_history')->initial('1'),
			GDO_Duration::make('login_timeout')->initial('600')->min(10)->max(72600),
			GDO_Int::make('login_tries')->initial('3')->min(1)->max(100),
		);
	}
	public function cfgCaptcha() { return $this->getConfigValue('login_captcha'); }
	public function cfgHistory() { return $this->getConfigValue('login_history'); }
	public function cfgFailureTimeout() { return $this->getConfigValue('login_timeout'); }
	public function cfgFailureAttempts() { return $this->getConfigValue('login_tries'); }
	
	##############
	### Navbar ###
	##############
	public function hookRightBar(GDO_Bar $navbar)
	{
		$user = User::current();
		if ($user->isGhost())
		{
			$navbar->addField(GDO_Link::make('signin')->label('btn_login')->href($this->getMethodHREF('Form')));
		}
		else
		{
			$navbar->addField(GDO_Link::make('signout')->label('btn_logout', [$user->displayName()])->href($this->getMethodHREF('Logout')));
		}
	}
}
