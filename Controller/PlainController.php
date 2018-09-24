<?php

namespace United\CoreBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class PlainController
 * Defines an index action and renders a template.
 * @package United\CoreBundle\Controller
 */
abstract class PlainController extends UnitedController
{

    /**
     * Returns the template for the given action. For the base implementation,
     * $action can be: index|create|update|delete. The base implementation will check for a method named
     * "getTemplateFor$actionAction() and returns its result.
     *
     * @param string $action the action to get the twig template for
     * @return string, the twig template to render
     */
    protected function getTemplateForAction($action)
    {
        $method = 'templateFor'.ucfirst($action).'Action';

        return $this->$method();
    }

    /**
     * Returns the template for the index action.
     *
     * @return string, the twig template to render
     */
    abstract protected function templateForIndexAction();

    /**
     * This method can alter the context for each action, that is passed to the
     * twig template.
     *
     * @param string $action
     * @param array $context
     * @return array
     */
    protected function alterContextForAction($action, &$context)
    {
    }

    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $this->checkActionAccess(); // Check if we can access this action

        $context = array();
        $this->alterContextForAction('index', $context);

        return $this->render($this->getTemplateForAction('index'), $context);
    }
}