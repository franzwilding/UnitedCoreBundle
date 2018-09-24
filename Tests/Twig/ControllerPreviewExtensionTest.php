<?php

namespace United\CoreBundle\Tests\DependencyInjection;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use United\CoreBundle\Tests\Mock\CRUDControllerMock;
use United\CoreBundle\Tests\Mock\RouterMock;
use United\CoreBundle\Tests\Mock\UnitedControllerMock;
use United\CoreBundle\Twig\ControllerPreviewInterfaceExtension;
use United\CoreBundle\Util\UnitedStructure;
use United\CoreBundle\Tests\Mock\AnnotationLoaderMock;

class ControllerPreviewExtensionTest extends \PHPUnit_Framework_TestCase
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
     * @var ControllerPreviewInterfaceExtension $extension
     */
    private $extension;

    /**
     * Test that getFunctions is returning the right array
     */
    public function testDefinedFunctions()
    {
        $this->assertEquals(
          array(
            new \Twig_SimpleFunction(
              'united_controller_preview', array(
                $this->extension,
                'controllerImplementsPreview'
              )
            ),
          ),
          $this->extension->getFunctions()
        );
    }

    /**
     * Test the isControllerPreview function to check the united structure item controller for preview interface.
     */
    public function testIsControllerPreview()
    {

        // Controller, that does not implement ControllerViewInterface
        $controller = new UnitedControllerMock();
        $id = 'randItem'.time();
        $this->structure->add($id, '/'.$id, $controller);
        $item = $this->structure->getTree()[$id];
        $this->assertFalse($this->extension->controllerImplementsPreview($item));

        // Controller, that does implement ControllerViewInterface
        $controller = new CRUDControllerMock();
        $item->setController($controller);
        $this->assertTrue($this->extension->controllerImplementsPreview($item));
    }

    /**
     * Creates an container and loads the extension.
     */
    protected function setUp()
    {
        $this->loader = new AnnotationLoaderMock();
        $this->structure = new UnitedStructure($this->loader);
        $this->extension = new ControllerPreviewInterfaceExtension();
    }
}