<?php

namespace United\CoreBundle\Tests\DependencyInjection;

use United\CoreBundle\Tests\Mock\UnitedControllerMock;
use United\CoreBundle\Util\UnitedStructure;
use United\CoreBundle\Tests\Mock\AnnotationLoaderMock;

class UnitedStructureTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var UnitedStructure $structure
     */
    private $structure;

    /**
     * Test the isPathInUse method
     */
    public function testIsPathInUse()
    {

    }

    /**
     * Test the getTree() method
     */
    public function testGettingTree()
    {

        // try to add a not supported class
        $msg = '';
        try {
            $this->structure->add(
              'c1',
              'faa',
              $this
            );
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }
        $this->assertEquals(
          $msg,
          'You must define an controller, that extends United\CoreBundle\Controller\UnitedController.'
        );

        // try to add no class
        $msg = '';
        try {
            $this->structure->add('c1', 'baa', 'fuu');
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }
        $this->assertEquals(
          $msg,
          'You must define an controller, that extends United\CoreBundle\Controller\UnitedController.'
        );

        $controller = new UnitedControllerMock();

        // try to add an valid controller
        $this->structure->add('c1', 'fuu', $controller);
        $this->assertEquals(
          array_keys($this->structure->getTree('united')),
          array('c1')
        );
        $this->assertEquals(
          array_keys($this->structure->getTree('faa')),
          array()
        );
        $item = $this->structure->getTree('united')['c1'];
        $this->assertEquals($item->getPath(), 'fuu');
        $this->assertEquals($item->getController(), $controller);
        $this->assertEquals($item->getConfig(), array());

        // try to add an valid controller with a specific namespace
        $this->structure->add('c2', 'c2url', $controller, 'mynamespace');
        $this->assertEquals(
          array_keys($this->structure->getTree('mynamespace')),
          array('c2')
        );
        $this->assertEquals(
          array_keys($this->structure->getTree('united')),
          array('c1')
        );

        // try to add an entry with the same url for the same namespace for the same level
        $msg = '';
        try {
            $this->structure->add(
              'c3',
              'c2url',
              $controller,
              'mynamespace'
            );
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }
        $this->assertEquals(
          $msg,
          'The url "c2url" is already in use. Please use a different one.'
        );

        // try to add an entry with the same ulr for different namespaces
        $this->structure->add('c4', 'c2url', $controller, 'united');
        $this->assertEquals(
          array_keys($this->structure->getTree('mynamespace')),
          array('c2')
        );
        $this->assertEquals(
          array_keys($this->structure->getTree('united')),
          array(
            'c1',
            'c4'
          )
        );

        // try to add an entry with the same id
        $msg = '';
        try {
            $this->structure->add(
              'c4',
              'c4url',
              $controller,
              'united'
            );
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }
        $this->assertEquals(
          $msg,
          'The id "c4" is already in use. Please use a different one.'
        );

        // try to add an entry with config
        $config = array(
          'faa' => 'baa',
          'fuu' => array(
            'faa' => new \stdClass(),
            'fuu' => array(),
          ),
        );
        $this->structure->add(
          'c5',
          'c5url',
          $controller,
          'united',
          null,
          null,
          $config
        );
        $this->assertEquals(
          array_keys($this->structure->getTree('united')),
          array(
            'c1',
            'c4',
            'c5'
          )
        );
        $item = $this->structure->getTree('united')['c5'];
        $this->assertEquals($item->getConfig(), $config);

        // add child items
        $this->structure->add(
          'd1',
          'd1',
          $controller,
          'united',
          'c5',
          null,
          $config
        );
        $this->structure->add(
          'd2',
          'd2',
          $controller,
          'united',
          'c5',
          null,
          $config
        );
        $this->structure->add(
          'd3',
          'd3',
          $controller,
          'united',
          'c5',
          null,
          $config
        );

        // try to add items with the same path, but for different parents
        $this->structure->add(
          'd11',
          'd1',
          $controller,
          'united',
          'd1',
          null,
          $config
        );
        $this->structure->add(
          'd12',
          'd2',
          $controller,
          'united',
          'd1',
          null,
          $config
        );
        $this->structure->add(
          'd13',
          'd3',
          $controller,
          'united',
          'd1',
          null,
          $config
        );

        // test that only top level items get returned for getTree
        $this->assertEquals(
          array(
            'c1',
            'c4',
            'c5',
          ),
          array_keys($this->structure->getTree())
        );
    }

    /**
     * Creates an container and loads the extension
     */
    protected function setUp()
    {
        $this->structure = new UnitedStructure(new AnnotationLoaderMock());
    }
}