<?php

namespace United\CoreBundle\Twig;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use United\CoreBundle\Controller\ControllerViewInterface;
use United\CoreBundle\Model\EntityInterface;
use United\CoreBundle\Util\UnitedStructure;
use United\CoreBundle\Util\UnitedStructureItem;

class ControllerViewInterfaceExtension extends \Twig_Extension
{

    /**
     * @var UnitedStructure $structure
     */
    private $structure;

    /**
     * @var RequestStack $requestStack
     */
    private $requestStack;

    /**
     * @var Router $router
     */
    private $router;

    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * @param UnitedStructure $structure
     * @param RequestStack $requestStack
     * @param Router $router
     * @param ContainerInterface $container
     */
    public function __construct(
      UnitedStructure $structure,
      RequestStack $requestStack,
      Router $router,
      ContainerInterface $container
    ) {
        $this->structure = $structure;
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
          new \Twig_SimpleFunction(
            'united_controller_view', array(
            $this,
            'controllerImplementsView'
          )
          ),
          new \Twig_SimpleFunction(
            'united_controller_view_entity', array(
            $this,
            'getControllerViewEntity'
          )
          ),
        );
    }

    /**
     * Returns true, if the controller of the structure item implements ControllerViewInterface.
     * @param UnitedStructureItem $item
     * @return bool
     */
    public function controllerImplementsView($item)
    {
        return ($item->getController() instanceof ControllerViewInterface);
    }

    /**
     * @param UnitedStructureItem $item
     * @param int $id
     * @return EntityInterface|null
     */
    public function getControllerViewEntity($item, $id = -1)
    {

        if (!$this->controllerImplementsView($item)) {
            return null;
        }

        // If id was not overridden and item has a paramName, let's try to get the
        // id from request params.
        if ($id < 0 && $item->getParamName()) {
            if (!$params = $this->requestStack->getCurrentRequest(
            )->attributes->get('_route_params')
            ) {
                $params = array();
            }

            if ($params[$item->getParamName()]) {
                $id = $params[$item->getParamName()];
            }
        }

        // If we have no id, we can't load the entity.
        if ($id < 0) {
            return null;
        }

        /**
         * @var Controller|ControllerViewInterface $controller
         */
        $controller = $item->getController();
        $controller->setContainer($this->container);

        try {
            return $controller->viewEntity($id);
        } catch (NotFoundHttpException $e) {
            return null;
        }
    }

    public function getName()
    {
        return 'united_core_controller_view';
    }
}