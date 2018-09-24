<?php

namespace United\CoreBundle\Tests\Mock;

class EntityRepositoryMock
{

    /**
     * @var EntityMock[]
     */
    public $data = array();

    public function findAll()
    {
        return $this->data;
    }

    public function find($id)
    {
        $obj = new EntityMock();
        $obj->setId($id);
        $obj->setTitle('Found Entity');

        return $obj;
    }

}