<?php

namespace United\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CollectionController
 * Defines a controller to display collections containing content provided by
 * another controller.
 * @package United\OneBundle\Controller
 */
abstract class CollectionController extends CRUDController implements ControllerViewInterface
{
    protected function alterContextForAction($action, &$context)
    {
        parent::alterContextForAction($action, $context);

        if ($action == 'view') {
            $context['itemTemplate'] = $this->getTemplateForAction('item');
        }
    }

    /**
     * Redirects to the first entity or renders a get started text.
     *
     * @Route("/")
     * @Method({"GET"})
     *
     * @return Response
     */
    public function indexAction()
    {
        $this->checkActionAccess(); // Check if we can access this action

        // Redirect to first entity view
        if (count($items = $this->findIndexEntities()) > 0) {
            $unitedStructure = $this->get('united.core.structure');
            $currentItem = $unitedStructure->getItemFromRequest(
              $this->get('request')
            );

            if (!$currentItem) {
                throw $this->createNotFoundException(
                  'No united structure item was found. Can\'t redirect'
                );
            }

            $route_parts = explode('.', $currentItem->getRoute());
            array_pop($route_parts);
            $route = join('.', $route_parts).'.view';

            return $this->redirectToRoute(
              $route,
              array('id' => $items[0]->getId())
            );
        } // Render a get started text
        else {
            $context = array();
            $this->alterContextForAction('index', $context);

            return $this->render(
              $this->getTemplateForAction('index'),
              $context
            );
        }
    }

    /**
     * Renders the items of one collection.
     *
     * @Route("/{id}", requirements={"id" = "\d+"})
     * @Method({"GET"})
     *
     * @param int $id
     * @return Response
     */
    public function viewAction($id)
    {
        $this->checkActionAccess(); // Check if we can access this action

        $entity = $this->findEntityById($id);
        $entities = $this->findIndexEntities();

        $context = array('entity' => $entity, 'entities' => $entities);
        $this->alterContextForAction('view', $context);

        return $this->render($this->getTemplateForAction('view'), $context);
    }

    /**
     * Returns the entity found by the id.
     *
     * @param int $id
     * @return null|\United\CoreBundle\Model\EntityInterface
     */
    public function viewEntity($id)
    {
        return $this->findEntityById($id);
    }
}