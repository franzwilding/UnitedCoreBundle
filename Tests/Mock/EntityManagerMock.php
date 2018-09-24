<?php

namespace United\CoreBundle\Tests\Mock;

use Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver;

class EntityManagerMock
{

    public $data = array();
    public $saved = array();

    public function findAll()
    {
        return $this->data;
    }

    public function create()
    {
        $obj = new EntityMock();
        $this->data[] = $obj;

        return $obj;
    }

    public function getMetadataFactory()
    {
        return new StaticPHPDriver(array());
    }

    public function initializeObject($entity)
    {
        $this->data[] = $entity;
    }

    public function persist($entity)
    {
        $this->saved[] = $entity;
    }

    public function remove($object)
    {
        array_pop($this->data);
        array_pop($this->saved);
    }

    public function flush()
    {
    }

    public function clear()
    {
    }

}