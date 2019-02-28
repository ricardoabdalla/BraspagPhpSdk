<?php

namespace BraspagSdk\Contracts\CartaoProtegido;

class SaveCreditCardRequest extends BaseRequest
{
    public $CustomerIdentification;

    public $CustomerName;

    public $CardHolder;

    public $CardNumber;

    public $CardExpiration;

    public $JustClickAlias;
}