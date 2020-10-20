<?php
namespace Movie;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use User\Model\UserTable;
use Cinema\Model\CinemaTable;
use Movie\Model\FavouriteTable;

class Module implements ConfigProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
            // __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                Model\MovieTable::class => function ($container) {
                    $tableGateway = $container->get('Model\MovieTableGateway');
                    return new Model\MovieTable($tableGateway);
                },
                'Model\MovieTableGateway' => function ($container) {
                    $dbAdapter          = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Movie());

                    return new TableGateway('movies', $dbAdapter, null, $resultSetPrototype);
                },
                Model\FavouriteTable::class => function ($container) {
                    $tableGateway = $container->get('Model\FavouriteTableGateway');
                    return new Model\FavouriteTable($tableGateway);
                },
                'Model\FavouriteTableGateway' => function ($container) {
                    $dbAdapter          = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Favourite());

                    return new TableGateway('favourites', $dbAdapter, null, $resultSetPrototype);
                },
            ],
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\MovieController::class => function ($container) {
                    return new Controller\MovieController(
                        $container->get(Model\MovieTable::class),
                        $container->get(UserTable::class),
                        $container->get(CinemaTable::class),
                        $container->get(FavouriteTable::class)
                    );
                },
            ],
        ];
    }
}