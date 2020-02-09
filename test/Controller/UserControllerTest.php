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
use Laminas\Stdlib\ArrayUtils;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Laminas\Session\Container;
use OnePlace\User\Model\TestUser;
use Laminas\Db\Adapter\AdapterInterface;

class UserControllerTest extends AbstractHttpControllerTestCase
{
    private function initFakeTestSession() {
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

        $this->setApplicationConfig(ArrayUtils::merge(
            include __DIR__ . '/../../../../../config/application.config.php',
            $configOverrides
        ));

        parent::setUp();
    }

    public function testSetupIsLoadedOnFirstLoad()
    {
        $this->dispatch('/', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/setup');
    }

    public function testSetupSucceedsWithDefaultData() {
        $this->dispatch('/setup', 'POST', [
            'setup_dbname'=>'plc_demo',
            'setup_dbhost'=>'localhost',
            'setup_dbuser'=>'travis',
            'setup_dbpass'=>'',
            'setup_adminname'=>'plc_travis',
            'setup_adminemail'=>'travis@1plc.ch',
            'setup_adminpass'=>'1234',
            'setup_adminpassrep'=>'1234',
        ]);
        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/login');
    }
}
