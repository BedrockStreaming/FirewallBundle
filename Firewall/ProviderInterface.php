<?php
namespace M6Web\Bundle\FirewallBundle\Firewall;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for Firewall manager
 */
interface ProviderInterface
{
     /**
     * Get a firewall set with a predefined configuration or with additional setting
     *
     * @param string|null $configName Predefined configuration name
     * @param array       $options    Additional setting
     * @param Request     $request    Request
     *
     * @return FirewallInterface
     */
    public function getFirewall($configName = null, array $options = array(), Request $request = null);

    /**
     * get predefined patterns
     *
     * @return array
     */
    public function getPatterns();
}
