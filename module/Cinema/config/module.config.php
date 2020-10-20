<?php

namespace Cinema;

return array(
    // 'controllers' => array(
    //     'factories' => [
    //         src\module\Cinema\Controller\CinemaController::class => function($container) {
    //             return new Controller\CinemaController(
    //                 $container->get('Cinema\Model\CinemaTable')
    //             );
    //         },
    //     ],
    //     // 'invokables' => array(
    //     //     'Cinema\Controller\Cinema' => function($container) {
    //     //         return new Cinema\Controller\CinemaController(
    //     //             $container->get('Cinema\Model\CinemaTable')
    //     //         );
    //     //     },
    //     // ),
        
    // ),
    

    'router' => array(
        'routes' => array(
            'cinema' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/cinema[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => Controller\CinemaController::class,//'Cinema\Controller\Cinema',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'cinema' => __DIR__ . '/../view',
        ),
    ),
);