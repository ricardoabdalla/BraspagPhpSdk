<?php

namespace BraspagSdk\Contracts\CartaoProtegido;

class InvalidateCreditCardRequest extends BaseRequest
{
    public $JustClickKey;

    public $JustClickAlias;
}