<?php

namespace BraspagSdk\Tests;

use BraspagSdk\Contracts\Pagador\AddressData;
use BraspagSdk\Contracts\Pagador\AvsData;
use BraspagSdk\Contracts\Pagador\CreditCardData;
use BraspagSdk\Contracts\Pagador\CustomerData;
use BraspagSdk\Contracts\Pagador\ExternalAuthenticationData;
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
        $saleRequest->Customer = $customer;
        $saleRequest->Payment = $payment;

        return [[$saleRequest, $pagadorClientOptions]];
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function createSale_forValidCredentials_returnsAuthorized(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);
        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $response->Payment->Status);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function createSale_forValidCreditCardWithAutomaticCapture_returnsPaymentConfirmed(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();
        $request->Payment->Capture = true;

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);
        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::PaymentConfirmed, $response->Payment->Status);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function createSale_withFullCustomerData_returnsAuthorized(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();

        $address = new AddressData();
        $address->Street = "Alameda Xingu";
        $address->Number = "512";
        $address->Complement = "27 andar";
        $address->District = "Alphaville";
        $address->City = "Barueri";
        $address->State = "SP";
        $address->Country = "Brasil";
        $address->ZipCode = "06455-030";
        $request->Customer->Address = $address;

        $deliveryAddress = new AddressData();
        $deliveryAddress->Street = "Av. Marechal Camara";
        $deliveryAddress->Number = "160";
        $deliveryAddress->Complement = "sala 934";
        $deliveryAddress->District = "Centro";
        $deliveryAddress->City = "Rio de Janeiro";
        $deliveryAddress->State = "RJ";
        $deliveryAddress->Country = "Brasil";
        $deliveryAddress->ZipCode = "20020-080";
        $request->Customer->DeliveryAddress = $deliveryAddress;

        $request->Customer->Birthdate = "1982-06-30";
        $request->Customer->Mobile = "(55) 11 99999-9999";
        $request->Customer->Phone = "(55) 11 9999-9999";

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);

        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $response->Payment->Status);
        $this->assertNotNull($response->Customer->Address);
        $this->assertNotNull($response->Customer->DeliveryAddress);
        $this->assertEquals("1982-06-30", $response->Customer->Birthdate);
        $this->assertEquals("(55) 11 99999-9999", $response->Customer->Mobile);
        $this->assertEquals("(55) 11 9999-9999", $response->Customer->Phone);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function createSale_withAvsAnalysis_returnsAuthorized(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();

        $avs = new AvsData();
        $avs->Street = "Alameda Xingu";
        $avs->Number = "512";
        $avs->Complement = "27 andar";
        $avs->District = "Alphaville";
        $avs->ZipCode = "04604007";
        $avs->Cpf = "76250252096";
        $request->Payment->CreditCard->Avs = $avs;

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);

        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $response->Payment->Status);
        $this->assertNotNull($response->Payment->CreditCard->Avs);
        $this->assertEquals("S", $response->Payment->CreditCard->Avs->ReturnCode);
        $this->assertEquals(3, $response->Payment->CreditCard->Avs->Status);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function createSale_withExternalAuthentication_returnsAuthorized(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();
        $externalAuthentication = new ExternalAuthenticationData();
        $externalAuthentication->Cavv = "AABBBlCIIgAAAAARJIgiEL0gDoE=";
        $externalAuthentication->Eci = "5";
        $externalAuthentication->Xid = "dnFoU3R4amdpWTJJdzJRVHNhNDZ";
        $request->Payment->ExternalAuthentication = $externalAuthentication;

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);

        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $response->Payment->Status);
        $this->assertNotNull($response->Payment->ExternalAuthentication);
        $this->assertEquals("AABBBlCIIgAAAAARJIgiEL0gDoE=", $response->Payment->ExternalAuthentication->Cavv);
        $this->assertEquals("5", $response->Payment->ExternalAuthentication->Eci);
        $this->assertEquals("dnFoU3R4amdpWTJJdzJRVHNhNDZ", $response->Payment->ExternalAuthentication->Xid);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function createSale_withAuthentication_returnsNotFinished(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();
        $request->Payment->Authenticate = true;
        $request->Payment->ReturnUrl = "http://www.test.com/redirect";

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);

        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::NotFinished, $response->Payment->Status);
        $this->assertNotNull($response->Payment->AuthenticationUrl);
        $this->assertEquals($request->Payment->ReturnUrl, $response->Payment->ReturnUrl);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function createSale_whenCardSaveIsTrue_returnsAuthorized(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();
        $request->Payment->CreditCard->SaveCard = true;

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);

        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $response->Payment->Status);
        $this->assertNotNull($response->Payment->CreditCard->CardToken);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function createSale_usingCardToken_returnsAuthorized(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();
        $request->Payment->CreditCard->Holder = null;
        $request->Payment->CreditCard->CardNumber = null;
        $request->Payment->CreditCard->Brand = null;
        $request->Payment->CreditCard->ExpirationDate = null;
        $request->Payment->CreditCard->CardToken = "283f90e4-1a90-4bf7-829f-d9e8f14215f1";

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);

        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $response->Payment->Status);
    }
}
