<?php

namespace United\CoreBundle\Controller;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use United\CoreBundle\Model\EntityInterface;
use United\CoreBundle\Util\UnitedStructure;

/**
 * Abstract base class for all CRUD controller
 * Class UnitedCRUDController
 * @package United\CoreBundle\Controller
 */
abstract class CRUDController extends PlainController implements ControllerPreviewInterface
{

    /**
     * Returns the entity repository for the CRUD operations.
     *
     * @return EntityRepository
     */
    abstract protected function getEntityRepository();

    /**
     * @param null|object $entity
     * @return Form
     */
    abstract protected function formForCreateAction($entity);

    /**
     * @param null|object $entity
     * @return Form
     */
    abstract protected function formForUpdateAction($entity);

    /**
     * @param null|object $entity
     * @return Form
     */
    abstract protected function formForDeleteAction($entity);

    /**
     * Returns the form the given action. For the base implementation,
     * $action can be: index|create|update|delete. The base implementation will check for a method named
     * "getFormFor$actionAction() and returns its result.
     *
     * @param string $action
     * @param null|object $entity
     * @return Form
     */
    protected function getFormForAction($action, $entity = null)
    {
        $method = 'formFor'.ucfirst($action).'Action';

        return $this->$method($entity);
    }

    /**
     * Returns all entities for the index action.
     *
     * @return array
     */
    protected function findIndexEntities()
    {
        $entities = $this->getEntityRepository()->findAll();

        if (count($entities) > 0 && !$entities[0] instanceof EntityInterface) {
            throw $this->createNotFoundException(
              'Entity must implement EntityInterface.'
            );
        }

        foreach ($entities as $key => $entity) {

            // Check if we can access the entities and unset them if not.
            if (!$this->checkEntityAccess('view', $entity, false)) {
                unset($entities[$key]);
            }
        }

        return $entities;
    }

    /**
     * Returns one entity found by the given id.
     *
     * @param $id
     * @return null|EntityInterface
     */
    protected function findEntityById($id)
    {

        if (!$entity = $this->getEntityRepository()->find($id)) {
            throw $this->createNotFoundException(
              'Entity with id '.$id.' was not found'
            );
        }

        if (!$entity instanceof EntityInterface) {
            throw $this->createNotFoundException(
              'Entity must implement EntityInterface.'
            );
        }

        $this->checkEntityAccess(
          'view',
          $entity
        ); // Check if we can access this action

        return $entity;
    }

    /**
     * This method is called after an action successfully finishes and must return
     * an response. Normally this will be an redirect.
     *
     * @param string $action
     * @param EntityInterface $entity
     * @return Response
     */
    protected function getSuccessForAction($action, $entity)
    {

        /**
         * @var UnitedStructure $unitedStructure
         */
        $unitedStructure = $this->get('united.core.structure');
        $currentItem = $unitedStructure->getItemFromRequest(
          $this->get('request')
        );

        return $this->redirect(
          $currentItem->getUrl('', $this->get('request'), $this->get('router'))
        );
    }

    /**
     * Returns a new entity.
     *
     * @return EntityInterface $entity
     */
    abstract protected function createNewEntity();

    /**
     * Renders all entities, found by findIndexEntities() in the template found
     * by calling getTemplateForAction('index').
     *
     * @Route("/")
     * @Method({"GET"})
     *
     * @return Response
     */
    public function indexAction()
    {
        $this->checkActionAccess(); // Check if we can access this action

        $context = array('entities' => $this->findIndexEntities());
        $this->alterContextForAction('index', $context);

        return $this->render($this->getTemplateForAction('index'), $context);
    }

