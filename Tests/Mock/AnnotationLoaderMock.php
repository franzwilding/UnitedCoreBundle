<?php

namespace United\CoreBundle\Tests\Mock;

use Symfony\Component\Routing\Loader\AnnotationClassLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class AnnotationLoaderMock extends AnnotationClassLoader
{

    public $routeCollection;

    public function __construct()
    {
        $this->routeCollection = new RouteCollection();
    }

    protected function configureRoute(
      Route $route,
      \ReflectionClass $class,
      \ReflectionMethod $method,
      $annot
    ) {

    }

    public function load($class, $type = null)
    {
        return $this->routeCollection;
    }
}