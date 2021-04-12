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
    const VERSION = '1.0.28';

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
     * Get Modules File Directory
     *
     * @return string
     * @since 1.0.25
     */
    public static function getModuleDir() : string
    {
        return __DIR__.'/../';
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
                $sRouteName = $routeMatch->getMatchedRouteName();
                $aRouteInfo = $routeMatch->getParams();

                $oDbAdapter = $sm->get(AdapterInterface::class);

                CoreController::$oTranslator = $sm->get(TranslatorInterface::class);
                CoreController::$oTranslator->setLocale('en_US');
                if(getenv('PLCWEBLANG')) {
                    if(getenv('PLCWEBLANG') != '') {
                        switch(getenv('PLCWEBLANG')) {
                            case 'de':
                                CoreController::$oTranslator->setLocale('de_DE');
                                break;
                            default:
                                break;
                        }
                    }
                }

                $sTravisBase = '/home/travis/build/OnePlc/PLC_X_User';
                if (is_dir($sTravisBase)) {
                    return;
                }

                /**
                # set session manager
                $config = new StandardConfig();
                $config->setOptions([
                'remember_me_seconds' => 1800,
                'name'                => 'plcauth',
                ]);
                $manager = new SessionManager($config);
                 **/

                $app->getMvcEvent()->getViewModel()->setVariables(['sRouteName' => $sRouteName]);

                /**
                 * preparign for firewall access log

                $log  = "User: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
                "URL: ".$sRouteName.PHP_EOL.
                "Attempt: ".('Success').PHP_EOL.
                "-------------------------".PHP_EOL;
                //Save string to log, use FILE_APPEND to append.
                file_put_contents('./log_'.date("Y-m-d").'.log', $log, FILE_APPEND);
                 * */

                # get session
                $container = new Container('plcauth');
                $bLoggedIn = false;

                # check if user is logged in
                if (isset($container->oUser)) {
                    $bLoggedIn = true;
                    # check permissions
                    CoreController::$oTranslator->setLocale($container->oUser->getLang());

                    $oSettingsTbl = new TableGateway('settings', $oDbAdapter);
                    //echo 'check for '.$aRouteInfo['action'].'-'.$aRouteInfo['controller'];

                    $container->oUser->setAdapter($oDbAdapter);

                    $bIsSetupController = stripos($aRouteInfo['controller'], 'InstallController');
                    if ($bIsSetupController === false) {
                        $aWhiteListedRoutes = [];
                        $oWhiteList = $oSettingsTbl->select(['settings_key' => 'firewall-user-whitelist']);
                        if(count($oWhiteList) > 0) {
                            $oWhiteList = $oWhiteList->current();
                            $aWhiteListedRoutesDB = json_decode($oWhiteList->settings_value);
                            if(is_array($aWhiteListedRoutesDB)) {
                                foreach($aWhiteListedRoutesDB as $sWhiteRoute) {
                                    $aWhiteListedRoutes[$sWhiteRoute] = [];
                                }
                            }
                        }

                        if(!array_key_exists($sRouteName, $aWhiteListedRoutes)) {
                            if (! $container->oUser->hasPermission($aRouteInfo['action'], $aRouteInfo['controller'])
                                && $sRouteName != 'denied') {
                                $response = $e->getResponse();
                                $response->getHeaders()->addHeaderLine(
                                    'Location',
                                    $e->getRouter()->assemble(
                                        ['permission' => $aRouteInfo['action'].'-'.str_replace(['\\'],['-'],$aRouteInfo['controller'])],
                                        ['name' => 'denied']
                                    )
                                );
                                $response->setStatusCode(302);
                                return $response;
                            }
                        }
                    } else {
                        # let user install module
                    }
                } else {

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
                        'setup' => [],
                        'login' => [],
                    ];
                    if(file_exists('config/autoload/local.php')) {
                        $oSettingsTbl = new TableGateway('settings', $oDbAdapter);
                        $oWhiteList = $oSettingsTbl->select(['settings_key' => 'firewall-whitelist']);
                        if(count($oWhiteList) > 0) {
                            $oWhiteList = $oWhiteList->current();
                            $aWhiteListedRoutesDB = json_decode($oWhiteList->settings_value);
                            if(is_array($aWhiteListedRoutesDB)) {
                                foreach($aWhiteListedRoutesDB as $sWhiteRoute) {
                                    $aWhiteListedRoutes[$sWhiteRoute] = [];
                                }
                            }
                        }
                    }


                    /**
                     * Redirect to Login Page if not logged in
                     */
                    if (! $bLoggedIn && ! array_key_exists($sRouteName, $aWhiteListedRoutes)) {
                        /**
                         * Setup before First Login
                         */
                        $sBaseConf = 'config/autoload/local.php';
                        if (! file_exists($sBaseConf) && $sRouteName != 'setup') {
                            $sTravisPath = $sTravisBase.'/vendor/oneplace/oneplace-core/config/autoload/local.php';
                            if (! file_exists($sTravisPath)) {
                                $response = $e->getResponse();
                                $response->getHeaders()
                                    ->addHeaderLine('Location', $e->getRouter()->assemble([], ['name' => 'setup']));
                                $response->setStatusCode(302);
                                //return $response;
                            } else {
                                $response = $e->getResponse();
                                $response->getHeaders()
                                    ->addHeaderLine('Location', $e->getRouter()->assemble([], ['name' => 'login']));
                                $response->setStatusCode(302);
                            }
                        } else {
                            $response = $e->getResponse();
                            $response->getHeaders()
                                ->addHeaderLine('Location', $e->getRouter()->assemble([], ['name' => 'login']));
                            $response->setStatusCode(302);
                            //return $response;
                        }
                    }
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
                Controller\FirewallController::class => function ($container) {
                    $oDbAdapter = $container->get(AdapterInterface::class);
                    return new Controller\FirewallController(
                        $oDbAdapter,
                        $container->get(Model\UserTable::class),
                        $container
                    );
                },
            ],
        ];
    }
}
