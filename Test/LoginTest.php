<?php
namespace GDO\Login\Test;

use GDO\Tests\MethodTest;
use GDO\Tests\TestCase;
use GDO\Login\Method\Form;
use GDO\Login\Method\Logout;
use function PHPUnit\Framework\assertTrue;
use function PHPUnit\Framework\assertFalse;
use GDO\User\GDO_User;

final class LoginTest extends TestCase
{
    
    public function testLoginBlocked()
    {
        $user = $this->userGizmore();
        
        MethodTest::make()->method(Logout::make())->execute();
        
        $user = GDO_User::$CURRENT;
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
        
    }
    
}