<?php

namespace United\CoreBundle\Tests\Mock;

use United\CoreBundle\Controller\PlainController;

class PlainControllerMock extends PlainController
{

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
        if ($action == 'index') {
            $context['faa'] = 'fuu';
        } else {
            $context['faa'] = 'faa';
        }
    }

    protected function templateForIndexAction()
    {
        return "UnitedCoreBundle:Tests:DumpContext.html.twig";
    }
}