<?php
/**
 * module.config.php - User Config
 *
 * Main Config File for Application Module
 *
 * @category Config
 * @package Application
 * @author Verein onePlace
 * @copyright (C) 2020  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

namespace OnePlace\User;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'login' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/login',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'login',
                    ],
                ],
            ],
            'tokenlogin' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/tokenlogin',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'tokenlogin',
                    ],
                ],
            ],
            'logout' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/logout',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'logout',
                    ],
                ],
            ],
            'signup' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/signup[/:token]',
                    'constraints' => [
                        'token' => '[a-zA-Z0-9.$]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'signup',
                    ],
                ],
            ],
            'denied' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/denied[/:permission]',
                    'constraints' => [
                        'permission' => '[a-zA-Z0-9-_]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'denied',
                    ],
                ],
            ],
            'forgot-pw' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/forgot-password',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'forgot',
                    ],
                ],
            ],
            'reset-pw' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/reset-password[/:username[/:token]]',
                    'constraints' => [
                        'username' => '[a-zA-Z0-9-_]*',
                        'token' => '[a-zA-Z0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'reset',
                    ],
                ],
            ],
            # Module Basic Route
            'user' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/user[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z0-9_-]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\UserController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'user-api' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/user/api[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\ApiController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'user-firewall' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/firewall[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\FirewallController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],

    'view_manager' => [
        'template_map' => [
            'layout/login'           => __DIR__ . '/../view/layout/login.phtml',
            'layout/signup'           => __DIR__ . '/../view/layout/signup.phtml',
        ],
        'template_path_stack' => [
            'user' => __DIR__ . '/../view',
        ],
    ],

    'plc_x_user_plugins' => [

    ],

    # Translator
    'translator' => [
        'locale' => 'de_DE',
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ],
        ],
    ],
];
