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
                        'controller' => Controller\UserController::class,
                        'action'     => 'login',
                    ],
                ],
            ],
            'tokenlogin' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/tokenlogin',
                    'defaults' => [
                        'controller' => Controller\UserController::class,
                        'action'     => 'tokenlogin',
                    ],
                ],
            ],
            'logout' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/logout',
                    'defaults' => [
                        'controller' => Controller\UserController::class,
                        'action'     => 'logout',
                    ],
                ],
            ],
            'denied' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/denied',
                    'defaults' => [
                        'controller' => Controller\UserController::class,
                        'action'     => 'denied',
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
        ],
    ],

    'view_manager' => [
        'template_map' => [
            'layout/login'           => __DIR__ . '/../view/layout/login.phtml',
        ],
        'template_path_stack' => [
            'user' => __DIR__ . '/../view',
        ],
    ],

    'plc_x_user_plugins' => [

    ],
];
