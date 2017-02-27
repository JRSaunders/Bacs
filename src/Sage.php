<?php
namespace Bacs;

/**
 * Class SageImport
 * @package Bacs
 */
class Sage extends Base
{


    public function __construct($payFromSortCode = null, $payFromAccountNumber = null, $payFromName = null, $defaultCurrency = null)
    {
        parent::__construct($payFromSortCode, $payFromAccountNumber, $payFromName, $defaultCurrency);
    }

    protected function formatPaymentAmount($amount = 0, $length = 13)
    {

        $number = round($amount * 100);
        return $this->fillNumber($number, $length);
    }

    public function fillName($name)
    {
        return str_pad($name, 36, ' ', STR_PAD_RIGHT);
    }

    public function execute()
    {
        $payments = $this->getPayments();
        $this->output('UHL' . $this->getUniqueRef() . "\n");
        $namespace = '';

        if ($payments) {
            $payFrom = $this->getPayFromAccount()->getObject();
            $totalPayeeCount = count($this->getPayments());
            foreach ($payments as $payment) {

                $this->output(
                    $payment->sortCode .
                    $payment->accountNumber .
                    '000' .
                    $payFrom->sortCode .
                    $payFrom->accountNumber .
                    '00' .
                    $this->formatPaymentAmount($payment->paymentAmount) .
                    $this->fillName($payFrom->name) .
                    $namespace .
                    $payment->name .
                    "\n"
                );
            }


            $this->output(
                $payFrom->sortCode .
                $payFrom->accountNumber .
                '000' .
                $payFrom->sortCode .
                $payFrom->accountNumber .
                '00' .
                $this->formatPaymentAmount($this->getTotalPaymentAmount()) .
                $payFrom->name .

                'CONTRA' .
                "\n"
            );

            $this->output(
                'UTL1' .
                $this->formatPaymentAmount($this->getTotalPaymentAmount()) .
                $this->formatPaymentAmount($this->getTotalPaymentAmount()) .
                '0000001' .
                $this->fillNumber($totalPayeeCount, 6)
            );
        }

        return $this->getOutput();


    }

}