<?php
namespace Bacs;

/**
 * Class Account
 * @package Bacs
 */
class Account
{
    private $accountNumber = null;
    private $sortCode = null;
    private $name = null;
    private $errors = null;

    public function __construct($sortCode = null, $accountNumber = null, $name = null)
    {
        $args = func_get_args();
        foreach ($args as $arg) {
            $this->isStringCheck($arg);
        }
        $this->setAccountNumber($accountNumber);
        $this->setSortCode($sortCode);
        $this->setName($name);
    }

    /**
     * @return null
     */
    public function getName()
    {

        if ($this->name == null) {
            throw new \Exception('No Name Set');
        }

        return strtoupper(substr($this->name, 0, 18));
    }

    public function fillNumber($input, $length = 8, $fillWith = '0')
    {
        $input = (string)$input;
        return str_pad($input, $length, $fillWith, STR_PAD_LEFT);
    }

	/**
	 * @return bool|\stdClass
	 */
    public function getObject()
    {
        if ($this->getErrors()) {
            return false;
        }

        $returnObject = new \stdClass();

        $returnObject->accountNumber = $this->getAccountNumber();
        $returnObject->sortCode = $this->getSortCode();
        $returnObject->formattedSortCode = $this->getPrintFormattedSortCode();
        $returnObject->name = $this->getName();
        return $returnObject;
    }

    public function isStringCheck($var)
    {
        if (is_string($var)) {
            return true;
        }
        throw new \Exception($var . ' is not string');
        return false;
    }

    /**
     * @param null $name
     */
    private function setName($name)
    {
        $this->name = $name;
    }

    public function stripLeadingZeros($number)
    {
        $number = (string)$number;
        $number = ltrim($number, '0');

        return $number;

    }

    /**
     * @return null
     */
    public function getAccountNumber()
    {

        if ($this->accountNumber == null) {
            throw new \Exception('No  Account Number Set');
        }

        return substr($this->fillNumber($this->stripLeadingZeros($this->accountNumber), 8), 0, 8);

    }

    /**
     * @param null $accountNumber
     */
    private function setAccountNumber($accountNumber)
    {

        $this->accountNumber = $accountNumber;
    }

    public function getPrintFormattedSortCode()
    {
        return implode("-", str_split($this->getSortCode(), 2));
    }

    /**
     * @return null
     */
    public function getSortCode()
    {

        if ($this->sortCode == null) {
            throw new \Exception('No Sort Code Set');
        }


        return substr($this->fillNumber($this->stripLeadingZeros($this->sortCode), 6), 0, 6);
    }

    /**
     * @param null $sortCode
     */
    private function setSortCode($sortCode)
    {
        $this->sortCode = $sortCode;
    }

    public function accountSetCorrectly()
    {
        $this->errors = null;
        try {
            $this->getAccountNumber();
        } catch (\Exception $e) {
            $this->setError($e);
        }
        try {
            $this->getSortCode();
        } catch (\Exception $e) {
            $this->setError($e);
        }
        try {
            $this->getName();
        } catch (\Exception $e) {
            $this->setError($e);
        }

        if ($this->getErrors(false)) {
            return false;
        }

        return true;
    }

    private function setError($e)
    {
        if (is_callable(array($e, 'getMessage'))) {
            $message = $e->getMessage();
            if (isset($this->errors) && is_array($this->errors)) {
                $this->errors[] = $message;
            } else {
                $this->errors = array();
                $this->errors[] = $message;
            }
        }
    }

    /**
     * @return null
     */
    public function getErrors($reload = true)
    {
        if ($reload) {
            $this->accountSetCorrectly();
        }
        if (is_array($this->errors)) {
            return join(' > ', $this->errors);
        }
        return false;
    }
}