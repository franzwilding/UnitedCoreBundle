<?php

namespace United\CoreBundle\Model;

/**
 * Interface EntityInterface
 *
 * @package United\CoreBundle\Model
 */
interface EntityInterface
{

    /**
     * @return string
     */
    public function __toString();

    /**
     * @return integer|string
     */
    public function getId();

}