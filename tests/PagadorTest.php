<?php

namespace BraspagSdk\Tests;

use BraspagSdk\Contracts\Pagador\CreditCardData;
use BraspagSdk\Contracts\Pagador\CustomerData;
use BraspagSdk\Contracts\Pagador\MerchantCredentials;
use BraspagSdk\Contracts\Pagador\PaymentDataRequest;
use BraspagSdk\Contracts\Pagador\TransactionStatus;
use BraspagSdk\Pagador\PagadorClientOptions;
use BraspagSdk\Contracts\Pagador\SaleRequest;
use BraspagSdk\Pagador\PagadorClient;
use PHPUnit\Framework\TestCase;

final class PagadorClientTest extends TestCase
{
    public function dataProvider()
    {
        $credentials = new MerchantCredentials("33B6AC07-C48D-4F13-A5B9-D3516A378A0C", "d6Rb3OParKvLfzNrURzwcT0f1lzNazS1o19yP6Y8");

        $pagadorClientOptions = new PagadorClientOptions($credentials);

        date_default_timezone_set("America/Sao_Paulo");
        $orderId = date("HisudmY");

        $customer = new CustomerData();
        $customer->Name = "Bjorn Ironside";
        $customer->Identity = "762.502.520-96";
        $customer->IdentityType = "CPF";
        $customer->Email = "bjorn.ironside@vikings.com.br";

        $card = new CreditCardData();
        $card->CardNumber = "4485623136297301";
        $card->Holder = "BJORN IRONSIDE";
        $card->ExpirationDate = "12/2025";
        $card->SecurityCode = "123";
        $card->Brand = "visa";

        $payment = new PaymentDataRequest();
        $payment->Provider = "Simulado";
        $payment->Type = "CreditCard";
        $payment->Currency = "BRL";
        $payment->Country = "BRA";
        $payment->Amount = 1000;
        $payment->Installments = 1;
        $payment->SoftDescriptor = "Braspag SDK";
        $payment->Capture = false;
        $payment->Authenticate = false;
        $payment->Recurrent = false;
        $payment->Credentials = null;
        $payment->Assignor = null;
        $payment->DebitCard = null;
        $payment->FraudAnalysis = null;
        $payment->ExternalAuthentication = null;
        $payment->Wallet = null;
        $payment->RecurrentPayment = null;
        $payment->ExternalAuthentication = null;
        $payment->ReturnUrl = null;

        $payment->CreditCard = $card;

        $saleRequest = new SaleRequest();
        $saleRequest->MerchantOrderId = $orderId;
        $saleRequest->Customer = $customer;
        $saleRequest->Payment = $payment;

        return [[$saleRequest, $pagadorClientOptions]];
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
//    /** @test */
    public function createSale_forValidCredentials_returnsSaleResponse(SaleRequest $request, PagadorClientOptions $options)
    {
        $sut = new PagadorClient($options);
        $result = $sut->CreateSale($request);
        $this->assertEquals(http_response_code(201), $result->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $result->Payment->ReasonMessage);
    }
}
