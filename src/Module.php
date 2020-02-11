<?php
/**
 * Module.php - Module Class
 *
 * Module Class File for User Module
 *
 * @category Config
 * @package User
 * @author Verein onePlace
 * @copyright (C) 2020  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

namespace OnePlace\User;

use Application\Controller\CoreController;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\EventManager\EventInterface as Event;
use Laminas\Mvc\MvcEvent;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Session\Config\StandardConfig;
use Laminas\Session\SessionManager;
use Laminas\Session\Container;
use Laminas\I18n\Translator\TranslatorInterface;

class Module
{
    /**
     * Module Version
     *
     * @since 1.0.0
     */
    const VERSION = '1.0.14';

    /**
     * Load module config file
     *
     * @return array
     * @since 1.0.0
     */
    public function getConfig() : array
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * On Bootstrap - is executed on every page request
     *
     * checks if user is logged in and has sufficient
     * permissions, redirects to login otherwise
     * so this is our basic firewall
     *
     * @param Event $e
     * @since 1.0.0
     */
    public function onBootstrap(\Laminas\EventManager\EventInterface $e)
    {
        CoreController::$aPerfomanceLogStart = getrusage();

        $app = $e->getApplication();
        $sm = $app->getServiceManager();
        $app->getEventManager()->attach(
            'route',
            function ($e) {
                # get basic info from application
                $app = $e->getApplication();
                $routeMatch = $e->getRouteMatch();
                $sm = $app->getServiceManager();

                $oDbAdapter = $sm->get(AdapterInterface::class);

                $translator = $sm->get(TranslatorInterface::class);
                $translator->setLocale('en_US');

                /**
                # set session manager
                $config = new StandardConfig();
                $config->setOptions([
                    'remember_me_seconds' => 1800,
                    'name'                => 'plcauth',
                ]);
                $manager = new SessionManager($config);
                **/

                $sRouteName = $routeMatch->getMatchedRouteName();
                $aRouteInfo = $routeMatch->getParams();

                $app->getMvcEvent()->getViewModel()->setVariables(['sRouteName' => $sRouteName]);

                # get session
                $container = new Container('plcauth');
                $bLoggedIn = false;

                # check if user is logged in
                if (isset($container->oUser)) {
                    $bLoggedIn = true;
                    # check permissions
                    $translator->setLocale($container->oUser->getLang());


                    //echo 'check for '.$aRouteInfo['action'].'-'.$aRouteInfo['controller'];

                    $container->oUser->setAdapter($oDbAdapter);

                    $bIsSetupController = stripos($aRouteInfo['controller'], 'InstallController');
                    if ($bIsSetupController === false) {
                        if (! $container->oUser->hasPermission($aRouteInfo['action'], $aRouteInfo['controller'])
                            && $sRouteName != 'denied') {
                            $response = $e->getResponse();
                            $response->getHeaders()->addHeaderLine(
                                'Location',
                                $e->getRouter()->assemble(
                                    ['id' => $aRouteInfo['action']],
                                    ['name' => 'denied']
                                )
                            );
                            $response->setStatusCode(302);
                            return $response;
                        }
                    } else {
                        # let user install module
                    }
                }

                /**
                 * Api Login
                 */
                $bIsApiController = stripos($aRouteInfo['controller'], 'ApiController');
                if (isset($_REQUEST['authkey']) && $bIsApiController !== false) {
                    try {
                        # Do Authtoken login
                        $oKeysTbl = new TableGateway('core_api_key', $oDbAdapter);
                        $oKeyActive = $oKeysTbl->select(['api_key' => $_REQUEST['authkey']]);
                        if (count($oKeyActive) > 0) {
                            $oKey = $oKeyActive->current();
                            if (password_verify($_REQUEST['authtoken'], $oKey->api_token)) {
                                $bLoggedIn = true;
                            }
                        }
                    } catch (\RuntimeException $e) {
                        # could not load auth key
                    }
                }

                # Whitelisted routes that need no authentication
                $aWhiteListedRoutes = [
                    'tokenlogin' => [],
                    'setup' => [],
                    'login' => [],
                    'reset-pw' => [],
                    'forgot-pw' => [],
                ];

                /**
                 * Redirect to Login Page if not logged in
                 */
                if (! $bLoggedIn && ! array_key_exists($sRouteName, $aWhiteListedRoutes)) {

                    /**
                     * Setup before First Login
                     */
                    if (! file_exists(__DIR__.'/../../../config/autoload/local.php') && $sRouteName != 'setup') {
                        echo $sRouteName;
                        echo 'no config yet3';

                        $response = $e->getResponse();
                        $response->getHeaders()
                            ->addHeaderLine('Location', $e->getRouter()->assemble([], ['name' => 'setup']));
                        $response->setStatusCode(302);
                        return $response;
                    } else {
                        $response = $e->getResponse();
                        $response->getHeaders()
                            ->addHeaderLine('Location', $e->getRouter()->assemble([], ['name' => 'login']));
                        $response->setStatusCode(302);
                        return $response;
                    }
                }

                /**
                 * Enforce Setup
                 */
                if (! file_exists(__DIR__.'/../../../config/autoload/local.php') && $sRouteName != 'setup') {
                    echo $sRouteName;
                    echo 'no config yet4';

                    $response = $e->getResponse();
                    $response->getHeaders()
                        ->addHeaderLine('Location', $e->getRouter()->assemble([], ['name' => 'setup']));
                    $response->setStatusCode(302);
                    return $response;
                }
            },
            -100
        );
    }

    /**
     * Load Models
     *
     * @since 1.0.0
     */
    public function getServiceConfig() : array
    {
        return [
            'factories' => [
                # User Module - Base Model
                Model\UserTable::class => function ($container) {
                    $tableGateway = $container->get(Model\UserTableGateway::class);
                    return new Model\UserTable($tableGateway);
                },
                Model\UserTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\User($dbAdapter));
                    return new TableGateway('user', $dbAdapter, null, $resultSetPrototype);
                },
                # User Module - Base Model
                Model\ApikeyTable::class => function ($container) {
                    $tableGateway = $container->get(Model\ApikeyTableGateway::class);
                    return new Model\ApikeyTable($tableGateway);
                },
                Model\ApikeyTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Apikey($dbAdapter));
                    return new TableGateway('core_api_key', $dbAdapter, null, $resultSetPrototype);
                },
                Model\UserPermissionGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new TableGateway('user_permission', $dbAdapter);
                },
            ],
        ];
    }

    /**
     * Load Controllers
     */
    public function getControllerConfig() : array
    {
        return [
            'factories' => [
                Controller\UserController::class => function ($container) {
                    $oDbAdapter = $container->get(AdapterInterface::class);
                    return new Controller\UserController(
                        $oDbAdapter,
                        $container->get(Model\UserTable::class),
                        $container
                    );
                },
                Controller\AuthController::class => function ($container) {
                    $oDbAdapter = $container->get(AdapterInterface::class);
                    return new Controller\AuthController(
                        $oDbAdapter,
                        $container->get(Model\UserTable::class),
                        $container
                    );
                },
                Controller\ApiController::class => function ($container) {
                    $oDbAdapter = $container->get(AdapterInterface::class);
                    return new Controller\ApiController(
                        $oDbAdapter,
                        $container->get(Model\UserTable::class),
                        $container
                    );
                },
            ],
        ];
    }
}
