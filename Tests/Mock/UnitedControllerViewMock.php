<?php

namespace United\CoreBundle\Tests\Mock;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Response;
use United\CoreBundle\Controller\ControllerViewInterface;
use United\CoreBundle\Controller\UnitedController;
use United\CoreBundle\Model\EntityInterface;

/**
 * Class UnitedControllerViewMock
 * Only used by the tests.
 *
 * @package United\CoreBundle\Tests\Mock
 */
class UnitedControllerViewMock extends UnitedController implements ControllerViewInterface
{

    /**
     * @Route("/")
     */
    public function indexAction()
    {
    }

    /**
     * @param int $id
     * @return EntityInterface
     */
    public function viewEntity($id)
    {
        $entity = new EntityMock();
        $entity->setId($id);

        return $entity;
    }

    /**
     * Renders the items of one collection.
     *
     * @Route("/{id}", requirements={"id" = "\d+"})
     * @Method({"GET"})
     *
     * @param int $id
     * @return Response
     */
    public function viewAction($id)
    {
        return null;
    }
}