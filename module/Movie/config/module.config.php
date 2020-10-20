<?php

namespace Movie;

return array(
    // 'controllers' => array(
    //     'factories' => [
    //         src\module\Movie\Controller\MovieController::class => function($container) {
    //             return new Controller\MovieController(
    //                 $container->get('Movie\Model\MovieTable')
    //             );
    //         },
    //     ],
    //     // 'invokables' => array(
    //     //     'Movie\Controller\Movie' => function($container) {
    //     //         return new Movie\Controller\MovieController(
    //     //             $container->get('Movie\Model\MovieTable')
    //     //         );
    //     //     },
    //     // ),
        
    // ),
    

    'router' => array(
        'routes' => array(
            'movie' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/movie[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => Controller\MovieController::class,//'Movie\Controller\Movie',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'movie' => __DIR__ . '/../view',
        ),
    ),
);