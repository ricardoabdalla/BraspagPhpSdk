<?php

namespace BraspagSdk\Contracts\Velocity;

class AnalysisRequest
{
    public $Transaction;

    public $Card;

    public $Customer;

    /**
     * AnalysisRequest constructor.
     */
    public function __construct()
    {
        $this->Transaction = new TransactionData();
        $this->Card = new CardData();
        $this->Customer = new CustomerData();
    }
}