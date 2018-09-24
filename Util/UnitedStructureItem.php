<?php

namespace United\CoreBundle\Util;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

use United\CoreBundle\Controller\UnitedController;

/**
 * Class UnitedStructureItem
 *
 * Represents one item in an UnitedStructure namespace.
 *
 * @package United\CoreBundle\Util
 */
class UnitedStructureItem
{

    /**
     * @var string
     */
    private static $baseController = 'United\CoreBundle\Controller\UnitedController';

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $path;

    /**
     * @var UnitedController
     */
    private $controller;

    /**
     * @var string
     */
    private $paramName;

    /**
     * @var UnitedStructureItem[]
     */
    private $children;

    /**
     * @var UnitedStructureItem
     */
    private $parent;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var array
     */
    private $config;

    /**
     * @var RouteCollection $routes
     */
    private $routes;

    /**
     * @var string $route
     */
    private $route;

    /**
     * @var string
     */
    private $subRoute;


    /**
     * @param string $id
     * @param string $path
     * @param UnitedController $controller
     * @param string $namespace
     * @param string $paramName
     * @param array $config
     * @param RouteCollection $routes
     */
    public function __construct(
      $id,
      $path,
      $controller,
      $namespace,
      $paramName = null,
      $config = array(),
      $routes = null
    ) {

        $this->children = [];
        $this->paramName = $paramName;
        $this->parent = null;
        $this->route = null;

        if (!$routes) {
            $routes = new RouteCollection();
        }

        $this
          ->setId($id)
          ->setPath($path)
          ->setController($controller)
          ->setNamespace($namespace)
          ->setConfig($config)
          ->setRoutes($routes);
    }

    public function __toString()
    {
        return $this->getId();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return UnitedStructureItem
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return UnitedStructureItem
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return UnitedController
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param UnitedController $controller
     * @return UnitedStructureItem
     *
     * @throws InvalidArgumentException if $controller does not extends the
     * baseController.
     */
    public function setController($controller)
    {

        $baseController = UnitedStructureItem::$baseController;

        if (!is_subclass_of($controller, $baseController)) {
            throw new InvalidArgumentException(
              "You must define an controller, that extends $baseController."
            );
        }

        $this->controller = $controller;

        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     * @return UnitedStructureItem
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return string
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param string $config
     * @return UnitedStructureItem
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return RouteCollection
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param RouteCollection $routes
     * @return UnitedStructureItem
     */
    public function setRoutes($routes)
    {
        $this->routes = $routes;

        return $this;
    }

    /**
     * Set cached route.
     *
     * @param string $route
     * @return UnitedStructureItem
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Returns the shortest route name of all routes in the RouteCollection or null.
     * @return null|string
     */
    public function getRoute()
    {

        if ($this->route) {
            return $this->route;
        }

        $firstRoutePath = null;
        $firstRouteName = null;

        if ($this->routes->count() > 0) {
            foreach ($this->routes->all() as $name => $route) {
                if ($firstRouteName == null || $firstRoutePath > $route->getPath(
                  )
                ) {
                    $firstRoutePath = $route->getPath();
                    $firstRouteName = $name;
                }
            }
        }

        $this->route = $firstRouteName;

        return $firstRouteName;
    }

    /**
     * Returns the base route of the current controller
     */
    public function getCurrentBaseRoute()
    {
        $route_parts = explode('.', $this->getRoute());
        array_pop($route_parts);

        return join('.', $route_parts);
    }

    /**
     * @param string $action
     * @param Request $request
     * @param Router $router
     * @param array $extra_params
     * @return mixed
     */
    public function getUrl(
      $action = '',
      $request,
      $router,
      $extra_params = array()
    ) {
        // Get the first action if no action is passed.
        if ($action) {
            $route_name = $this->getCurrentBaseRoute().'.'.$action;
        } else {
            $route_name = $this->getRoute();
        }

        // Get params from request.
        if (!$params = $request->attributes->get('_route_params')) {
            $params = array();
        }

        // Only use the params, that are defined for the route.
        $route = $router->getRouteCollection()->get($route_name);

        if (!$route) {
            return null;
        }

        $route_variables = $route->compile()->getVariables();
        foreach ($params as $key => $value) {
            if (!in_array($key, $route_variables)) {
                unset($params[$key]);
            }
        }

        // Merge auto generated parameters with extra parameters.
        $params = array_merge($params, $extra_params);

        // Return the generated url.
        return $router->generate($route_name, $params);
    }

    /**
     * @return null|string
     */
    public function getSubRoute()
    {
        return $this->subRoute;
    }

    /**
     * @return null|string
     */
    public function getSubRouteName()
    {
        return $this->subRoute;
    }

    public function getAction()
    {
        $parts = explode('.', $this->subRoute);
        return array_pop($parts);
    }

    /**
     * @param string $subRoute
     * @return UnitedStructureItem
     */
    public function setSubRoute($subRoute)
    {
        $this->subRoute = $subRoute;

        return $this;
    }

    /**
     * @return UnitedStructureItem[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param string $paramName
     * @return UnitedStructureItem
     */
    public function setParamName($paramName)
    {
        $this->paramName = $paramName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getParamName()
    {
        return $this->paramName;
    }

    /**
     * @param UnitedStructureItem $parent
     * @return UnitedStructureItem
     */
    public function setParent(UnitedStructureItem $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return null|UnitedStructureItem
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return UnitedStructureItem[]
     */
    public function getParents()
    {
        $parents = array();

        if ($this->getParent()) {
            $parent = $this->getParent();
            $parents[] = $parent;
            $parents = array_merge($parents, $parent->getParents());
        }

        return $parents;
    }

    /**
     * @param UnitedStructureItem $child
     * @return UnitedStructureItem
     */
    public function addChild(UnitedStructureItem $child)
    {
        $child->setParent($this);
        $this->children[$child->getId()] = $child;

        return $this;
    }

    /**
     * Returns true if this item or any parent item is active
     * @return bool
     */
    public function activeTrail()
    {
        if ($this->active()) {
            return true;
        }

        foreach ($this->getChildren() as $child) {
            if ($child->activeTrail()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if this item is active
     * @return bool
     */
    public function active()
    {
        return $this->getSubRoute() !== null;
    }
}