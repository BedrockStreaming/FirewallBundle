<?php
namespace M6Web\Bundle\FirewallBundle\Annotation;

/**
 * Add a firewall before a controller or before one or more of its methods
 *
 * @author Jérémy JOURDIN <jjourdin.externe@m6.fr>
 *
 * @Annotation
 */
class Firewall
{
    /**
     * @var string $config Name of the predefined configuration to use in the firewall
     */
    public $config;

    /**
     * @var array $actions Controller methods where the firewall is applied
     */
    public $actions;

    /**
     * @var array $options Firewall setting
     */
    public $options;

    /**
     * Constructor, set the annotation data
     *
     * @param array $data Annotation data
     */
    public function __construct(array $data)
    {
        $this->config   = ( isset($data['config']) ? $data['config'] : null );
        $this->actions  = ( isset($data['actions']) ? $data['actions'] : null );
        $this->options  = array(
            "entries"       => ( isset($data['entries'])       ? $data['entries']       : array() ),
            "lists"         => ( isset($data['lists'])         ? $data['lists']         : array() ),
            "callback"      => ( isset($data['callback'])      ? $data['callback']      : null ),
            "default_state" => ( isset($data['default_state']) ? $data['default_state'] : null ),
            "error_message" => ( isset($data['error_message']) ? $data['error_message'] : null ),
            "error_code"    => ( isset($data['error_code'])    ? $data['error_code']    : null ),
            "throw_error"   => ( isset($data['throw_error'])   ? $data['throw_error']   : null ),
        );
    }
}
