<?php

namespace App;

use Interop\Container\ContainerInterface;
use App\Handlers\NotFoundHandler as NotFound;
use App\Handlers\NotAllowedHandler as NotAllowed;

class App extends \DI\Bridge\Slim\App {

  protected function configureContainer(\DI\ContainerBuilder $builder) {

    $definitions = [
      'settings.displayErrorDetails' => true,
      'settings.determineRouteBeforeAppMiddleware' => true,
      'notFoundHandler' => function(ContainerInterface $container) {
        return new NotFound($container);
      },
      'notAllowedHandler' => function(ContainerInterface $container) {
        return new NotAllowed($container);
      },
    ];

    $builder->addDefinitions($definitions);
  }
}