<?php

namespace United\CoreBundle\Tests\Mock;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AuthorizationCheckerMock implements AuthorizationCheckerInterface
{

    public $last = array();
    public $grantActions = array();

    public function isGranted($attributes, $object = null)
    {
        $this->last[] = array(
          'attributes' => $attributes,
          'object' => $object,
        );

        if (is_string($attributes) && in_array(
            $attributes,
            $this->grantActions
          )
        ) {
            return true;
        } else {
            return false;
        }
    }
}