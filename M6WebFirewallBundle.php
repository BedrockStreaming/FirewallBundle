<?php

namespace M6Web\Bundle\FirewallBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * class FirewallBundle
 */
class M6WebFirewallBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new DependencyInjection\M6WebFirewallExtension();
    }
}
