<?php

namespace United\CoreBundle\Tests\Mock;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use United\CoreBundle\Controller\UnitedController;

/**
 * Class UnitedControllerMock
 * Only used by the tests.
 *
 * @package United\CoreBundle\Tests\Mock
 */
class UnitedControllerMock extends UnitedController
{

    /**
     * @Route("/")
     */
    public function indexAction()
    {
    }

}