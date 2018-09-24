<?php

namespace United\CoreBundle\Tests\Controller;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Scope;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use appTestDebugProjectContainer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Route;
use United\CoreBundle\Controller\UnitedController;
use United\CoreBundle\Tests\Mock\AnnotationLoaderMock;
use United\CoreBundle\Tests\Mock\AuthorizationCheckerMock;
use United\CoreBundle\Tests\Mock\EntityManagerMock;
use United\CoreBundle\Util\UnitedStructure;

class UnitedControllerTestCase extends KernelTestCase
{

    /**
     * @var EntityManagerMock $mock_entityManager
     */
    protected $mock_entityManager;

    /**
     * @var appTestDebugProjectContainer $container
     */
    protected $container;

    /**
     * @var UnitedController $controller
     */
    protected $controller;

    /**
     * @param string $postfix
     * @return string
     */
    protected function getClassPrefix($postfix = '')
    {
        return str_replace('\\', '_', get_class($this)).$postfix;
    }

    /**
     * Checks if the controller routes have the path and defaults as expected.
     *
     * @param array $expected
     */
    protected function checkControllerRoutes($expected = array())
    {

        // Check that the index "/" route is working for any METHOD.
        $routes = $this->getControllerRoutes()->all();

        foreach ($expected as $index => $vars) {
            $this->assertArrayHasKey($index, $routes);
            $this->assertEquals($routes[$index]->getPath(), $vars['path']);
            $this->assertEquals(
              $routes[$index]->getDefaults(),
              $vars['defaults']
            );
        }
    }

    /**
     * Get routes from controller
     * @param string $postfix
     * @return \Symfony\Component\Routing\RouteCollection
     */
    protected function getControllerRoutes($postfix = '')
    {

        $name = $this->getClassPrefix($postfix);

        $this->container->get('united.core.structure')->add(
          $name,
          $name,
          $this->controller,
          $name
        );

        return $this->container->get('routing.loader')
          ->load('.', 'united.'.$name);
    }

    /**
     * @param string $action
     * @param array $params
     * @param bool $checkException
     * @return Response
     */
    protected function getControllerActionResponse(
      $action,
      $params = array(),
      $checkException = true
    ) {
        $action = $action.'Action';
        $response = null;
        $msg = '';

        // Check, that getting the response is working.
        if ($checkException) {
            try {
                $response = call_user_func_array(
                  array(
                    $this->controller,
                    $action
                  ),
                  $params
                );
            } catch (\Exception $e) {
                $msg = $e->getMessage();
            }
        } else {
            $response = call_user_func_array(
              array(
                $this->controller,
                $action
              ),
              $params
            );
        }

        $this->assertEquals('', $msg);
        $this->assertNotNull($response);

        return $response;
    }

    /**
     * @param string $action
     * @param array $params
     * @param bool $checkException
     * @return string
     */
    protected function getActionContent(
      $action,
      $params = array(),
      $checkException = true
    ) {
        return $this->getControllerActionResponse(
          $action,
          $params,
          $checkException
        )
          ->getContent();
    }


    protected function enableStructureSecurity($bool = true)
    {

        $namespaces = $this->container->getParameter('united.core.config');
        foreach ($namespaces as $key => $namespace) {
            $namespaces[$key]['secure'] = true;
        }
        $this->container->setParameter('united.core.config', $namespaces);
    }

    /**
     * @param $action
     * @param bool $exception
     * @param array $params
     * @return string
     */
    protected function checkAccessDeniedException(
      $action,
      $exception = true,
      $params = array()
    ) {
        $content = '';

        // try to access getActionContent without granting it.
        $msg = '';
        try {
            $content = $this->getActionContent($action, $params, false);
        } catch (AccessDeniedHttpException $e) {
            $msg = $e->getMessage();
        }

        if ($exception) {
            $this->assertNotEquals('', $msg);
        } else {
            $this->assertEquals('', $msg);
        }

        return $content;
    }

    /**
     * Sets the actions, the authorization_checker mock should grant.
     * @param array $actions
     */
    protected function setAuthCheckerGrant($actions = array())
    {
        $this->container->get('security.authorization_checker')->last = array();
        $this->container->get(
          'security.authorization_checker'
        )->grantActions = $actions;
    }

    protected function checkAuthChecker($index, $id, $attributes)
    {
        $this->assertGreaterThan(
          $index,
          count($this->container->get('security.authorization_checker')->last)
        );
        $this->assertEquals(
          $id,
          $this->container->get(
            'security.authorization_checker'
          )->last[$index]['object']->getId()
        );
        $this->assertEquals(
          $attributes,
          $this->container->get(
            'security.authorization_checker'
          )->last[$index]['attributes']
        );
    }

    /**
     * Injects an united structure item into the container, creates a route for it and set the current request to point
     * to that route.
     *
     * @param string $namespace
     * @param string $parent
     * @param string $paramName
     * @param array $config
     * @param string $id
     * @return string
     */
    protected function injectStructureItem(
      $namespace = null,
      $parent = null,
      $paramName = null,
      $config = array(),
      $id = null,
      $route = null
    ) {
        if (!$namespace) {
            $namespace = 'united';
        }


        // create rand route and load it into the annotationLoader
        if (!$id) {
            $id = 'randItemId'.time();
        }

        $path = '/'.$id;
        $route = $id.'.route';
        $annotationLoader = new AnnotationLoaderMock();
        $annotationLoader->routeCollection->add($route, new Route($path));
        $unitedStructure = new UnitedStructure($annotationLoader);

        // Add UnitedStructureItem
        $this->container->set('united.core.structure', $unitedStructure);
        $this->container->get('united.core.structure')
          ->add(
            $id,
            $path,
            $this->controller,
            $namespace,
            $parent,
            $paramName,
            $config
          );

        // Test sending request for the index action
        $request = new Request(
          array(),
          array(),
          array('_route' => $id.'.'.$route)
        );
        $this->container->set('request', $request, 'request');

        return $id;
    }

    protected function setUp()
    {

        self::bootKernel();

        $this->container = static::$kernel->getContainer();
        $this->mock_entityManager = new EntityManagerMock();
        $this->container->set(
          'doctrine.orm.default_entity_manager',
          $this->mock_entityManager
        );
        $this->container->set(
          'security.authorization_checker',
          new AuthorizationCheckerMock()
        );

        $container = new ContainerBuilder();

        foreach ($this->container->getServiceIds() as $id) {
            if ($id != 'assetic.controller' && $id != 'request' && $id != 'templating.helper.assets') {
                $container->set($id, $this->container->get($id));
            }
        }

        $request = new Request();
        $container->addScope(new Scope('request'));
        $container->enterScope('request');
        $container->set('request', $request, 'request');
        $container->setParameter(
          'united.core.config',
          array(
            'united' => array(
              'theme' => '@UnitedOne',
              'secure' => false
            )
          )
        );

        $this->container = $container;
        $this->controller->setContainer($this->container);
    }

}