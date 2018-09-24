<?php

namespace United\CoreBundle\Tests\Controller;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Scope;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use United\CoreBundle\Tests\Mock\AnnotationLoaderMock;
use United\CoreBundle\Tests\Mock\PlainControllerMock;
use United\CoreBundle\UnitedCoreBundle;
use United\CoreBundle\Util\UnitedStructure;

class PlainControllerTest extends UnitedControllerTestCase
{

    protected function setUp()
    {
        $this->controller = new PlainControllerMock();
        parent::setUp();
    }

    /**
     * Calling indexAction on PlainController should render the template, set
     * by getTemplateForAction('index').
     */
    public function testIndexAction()
    {

        // Test sending emptyrequest
        $request = new Request();
        $this->container->enterScope('request');
        $this->container->set('request', $request, 'request');

        // Check index template rendering
        $context = json_decode($this->getActionContent('index'), true);
        $this->assertArrayHasKey('app', $context);

        // Check controller routes
        $this->checkControllerRoutes(
          array(

              // index route
            $this->getClassPrefix().'.index' => array(
              'path' => '/United_CoreBundle_Tests_Controller_PlainControllerTest/',
              'defaults' => array(
                '_controller' => 'United\CoreBundle\Tests\Mock\PlainControllerMock::indexAction',
              ),
            ),
              // root redirect route
            'united.'.$this->getClassPrefix() => array(
              'path' => '/',
              'defaults' => array(
                '_controller' => 'Symfony\Bundle\FrameworkBundle\Controller\RedirectController::redirectAction',
                'route' => $this->getClassPrefix().'.index',
                'permanent' => true,
              ),
            ),
          )
        );
    }

    /**
     * Check that access on index action is checked by the controller, if we set the united config secure flag true.
     */
    public function testIndexActionAccess()
    {

        // Enable security
        $this->enableStructureSecurity();

        // Inject an structure item
        $id = $this->injectStructureItem();

        // Accessing index without granting should throw an access denied exception.
        $this->setAuthCheckerGrant(array());
        $this->checkAccessDeniedException('index', true);
        $this->checkAuthChecker(0, $id, 'access');

        // When we grant the access, we should not get any exceptions
        $this->setAuthCheckerGrant(array('access'));
        $this->checkAccessDeniedException('index', false);
        $this->checkAuthChecker(0, $id, 'access');
    }
}