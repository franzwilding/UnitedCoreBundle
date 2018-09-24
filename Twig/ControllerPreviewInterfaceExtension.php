<?php

namespace United\CoreBundle\Twig;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use United\CoreBundle\Controller\ControllerPreviewInterface;
use United\CoreBundle\Util\UnitedStructureItem;

class ControllerPreviewInterfaceExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
          new \Twig_SimpleFunction(
            'united_controller_preview', array(
              $this,
              'controllerImplementsPreview'
            )
          ),
        );
    }

    /**
     * Returns true, if the controller of the structure item implements ControllerPreviewInterface.
     * @param UnitedStructureItem $item
     * @return bool
     */
    public function controllerImplementsPreview($item)
    {
        return ($item->getController() instanceof ControllerPreviewInterface);
    }

    public function getName()
    {
        return 'united_core_controller_preview';
    }
}