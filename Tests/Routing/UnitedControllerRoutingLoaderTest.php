<?php

namespace United\CoreBundle\Tests\DependencyInjection;

use appTestDebugProjectContainer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use United\CoreBundle\Tests\Mock\UnitedControllerMock;

class UnitedControllerRoutingLoaderTest extends KernelTestCase
{

    /**
     * @var appTestDebugProjectContainer $container
     */
    private $container;


    /**
     * Tests if we can create a controller_routing route.
     * Testroutes are:
     *  - /united/foo
     *  - /bar
     */
    public function testGenerateRoutes()
    {

        $controller = new UnitedControllerMock();

        // add basic test
        $this->container->get('united.core.structure')->add(
          'c1',
          'test',
          $controller,
          'core_test'
        );
        $routes = $this->container->get('routing.loader')
          ->load('.', 'united.core_test');
        $routes->addPrefix('/uctr1');

        // check that there are two routes: one redirecting to the first controller route
        $this->matchRoute(
          $routes,
          '/uctr1/',
          array(
            '_controller' => 'Symfony\Bundle\FrameworkBundle\Controller\RedirectController::redirectAction',
            '_route' => 'united.core_test',
            'route' => 'c1.index',
          )
        );

        // and one for the first controller route
        $this->matchRoute(
          $routes,
          '/uctr1/test/',
          array(
            '_controller' => 'United\CoreBundle\Tests\Mock\UnitedControllerMock::indexAction',
            '_route' => 'c1.index',
          )
        );

        // add config the item
        $this->container->get('united.core.structure')->add(
          'c2',
          'test1',
          $controller,
          'core_test2',
          null,
          null,
          array('fuu' => 'baa')
        );
        $routes = $this->container->get('routing.loader')
          ->load('.', 'united.core_test2');
        $routes->addPrefix('/uctr2');

        // check that there are two routes: one redirecting to the first controller route
        $this->matchRoute(
          $routes,
          '/uctr2/',
          array(
            '_controller' => 'Symfony\Bundle\FrameworkBundle\Controller\RedirectController::redirectAction',
            '_route' => 'united.core_test2',
            'route' => 'c2.index',
          )
        );

        // and one for the first controller route
        $this->matchRoute(
          $routes,
          '/uctr2/test1/',
          array(
            '_controller' => 'United\CoreBundle\Tests\Mock\UnitedControllerMock::indexAction',
            '_route' => 'c2.index',
          )
        );

        // try to add router, not following type syntax
        $msg = '';
        try {
            $this->container->get('routing.loader')->load('.', 'unitedOne');
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }
        $this->assertEquals($msg, 'Cannot load resource ".".');

        $msg = '';
        try {
            $this->container->get('routing.loader')->load('.', 'unitedTest');
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }
        $this->assertEquals($msg, 'Cannot load resource ".".');

        $msg = '';
        try {
            $this->container->get('routing.loader')->load('.', 'United.Test');
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }
        $this->assertEquals($msg, 'Cannot load resource ".".');

        $msg = '';
        try {
            $this->container->get('routing.loader')->load('.', 'UNITED.test');
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }
        $this->assertEquals($msg, 'Cannot load resource ".".');

        // add router without any child resources
        $routes = $this->container->get('routing.loader')
          ->load('.', 'united.faa');
        $this->matchRoute($routes, '/', array(), true);

        // add resources to the other namespace
        $this->container->get('united.core.structure')->add(
          'c3',
          'c3test',
          $controller,
          'faa'
        );
        $routes = $this->container->get('routing.loader')
          ->load('.', 'united.faa');
        $this->matchRoute($routes, '/', array());
        $this->matchRoute($routes, '/c3test/', array());
    }

    protected function setUp()
    {
        self::bootKernel();
        $this->container = static::$kernel->getContainer();
    }


    /**
     * @param RouteCollection $routes
     * @param Route $route
     * @param array $params
     * @param bool $noMatch
     */
    private function matchRoute(
      $routes,
      $route,
      $params = array(),
      $noMatch = false
    ) {
        $context = new RequestContext();
        $matcher = new UrlMatcher($routes, $context);

        $match = array();

        try {
            $match = $matcher->match($route);
        } catch (\Exception $e) {
            $this->assertTrue($noMatch);
        }

        if (!$noMatch) {
            $this->assertGreaterThan(0, count($match));
        }

        foreach ($params as $key => $param) {
            $this->assertEquals($match[$key], $param);
        }
    }
}