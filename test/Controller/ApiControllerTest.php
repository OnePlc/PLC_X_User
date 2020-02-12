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

use mysql_xdevapi\Exception;
use OnePlace\User\Controller\UserController;
use Application\Controller\CoreController;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Laminas\Session\Container;
use OnePlace\User\Model\TestUser;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Stdlib\Parameters;
use OnePlace\User\Model\User;

/**
 * Class UserControllerTest
 * @covers \OnePlace\User\Controller\ApiController
 * @package OnePlaceTest\User\Controller
 */
class ApiControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;
    protected $backupGlobalsBlacklist = [ '_SESSION' ];

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
     * @covers \OnePlace\User\Controller\ApiController::indexAction
     */
    public function testAPIIndexIsLoading()
    {
        /**
         * Init Test Session to Fake Login
         */
        $oSm = $this->getApplicationServiceLocator();
        $oDbAdapter = $oSm->get(AdapterInterface::class);
        $oTestUser = new User($oDbAdapter);
        $oTestUser->exchangeArray([
            'username' => 'travis',
            'email' => 'travis@1plc.ch',
            'id' => 1,
            'full_name' => 'Travis CI',
        ]);
        CoreController::$oSession = new Container('plcauth');
        CoreController::$oSession->oUser = $oTestUser;

        $this->dispatch('/user/api', 'GET');
        $this->assertResponseStatusCode(200);

        $data = json_decode($this->getResponse()->getBody(), true);
        if ( !is_array($data)) {
            var_dump($data);
            throw new \Exception('invalid api response');
        }
        $this->assertArrayHasKey('message', (array)$data);
    }

    /**
     * @covers \OnePlace\User\Controller\ApiController::listAction
     */
    public function testAPIListIsLoading()
    {
        /**
         * Init Test Session to Fake Login
         */
        $oSm = $this->getApplicationServiceLocator();
        $oDbAdapter = $oSm->get(AdapterInterface::class);
        $oTestUser = new User($oDbAdapter);
        $oTestUser->exchangeArray([
            'username' => 'travis',
            'email' => 'travis@1plc.ch',
            'id' => 1,
            'full_name' => 'Travis CI',
        ]);
        CoreController::$oSession = new Container('plcauth');
        CoreController::$oSession->oUser = $oTestUser;

        $this->dispatch('/user/api/list', 'GET');
        $this->assertResponseStatusCode(200);

        $data = json_decode($this->getResponse()->getBody(), true);
        if ( !is_array($data)) {
            var_dump($data);
            throw new \Exception('invalid api response');
        }

        $this->assertArrayHasKey('results', (array)$data);
    }

    /**
     * @covers \OnePlace\User\Controller\ApiController::addAction
     */
    public function testAPIAddFormIsLoading()
    {
        /**
         * Init Test Session to Fake Login
         */
        $oSm = $this->getApplicationServiceLocator();
        $oDbAdapter = $oSm->get(AdapterInterface::class);
        $oTestUser = new User($oDbAdapter);
        $oTestUser->exchangeArray([
            'username' => 'travis',
            'email' => 'travis@1plc.ch',
            'id' => 1,
            'full_name' => 'Travis CI',
        ]);
        CoreController::$oSession = new Container('plcauth');
        CoreController::$oSession->oUser = $oTestUser;

        $this->dispatch('/user/api/add', 'GET');
        $this->assertResponseStatusCode(200);
    }

    /**
     * @covers \OnePlace\User\Controller\ApiController::manageAction
     */
    public function testAPIManageListIsLoading()
    {
        /**
         * Init Test Session to Fake Login
         */
        $oSm = $this->getApplicationServiceLocator();
        $oDbAdapter = $oSm->get(AdapterInterface::class);
        $oTestUser = new User($oDbAdapter);
        $oTestUser->exchangeArray([
            'username' => 'travis',
            'email' => 'travis@1plc.ch',
            'id' => 1,
            'full_name' => 'Travis CI',
        ]);
        CoreController::$oSession = new Container('plcauth');
        CoreController::$oSession->oUser = $oTestUser;

        $this->dispatch('/user/api/manage', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertQuery('table.plc-core-basic-table');
    }

    /**
     * @covers \OnePlace\User\Controller\ApiController::getAction
     */
    public function testAPIGetIsLoading()
    {
        /**
         * Init Test Session to Fake Login
         */
        $oSm = $this->getApplicationServiceLocator();
        $oDbAdapter = $oSm->get(AdapterInterface::class);
        $oTestUser = new User($oDbAdapter);
        $oTestUser->exchangeArray([
            'username' => 'travis',
            'email' => 'travis@1plc.ch',
            'id' => 1,
            'full_name' => 'Travis CI',
        ]);
        CoreController::$oSession = new Container('plcauth');
        CoreController::$oSession->oUser = $oTestUser;

        $this->dispatch('/user/api/get/1', 'GET');
        $this->assertResponseStatusCode(200);

        $data = json_decode($this->getResponse()->getBody(), true);
        if ( !is_array($data)) {
            var_dump($data);
            throw new \Exception('invalid api response');
        }

        $this->assertArrayHasKey('oItem', (array)$data);
    }
}
