<?php

namespace United\CoreBundle\Tests\DependencyInjection;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use United\CoreBundle\Tests\Mock\RouterMock;
use United\CoreBundle\Tests\Mock\UnitedControllerMock;
use United\CoreBundle\Util\UnitedStructureItem;

class UnitedStructureItemTest extends \PHPUnit_Framework_TestCase
{

    public function testWrongController()
    {

        $controller = new \stdClass();

        // try to create an wrong controller
        $msg = '';
        try {
            new UnitedStructureItem('id1', 'path1', $controller, 'united');
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }

        $this->assertEquals(
          'You must define an controller, that extends United\CoreBundle\Controller\UnitedController.',
          $msg
        );
    }

    public function testBasicGetterAndSetter()
    {

        $controller = new UnitedControllerMock();
        $item = new UnitedStructureItem('id1', 'path1', $controller, 'united');

        // test basic getters
        $this->assertEquals('id1', $item->getId());
        $this->assertEquals('path1', $item->getPath());
        $this->assertEquals($controller, $item->getController());
        $this->assertEquals('united', $item->getNamespace());
        $this->assertEquals(array(), $item->getConfig());
        $this->assertEquals(null, $item->getSubRoute());
        $this->assertEquals(null, $item->getRoute());
        $this->assertEquals(new RouteCollection(), $item->getRoutes());
        $this->assertEquals(false, $item->active());
        $this->assertEquals(false, $item->activeTrail());

        // test basic setters
        $controller2 = new UnitedControllerMock();
        $this->assertEquals('newid1', $item->setId('newid1')->getId());
        $this->assertEquals('newpath', $item->setPath('newpath')->getPath());
        $this->assertEquals(
          $controller2,
          $item->setController($controller2)
            ->getController()
        );
        $this->assertEquals(
          'newnamespace',
          $item->setNamespace('newnamespace')
            ->getNamespace()
        );
        $this->assertEquals(
          array('faa'),
          $item->setConfig(array('faa'))
            ->getConfig()
        );
        $this->assertEquals(
          'any_sub_route',
          $item->setSubRoute('any_sub_route')
            ->getSubRoute()
        );
        $this->assertEquals(true, $item->active());
        $this->assertEquals(true, $item->activeTrail());

        // test getter / setter on routes
        $routes = new RouteCollection();
        $route1 = new Route('newroute1');
        $route2 = new Route('newroute2');
        $routes->add('newid2', $route2);
        $routes->add('newid1', $route1);

        $this->assertEquals($routes, $item->setRoutes($routes)->getRoutes());
        $this->assertEquals('newid1', $item->getRoute());
        $this->assertEquals(true, $item->active());
        $this->assertEquals(true, $item->activeTrail());

        // test children / parent
        $item1 = new UnitedStructureItem('id2', 'path1', $controller, 'united');
        $item1->addChild($item);
        $this->assertEquals(false, $item1->active());
        $this->assertEquals(true, $item1->activeTrail());
        $this->assertCount(1, $item1->getChildren());

        $children = $item1->getChildren();
        $child = array_pop($children);
        $this->assertEquals($item, $child);
        $this->assertEquals($item1, $item->getParent());
        $this->assertCount(1, $item->getParents());

        $item2 = new UnitedStructureItem('id3', 'path2', $controller, 'united');
        $item2->addChild($item1);
        $this->assertCount(2, $item->getParents());
    }

    public function testGetUrl()
    {
        $controller = new UnitedControllerMock();
        $request = new Request();
        $router = new RouterMock();

        // Set no routes
        $item = new UnitedStructureItem('id', 'path', $controller, 'united');
        $this->assertNull($item->getUrl('', $request, $router));

        // Set routes
        $routes = new RouteCollection();
        $route1 = new Route('newroute1');
        $route2 = new Route('newroute2');
        $routes->add('router.newid2', $route2);
        $routes->add('router.newid1', $route1);
        $router->collection = $routes;

        // Set no action
        $item = new UnitedStructureItem('id', 'path', $controller, 'united');
        $item->setRoutes($routes);
        $this->assertEquals('path', $item->getUrl('', $request, $router));

        // Set action
        $item = new UnitedStructureItem('id', 'path', $controller, 'united');
        $item->setRoutes($routes);
        $this->assertNull($item->getUrl('index', $request, $router));
        $router->collection->add('router_index', $route1);
        $this->assertEquals('path', $item->getUrl('', $request, $router));

        // Set route params
        $request->attributes->set(
          '_route_params',
          array(
            'p1' => '1',
            'p2' => '2',
            'p3' => '3'
          )
        );
        $router->collection->add(
          'router.test_params',
          new Route('testparams{p1}{p3}')
        );
        $this->assertEquals(
          'path|1,3',
          $item->getUrl('test_params', $request, $router)
        );

        // Set extra params
        $this->assertEquals(
          'path|5,3,6,7',
          $item->getUrl(
            'test_params',
            $request,
            $router,
            array(
              'p1' => 5,
              'p2' => 6,
              'p4' => 7
            )
          )
        );
    }

}