<?php

namespace United\CoreBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Request;
use United\CoreBundle\Tests\Mock\CRUDControllerMock;
use United\CoreBundle\Tests\Mock\EntityMock;
use United\CoreBundle\Tests\Mock\EntityRepositoryMock;
use Symfony\Component\DomCrawler\Crawler;

class CRUDControllerTest extends UnitedControllerTestCase
{

    /**
     * @var EntityRepositoryMock $repo
     */
    protected $repo;

    protected function setUp()
    {
        $this->controller = new CRUDControllerMock();
        $this->repo = new EntityRepositoryMock();
        $this->controller->mock_repository = $this->repo;
        parent::setUp();
    }

    /**
     * Check generation of all routes.
     */
    public function testRoutesAction()
    {

        // Check controller routes
        $this->checkControllerRoutes(
          array(

              // index route
            $this->getClassPrefix().'.index' => array(
              'path' => '/United_CoreBundle_Tests_Controller_CRUDControllerTest/',
              'defaults' => array(
                '_controller' => 'United\CoreBundle\Tests\Mock\CRUDControllerMock::indexAction',
              ),
            ),
              // create route
            $this->getClassPrefix().'.create' => array(
              'path' => '/United_CoreBundle_Tests_Controller_CRUDControllerTest/create',
              'defaults' => array(
                '_controller' => 'United\CoreBundle\Tests\Mock\CRUDControllerMock::createAction',
              ),
            ),
              // update route
            $this->getClassPrefix().'.update' => array(
              'path' => '/United_CoreBundle_Tests_Controller_CRUDControllerTest/{id}/update',
              'defaults' => array(
                '_controller' => 'United\CoreBundle\Tests\Mock\CRUDControllerMock::updateAction',
              ),
            ),
              // delete route
            $this->getClassPrefix().'.delete' => array(
              'path' => '/United_CoreBundle_Tests_Controller_CRUDControllerTest/{id}/delete',
              'defaults' => array(
                '_controller' => 'United\CoreBundle\Tests\Mock\CRUDControllerMock::deleteAction',
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
     * Test indexAction response.
     */
    public function testIndexResponse()
    {

        // Test sending emptyrequest
        $request = new Request();
        $this->container->enterScope('request');
        $this->container->set('request', $request, 'request');

        $v = 'fuu'.time();
        $this->controller->mock_context = array('faa' => $v);
        $this->controller->mock_template = 'UnitedCoreBundle:Tests:DumpContext.html.twig';

        // test processing PlainIndex
        $context = json_decode($this->getActionContent('index'), true);
        $this->assertArrayHasKey('entities', $context);
        $this->assertArrayHasKey('faa', $context);
        $this->assertEquals($v, $context['faa']);

        // test processing 0 entities
        $this->controller->mock_template = 'UnitedCoreBundle:Tests:ListEntities.html.twig';
        $this->assertEquals('<ul></ul>', $this->getActionContent('index'));

        // test processing 2 entities
        $this->repo->data[0] = new EntityMock();
        $this->repo->data[0]->setId(25);
        $this->repo->data[0]->setTitle('Test Entity 25.');
        $this->repo->data[1] = new EntityMock();
        $this->repo->data[1]->setId(654);
        $this->repo->data[1]->setTitle('Test Entity 654.');

        $this->assertEquals(
          '<ul><li>25: Test Entity 25.</li><li>654: Test Entity 654.</li></ul>',
          $this->getActionContent('index')
        );
    }

    public function testCreateResponse()
    {

        // Set template to form
        $this->controller->mock_template = 'UnitedCoreBundle:Tests:Form.html.twig';

        // Test sending emptyrequest
        $request = new Request();
        $this->container->enterScope('request');
        $this->container->set('request', $request, 'request');

        $crawler = new Crawler(
          $this->getActionContent('create', array($request)),
          'http://example.com'
        );
        $inputs = $crawler->filter('input')->extract(array('name', 'value'));
        $button = $crawler->filter('button')->extract(array('name', 'type'));

        // We should see an empty form with title and csrf fields
        $this->assertGreaterThan(0, $inputs);

        $this->assertEquals('form[title]', $inputs[0][0]);
        $this->assertEmpty($inputs[0][1]);

        $this->assertEquals('form[submit]', $button[0][0]);
        $this->assertEquals('submit', $button[0][1]);

        // Fill out the form
        $form = $crawler->selectButton('Submit')->form();
        $form['form[title]'] = 'Create Entity Test';

        $request->request->replace($form->getPhpValues());
        $request->setMethod('POST');


        // Try to "send" the post data to the preview form
        $this->controller->mock_template = 'UnitedCoreBundle:Tests:DumpContext.html.twig';
        $content = $this->getActionContent(
          'preview',
          array('create', 'preview', $request)
        );
        $context = json_decode($content, true);
        $this->assertArrayHasKey('entity', $context);

        // Now try to "send" the form data to the create method
        $this->controller->mock_template = 'UnitedCoreBundle:Tests:Form.html.twig';
        $response = $this->getControllerActionResponse(
          'create',
          array($request)
        );

        // Response should be an redirect, entity manager mock should have the new entity.
        $this->assertTrue($response->isRedirect('/'));

        // We should now have an entity in our entity manager
        $this->assertCount(1, $this->mock_entityManager->saved);

        // Test if the entity got updated
        $this->assertEquals(
          'Create Entity Test',
          $this->mock_entityManager->data[0]->getTitle()
        );

        // Try to send without any data
        $request->request->replace(array());

        // Now try to "send" the form data to the create method
        $response = $this->getControllerActionResponse(
          'create',
          array($request)
        );
        $this->assertFalse($response->isRedirect('/'));
    }

    public function testUpdateResponse()
    {

        // Set template to form
        $this->controller->mock_template = 'UnitedCoreBundle:Tests:Form.html.twig';

        // Test sending emptyrequest
        $request = new Request();
        $this->container->enterScope('request');
        $this->container->set('request', $request, 'request');

        $crawler = new Crawler(
          $this->getActionContent(
            'update',
            array(
              1,
              $request
            )
          ), 'http://example.com'
        );
        $form = $crawler->selectButton('Submit')->form();
        $values = $form->getValues();

        // Test that passing the entity to the form works
        $this->assertEquals('Found Entity', $values['form[title]']);

        // Test changing the title
        $form['form[title]'] = 'Updated Entity';
        $request->request->replace($form->getPhpValues());
        $request->setMethod('POST');

        // Now try to "send" the form data to the update method
        $response = $this->getControllerActionResponse(
          'update',
          array(
            1,
            $request
          )
        );

        // Response should be an redirect, entity manager mock should have the new entity.
        $this->assertTrue($response->isRedirect('/'));

        // We should now have an entity in our entity manager
        $this->assertCount(1, $this->mock_entityManager->data);

        // Test if the entity got updated
        $this->assertEquals(
          'Updated Entity',
          $this->mock_entityManager->data[0]->getTitle()
        );

        // Try to send without any data
        $request->request->replace(array());

        // Now try to "send" the form data to the update method
        $response = $this->getControllerActionResponse(
          'update',
          array(
            1,
            $request
          )
        );
        $this->assertFalse($response->isRedirect('/'));

    }

    public function testDeleteResponse()
    {

        // Set template to form
        $this->controller->mock_template = 'UnitedCoreBundle:Tests:Form.html.twig';

        // Test sending empty request
        $request = new Request();
        $this->container->enterScope('request');
        $this->container->set('request', $request, 'request');

        $crawler = new Crawler(
          $this->getActionContent(
            'delete',
            array(
              1,
              $request
            )
          ), 'http://example.com'
        );
        $form = $crawler->selectButton('Submit')->form();
        $values = $form->getValues();

        // Test that passing the entity to the form works
        $this->assertEquals('Found Entity', $values['form[title]']);

        // Try to submit the form
        $request->request->replace($form->getPhpValues());
        $request->setMethod('POST');
        $response = $this->getControllerActionResponse(
          'delete',
          array(
            1,
            $request
          )
        );

        // Response should be an redirect, entity manager mock should not have the entity anymore
        $this->assertTrue($response->isRedirect('/'));

        // We should now have no entity in our entity manager
        $this->assertCount(0, $this->mock_entityManager->data);

        // Try to send without any data
        $request->request->replace(array());

        // Now try to "send" the form data to the update method
        $response = $this->getControllerActionResponse(
          'delete',
          array(
            1,
            $request
          )
        );
        $this->assertFalse($response->isRedirect('/'));
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
     * Check that access on create action is checked by the controller, if we set the united config secure flag true.
     */
    public function testCreateActionAccess()
    {
        // Enable security
        $this->enableStructureSecurity();

        // Inject an structure item
        $id = $this->injectStructureItem();

        // Accessing index without granting should throw an access denied exception.
        $this->setAuthCheckerGrant();
        $this->checkAccessDeniedException(
          'create',
          true,
          array($this->container->get('request'))
        );
        $this->checkAuthChecker(0, $id, 'access');

        // When we grant the access, we should not get any exceptions
        $this->setAuthCheckerGrant(array('access'));
        $this->checkAccessDeniedException(
          'create',
          false,
          array($this->container->get('request'))
        );
        $this->checkAuthChecker(0, $id, 'access');
    }

    /**
     * Check that access on update action is checked by the controller and that view and update on the entity is
     * checked, if we set the united config secure flag true.
     */
    public function testUpdateActionAccess()
    {
        // Enable security
        $this->enableStructureSecurity();

        // Inject an structure item
        $id = $this->injectStructureItem();

        // Accessing index without granting should throw an access denied exception.
        $this->setAuthCheckerGrant(array());
        $this->checkAccessDeniedException(
          'update',
          true,
          array(
            1,
            $this->container->get('request')
          )
        );
        $this->checkAuthChecker(0, $id, 'access');

        // When we grant the access, we should still get an exception, since view on entity will also be checked
        $this->setAuthCheckerGrant(array('access'));
        $this->checkAccessDeniedException(
          'update',
          true,
          array(
            1,
            $this->container->get('request')
          )
        );
        $this->checkAuthChecker(0, $id, 'access');
        $this->checkAuthChecker(1, 1, 'view');

        // When we also grant for view we should still get an exception, since update on entity will also be checked
        $this->setAuthCheckerGrant(array('access', 'view'));
        $this->checkAccessDeniedException(
          'update',
          true,
          array(
            1,
            $this->container->get('request')
          )
        );
        $this->checkAuthChecker(0, $id, 'access');
        $this->checkAuthChecker(1, 1, 'view');
        $this->checkAuthChecker(2, 1, 'update');

        // When we also allow update, no exceptions will be thrown
        $this->setAuthCheckerGrant(array('access', 'view', 'update'));
        $this->checkAccessDeniedException(
          'update',
          false,
          array(
            1,
            $this->container->get('request')
          )
        );
        $this->checkAuthChecker(0, $id, 'access');
        $this->checkAuthChecker(1, 1, 'view');
        $this->checkAuthChecker(2, 1, 'update');
    }

    /**
     * Check that access on delete action is checked by the controller and that view and delete on the entity is
     * checked, if we set the united config secure flag true.
     */
    public function testDeleteActionAccess()
    {
        // Enable security
        $this->enableStructureSecurity();

        // Inject an structure item
        $id = $this->injectStructureItem();

        // Accessing index without granting should throw an access denied exception.
        $this->setAuthCheckerGrant(array());
        $this->checkAccessDeniedException(
          'delete',
          true,
          array(
            1,
            $this->container->get('request')
          )
        );
        $this->checkAuthChecker(0, $id, 'access');

        // When we grant the access, we should still get an exception, since view on entity will also be checked
        $this->setAuthCheckerGrant(array('access'));
        $this->checkAccessDeniedException(
          'delete',
          true,
          array(
            1,
            $this->container->get('request')
          )
        );
        $this->checkAuthChecker(0, $id, 'access');
        $this->checkAuthChecker(1, 1, 'view');

        // When we also grant for view we should still get an exception, since update on entity will also be checked
        $this->setAuthCheckerGrant(array('access', 'view'));
        $this->checkAccessDeniedException(
          'delete',
          true,
          array(
            1,
            $this->container->get('request')
          )
        );
        $this->checkAuthChecker(0, $id, 'access');
        $this->checkAuthChecker(1, 1, 'view');
        $this->checkAuthChecker(2, 1, 'delete');

        // When we also allow update, no exceptions will be thrown
        $this->setAuthCheckerGrant(array('access', 'view', 'delete'));
        $this->checkAccessDeniedException(
          'delete',
          false,
          array(
            1,
            $this->container->get('request')
          )
        );
        $this->checkAuthChecker(0, $id, 'access');
        $this->checkAuthChecker(1, 1, 'view');
        $this->checkAuthChecker(2, 1, 'delete');
    }
}