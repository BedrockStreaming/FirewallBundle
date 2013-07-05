<?php
namespace M6Web\Bundle\FirewallBundle\Firewall;

use Symfony\Component\HttpFoundation\Request;

interface FirewallInterface
{
    /**
     * Set the firewall provider
     *
     * @param Provider $provider Firewall manager
     *
     * @return $this
     */
    public function setProvider(Provider $provider);

    /**
     * Set the contextual request
     *
     * @param Request $request Symfony request
     *
     * @return $this
     */
    public function setRequest(Request $request);

    /**
     * Handle the current request
     *
     * @param callable $callBack Result handler
     *
     * @return boolean
     */
    public function handle(callable $callBack = null);

    /**
     * Get Client IP
     *
     * @return string
     */
    public function getIpAddress();

    /**
     * Add a list
     *
     * @param array        $list     List
     * @param string       $listName Identifier for the list
     * @param boolean|null $state    Whether the list is trusted or not
     *
     * @return $this
     */
    public function addList(array $list, $listName, $state);

    /**
     * Set default returned value
     *
     * @param boolean $state Default returned value
     *
     * @return $this
     */
    public function setDefaultState($state);

    /**
     * Set the error code
     *
     * @param integer $code Error code
     *
     * @return $this
     */
    public function setErrorCode($code);

    /**
     * Set the error message
     *
     * @param string $message Error message
     *
     * @return $this
     */
    public function setErrorMessage($message);

    /**
     * Activate/Disable the exception throw
     *
     * @param boolean $throw Throw or not an exception in error case
     *
     * @return $this
     */
    public function setThrowError($throw);
}
