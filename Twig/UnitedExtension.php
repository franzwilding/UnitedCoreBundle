<?php

namespace United\CoreBundle\Twig;

use United\CoreBundle\Util\United;

class UnitedExtension extends \Twig_Extension
{

    /**
     * @var United $united
     */
    private $united;

    /**
     * @param United $united
     */
    public function __construct(United $united)
    {
        $this->united = $united;
    }

    /**
     * @return array
     */
    public function getGlobals()
    {
        return array(
          'united' => $this->united,
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'united';
    }
}