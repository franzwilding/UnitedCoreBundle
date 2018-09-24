<?php

namespace United\CoreBundle\Tests;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use United\CoreBundle\UnitedCoreBundle;
use United\CoreBundle\DependencyInjection\UnitedControllerCompilerPass;

class UnitedCoreBundleTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var UnitedCoreBundle $coreBundle
     */
    private $coreBundle;

    protected function setUp()
    {
        $this->coreBundle = new UnitedCoreBundle();
    }


    /**
     * @short Tests that the compiler pass was added to the container
     */
    public function testaddCompilerPass()
    {
        $container = new ContainerBuilder();
        $this->coreBundle->build($container);
        $found = 0;
        foreach ($container->getCompiler()
                   ->getPassConfig()
                   ->getPasses() as $pass) {
            if ($pass instanceof UnitedControllerCompilerPass) {
                $found++;
            }
        }

        // check, that UnitedControllerCompilerPass was added exactly once
        $this->assertEquals($found, 1);
    }
}