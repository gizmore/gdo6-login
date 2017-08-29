<?php
namespace GDO\Login\Method;

use GDO\Core\GDT_Hook;
use GDO\Core\Method;
use GDO\User\Session;
use GDO\User\User;
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
        $session = Session::instance();
        $user = User::current();
        $user->tempReset();
        $session->setValue('sess_user', null);
        $session->setValue('sess_data', null);
        $session->save();
        User::$CURRENT = User::ghost();
        GDT_Hook::call('UserLoggedOut', $user);
        return $this->message('msg_logged_out');
    }
}
