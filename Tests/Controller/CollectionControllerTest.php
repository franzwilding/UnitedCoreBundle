<?php

namespace United\CoreBundle\Tests\Controller;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Scope;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use United\CoreBundle\Tests\Mock\AnnotationLoaderMock;
use United\CoreBundle\Tests\Mock\CollectionControllerMock;
use United\CoreBundle\Tests\Mock\EntityMock;
use United\CoreBundle\Tests\Mock\EntityRepositoryMock;
use United\CoreBundle\Tests\Mock\RouterMock;
use United\CoreBundle\UnitedCoreBundle;
use United\CoreBundle\Util\UnitedStructure;

class CollectionControllerTest extends UnitedControllerTestCase
{

    /**
     * @var EntityRepositoryMock $repo
     */
    protected $repo;

    protected function setUp()
    {
        $this->controller = new CollectionControllerMock();
        $this->repo = new EntityRepositoryMock();
        $this->controller->mock_repository = $this->repo;
        parent::setUp();
    }

    /**
     * Calling indexAction on CollectionController should ether renders the index or redirect to first entity.
     */
    public function testIndexAction()
    {

        // Check index template rendering
        $this->controller->mock_template = "UnitedCoreBundle:Tests:DumpContext.html.twig";
        $context = json_decode($this->getActionContent('index'), true);
        $this->assertArrayHasKey('app', $context);

        // add two entities to the repo, now we should get redirected
        $this->repo->data[0] = new EntityMock();
        $this->repo->data[0]->setId(25);
        $this->repo->data[0]->setTitle('Test Entity 25.');
        $this->repo->data[1] = new EntityMock();
        $this->repo->data[1]->setId(654);
        $this->repo->data[1]->setTitle('Test Entity 654.');

        $this->container->set('router', new RouterMock());

        $this->injectStructureItem('united', null, null, array());

        $response = $this->getControllerActionResponse('index');
        $this->assertEquals(302, $response->getStatusCode());

    }

    /**
     * Calling viewAction on CollectionController should render the entity and the list of entities.
     */
    public function testViewAction()
    {

        // add two entities to the repo
        $this->repo->data[0] = new EntityMock();
        $this->repo->data[0]->setId(25);
        $this->repo->data[0]->setTitle('Test Entity 25.');
        $this->repo->data[1] = new EntityMock();
        $this->repo->data[1]->setId(654);
        $this->repo->data[1]->setTitle('Test Entity 654.');

        // Check index template rendering
        $content = $this->getActionContent('view', array(25));
        $this->assertEquals('ENTITY_ID:25, ENTITIES: 25|654|', $content);
    }

    /**
     * Check, that all routes are getting generated correctly.
     */
    public function testRoutes()
    {
        // Check controller routes
        $this->checkControllerRoutes(
          array(

              // index route
            $this->getClassPrefix().'.index' => array(
              'path' => '/United_CoreBundle_Tests_Controller_CollectionControllerTest/',
              'defaults' => array(
                '_controller' => 'United\CoreBundle\Tests\Mock\CollectionControllerMock::indexAction',
              ),
            ),
              // view route
            $this->getClassPrefix().'.view' => array(
              'path' => '/United_CoreBundle_Tests_Controller_CollectionControllerTest/{id}',
              'defaults' => array(
                '_controller' => 'United\CoreBundle\Tests\Mock\CollectionControllerMock::viewAction',
              ),
            ),
              // create route
            $this->getClassPrefix().'.create' => array(
              'path' => '/United_CoreBundle_Tests_Controller_CollectionControllerTest/create',
              'defaults' => array(
                '_controller' => 'United\CoreBundle\Tests\Mock\CollectionControllerMock::createAction',
              ),
            ),
              // update route
            $this->getClassPrefix().'.update' => array(
              'path' => '/United_CoreBundle_Tests_Controller_CollectionControllerTest/{id}/update',
              'defaults' => array(
                '_controller' => 'United\CoreBundle\Tests\Mock\CollectionControllerMock::updateAction',
              ),
            ),
              // delete route
            $this->getClassPrefix().'.delete' => array(
              'path' => '/United_CoreBundle_Tests_Controller_CollectionControllerTest/{id}/delete',
              'defaults' => array(
                '_controller' => 'United\CoreBundle\Tests\Mock\CollectionControllerMock::deleteAction',
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

    /**
     * Check that access on view action is checked by the controller, if we set the united config secure flag true.
     */
    public function testViewActionAccess()
    {

        // Enable security
        $this->enableStructureSecurity();

        // Inject an structure item
        $id = $this->injectStructureItem();

        $this->repo->data[0] = new EntityMock();
        $this->repo->data[0]->setId(25);
        $this->repo->data[0]->setTitle('Test Entity 25.');
        $this->repo->data[1] = new EntityMock();
        $this->repo->data[1]->setId(654);
        $this->repo->data[1]->setTitle('Test Entity 654.');

        // Accessing index without granting should throw an access denied exception.
        $this->setAuthCheckerGrant(array());
        $this->checkAccessDeniedException(
          'view',
          true,
          array(
            25,
            $this->container->get('request')
          )
        );
        $this->checkAuthChecker(0, $id, 'access');

        // When we grant the access, we should still get an exception, because we need the view grant for the entity
        $this->setAuthCheckerGrant(array('access'));
        $this->checkAccessDeniedException(
          'view',
          true,
          array(
            25,
            $this->container->get('request')
          )
        );
        $this->checkAuthChecker(0, $id, 'access');
        $this->checkAuthChecker(1, 25, 'view');

        // When we also grant view on entities, we now should not get any exceptions.
        $this->setAuthCheckerGrant(array('access', 'view'));
        $this->checkAccessDeniedException(
          'view',
          false,
          array(
            25,
            $this->container->get('request')
          )
        );
        $this->checkAuthChecker(0, $id, 'access');

        // Check, that the entity and the list of entities get checked.
        $this->checkAuthChecker(1, 25, 'view');
        $this->checkAuthChecker(2, 25, 'view');
        $this->checkAuthChecker(3, 654, 'view');

    }
}