    /**
     * Renders the create form and processes form data if available.
     *
     * @Route("/create")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function createAction(Request $request)
    {
        $this->checkActionAccess(); // Check if we can access this action

        // Create a form for a new entity
        $entity = $this->createNewEntity();

        if (!$entity instanceof EntityInterface) {
            throw new \Exception(
              'createNewEntity() must return an entity that implements United\CoreBundle\Model\EntityInterface.'
            );
        }

        if (!$form = $this->getFormForAction('create', $entity)) {
            throw new \Exception(
              'You must define a create form by implementing getFormForCreateAction($entity).'
            );
        }

        $form->handleRequest($request);

        // If form is valid, we save the new entity and redirect to indexAction.
        if ($form->isValid()) {

            // Save the entity
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            // Call success method for this action
            return $this->getSuccessForAction('create', $entity);
        } // If form is not valid, we need to render the create form
        else {
            $context = array(
              'entity' => $entity,
              'form' => $form->createView(),
              'errors' => $form->getErrors(true)
            );
            $this->alterContextForAction('create', $context);

            return $this->render(
              $this->getTemplateForAction('create'),
              $context
            );
        }
    }

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
    public function previewAction($action, $template, Request $request)
    {
        $this->checkActionAccess(); // Check if we can access this action

        // Create a form for a new entity
        $entity = $this->createNewEntity();

        if (!$entity instanceof EntityInterface) {
            throw new \Exception(
              'createNewEntity() must return an entity that implements United\CoreBundle\Model\EntityInterface.'
            );
        }

        if (!$form = $this->getFormForAction($action, $entity)) {
            throw new \Exception(
              'You must define a create form by implementing getFormForCreateAction($entity).'
            );
        }

        $form->handleRequest($request);

        // If form is valid, we can return the rendered entity.
        if ($form->isValid()) {

            $context = array('entity' => $entity);
            $this->alterContextForAction($template, $context);

            return $this->render($this->getTemplateForAction($template), $context);

        } // If form is not valid, we need to render the create form.
        else {

            $context = array(
              'entity' => $entity,
              'error' => $form->getErrors(true)
            );
            $this->alterContextForAction('error', $context);

            return $this->render(
              $this->getTemplateForAction('error'),
              $context
            );
        }
    }

    /**
     * Renders the update form and processes form data if available.
     *
     * @Route("/{id}/update")
     * @Method({"GET", "POST"})
     *
     * @param mixed $id
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function updateAction($id, Request $request)
    {
        $this->checkActionAccess(); // Check if we can access this action

        // Create an update form for the entity
        if (!$entity = $this->findEntityById($id)) {
            throw $this->createNotFoundException(
              'Entity with id: '.$id.' not found'
            );
        }

        $this->checkEntityAccess(
          'update',
          $entity
        ); // Check if we can access this action

        if (!$form = $this->getFormForAction('update', $entity)) {
            throw new \Exception(
              'You must define an update form by implementing getFormForUpdateAction($entity).'
            );
        }

        $form->handleRequest($request);

        // If form is valid, we save the entity and redirect to indexAction.
        if ($form->isValid()) {

            // Save the entity
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            // Call success method for this action
            return $this->getSuccessForAction('update', $entity);
        } // If form is not valid, we need to render the update form
        else {
            $context = array(
              'entity' => $entity,
              'form' => $form->createView(),
              'errors' => $form->getErrors(true)
            );
            $this->alterContextForAction('update', $context);

            return $this->render(
              $this->getTemplateForAction('update'),
              $context
            );
        }
    }

    /**
     * Renders the delete form and processes form data if available.
     *
     * @Route("/{id}/delete")
     * @Method({"GET", "POST"})
     *
     * @param mixed $id
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function deleteAction($id, Request $request)
    {
        $this->checkActionAccess(); // Check if we can access this action

        // Create a delete form for the entity
        if (!$entity = $this->findEntityById($id)) {
            throw $this->createNotFoundException(
              'Entity with id: '.$id.' not found'
            );
        }

        $this->checkEntityAccess(
          'delete',
          $entity
        ); // Check if we can access this action

        if (!$form = $this->getFormForAction('delete', $entity)) {
            throw new \Exception(
              'You must define a delete form by implementing getFormForDeleteAction($entity).'
            );
        }

        $form->handleRequest($request);

        // If form is valid, then we delete the entity and redirect to indexAction.
        if ($form->isValid()) {

            // Delete the entity
            $em = $this->getDoctrine()->getManager();
            $em->remove($entity);
            $em->flush();

            // Call success method for this action
            return $this->getSuccessForAction('delete', $entity);
        } // If form is not valid, we need to render the delete form
        else {
            $context = array(
              'entity' => $entity,
              'form' => $form->createView(),
              'errors' => $form->getErrors(true)
            );
            $this->alterContextForAction('delete', $context);

            return $this->render(
              $this->getTemplateForAction('delete'),
              $context
            );
        }
    }

}