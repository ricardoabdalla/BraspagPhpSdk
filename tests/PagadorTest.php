<?php

namespace BraspagSdk\Tests;

use BraspagSdk\Common\Environment;
use BraspagSdk\Contracts\Pagador\CreditCardData;
use BraspagSdk\Contracts\Pagador\CustomerData;
use BraspagSdk\Contracts\Pagador\MerchantCredentials;
use BraspagSdk\Contracts\Pagador\PaymentDataRequest;
use BraspagSdk\Pagador\PagadorClientOptions;
use BraspagSdk\Contracts\Pagador\SaleRequest;
use BraspagSdk\Pagador\PagadorClient;
use PHPUnit\Framework\TestCase;

final class PagadorClientTest extends TestCase
{
    /** @test */
    public function createSale_forValidCredentials_returnsSaleResponse()
    {
        $credentials = new MerchantCredentials("33B6AC07-C48D-4F13-A5B9-D3516A378A0C", "d6Rb3OParKvLfzNrURzwcT0f1lzNazS1o19yP6Y8");

        $pagadorClientOptions = new PagadorClientOptions($credentials, Environment::SANDBOX);
        $pagadorClientOptions->Environment = Environment::SANDBOX;

        date_default_timezone_set("America/Sao_Paulo");
        $orderId = date("HisdmY");

        $customer = new CustomerData();
        $customer->Name = "Nome do fulano";
        $customer->Identity = "12312312312";
        $customer->IdentityType = "CPF";

        $card = new CreditCardData();
        $card->CardNumber = "1111222233334444";
        $card->Holder = $customer->Name;
        $card->ExpirationDate = "10/2050";
        $card->SecurityCode = "123";
        $card->Brand = "Visa";

        $payment = new PaymentDataRequest();
        $payment->Provider = "Simulado";
        $payment->Type = "CreditCard";
        $payment->Amount = 100;
        $payment->Installments = 1;
        $payment->CreditCard = $card;

        $saleRequest = new SaleRequest();
        $saleRequest->MerchantOrderId = $orderId;
        $saleRequest->Customer = $customer;
        $saleRequest->Payment = $payment;

        $sut = new PagadorClient($pagadorClientOptions);
        $result = $sut->CreateSale($saleRequest);
        $this->assertEquals(201, $result->HttpStatus);
        $this->assertEquals("Successful", $result->Payment->ReasonMessage);
        $this->assertNotNull($result->MerchantOrderId);
    }
}
