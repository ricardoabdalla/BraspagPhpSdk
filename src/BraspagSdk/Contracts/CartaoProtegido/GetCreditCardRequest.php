<?php

namespace BraspagSdk\Contracts\CartaoProtegido;

class GetCreditCardRequest extends BaseRequest
{
    public $JustClickKey;

    public $JustClickAlias;
}