<?php

namespace United\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('united_core');
        $rootNode
          ->children()
          ->arrayNode('config')
          ->useAttributeAsKey('namespace')
          ->prototype('array')
          ->children()
          ->scalarNode('theme')->isRequired()->end()
          ->booleanNode('secure')->isRequired()->end()
          ->end()
          ->end()
          ->defaultValue(
            array(
              'united' => array(
                'theme' => '@UnitedOne',
                'secure' => false
              )
            )
          )
          ->end()
          ->end();

        return $treeBuilder;
    }
}
