<?php
namespace GDO\Login\Test;

use GDO\Tests\MethodTest;
use GDO\Tests\TestCase;
use GDO\Login\Method\Form;

final class LoginTest extends TestCase
{
    
    public function testLoginBlocked()
    {
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