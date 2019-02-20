<?php

namespace BraspagSdk\Contracts\Pagador;

class CreditCardData
{
    /// Token representativo do cartão no Cartão Protegido
    public $CardToken;

    public $CardNumber;

    public $Holder;

    public $ExpirationDate;

    /// Código de segurança impresso no verso do cartão (CVV)
    public $SecurityCode;

    /// Token obtido através do Silent Order Post
    public $PaymentToken;

    public $Brand;

    public $SaveCard;

    public $Alias;

    public $Avs;

    /**
     * CreditCardData constructor.
     */
    public function __construct()
    {
        $this->Avs = new AvsData();
    }
}