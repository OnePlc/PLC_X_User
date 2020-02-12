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

if ( !isset( $_SESSION ) ) $_SESSION = [];

class UserControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;
    protected $backupGlobalsBlacklist = [ '_SESSION' ];

    private function initFakeTestSession()
    {

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
    public function testLoginIsSuccessful()
    {
        /**
         * Init Test Session to Fake Login
         */
        $oSm = $this->getApplicationServiceLocator();
        $oDbAdapter = $oSm->get(AdapterInterface::class);
        $oTestUser = new User($oDbAdapter);
        $oTestUser->exchangeArray(['username'=>'travis','email'=>'travis@1plc.ch','id'=>1,'full_name'=>'Travis CI']);
        CoreController::$oSession = new Container('plcauth');
        CoreController::$oSession->oUser = $oTestUser;

        $this->getRequest()->setMethod('POST')
            ->setPost(new Parameters([
                'plc_login_user' => 'travis',
                'plc_login_pass' => '1234',
            ]));
        $this->dispatch('/login');
        $this->assertRedirectTo('/');
    }

    /**
     * @covers \OnePlace\User\Controller\AuthController::logoutAction
     */
    public function testLogoutIsSuccessful()
    {
        /**
         * Init Test Session to Fake Login
         */
        $oSm = $this->getApplicationServiceLocator();
        $oDbAdapter = $oSm->get(AdapterInterface::class);
        $oTestUser = new User($oDbAdapter);
        $oTestUser->exchangeArray(['username'=>'travis','email'=>'travis@1plc.ch','id'=>1,'full_name'=>'Travis CI']);
        CoreController::$oSession = new Container('plcauth');
        CoreController::$oSession->oUser = $oTestUser;

        $this->dispatch('/logout');
        $this->assertRedirectTo('/login');
    }

    /**
     * @covers \OnePlace\User\Controller\AuthController::deniedAction
     */
    public function testDeniedIsSuccessful()
    {
        /**
         * Init Test Session to Fake Login
         */
        $oSm = $this->getApplicationServiceLocator();
        $oDbAdapter = $oSm->get(AdapterInterface::class);
        $oTestUser = new User($oDbAdapter);
        $oTestUser->exchangeArray(['username'=>'travis','email'=>'travis@1plc.ch','id'=>1,'full_name'=>'Travis CI']);
        CoreController::$oSession = new Container('plcauth');
        CoreController::$oSession->oUser = $oTestUser;

        $this->dispatch('/denied');
        $this->assertQuery('div.alert-warning');
    }

    /**
     * @covers \OnePlace\User\Controller\AuthController::forgotAction
     */
    public function testForgotIsSuccessful()
    {
        /**
         * Init Test Session to Fake Login
         */
        $oSm = $this->getApplicationServiceLocator();
        $oDbAdapter = $oSm->get(AdapterInterface::class);
        $oTestUser = new User($oDbAdapter);
        $oTestUser->exchangeArray(['username'=>'travis','email'=>'travis@1plc.ch','id'=>1,'full_name'=>'Travis CI']);
        CoreController::$oSession = new Container('plcauth');
        CoreController::$oSession->oUser = $oTestUser;

        $this->dispatch('/forgot-password');
        $this->assertQuery('input#plc_login_user');
    }

    /**
     * @covers \OnePlace\User\Controller\UserController::updatesettingAction
     */
    public function testUpdateSettingIsSuccessful()
    {
        /**
         * Init Test Session to Fake Login
         */
        $oSm = $this->getApplicationServiceLocator();
        $oDbAdapter = $oSm->get(AdapterInterface::class);
        $oTestUser = new User($oDbAdapter);
        $oTestUser->exchangeArray(['username'=>'travis','email'=>'travis@1plc.ch','id'=>1,'full_name'=>'Travis CI']);
        CoreController::$oSession = new Container('plcauth');
        CoreController::$oSession->oUser = $oTestUser;

        $this->getRequest()->setMethod('POST')
            ->setPost(new Parameters([
                'setting_name' => 'test-setting',
                'setting_value' => '1234',
            ]));
        $this->dispatch('/user/updatesetting');
        $this->assertResponseStatusCode(200);
    }

    /**
     * @covers \OnePlace\User\Controller\UserController::indexAction
     */
    public function testUserIndexLoading()
    {
        /**
         * Init Test Session to Fake Login
         */
        $oSm = $this->getApplicationServiceLocator();
        $oDbAdapter = $oSm->get(AdapterInterface::class);
        $oTestUser = new User($oDbAdapter);
        $oTestUser->exchangeArray(['username'=>'travis','email'=>'travis@1plc.ch','id'=>1,'full_name'=>'Travis CI']);
        CoreController::$oSession = new Container('plcauth');
        CoreController::$oSession->oUser = $oTestUser;

        $this->dispatch('/user', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertQuery('table.plc-core-basic-table');
    }

    /**
     * @covers \OnePlace\User\Controller\UserController::addAction
     */
    public function testUserAddFormIndexLoading()
    {
        /**
         * Init Test Session to Fake Login
         */
        $oSm = $this->getApplicationServiceLocator();
        $oDbAdapter = $oSm->get(AdapterInterface::class);
        $oTestUser = new User($oDbAdapter);
        $oTestUser->exchangeArray(['username'=>'travis','email'=>'travis@1plc.ch','id'=>1,'full_name'=>'Travis CI']);
        CoreController::$oSession = new Container('plcauth');
        CoreController::$oSession->oUser = $oTestUser;

        $this->dispatch('/user/add', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertQuery('form.plc-core-basic-form');
    }

    /**
     * @covers \OnePlace\User\Controller\UserController::viewAction
     */
    public function testUserViewFormLoading()
    {
        /**
         * Init Test Session to Fake Login
         */
        $oSm = $this->getApplicationServiceLocator();
        $oDbAdapter = $oSm->get(AdapterInterface::class);
        $oTestUser = new User($oDbAdapter);
        $oTestUser->exchangeArray(['username'=>'travis','email'=>'travis@1plc.ch','id'=>1,'full_name'=>'Travis CI']);
        CoreController::$oSession = new Container('plcauth');
        CoreController::$oSession->oUser = $oTestUser;

        $this->dispatch('/user/view/1', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertQuery('div.plc-core-basic-view');
    }

    /**
     * @covers \OnePlace\User\Controller\UserController::editAction
     */
    public function testUseEditFormLoading()
    {
        /**
         * Init Test Session to Fake Login
         */
        $oSm = $this->getApplicationServiceLocator();
        $oDbAdapter = $oSm->get(AdapterInterface::class);
        $oTestUser = new User($oDbAdapter);
        $oTestUser->exchangeArray(['username'=>'travis','email'=>'travis@1plc.ch','id'=>1,'full_name'=>'Travis CI']);
        CoreController::$oSession = new Container('plcauth');
        CoreController::$oSession->oUser = $oTestUser;

        $this->dispatch('/user/edit/1', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertQuery('form.plc-core-basic-form');
    }

    /**
     * @covers \OnePlace\User\Controller\UserController::profileAction
     */
    public function testUserProfileIsLoading()
    {
        /**
         * Init Test Session to Fake Login
         */
        $oSm = $this->getApplicationServiceLocator();
        $oDbAdapter = $oSm->get(AdapterInterface::class);
        $oTestUser = new User($oDbAdapter);
        $oTestUser->exchangeArray(['username'=>'travis','email'=>'travis@1plc.ch','id'=>1,'full_name'=>'Travis CI']);
        CoreController::$oSession = new Container('plcauth');
        CoreController::$oSession->oUser = $oTestUser;

        $this->dispatch('/user/profile', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertQuery('h3.card-title');
    }

    /**
     * @covers \OnePlace\User\Controller\UserController::settingsAction
     */
    public function testUserSettingsIsLoading()
    {
        /**
         * Init Test Session to Fake Login
         */
        $oSm = $this->getApplicationServiceLocator();
        $oDbAdapter = $oSm->get(AdapterInterface::class);
        $oTestUser = new User($oDbAdapter);
        $oTestUser->exchangeArray(['username'=>'travis','email'=>'travis@1plc.ch','id'=>1,'full_name'=>'Travis CI']);
        CoreController::$oSession = new Container('plcauth');
        CoreController::$oSession->oUser = $oTestUser;

        $this->dispatch('/user/settings', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertQuery('h3.card-title');
    }

    /**
     * @covers \OnePlace\User\Controller\UserController::languagesAction
     */
    public function testUserLanguagesIsLoading()
    {
        /**
         * Init Test Session to Fake Login
         */
        $oSm = $this->getApplicationServiceLocator();
        $oDbAdapter = $oSm->get(AdapterInterface::class);
        $oTestUser = new User($oDbAdapter);
        $oTestUser->exchangeArray(['username'=>'travis','email'=>'travis@1plc.ch','id'=>1,'full_name'=>'Travis CI']);
        CoreController::$oSession = new Container('plcauth');
        CoreController::$oSession->oUser = $oTestUser;

        $this->dispatch('/user/languages', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertQuery('ul.list-group');
    }
}
