<?php

namespace United\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use United\CoreBundle\Model\EntityInterface;
use United\CoreBundle\Util\UnitedStructureItem;

abstract class UnitedController extends Controller
{

    /**
     * Checks if united security is enabled and that the current action is accessible.
     *
     * @throws AccessDeniedException
     * @return bool
     */
    protected function checkActionAccess()
    {

        $config = $this->container->getParameter('united.core.config');
        $unitedStructure = $this->get('united.core.structure');

        /**
         * @var UnitedStructureItem $currentItem
         */
        $currentItem = $unitedStructure->getItemFromRequest(
          $this->get('request')
        );

        if (!$currentItem) {
            return true;
        }

        $namespace = $currentItem->getNamespace();

        // If security is not defined or false, we can just return true
        if (!array_key_exists(
            $namespace,
            $config
          ) || !$config[$namespace]['secure']
        ) {
            return true;
        }

        if (false === $this->get('security.authorization_checker')
            ->isGranted('access', $currentItem)
        ) {
            throw new AccessDeniedHttpException(
              'You are not allowed to access this page!'
            );
        }

        return true;
    }

    /**
     * Checks if united security is enabled and that action is allowed on the entity.
     *
     * @throws AccessDeniedException
     * @param string $action
     * @param EntityInterface $entity
     * @param bool $stop
     * @return bool
     */
    protected function checkEntityAccess($action, $entity, $stop = true)
    {

        $config = $this->container->getParameter('united.core.config');
        $unitedStructure = $this->get('united.core.structure');

        /**
         * @var UnitedStructureItem $currentItem
         */
        $currentItem = $unitedStructure->getItemFromRequest(
          $this->get('request')
        );

        if (!$currentItem) {
            return true;
        }

        $namespace = $currentItem->getNamespace();

        // If security is not defined or false, we can just return true
        if (!array_key_exists(
            $namespace,
            $config
          ) || !$config[$namespace]['secure']
        ) {
            return true;
        }

        if (false === $this->get('security.authorization_checker')
            ->isGranted($action, $entity)
        ) {

            // If stop is set, we stop the execution by throwing AccessDeniedException.
            if ($stop) {
                throw new AccessDeniedHttpException(
                  'You are not allowed to '.$action.' the Entity: "'.$entity.'"!'
                );
            } // If stop is set to false, we just return false so that the controller can continue.
            else {
                return false;
            }
        }

        return true;
    }

}