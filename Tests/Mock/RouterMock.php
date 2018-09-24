<?php

namespace United\CoreBundle\Tests\Mock;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\RouteCollection;

class RouterMock extends Router
{

    public $collection;

    public function __construct()
    {
        $this->collection = new RouteCollection();
    }

    public function getRouteCollection()
    {
        return $this->collection;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(
      $name,
      $parameters = array(),
      $referenceType = self::ABSOLUTE_PATH
    ) {
        if (count($parameters) > 0) {
            return 'path|'.join(',', $parameters);
        } else {
            return 'path';
        }
    }
}