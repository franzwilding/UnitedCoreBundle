<?php

namespace United\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ControllerPreviewInterface
{
    /**
     * Renders the entity with the given form and template, but don't save it
     * to the database.
     *
     * @Route(
     *  "/preview/{action}/{template}", requirements={"action" = "[a-z0-9A-Z-]+", "template" = "[a-z0-9A-Z-]+"}, defaults={"action" = "create", "template" = ""}
     * )
     * @Method({"POST"})
     *
     * @param string $action
     * @param string $template
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function previewAction($action, $template, Request $request);

}