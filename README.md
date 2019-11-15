# Firewall Bundle [![Build Status](https://secure.travis-ci.org/M6Web/FirewallBundle.png)](http://travis-ci.org/M6Web/FirewallBundle)

This bundle provides IP filtering features for your Symfony applications.  
It uses the [Firewall component](https://github.com/M6Web/Firewall) and offers service and annotations configuration.

For implementation into a Symfony 3 or Symfony 4 application, please use the release `v3.0.0` at least.

## Installation

Add this line in your `composer.json` :

```json
{
    "require": {
        "m6web/firewall-bundle": "dev-master"
    }
}
```

Update your vendors :

```
composer update m6web/firewall-bundle
```

## Registering

```php
class AppKernel extends \Symfony\Component\HttpKernel\Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new M6Web\Bundle\FirewallBundle\M6WebFirewallBundle(),
        );
    }
}
```

## Usage

#### Configuration

```yaml
m6web_firewall:
    lists:             	                   # Define some IP lists into the Firewall Provider
        self: 				                    # Define a list named "self"
            - '127.0.0.1' 			                # IPV4
            - '::1'      	 	        	        # IPV6 short notation
        lan:     			                    # Define a list named "lan"
            - '192.168.0.*' 		                # IPV4 with Wildcard (* = all)
            - '192.168.0.0/24' 		                # IPV4 with CIDR Mask
            - '192.168.0.0/255.255.255.0' 	        # IPV4 with Subnet Mask
    configs: 				               # Define some pre-defined configurations into the Firewall Provider
        default: 				                # Define a configuration named "default"
            default_state: true 		            # Default returned value (default: true)
            throw_error: true 		                # Throw an exception for rejected users (default: true)
            error_code: 403 		                # Exception status code (default: 403)
            error_message: 'Forbidden' 	            # Exception message (default: Forbidden)
            lists: 			                        # Lists access state
                self: true 			                    # "self" list records will be allowed by the firewall
                lan: false 			                    # "lan" list records will be rejected by the firewall
            entries: 			                    # Define custom IP's access state
                '192.168.0.10': true 	                # "192.168.0.10" will be allowed
                '192.168.0.20': false 	                # "192.168.0.20" will be rejected
```

#### Global annotation

```php
use M6Web\Bundle\FirewallBundle\Annotation\Firewall;

/**
 * @Firewall(
 *      config="default",
 *      actions={
 *          'myFirstAction'
 *      },
 *      default_state=true,
 *      lists={
 *          'default': true
 *      },
 *      entries={
 *          '192.168.0.50': false
 *      },
 *      throw_error: false,
 *      callback="myFirewallResponseHandler",
 *      error_message: 'Forbiden',
 *      error_code: 403
 * )
 */
```

* `config` parameter sets which pre-defined configuration to use,  
* `actions` parameter sets which actions of the controller are protected (in case of Class Annotation).

All default [set](#configuration) parameters can be overloaded by annotation.

#### Class annotation

```php
use M6Web\Bundle\FirewallBundle\Annotation\Firewall;

/**
 * @Firewall(
 *      config="default",
 *      actions={
 *          'myFirstAction'
 *      }
 * )
 */
class MyBundleController extends Controller
{
    public function myFirstAction()
    {
    }

    public function mySecondAction()
    {
    }
}
```

* `myFirstAction` is protected by the [pre-defined configuration](#configuration) `default`.  
In this case we can set one (or many) firewall used for many actions.

#### Method annotation

```php
use M6Web\Bundle\FirewallBundle\Annotation\Firewall;

class MyBundleController extends Controller
{
    /**
     * @Firewall(
     *      config="default"
     * )
     */
    public function myFirstAction()
    {
    }

    /**
     * @Firewall(
     *      default_state=true,
     *      lists={
     *           'lan': false
     *      },
     *      entries={
     *          '20.30.40.50': false
     *      }
     * )
     */
    public function mySecondAction()
    {
    }
}
```

* `myFirstAction` uses its own firewall with [pre-defined configuration](#configuration) `default`,
* `mySecondAction` uses its own firewall with a custom configuration.

#### Path configuration

```yaml
m6web_firewall:
    patterns:                             # define some routing pattern to filter
        api:
            config: default                         # config associed to the path
            path: /api                              # path to filter
```

* `config` parameter sets which pre-defined configuration to use,  
* `path` parameter sets which path are protected.

## Running the tests

```
$ php composer.phar install --dev
$ ./vendor/bin/atoum -d Tests/
```

## Credits

Developped by the [Cytron Team](http://cytron.fr/) of [M6 Web](http://tech.m6web.fr/).  
Tested with [atoum](http://atoum.org).

## License

The FirewallBundle is licensed under the [MIT license](LICENSE).
