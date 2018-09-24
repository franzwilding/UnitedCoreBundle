<?php

namespace United\CoreBundle\Util;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Router;

/**
 * Class United. You can get all united config, information and objects from this service class.
 * @package United\CoreBundle\Util
 */
class United
{

    /**
     * @var UnitedStructure $unitedStructure
     */
    private $unitedStructure;

    /**
     * @var RequestStack $requestStack
     */
    private $requestStack;

    /**
     * @var array $config
     */
    private $config;

    /**
     * @var Router $router
     */
    private $router;

    /**
     * @param UnitedStructure $unitedStructure
     * @param RequestStack $requestStack
     * @param $config
     * @param Router $router
     */
    public function __construct(
      UnitedStructure $unitedStructure,
      RequestStack $requestStack,
      $config,
      Router $router
    ) {
        $this->unitedStructure = $unitedStructure;
        $this->requestStack = $requestStack;
        $this->config = $config;
        $this->router = $router;
    }

    /**
     * Returns the current UnitedStructureItem for the current namespace by checking the request route.
     *
     * @return UnitedStructureItem
     */
    public function getItem()
    {
        return $this->unitedStructure->getItemFromRequest(
          $this->requestStack->getCurrentRequest()
        );
    }

    /**
     * Returns all UnitedStructureItems with parent==null for the current namespace.
     *
     * @return UnitedStructureItem[]
     */
    public function getTree()
    {
        return $this->unitedStructure->getTreeFromRequest(
          $this->requestStack->getCurrentRequest()
        );
    }

    /**
     * Generates a url for a given structure item and action.
     *
     * @param UnitedStructureItem $item
     * @param string $action
     * @param array $extra_params
     * @return string
     */
    public function getUrl($action = '', $item = null, $extra_params = array())
    {

        // Get the current item, if no item is passed.
        if (!$item) {
            $item = $this->getItem();
        }

        // Generate url
        return $item->getUrl(
          $action,
          $this->requestStack->getCurrentRequest(),
          $this->router,
          $extra_params
        );
    }

    /**
     * Returns the config for the current namespace.
     *
     * @return array
     */
    public function getConfig()
    {
        if (!$item = $this->getItem()) {
            return array();
        }

        if (!array_key_exists($item->getNamespace(), $this->config)) {
            return array();
        }

        return $this->config[$item->getNamespace()];
    }

    /**
     * Returns the path for current united theme.
     * @param string $path , an postfix for the path
     * @return null|string
     */
    public function getTheme($path = null)
    {
        if (!array_key_exists('theme', $config = $this->getConfig())) {
            return null;
        }

        if ($path && is_string($path)) {
            $path = rtrim($path, '/');

            return $config['theme'].'/'.$path;
        }

        return $config['theme'];
    }

}