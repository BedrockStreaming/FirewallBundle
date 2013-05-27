<?php
namespace M6Web\Bundle\FirewallBundle\Controller;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

use Doctrine\Common\Annotations\Reader;

use M6Web\Bundle\FirewallBundle\Annotation\Firewall,
    M6Web\Bundle\FirewallBundle\Firewall\Provider;

/**
 * Listener of the controller events
 *
 * @author Jérémy JOURDIN <jjourdin.externe@m6.fr>
 */
class Listener
{
    /**
     * @var Reader $reader Doctrine annotation reader
     */
    protected $reader;

    /**
     * @var Provider $provider Firewall manager
     */
    protected $provider;

    /**
     * Constructor
     *
     * @param Reader   $reader   Doctrine annotation reader
     * @param Provider $provider Firewall manager
     */
    public function __construct(Reader $reader, Provider $provider)
    {
        $this->reader = $reader;
        $this->provider = $provider;
    }

    /**
     * Get the firewall manager
     *
     * @return Provider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Get the annotation reader
     *
     * @return Reader
     */
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * Event before action execution
     *
     * @param FilterControllerEvent $event Symfony trigger event
     */
    public function onCoreController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $class  = new \ReflectionClass($controller[0]);
        $method = $class->getMethod($controller[1]);

        if ($classAnnotations = $this->reader->getClassAnnotations($class)) {
            foreach ($classAnnotations as $classAnnotation) {
                if ($classAnnotation instanceof Firewall) {
                    if (is_array($classAnnotation->actions) && in_array($method->name, $classAnnotation->actions)) {
                        $this->loadFirewall($classAnnotation);
                    } elseif ($classAnnotation->actions === null) {
                        $this->loadFirewall($classAnnotation);
                    }
                }
            }
        }

        if ($methodAnnotation = $this->reader->getMethodAnnotation($method, 'M6Web\Bundle\FirewallBundle\Annotation\Firewall')) {
            $this->loadFirewall($methodAnnotation);
        }
    }

    /**
     * Load firewall with a configuration
     *
     * @param \M6Web\Bundle\FirewallBundle\Annotation\Firewall $annotation Annotation with configuration data
     */
    protected function loadFirewall($annotation)
    {
        $firewall = $this->provider->getFirewall($annotation->config, $annotation->options);
        $firewall->handle($annotation->options['callback']);
    }
}
