<?php

namespace United\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use United\CoreBundle\DependencyInjection\UnitedControllerCompilerPass;

class UnitedCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new UnitedControllerCompilerPass());
    }
}
