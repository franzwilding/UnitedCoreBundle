<?php

namespace United\CoreBundle\Tests\Mock;

use United\CoreBundle\Model\EntityInterface;

class EntityMock implements EntityInterface
{

    private $id;
    private $title;

    public function __toString()
    {
        return $this->title;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function loadMetadata()
    {
        return null;
    }
}