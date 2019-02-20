<?php

namespace BraspagSdk\Contracts\Pagador;

class RecurrentPaymentDataRequest
{
    public $StartDate;

    public $EndDate;

    public $Interval;

    public $AuthorizeNow;
}