<?php

namespace BraspagSdk\Contracts\Pagador;

class PaymentDataResponse
{
    /// Id da transação no provedor do meio de pagamento
    public $AcquirerTransactionId;

    /// Valor da transação em centavos
    public $Amount;

    public $Address;

    /// URL para qual o Lojista deve redirecionar o Cliente para o fluxo de autenticação
    public $AuthenticationUrl;

    public $Assignor;

    /// Código de autorização da transação
    public $AuthorizationCode;

    public $BarCodeNumber;

    public $BoletoNumber;

    public $CapturedAmount;

    public $CapturedDate;

    public $Country;

    // CredentialsData
    public $Credentials;

    // CreditCardData
    public $CreditCard;

    public $Currency;

    public $DaysToFine;

    public $DaysToInterest;

    // DebitCardData
    public $DebitCard;

    public $Demonstrative;

    public $DigitableLine;

    public $Eci;

    public $ExpirationDate;

    // ExternalAuthenticationData
    public $ExternalAuthentication;

    // List<ExtraData>
    public $ExtraDataCollection;

    public $FineAmount;

    public $FineRate;

    // FraudAnalysisRequestData
    public $FraudAnalysis;

    public $Identification;

    /// Número de parcelas da transação
    public $Installments;

    public $Instructions;

    public $Interest;

    public $InterestAmount;

    public $InterestRate;

    // List<LinkData>
    public $Links;

    /// ID da transação na Braspag
    public $PaymentId;

    /// Número do Comprovante de Venda
    public $ProofOfSale;

    /// Nome do provedor do meio de pagamento
    public $Provider;

    /// Código retornado pelo provedor do meio de pagamento (adquirente e bancos)
    public $ProviderReturnCode;

    /// Mensagem retornada pelo provedor do meio de pagamento (adquirente e bancos)
    public $ProviderReturnMessage;

    /// Código de retorno da Operação
    public $ReasonCode;

    /// Mensagem de retorno da Operação
    public $ReasonMessage;

    /// Data em que a transação foi recebida pela Braspag
    public $ReceivedDate;

    public $Recurrent;

    // RecurrentPaymentDataResponse
    public $RecurrentPayment;

    public $ReturnUrl;

    public $ServiceTaxAmount;

    public $SoftDescriptor;

    /// Status da Transação
    /// TransactionStatus
    public $Status;

    /// Tipo do Meio de Pagamento (CreditCard, DebitCard)
    public $Type;

    /// ID da transação na adquirente (Wallets)
    public $Tid;

    public $Url;

    // WalletData
    public $Wallet;
}