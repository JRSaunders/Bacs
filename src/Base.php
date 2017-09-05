<?php

namespace Bacs;

/**
 * Class Base
 * @package Bacs
 * @property Account $payFromAccount
 */
abstract class Base implements RequiredMethods {

	private $payFromAccount = null;
	private $defaultCurrency = null;
	private $payments = array();
	private $failedPayments = array();
	private $uniqueRef = null;
	private $validCurrencyArray = array(
		'GBP',
		'USD',
		'AUD'
	);
	private $totalPaymentAmount = array();

	protected $output = '';
	protected $paymentDate = null;

	/**
	 * Base constructor.
	 *
	 * @param null $payFromSortCode
	 * @param null $payFromAccountNumber
	 * @param null $payFromName
	 * @param string $defaultCurrency
	 * @param null $validCurrencyArray
	 */
	public function __construct( $payFromSortCode = null, $payFromAccountNumber = null, $payFromName = null, $defaultCurrency = 'GBP', $validCurrencyArray = null ) {
		$this->setPayFromAccount( new Account( $payFromSortCode, $payFromAccountNumber, $payFromName ) );
		$this->defaultCurrency = $defaultCurrency;
		if ( isset( $validCurrencyArray ) && is_array( $validCurrencyArray ) ) {
			$this->$validCurrencyArray = $validCurrencyArray;
		}
		$this->createUniqueRef();
	}

	/**
	 * @param $currency
	 *
	 * @return int|mixed
	 */
	public function getTotalPaymentAmount( $currency = null ) {
		if ( ! isset( $currency ) ) {
			$currency = $this->getDefaultCurrency();
		}
		if ( isset( $this->totalPaymentAmount[ $currency ] ) ) {
			return $this->totalPaymentAmount[ $currency ];
		}

		return 0;
	}

	/**
	 * @param int $totalPaymentAmount
	 */
	protected function addPaymentAmount( $totalPaymentAmount, $currency = null ) {
		if ( ! isset( $currency ) ) {
			$currency = $this->getDefaultCurrency();
		}
		if ( ! isset( $this->totalPaymentAmount[ $currency ] ) ) {
			$this->totalPaymentAmount[ $currency ] = 0;
		}
		$this->totalPaymentAmount[ $currency ] = $this->totalPaymentAmount[ $currency ] + $totalPaymentAmount;
	}

	/**
	 * @return array
	 */
	public function getFailedPayments() {
		return $this->failedPayments;
	}

	/**
	 * @return \DateTime
	 */
	public function getPaymentDate() {
		if ( is_a( $this->paymentDate, \DateTime::class ) ) {
			return $this->paymentDate;
		}

		$paymentDate = new \DateTime();

		$paymentDate->modify( '+ 2 day' );

		return $paymentDate;
	}

	/**
	 * @param \DateTime $paymentDate
	 */
	public function setPaymentDate( \DateTime $paymentDate ) {
		$this->paymentDate = $paymentDate;
	}

	protected function fillNumber( $input, $length = 8, $fillWith = '0' ) {
		$input = (string) $input;

		return str_pad( $input, $length, $fillWith, STR_PAD_LEFT );
	}

	/**
	 * @return null
	 */
	public function getUniqueRef() {
		return $this->uniqueRef;
	}


	/**
	 * @return array
	 */
	protected function getValidCurrencyArray() {
		return $this->validCurrencyArray;
	}

	private function createUniqueRef() {
		$a = rand( 1, 9 );
		$b = substr( str_shuffle( str_repeat( "ABCDEFG", 1 ) ), 0, 1 );
		$c = $this->getJulienDate( 2 );

		return $this->uniqueRef = (string) $a . $b . $c;
	}

	public function getJulienDate( $plusDays = 0 ) {
		if ( date( 'z' ) >= ( 365 - $plusDays ) ) {
			$plusDays = 0;
		}

		return (string) ( date( 'y' ) . str_pad( (string) ( date( 'z' ) + $plusDays ), 3, '0', STR_PAD_LEFT ) );
	}


	/**
	 * @return Account $payFromAccount
	 */
	public function getPayFromAccount() {
		return $this->payFromAccount;
	}

	/**
	 * @param null $payFromAccount
	 */
	private function setPayFromAccount( Account $payFromAccount ) {
		$this->payFromAccount = $payFromAccount;
	}

	/**
	 * @param null $sortCode
	 * @param null $accountNumber
	 * @param null $name
	 * @param null $paymentAmount
	 * @param null $currency
	 */
	public function addPayment( $sortCode = null, $accountNumber = null, $name = null, $paymentAmount = null, $currency = null ) {

		$payeeAccount = new Account( $sortCode, $accountNumber, $name );
		$errors       = $payeeAccount->getErrors();
		if ( ! $errors && $this->validCurrency( $currency ) ) {
			$currency                      = $this->validCurrency( $currency, true );
			$paymentsObject                = $payeeAccount->getObject();
			$paymentsObject->paymentAmount = $paymentAmount;
			$paymentsObject->currency      = $currency;
			$this->payments[]              = $paymentsObject;
			$this->addPaymentAmount( $paymentAmount, $currency );
		} else {
			$this->addFailedPayment( $errors, $sortCode, $accountNumber, $name, $paymentAmount, $currency );
		}

	}

	/**
	 * @return array|bool
	 */
	public function getPayments() {
		if ( count( $this->payments ) ) {
			return $this->payments;
		}

		return false;
	}

	/**
	 * @param $currency
	 * @param bool $ouputString
	 *
	 * @return bool|null|string
	 */
	public function validCurrency( $currency, $ouputString = false ) {
		if ( $currency === null ) {
			if ( $ouputString ) {
				return $this->getDefaultCurrency();
			}

			return true;
		}
		if ( in_array( $currency, $this->validCurrencyArray ) ) {

			if ( $ouputString ) {
				return $currency;
			}

			return true;
		}
		if ( $ouputString ) {
			return $this->getDefaultCurrency();
		}

		return false;
	}

	public function getDefaultCurrency() {
		return $this->defaultCurrency;
	}

	/**
	 * @param $errors
	 * @param null $sortCode
	 * @param null $accountNumber
	 * @param null $name
	 * @param null $paymentAmount
	 * @param null $currency
	 */
	private function addFailedPayment( $errors, $sortCode = null, $accountNumber = null, $name = null, $paymentAmount = null, $currency = null ) {
		$this->failedPayments[] = array( $errors, $sortCode, $accountNumber, $name, $paymentAmount, $currency );
	}

	/**
	 * @param $str
	 */
	protected function output( $str ) {
		$this->output .= $str;
	}

	public function getOutput() {
		return $this->output;
	}

	/**
	 * @param $name
	 */
	public function outputToCsv( $name ) {
		header( 'Content-Type: text / csv; charset = utf-8' );
		header( 'Content-Disposition: attachment; filename = BACS-' . $name . '-' . date( "Y-m-d-H-i-s" ) . '.csv' );
		$fh = fopen( 'php://output', 'w' );

		fputs( $fh, $this->getOutput() );
		fclose( $fh );
		die();
	}


}