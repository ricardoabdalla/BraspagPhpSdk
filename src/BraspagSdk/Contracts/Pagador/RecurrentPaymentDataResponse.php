<?php

namespace BraspagSdk\Contracts\Pagador;


class RecurrentPaymentDataResponse
{
    public $RecurrentPaymentId;

    public $StartDate;

    public $EndDate;

    public $NextRecurrency;

    public $Interval;

    public $AuthorizeNow;

    public $ReasonCode;

    public $ReasonMessage;
}