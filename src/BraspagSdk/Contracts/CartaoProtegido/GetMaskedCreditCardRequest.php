<?php

namespace BraspagSdk\Contracts\CartaoProtegido;

class GetMaskedCreditCardRequest extends BaseRequest
{
    public $JustClickKey;

    public $JustClickAlias;
}