<?php

namespace BraspagSdk\Contracts\Pagador;

class RecurrentPaymentData
{
    public $Installments;
    
    public $RecurrentPaymentId;
    
    public $NextRecurrency;
    
    public $StartDate;
    
    public $EndDate;
    
    public $Interval;
    
    public $Amount;
    
    public $Country;
    
    public $CreateDate;
    
    public $Currency;
    
    public $CurrentRecurrencyTry;
    
    public $OrderNumber;
    
    public $Provider;
    
    public $RecurrencyDay;
    
    public $SuccessfulRecurrences;
    
    public $Status;

    // List<RecurrentTransactionData>
    public $RecurrentTransactions;

    public function __construct()
    {
        $this->RecurrentTransactions = array();
    }
}