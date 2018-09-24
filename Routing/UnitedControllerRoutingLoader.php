<?php
/**
 * @file TwoRobotsUnitedContentRouter is a generic router for domain content.
 * When defining content collections, you can pass a route file that will
 * processed by this router.
 */

namespace United\CoreBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

use United\CoreBundle\Util\UnitedStructure;
use United\CoreBundle\Util\UnitedStructureItem;

class UnitedControllerRoutingLoader extends Loader
{

    /**
     * @var UnitedStructure $structure
     */
    private $structure;

    /**
     * @param \United\CoreBundle\Util\UnitedStructure $structure
     */
    public function __construct(UnitedStructure $structure)
    {
        $this->structure = $structure;
    }

    /**
     * Loads a resource.
     *
     * @param mixed $resource The resource
     * @param string|null $type The resource type or null if unknown
     * @return RouteCollection
     *
     * @throws \Exception If something went wrong
     */
    public function load($resource, $type = null)
    {

        $parts = explode('.', $type);
        $namespace = null;

        if (count($parts) > 1) {
            $tail = $parts;
            array_shift($tail);
            $namespace = join('.', $tail);
        }

        // get structure
        $tree = $this->structure->getTree($namespace);

        $routes = $this->loadUnitedStructureItems($tree, $type);

        // add a redirection route to the first resource
        $route_names = array_keys($routes->all());
        if (count($route_names) > 0) {

            $redirect = new Route(
              '/', array(
              '_controller' => 'FrameworkBundle:Redirect:redirect',
              'route' => array_shift($route_names),
              'permanent' => true,
            )
            );

            $routes->add($type, $redirect);
        }

        return $routes;
    }

    /**
     * Loads all united structure items from an nested tree.
     *
     * @param UnitedStructureItem[] $items
     * @param string $type
     * @return RouteCollection
     */
    private function loadUnitedStructureItems($items, $type)
    {

        $routes = new RouteCollection();

        // Add all items from this level
        if (count($items) > 0) {

            // add all resources
            foreach ($items as $key => $item) {

                $item_routes = clone $item->getRoutes();

                // if we have children, let's add the child routes as well
                if (count($item->getChildren()) > 0) {

                    $sub_routes = $this->loadUnitedStructureItems(
                      $item->getChildren(),
                      $type
                    );

                    if ($item->getParamName()) {
                        $sub_routes->addPrefix(
                          $item->getPath().'/{'.$item->getParamName().'}'
                        );
                    } else {
                        $sub_routes->addPrefix($item->getPath());
                    }

                    $item_routes->addCollection($sub_routes);
                }

                $routes->addCollection($item_routes);
            }
        }

        return $routes;
    }

    /**
     * Returns whether this class supports the given resource.
     * Type must be prefixed by "united." e.g.: united.admin or just "united".
     *
     * @param mixed $resource A resource
     * @param string|null $type The resource type or null if unknown
     *
     * @return bool True if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        $parts = explode('.', $type);

        return count($parts) > 0 && $parts[0] === 'united';
    }
}