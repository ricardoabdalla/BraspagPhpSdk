<?php

namespace BraspagSdk\Contracts\Pagador;

class DebitCardData
{
    public $CardNumber;

    public $Holder;

    public $ExpirationDate;

    /// Código de segurança impresso no verso do cartão (CVV)
    public $SecurityCode;

    public $Brand;

    public $SaveCard;
}