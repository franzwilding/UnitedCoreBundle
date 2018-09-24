<?php

namespace United\CoreBundle\Util;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\AnnotationClassLoader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

use United\CoreBundle\Controller\UnitedController;

/**
 * Class UnitedStructure
 *
 * Use this service to get the controller structure of an united instance.
 *
 * @package United\CoreBundle\Util
 */
class UnitedStructure
{

    /**
     * @var string
     */
    private static $baseController = 'United\CoreBundle\Controller\UnitedController';

    /**
     * @var array $controllers
     */
    private $controllers = array();

    private $cachedRouteItems = array();

    /**
     * @var AnnotationClassLoader $loader
     */
    private $loader;

    /**
     * @param AnnotationClassLoader $loader
     */
    public function __construct(AnnotationClassLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Adds an controller to the structure.
     *
     * @param string $id
     * @param string $path
     * @param UnitedController $controller
     * @param string $namespace
     * @param string $parent
     * @param string $paramName
     * @param array $config
     *
     * @throws InvalidArgumentException if $path is already in use for the namespace
     * and level.
     *
     * @throws InvalidArgumentException if $id is already in use.
     */
    public function add(
      $id,
      $path,
      $controller,
      $namespace = null,
      $parent = null,
      $paramName = null,
      $config = array()
    ) {
        if (!$namespace) {
            $namespace = 'united';
        }

        if (!array_key_exists($namespace, $this->controllers)) {
            $this->controllers[$namespace] = array();
        }

        // Ff the item has a parent set, check, that the parent is defined.
        if ($parent && !array_key_exists(
            $parent,
            $this->controllers[$namespace]
          )
        ) {
            throw new InvalidArgumentException(
              "No parent with id \"$parent\" found."
            );
        }

        // If we have a parent, we just need to check against child urls of this parent.
        if ($parent) {

            /**
             * @var UnitedStructureItem $parentController
             */
            $parentController = $this->controllers[$namespace][$parent];
            $parentUrl = $parentController->getPath();

            if ($this->isPathInUse($path, $parentController->getChildren())) {
                throw new InvalidArgumentException(
                  "The url \"$path\" under the parent url \"$parentUrl\" is already in use. Please use a different one."
                );
            }

        } else {
            if ($this->isPathInUse($path, $this->controllers[$namespace])) {
                throw new InvalidArgumentException(
                  "The url \"$path\" is already in use. Please use a different one."
                );
            }
        }

        if (array_key_exists($id, $this->controllers[$namespace])) {
            throw new InvalidArgumentException(
              "The id \"$id\" is already in use. Please use a different one."
            );
        }

        $baseController = $this::$baseController;
        if (!is_object($controller) || !is_subclass_of(
            $controller,
            $baseController
          )
        ) {
            throw new InvalidArgumentException(
              "You must define an controller, that extends $baseController."
            );
        }

        // load routes for the controller
        $routes = $this->loader->load(get_class($controller));
        $routes->addPrefix($path);

        // add id of this item to the route name
        $newRoutes = new RouteCollection();

        foreach ($routes->all() as $name => $route) {
            $routeNameParts = explode('_', $name);
            $newRoutes->add($id.'.'.array_pop($routeNameParts), $route);
        }

        // Create UnitedStructureItem
        $this->controllers[$namespace][$id] = new UnitedStructureItem(
          $id,
          $path,
          $controller,
          $namespace,
          $paramName,
          $config,
          $newRoutes
        );

        // If we have an parent set, we need to add ourself as child of the parent
        if ($parent) {

            /**
             * @var UnitedStructureItem $parentController
             */
            $parentController = $this->controllers[$namespace][$parent];
            $parentController->addChild($this->controllers[$namespace][$id]);
        }
    }

    /**
     * @param string $path
     * @param UnitedStructureItem[] $items
     * @return bool
     */
    private function isPathInUse($path, $items)
    {
        foreach ($items as $item) {
            if ($item->getPath() == $path) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the tree structure for a namespace.
     *
     * @param $namespace
     * @return UnitedStructureItem[]
     */
    public function getTree($namespace = null)
    {

        if (!$namespace) {
            $namespace = 'united';
        }

        if (!array_key_exists($namespace, $this->controllers)) {
            return array();
        }

        return $this->getTopLevelItems($this->controllers[$namespace]);
    }

    /**
     * Returns the tree structure for a given request.
     *
     * @param Request $request
     * @return UnitedStructureItem[]
     */
    public function getTreeFromRequest(Request $request)
    {
        $route = $request->attributes->get('_route');

        foreach ($this->controllers as $namespace => $items) {

            /**
             * @var UnitedStructureItem $item
             */
            foreach ($items as $item) {
                if (array_key_exists($route, $item->getRoutes()->all())) {
                    $item->setSubRoute($route);

                    return $this->getTopLevelItems($items);
                }
            }
        }

        return array();
    }

    /**
     * @param UnitedStructureItem[] $items
     * @return UnitedStructureItem[]
     */
    private function getTopLevelItems($items)
    {
        $controllers = array();
        foreach ($items as $id => $controller) {
            if (!$controller->getParent()) {
                $controllers[$id] = $controller;
            }
        }

        return $controllers;
    }

    /**
     * Returns the one structure item for a given request.
     *
     * @param Request $request
     * @return UnitedStructureItem|null
     */
    public function getItemFromRequest(Request $request)
    {
        $route = $request->attributes->get('_route');


        if (array_key_exists($route, $this->cachedRouteItems)) {
            return $this->cachedRouteItems[$route];
        }

        foreach ($this->controllers as $namespace => $items) {

            /**
             * @var UnitedStructureItem $item
             */
            foreach ($items as $item) {


                if (array_key_exists($route, $item->getRoutes()->all())) {
                    $item->setSubRoute($route);
                    $this->cachedRouteItems[$route] = $item;

                    return $item;
                }
            }
        }

        return null;
    }

}