<?php

namespace United\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use United\CoreBundle\Model\EntityInterface;

interface ControllerViewInterface
{

    /**
     * @param int $id
     * @return EntityInterface
     */
    public function viewEntity($id);

    /**
     * Renders the items of one collection.
     *
     * @Route("/{id}", requirements={"id" = "\d+"})
     * @Method({"GET"})
     *
     * @param int $id
     * @return Response
     */
    public function viewAction($id);

}