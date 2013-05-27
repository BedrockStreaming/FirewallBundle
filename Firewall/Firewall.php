<?php
namespace M6Web\Bundle\FirewallBundle\Firewall;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpKernel\Exception\HttpException;

use M6Web\Component\Firewall\Firewall as FirewallComponent;

/**
 * {@inheritdoc}
 */
class Firewall extends FirewallComponent implements FirewallInterface
{
    /**
     * @var Provider $provider Firewall manager
     */
    protected $provider;

    /**
     * @var Request $request Symfony request
     */
    protected $request;

    /**
     * @var boolean $throwError Throw exception if the input is invalid
     */
    protected $throwError = true;

    /**
     * @var integer $errorCode Error code to return for invalid input
     */
    protected $errorCode = 403;

    /**
     * @var string $errorMessage Error message to return for invalid input
     */
    protected $errorMessage = 'Forbidden';

    /**
     * {@inheritdoc}
     */
    public function setProvider(Provider $provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Add/remove a predefined list in the firewall
     *
     * @param string       $listName List name
     * @param boolean|null $state    List state in the firewall (true=white, false=black, null=undefined)
     *
     * @return $this
     */
    public function setPresetList($listName, $state=null)
    {
        $lists = $this->provider->getLists();

        if (is_array($listName)) {
            foreach ($listName as $list) {
                $this->setPresetList($list, $state);
            }
        } else {
            parent::setList($lists[$listName], $listName, $state);
        }

        return $this;
    }

    /**
     * Set a list as "black" (banned)
     *
     * @param string $listName List name
     *
     * @return $this
     */
    public function setListBlack($listName)
    {
        $this->setPresetList($listName, false);

        return $this;
    }

    /**
     * Set a list as "white" (allowed)
     *
     * @param string $listName List name
     *
     * @return $this
     */
    public function setListWhite($listName)
    {
        $this->setPresetList($listName, true);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setErrorCode($code)
    {
        if (is_int($code) || ctype_digit($code)) {
            $this->errorCode = (int) $code;
        }

        return $this;
    }

    /**
     * Get the error code
     *
     * @return integer
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * {@inheritdoc}
     */
    public function setErrorMessage($message)
    {
        $this->errorMessage = (string) $message;

        return $this;
    }

    /**
     * Get the error message
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function setThrowError($throw)
    {
        if (is_bool($throw)) {
            $this->throwError = $throw;
        } else {
            throw new \InvalidArgumentException(sprintf("Firewall::setThrowError() Arg#1 must be a boolean, %s given.", gettype($throw)));
        }

        return $this;
    }

    /**
     * Check if an exception is throwed in error case
     *
     * @return boolean
     */
    public function isThrowError()
    {
        return $this->throwError;
    }

    /**
     * {@inheritdoc}
     *
     * @throws HttpException If input is invalid and $throwError = true
     */
    public function handle(callable $callBack = null)
    {
        $ipAddress = $this->request->getClientIp();

        $this->setIpAddress($ipAddress);

        $isAllowed = parent::handle($callBack);

        if (!$isAllowed && $this->throwError) {
            throw new HttpException($this->errorCode, sprintf($this->errorMessage, $ipAddress));
        }

        return $isAllowed;
    }
}
