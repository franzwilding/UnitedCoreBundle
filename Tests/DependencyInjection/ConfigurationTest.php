<?php

namespace United\CoreBundle\Tests\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;
use United\CoreBundle\DependencyInjection\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test all allowed root params
     */
    public function testRootParams()
    {

        // allowed root params
        $this->assertValid('');
        $this->assertValid('~');
        $this->assertValid('config: ~');

        // all other values are not allowed
        $this->assertInValid('config: string');
        $this->assertInValid('string');
        $this->assertInValid('anyothername: ~');
    }

    /**
     * Test setting the config params for different namespaces
     */
    public function testConfigParam()
    {
        $this->assertValid(
          '
config:
  united: { theme: any, secure: false }
'
        );

        $this->assertValid(
          '
config:
  united: { theme: any, secure: false }
  faa: { theme: any, secure: false }
  fuu: { theme: any, secure: false }
'
        );

        $this->assertInValid(
          '
config:
  united: string
'
        );

        $this->assertInValid(
          '
config:
  united: { }
'
        );

        $this->assertInValid(
          '
config:
  united: { theme: any }
'
        );

        $this->assertInValid(
          '
config:
  united: { secure: true }
'
        );

        $this->assertInValid(
          '
config:
  united: { secure: false }
'
        );

        $this->assertInValid(
          '
config:
  united: { anyother: true }
'
        );

        $this->assertInValid(
          '
config:
  united: { theme: faa, anyother: true }
'
        );

        $this->assertInValid(
          '
config:
  united: { theme: faa, secure: fuu }
'
        );

        $this->assertInValid(
          '
config:
  united: { theme: faa, secure: true, anyother: fuu }
'
        );

    }


    public function testConfigProcessing()
    {

        $default = array(
          'config' => array(
            'united' => array(
              'theme' => '@UnitedOne',
              'secure' => false,
            ),
          ),
        );

        // test default values
        $this->assertEquals($default, $this->assertValid(''));
        $this->assertEquals($default, $this->assertValid('~'));

        // test empty config
        $empty = array('config' => array(),);
        $this->assertEquals($empty, $this->assertValid('config: '));

        // test setting a custom config
        $this->assertEquals(
          array(
            'config' => array(
              'faa' => array(
                'theme' => 'fuu',
                'secure' => true,
              ),
            ),
          ),
          $this->assertValid(
            '
config:
  faa: { theme: fuu, secure: true }'
          )
        );

        // test setting multiple custom configs
        $this->assertEquals(
          array(
            'config' => array(
              'faa' => array(
                'theme' => 'fuu',
                'secure' => true,
              ),
              'fuu' => array(
                'theme' => 'baa',
                'secure' => false,
              ),
              'anyother' => array(
                'theme' => 'faa',
                'secure' => false,
              ),
            ),
          ),
          $this->assertValid(
            '
config:
  faa: { theme: fuu, secure: true }
  fuu: { theme: baa, secure: false }
  anyother: { theme: faa, secure: false }'
          )
        );


    }


    private function processConfig($yml, $valid = true)
    {

        $config = array();

        $msg = '';
        try {
            $config = Yaml::parse($yml);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }
        $this->assertEquals('', $msg);

        $processor = new Processor();
        $configuration = new Configuration();
        $ret = array();

        $msg = '';
        try {
            $ret = $processor->processConfiguration(
              $configuration,
              array($config)
            );
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }

        if ($valid) {
            $this->assertEquals('', $msg);
        } else {
            $this->assertNotEquals('', $msg);
        }

        return $ret;
    }

    private function assertValid($yml)
    {
        return $this->processConfig($yml);
    }

    private function assertInValid($yml)
    {
        return $this->processConfig($yml, false);
    }
}