<?php

namespace United\CoreBundle\Tests\DependencyInjection;

use AppKernel;
use appTestDebugProjectContainer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UnitedCoreExtensionTest extends KernelTestCase
{

    /**
     * @var appTestDebugProjectContainer $container
     */
    private $container;

    /**
     * Creates an container and loads the extension
     */
    protected function setUp()
    {
        self::bootKernel();
        $this->container = static::$kernel->getContainer();
    }

    /**
     * Tests if all services, defined in services.yml are available.
     */
    public function testServices()
    {
        $services = array(
          'service_container',
          'united.core.router',
          'united.core.structure',
        );

        foreach ($services as $service) {
            $this->assertTrue($this->container->has($service));
        }
    }
}