<?php
namespace GDO\Login\Test;

use GDO\Tests\MethodTest;
use GDO\Tests\TestCase;
use GDO\Login\Method\Form;
use GDO\Login\Method\Logout;
use function PHPUnit\Framework\assertTrue;
use function PHPUnit\Framework\assertFalse;
use GDO\User\GDO_User;
use function PHPUnit\Framework\assertStringContainsString;
use GDO\Core\Website;
use GDO\Language\Trans;

final class LoginTest extends TestCase
{
    public function testLoginSuccess()
    {
        $this->userGhost();
        
        $parameters = array(
            'login' => 'gizmore',
            'password' => '11111111',
            'bindip' => '0',
        );
        
        $m = MethodTest::make()->method(Form::make())->parameters($parameters);
        $r = $m->execute();
        
        $user1 = GDO_User::current();
        $user2 = $this->gizmore();
        
        assertTrue($user1 === $user2, 'Check if gizmore can login.');
    }
    
    public function testLogoutAndLoginBlocked()
    {
        Trans::setISO('en');
        $user = $this->userGizmore();
        
        MethodTest::make()->method(Logout::make())->execute();
        
        $user = GDO_User::current();
        assertFalse($user->isAuthenticated());
        
        $parameters = array(
            'login' => 'gizmore',
            'password' => 'incorrect',
            'bindip' => '0',
        );
        
        MethodTest::make()->method(Form::make())->parameters($parameters)->execute();
        
        MethodTest::make()->method(Form::make())->parameters($parameters)->execute();
        
        MethodTest::make()->method(Form::make())->parameters($parameters)->execute();
        
        MethodTest::make()->method(Form::make())->parameters($parameters)->execute();
       
        $html = Website::$TOP_RESPONSE->renderCell();
        assertStringContainsString('Please wait', $html, 'Check if login is blocked after N attempts.');
    }
    
}