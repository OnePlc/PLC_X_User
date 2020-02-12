<?php

/**
 * UserControllerTest.php - Main Controller Test Class
 *
 * Test Class for Main Controller of User Module
 *
 * @category Test
 * @package User
 * @author Verein onePlace
 * @copyright (C) 2020  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

declare(strict_types=1);

namespace OnePlaceTest\User\Controller;

use OnePlace\User\Controller\UserController;
use Application\Controller\CoreController;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Laminas\Session\Container;
use OnePlace\User\Model\TestUser;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Stdlib\Parameters;
use OnePlace\User\Model\User;

class UserControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    private function initFakeTestSession()
    {
        /**
         * Init Test Session to Fake Login

        $oSm = $this->getApplicationServiceLocator();
        $oDbAdapter = $oSm->get(AdapterInterface::class);
        $oSession = new Container('plcauth');
        $oTestUser = new TestUser($oDbAdapter);
        $oTestUser->exchangeArray(['full_name'=>'Test','email'=>'admin@1plc.ch','User_ID'=>1]);
        $oSession->oUser = $oTestUser; */
    }

    public function setUp() : void
    {
        // The module configuration should still be applicable for tests.
        // You can override configuration here with test case specific values,
        // such as sample view templates, path stacks, module_listener_options,
        // etc.
        $configOverrides = [];

        $sAppFile = __DIR__.'/../../../../../config/application.config.php';
        $sTravisBase = '/home/travis/build/OnePlc/PLC_X_User';
        if (file_exists($sTravisBase.'/vendor/oneplace/oneplace-core/config/application.config.php')) {
            $sAppFile = $sTravisBase.'/vendor/oneplace/oneplace-core/config/application.config.php';
        }

        $this->setApplicationConfig(ArrayUtils::merge(
            include $sAppFile,
            $configOverrides
        ));

        parent::setUp();
    }

    /**
     * @covers \OnePlace\User\Controller\AuthController::loginAction
     */
    public function testLoginIsLoadedOnSecondLoad()
    {
        $this->dispatch('/', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/login');
    }

    /**
     * @covers \OnePlace\User\Controller\AuthController::loginAction
     */
    public function testLoginIsSuccessful()
    {
        $oSm = $this->getApplicationServiceLocator();
        $oDbAdapter = $oSm->get(AdapterInterface::class);
        $oTestUser = new User($oDbAdapter);
        $oTestUser->exchangeArray(['username'=>'Test','email'=>'travis@travis.com']);

        CoreController::$oSession->oUser = $oTestUser;
        $this->getRequest()->setMethod('POST')
            ->setPost(new Parameters([
                'plc_login_user' => 'plc_travis',
                'plc_login_pass' => '1234',
            ]));
        $this->dispatch('/login');
        $this->assertRedirectTo('/');
    }

    /**
     * @covers \OnePlace\User\Controller\UserController::indexAction
     */
    public function testUserIndexLoading()
    {
        $oSm = $this->getApplicationServiceLocator();
        $oDbAdapter = $oSm->get(AdapterInterface::class);
        $oTestUser = new User($oDbAdapter);
        $oTestUser->exchangeArray(['username'=>'Test','email'=>'travis@travis.com']);

        $this->dispatch('/user', 'GET');
        $this->assertResponseStatusCode(200);
    }

    /**
     * @covers \OnePlace\User\Controller\UserController::addAction
     */
    public function testUserAddFormIndexLoading()
    {
        $oSm = $this->getApplicationServiceLocator();
        $oDbAdapter = $oSm->get(AdapterInterface::class);
        $oTestUser = new User($oDbAdapter);
        $oTestUser->exchangeArray(['username'=>'Test','email'=>'travis@travis.com']);

        $this->dispatch('/user/add', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertQuery('form.plc-core-basic-form');
    }
}
