<?php

namespace United\CoreBundle\Tests\DependencyInjection;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use United\CoreBundle\Tests\Mock\RouterMock;
use United\CoreBundle\Tests\Mock\UnitedControllerMock;
use United\CoreBundle\Tests\Mock\UnitedControllerViewMock;
use United\CoreBundle\Twig\ControllerViewInterfaceExtension;
use United\CoreBundle\Util\UnitedStructure;
use United\CoreBundle\Tests\Mock\AnnotationLoaderMock;

class ControllerViewExtensionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var AnnotationLoaderMock $loader
     */
    private $loader;

    /**
     * @var UnitedStructure $structure
     */
    private $structure;

    /**
     * @var RequestStack $requestStack
     */
    private $requestStack;

    /**
     * @var ControllerViewInterfaceExtension $extension
     */
    private $extension;

    /**
     * @var RouterMock $router
     */
    private $router;


    /**
     * Test that getFunctions is returning the right array
     */
    public function testDefinedFunctions()
    {
        $this->assertEquals(
          array(
            new \Twig_SimpleFunction(
              'united_controller_view', array(
              $this->extension,
              'controllerImplementsView'
            )
            ),
            new \Twig_SimpleFunction(
              'united_controller_view_entity', array(
              $this->extension,
              'getControllerViewEntity'
            )
            ),
          ),
          $this->extension->getFunctions()
        );
    }

    /**
     * Test the isControllerView function to check the united structure item controller for view interface.
     */
    public function testIsControllerView()
    {

        // Controller, that does not implement ControllerViewInterface
        $controller = new UnitedControllerMock();
        $id = 'randItem'.time();
        $this->structure->add($id, '/'.$id, $controller);
        $item = $this->structure->getTree()[$id];
        $this->assertFalse($this->extension->controllerImplementsView($item));

        // Controller, that does implement ControllerViewInterface
        $controller = new UnitedControllerViewMock();
        $item->setController($controller);
        $this->assertTrue($this->extension->controllerImplementsView($item));
    }

    /**
     * Test the getControllerViewEntity function to get the entity for an united structure item controller.
     */
    public function testGetControllerViewEntity()
    {
        // Controller, that does not implement ControllerViewInterface
        $controller = new UnitedControllerMock();
        $id = 'randItem'.time();
        $this->structure->add($id, '/'.$id, $controller);
        $item = $this->structure->getTree()[$id];
        $this->assertNull($this->extension->getControllerViewEntity($item));

        // Controller implements ControllerViewInterface, but no id is given
        $controller = new UnitedControllerViewMock();
        $item->setController($controller);
        $this->assertNull($this->extension->getControllerViewEntity($item));

        // Controller implements ControllerViewInterface, and routeParam is set
        $eid = time();
        $item->setParamName($id);
        $this->requestStack->getCurrentRequest()->attributes->set(
          '_route_params',
          array($id => $eid)
        );
        $this->assertNotNull($this->extension->getControllerViewEntity($item));
        $this->assertEquals(
          $eid,
          $this->extension->getControllerViewEntity($item)
            ->getId()
        );

        // Controller implements ControllerViewInterface and id is set
        $eid = time() * 2;
        $this->assertNotNull(
          $this->extension->getControllerViewEntity($item, $eid)
        );
        $this->assertEquals(
          $eid,
          $this->extension->getControllerViewEntity($item, $eid)
            ->getId()
        );
    }


    /**
     * Creates an container and loads the extension.
     */
    protected function setUp()
    {
        $this->loader = new AnnotationLoaderMock();
        $this->requestStack = new RequestStack();
        $this->requestStack->push(new Request());
        $this->structure = new UnitedStructure($this->loader);
        $this->router = new RouterMock();
        $container = $this->getMock(
          'Symfony\Component\DependencyInjection\ContainerInterface'
        );
        $this->extension = new ControllerViewInterfaceExtension(
          $this->structure, $this->requestStack, $this->router, $container
        );
    }
}