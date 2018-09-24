<?php

namespace United\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

class UnitedControllerCompilerPass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @throws InvalidArgumentException if path is not defined.
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('united.core.structure')) {
            return;
        }

        $definition = $container->getDefinition('united.core.structure');
        $taggedServices = $container->findTaggedServiceIds('united.controller');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {

                // add id attribute
                $attr = array($id);

                // add path attribute
                if (array_key_exists('path', $attributes)) {
                    $attr[] = $attributes['path'];
                    unset($attributes['path']);
                } else {
                    throw new InvalidArgumentException(
                      "You must define an path attribute for: \"$id\"."
                    );
                }

                // add reference (controller) attribute
                $attr[] = new Reference($id);

                // add namespace attribute
                if (array_key_exists('namespace', $attributes)) {
                    $attr[] = $attributes['namespace'];
                    unset($attributes['namespace']);
                } else {
                    $attr[] = null;
                }

                // add parent attribute
                if (array_key_exists('parent', $attributes)) {
                    $attr[] = $attributes['parent'];
                    unset($attributes['parent']);
                } else {
                    $attr[] = null;
                }

                // add parent attribute
                if (array_key_exists('paramName', $attributes)) {
                    $attr[] = $attributes['paramName'];
                    unset($attributes['paramName']);
                } else {
                    $attr[] = null;
                }

                // add all other attributes as config
                $attr[] = $attributes;

                $definition->addMethodCall('add', $attr);
            }
        }
    }
}