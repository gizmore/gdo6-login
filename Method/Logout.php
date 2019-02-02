<?php
namespace GDO\Login\Method;

use GDO\Core\GDT_Hook;
use GDO\Core\Method;
use GDO\User\GDO_Session;
use GDO\User\GDO_User;
/**
 * Logout method.
 * 
 * @author gizmore
 * @version 5.0
 */
final class Logout extends Method
{
	public function isUserRequired()
	{
		return true;
	}

	public function execute()
	{
		$session = GDO_Session::instance();
		$user = GDO_User::current();
// 		$user->tempReset();
// 		$user->recache();
		$session->setVar('sess_user', null);
		$session->setVar('sess_data', null);
		$session->save();
		$session->recache();
		GDO_User::$CURRENT = GDO_User::ghost();
		GDT_Hook::callWithIPC('UserLoggedOut', $user);
		return $this->message('msg_logged_out');
	}
}
