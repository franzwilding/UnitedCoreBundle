<?php

namespace United\CoreBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

use United\CoreBundle\DependencyInjection\UnitedControllerCompilerPass;

class UnitedControllerCompilerPassTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test the processing without any controllers
     */
    public function testEmptyProcessControllers()
    {

        // Test processing without any controller
        $this->assertEquals($this->process($this->initContainer()), array());
    }

    /**
     * Testing if different service registrations are getting added to the
     * united structure service.
     */
    public function testProcessControllers()
    {

        // try to add simple definition without path
        $s1 = new Definition();
        $s1->addTag('united.controller');
        $s1->setClass(
          'United\CoreBundle\Tests\Controller\UnitedTestController'
        );
        $c = $this->initContainer(array('c1' => $s1));
        $controllerPass = new UnitedControllerCompilerPass();

        $msg = '';
        try {
            $controllerPass->process($c);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }

        $this->assertEquals(
          $msg,
          'You must define an path attribute for: "c1".'
        );

        // add the path attribute
        $s1->clearTag('united.controller');
        $s1->addTag('united.controller', array('path' => 'c1path'));

        $p = $this->process($c)[0];
        $this->assertEquals($p[0], 'add');
        $this->assertEquals($p[1][0], 'c1');
        $this->assertEquals($p[1][1], 'c1path');
        $this->assertEquals((string) $p[1][2], 'c1');
        $this->assertEquals($p[1][3], null);

        // try to add an namespace
        $s1->clearTag('united.controller');
        $s1->addTag(
          'united.controller',
          array(
            'namespace' => 'fuu',
            'path' => 'c1path'
          )
        );
        $p = $this->process($c)[1];
        $this->assertEquals($p[0], 'add');
        $this->assertEquals($p[1][0], 'c1');
        $this->assertEquals($p[1][1], 'c1path');
        $this->assertEquals((string) $p[1][2], 'c1');
        $this->assertEquals($p[1][3], 'fuu');

        // try to add config
        $config = array(
          'icon' => 'my-icon',
          'title' => 'Faa',
          'test' => array(
            'baa' => 'fuu',
            'fuu' => new \stdClass(),
            'laa' => array(),
          )
        );

        $attr = $config;
        $attr['path'] = 'test';

        $s1->clearTag('united.controller');
        $s1->addTag('united.controller', $attr);
        $p = $this->process($c)[2];
        $this->assertEquals($config, $p[1][6]);

        // try to add config and namespace
        $config = array(
          'icon' => 'my-icon',
          'title' => 'Faa',
          'test' => array(
            'baa' => 'fuu',
            'fuu' => new \stdClass(),
            'laa' => array(),
          )
        );

        $attr = $config;
        $attr['namespace'] = 'fuu';
        $attr['path'] = 'test';

        $s1->clearTag('united.controller');
        $s1->addTag('united.controller', $attr);
        $p = $this->process($c)[3];
        $this->assertEquals($p[0], 'add');
        $this->assertEquals($p[1][0], 'c1');
        $this->assertEquals($p[1][1], 'test');
        $this->assertEquals((string) $p[1][2], 'c1');
        $this->assertEquals($p[1][3], 'fuu');
        $this->assertEquals($p[1][6], $config);
    }

    /**
     * Creates an container and loads the extension
     * @param array $definitions
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private function initContainer($definitions = array())
    {

        $definitions['united.core.structure'] = new Definition();
        $definitions['united.core.structure']->setClass(
          'United\CoreBundle\Util\UnitedStructure'
        );

        $container = new ContainerBuilder();
        $container->addDefinitions($definitions);
        $container->compile();

        return $container;
    }

    /**
     * Process an container and returns all method calls against united.core.structure.
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @return array
     */
    protected function process(ContainerBuilder $container)
    {
        $controllerPass = new UnitedControllerCompilerPass();

        // try to process the container
        $msg = '';
        try {
            $controllerPass->process($container);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }
        $this->assertEquals($msg, '');

        return $container->getDefinition('united.core.structure')
          ->getMethodCalls();
    }
}