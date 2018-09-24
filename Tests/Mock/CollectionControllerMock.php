<?php

namespace United\CoreBundle\Tests\Mock;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormTypeInterface;
use United\CoreBundle\Controller\CollectionController;

class CollectionControllerMock extends CollectionController
{

    /**
     * @var string $mock_template
     */
    public $mock_template;

    /**
     * @var array $mock_context
     */
    public $mock_context = array();

    /**
     * @var EntityRepository $mock_repository
     */
    public $mock_repository;

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

        foreach ($this->mock_context as $key => $value) {
            $context[$key] = $value;
        }
    }

    protected function templateForViewAction()
    {
        return "UnitedCoreBundle:Tests:CollectionView.html.twig";
    }

    protected function templateForIndexAction()
    {
        return "UnitedCoreBundle:Tests:DumpContext.html.twig";
    }

    /**
     * Returns the entity repository for the CRUD operations.
     *
     * @return EntityRepository
     */
    protected function getEntityRepository()
    {
        return $this->mock_repository;
    }

    /**
     * Returns the form the given action. For the base implementation,
     * $action can be: index|create|update|delete.
     *
     * @param string $action
     * @param null|object $entity
     * @return string|FormTypeInterface
     */
    protected function getFormForAction($action, $entity = null)
    {
        $form_factory = $this->container->get('form.factory');

        return $form_factory->createBuilder('form', $entity)
          ->add('title', 'text')
          ->add('submit', 'submit')
          ->getForm();
    }

    /**
     * Returns a new entity.
     *
     * @return object
     */
    protected function createNewEntity()
    {
        return new EntityMock();
    }

    protected function getSuccessForAction($action, $entity)
    {
        return $this->redirect('/');
    }

    /**
     * @param null|object $entity
     * @return Form
     */
    protected function formForCreateAction($entity)
    {
        return $this->getFormForAction('create', $entity);
    }

    /**
     * @param null|object $entity
     * @return Form
     */
    protected function formForUpdateAction($entity)
    {
        return $this->getFormForAction('update', $entity);
    }

    /**
     * @param null|object $entity
     * @return Form
     */
    protected function formForDeleteAction($entity)
    {
        return $this->getFormForAction('delete', $entity);
    }
}