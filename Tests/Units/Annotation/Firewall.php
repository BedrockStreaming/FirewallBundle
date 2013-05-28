<?php
namespace M6Web\Bundle\FirewallBundle\Tests\Units\Annotation;

require_once __DIR__.'/../../bootstrap.php';

use atoum\AtoumBundle\Test\Units;

use M6Web\Bundle\FirewallBundle\Annotation\Firewall as FirewallAnnotation;

/**
 * Firewall annotation class test
 */
class Firewall extends Units\Test
{
    protected $data = array(
        'default_state' => true,
        'error_code'    => 403,
        'error_message' => 'Interdit',
        'throw_error'   => false,
        'callback'      => 'testMyCallback',
        'entries'       => array(
            '192.168.25.3' => false,
            '127.0.0.1'    => true,
            '10.36.25.*'   => true,
        ),
        'lists'         => array(
            'default'   => false,
            'partners'  => true,
        ),
        'config'        => "default",
        'actions'       => array(
            'indexAction',
            'testAction',
        ),
    );

    public function testConstructor()
    {
        $annotation = new FirewallAnnotation($this->data);

        foreach ($this->data as $name => $data) {
            switch ($name) {
                case 'config':
                case 'actions':
                    $this->assert
                        ->variable($this->data[$name])
                            ->isIdenticalTo($annotation->$name)
                    ;
                    break;

                default:
                    $this->assert
                        ->variable($this->data[$name])
                            ->isIdenticalTo($annotation->options[$name])
                    ;
                    break;
            }
        }
    }

    public function testConstructorWithoutData()
    {
        $annotation = new FirewallAnnotation(array());

        $this->assert
            ->variable($annotation->config)
                ->isNull()
            ->variable($annotation->actions)
                ->isNull()
        ;

        foreach ($annotation->options as $name => $value) {
            switch ($name) {
                case 'entries':
                case 'lists':
                    $this->assert
                        ->array($value)
                            ->isEmpty()
                    ;
                    break;

                default:
                    $this->assert
                        ->variable($value)
                            ->isNull()
                    ;
                    break;
            }
        }
    }
}
