<?php

namespace Bacs;

/**
 * Class Lloyds
 * @package Bacs
 */
class Lloyds extends Base {
	/**
	 * @var string
	 */
	protected $statementReference = "";
	/**
	 * @var string
	 */
	protected $payeeStatementReference = "";

	/**
	 * Lloyds constructor.
	 *
	 * @param null $payFromSortCode
	 * @param null $payFromAccountNumber
	 * @param null $payFromName
	 * @param null $defaultCurrency
	 * @param string $debitStatementReference
	 * @param string $payeeStatementReference
	 */
	public function __construct(
		$payFromSortCode = null,
		$payFromAccountNumber = null,
		$payFromName = null,
		$defaultCurrency = null,
		$debitStatementReference = "Bacs Payment",
		$payeeStatementReference = "Bacs Payment"
	) {
		parent::__construct( $payFromSortCode, $payFromAccountNumber, $payFromName, $defaultCurrency );
		$this->setPayeeStatementReference( $payeeStatementReference );
		$this->setStatementReference( $debitStatementReference );
	}

	public function execute() {
		$account  = $this->getPayFromAccount();
		$payments = $this->getPayments();
		$date     = new \DateTime();
		$today    = $date->format( "Ymd" );
		$stateRef = $this->getStatementReference();
		$uniqueId = substr( substr( $stateRef, 0, 5 ) . '-' . time(), 0, 16 );
		$this->output( "H,{$today},{$uniqueId}\n" );
		$date->modify( "+ 2 day" );
		$payDate       = $date->format( "Ymd" );
		$accountNumber = $account->getAccountNumber();
		$sortCode      = $account->getSortCode();
		$this->output( "D,{$payDate},{$stateRef},{$sortCode}-{$accountNumber}\n" );
		if ( $payments ) {
			foreach ( $payments as $payment ) {
				$payAmmount       = (string) number_format( $payment->paymentAmount, 2, '.', '' );
				$payName          = $payment->name;
				$payAccountNumber = $payment->accountNumber;
				$paySortCode      = (string) str_replace( '-', '', $payment->formattedSortCode );
				$payRef           = $this->getPayeeStatementReference();
				$this->output( "C,{$payAmmount},{$payName},{$payAccountNumber},{$paySortCode},{$payRef}\n" );
			}
		}
		$this->output( "T" );

		return $this->getOutput();
	}

	/**
	 * @return string
	 */
	public function getStatementReference() {
		return $this->statementReference;
	}

	/**
	 * @param string $statementReference
	 */
	public function setStatementReference( $statementReference ) {
		$this->statementReference = substr( $statementReference, 0, 16 );
	}

	/**
	 * @return string
	 */
	public function getPayeeStatementReference() {
		return $this->payeeStatementReference;
	}

	/**
	 * @param string $payeeStatementReference
	 */
	public function setPayeeStatementReference( $payeeStatementReference ) {
		$this->payeeStatementReference = $payeeStatementReference;
	}

}