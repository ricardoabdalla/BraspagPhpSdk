<?php

namespace BraspagSdk\Contracts\Pagador;

use phpDocumentor\Reflection\Types\Array_;

class PaymentDataRequest
{
    public $Provider;

    public $Type;

    public $Amount;
    
    public $ServiceTaxAmount;

    public $Capture;

    public $Installments;

    public $Interest;

    public $Currency;

    public $Country;

    public $Authenticate;

    public $Recurrent;

    public $SoftDescriptor;

    public $ReturnUrl;

    public $BoletoNumber;

    public $Assignor;

    public $Demonstrative;

    /// Data para vencimento do boleto. Formato: AAAA-MM-DD
    public $ExpirationDate;

    /// CNPJ do Cedente
    public $Identification;

    public $Instructions;

    public $DaysToFine;

    public $FineRate;

    public $FineAmount;

    public $DaysToInterest;

    public $InterestRate;

    public $InterestAmount;

    public $CreditCard;

    public $DebitCard;

    public $Wallet;

    public $Credentials;

    public $ExternalAuthentication;

    public $FraudAnalysis;

    public $RecurrentPayment;

    public $ExtraDataCollection;

    public function __construct()
    {
        $this->CreditCard = new CreditCardData();
        $this->DebitCard = new DebitCardData();
        $this->Wallet = new WalletData();
        $this->Credentials = new CredentialsData();
        $this->ExternalAuthentication = new ExternalAuthenticationData();
        $this->FraudAnalysis = new FraudAnalysisRequestData();
        $this->RecurrentPayment = new RecurrentPaymentDataRequest();
        //$this->ExtraDataCollection = new ExtraData();
    }
}