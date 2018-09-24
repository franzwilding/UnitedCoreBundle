<?php

namespace United\CoreBundle\Tests\Twig;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Router;
use United\CoreBundle\Tests\Mock\AnnotationLoaderMock;
use United\CoreBundle\Tests\Mock\RouterMock;
use United\CoreBundle\Twig\UnitedExtension;
use United\CoreBundle\Util\United;
use United\CoreBundle\Util\UnitedStructure;

class UnitedExtensionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var AnnotationLoaderMock $loader
     */
    private $loader;

    /**
     * @var United $united
     */
    private $united;

    /**
     * @var UnitedStructure $structure
     */
    private $structure;

    /**
     * @var RequestStack $requestStack
     */
    private $requestStack;

    /**
     * @var Router $router
     */
    private $router;

    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * @var UnitedExtension $extension
     */
    private $extension;

    /**
     * Test that getGlobals is returning a United object
     */
    public function testDefined()
    {
        $globals = $this->extension->getGlobals();

        $this->assertArrayHasKey('united', $globals);
        $this->assertEquals($this->united, $globals['united']);
    }

    /**
     * Creates an container and loads the extension
     */
    protected function setUp()
    {
        $this->loader = new AnnotationLoaderMock();
        $this->requestStack = new RequestStack();
        $this->requestStack->push(new Request());
        $this->structure = new UnitedStructure($this->loader);
        $this->router = new RouterMock();
        $this->container = $this->getMock(
          'Symfony\Component\DependencyInjection\ContainerInterface'
        );
        $this->united = new United(
          $this->structure,
          $this->requestStack,
          array(),
          $this->router
        );
        $this->extension = new UnitedExtension($this->united);
    }
}