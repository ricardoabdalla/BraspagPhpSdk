<?php

namespace BraspagSdk\Tests;

use BraspagSdk\Common\Endpoints;
use BraspagSdk\Common\Environment;
use BraspagSdk\Contracts\Pagador\AddressData;
use BraspagSdk\Contracts\Pagador\AvsData;
use BraspagSdk\Contracts\Pagador\CaptureRequest;
use BraspagSdk\Contracts\Pagador\CreditCardData;
use BraspagSdk\Contracts\Pagador\CustomerData;
use BraspagSdk\Contracts\Pagador\DebitCardData;
use BraspagSdk\Contracts\Pagador\ExternalAuthenticationData;
use BraspagSdk\Contracts\Pagador\MerchantCredentials;
use BraspagSdk\Contracts\Pagador\PaymentDataRequest;
use BraspagSdk\Contracts\Pagador\RecurrencyInterval;
use BraspagSdk\Contracts\Pagador\RecurrentPaymentDataRequest;
use BraspagSdk\Contracts\Pagador\TransactionStatus;
use BraspagSdk\Contracts\Pagador\VoidRequest;
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

    #region CreateSale

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
    public function createSale_forInvalidCredentials_returns401(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();

        $options->Credentials->MerchantId = "99999999-9999-9999-9999-999999999999";
        $options->Credentials->MerchantKey = "9999999999999999999999999999999999999999";

        $sut = new PagadorClient($options);

        $response = $sut->createSale($request);
        $this->assertEquals(401, $response->HttpStatus);
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

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function createSale_usingDebitCard_returnsNotFinished(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();

        $debitCard = new DebitCardData();
        $debitCard->CardNumber = "4551870000000181";
        $debitCard->Holder = "BJORN IRONSIDE";
        $debitCard->ExpirationDate = "12/2025";
        $debitCard->SecurityCode = "123";
        $debitCard->Brand = "Visa";

        $request->Payment->DebitCard = $debitCard;
        $request->Payment->Type = "DebitCard";
        $request->Payment->Authenticate = true;
        $request->Payment->ReturnUrl = "http://www.test.com/redirect";

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);

        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::NotFinished, $response->Payment->Status);
        $this->assertNotNull($response->Payment->DebitCard);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function createSale_usingRegisteredBoleto_returnsAuthorized(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();

        $request->Payment->Type = "Boleto";
        $request->Payment->BoletoNumber = "2017091101";
        $request->Payment->Assignor = "Braspag";
        $request->Payment->Demonstrative = "Texto demonstrativo";
        $request->Payment->ExpirationDate = date("Y-m-d");
        $request->Payment->Identification = "11017523000167";
        $request->Payment->Instructions = "Aceitar somente até a data de vencimento.";

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);

        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $response->Payment->Status);
        $this->assertNotNull($response->Payment->Assignor);
        $this->assertNotNull($response->Payment->Address);
        $this->assertNotNull($response->Payment->BarCodeNumber);
        $this->assertNotNull($response->Payment->BoletoNumber);
        $this->assertNotNull($response->Payment->Demonstrative);
        $this->assertNotNull($response->Payment->DigitableLine);
        $this->assertNotNull($response->Payment->ExpirationDate);
        $this->assertNotNull($response->Payment->Identification);
        $this->assertNotNull($response->Payment->Instructions);
        $this->assertNotNull($response->Payment->Url);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function createSale_usingRecurrentPayment_returnsAuthorized(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();

        $recurrentPayment = new RecurrentPaymentDataRequest();
        $recurrentPayment->AuthorizeNow = true;
        $recurrentPayment->EndDate = date('Y-m-d', strtotime("+3 months", strtotime(date("Y-m-d"))));
        $recurrentPayment->Interval = "Monthly";

        $request->Payment->RecurrentPayment = $recurrentPayment;

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);

        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $response->Payment->Status);
        $this->assertNotNull($response->Payment->RecurrentPayment);
        $this->assertNotNull($response->Payment->RecurrentPayment->RecurrentPaymentId);
        $this->assertNotNull($response->Payment->RecurrentPayment->NextRecurrency);
        $this->assertNotNull($response->Payment->RecurrentPayment->Interval);
        $this->assertNotNull($response->Payment->RecurrentPayment->EndDate);
    }

    #endregion

    #region MultiStep_Tests

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function createSale_thenCapture_thenVoid_thenGet(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);

        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $response->Payment->Status);

        $captureRequest = new CaptureRequest();
        $captureRequest->PaymentId = $response->Payment->PaymentId;
        $captureRequest->Amount = $response->Payment->Amount;

        $captureResponse = $sut->capture($captureRequest);

        $this->assertEquals(200, $captureResponse->HttpStatus);
        $this->assertEquals(TransactionStatus::PaymentConfirmed, $captureResponse->Status);

        $voidRequest = new VoidRequest();
        $voidRequest->PaymentId = $response->Payment->PaymentId;
        $voidRequest->Amount = $response->Payment->Amount;

        $voidResponse = $sut->void($voidRequest);

        $this->assertEquals(200, $voidResponse->HttpStatus);
        $this->assertEquals(TransactionStatus::Voided, $voidResponse->Status);

        $getResponse = $sut->get($response->Payment->PaymentId);

        $this->assertEquals(200, $getResponse->HttpStatus);
        $this->assertNotNull($getResponse->MerchantOrderId);
        $this->assertNotNull($getResponse->Customer);
        $this->assertNotNull($getResponse->Payment);
        $this->assertEquals(TransactionStatus::Voided, $getResponse->Payment->Status);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function createSale_thenGetByOrderId(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);

        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $response->Payment->Status);

        $getResponse = $sut->getByOrderId($response->MerchantOrderId);

        $this->assertEquals(200, $getResponse->HttpStatus);
        $this->assertNotEmpty($getResponse->Payments);
        $this->assertNotNull($getResponse->Payments[0]->PaymentId);
    }

    #endregion

    #region Recurrent

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function changeRecurrencyCustomer_returnsOk(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();

        $recurrentPayment = new RecurrentPaymentDataRequest();
        $recurrentPayment->AuthorizeNow = true;
        $recurrentPayment->EndDate = date('Y-m-d', strtotime("+3 months", strtotime(date("Y-m-d"))));
        $recurrentPayment->Interval = "Monthly";

        $request->Payment->RecurrentPayment = $recurrentPayment;

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);

        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $response->Payment->Status);
        $this->assertNotNull($response->Payment->RecurrentPayment);
        $this->assertNotNull($response->Payment->RecurrentPayment->RecurrentPaymentId);
        $this->assertNotNull($response->Payment->RecurrentPayment->NextRecurrency);
        $this->assertNotNull($response->Payment->RecurrentPayment->Interval);
        $this->assertNotNull($response->Payment->RecurrentPayment->EndDate);

        $customer = new CustomerData();
        $customer->Name = "Ragnar Lothbrok";
        $customer->Email = "ragnar.lothbrok@vikings.com.br";
        $customer->IdentityType = "CPF";
        $customer->Identity = "637.952.420-70";

        $recurrentResponse = $sut->changeRecurrencyCustomer($response->Payment->RecurrentPayment->RecurrentPaymentId, $customer);

        $this->assertEquals(200, $recurrentResponse);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function changeRecurrencyEndDate_returnsOk(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();

        $recurrentPayment = new RecurrentPaymentDataRequest();
        $recurrentPayment->AuthorizeNow = true;
        $recurrentPayment->EndDate = date('Y-m-d', strtotime("+3 months", strtotime(date("Y-m-d"))));
        $recurrentPayment->Interval = "Monthly";

        $request->Payment->RecurrentPayment = $recurrentPayment;

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);

        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $response->Payment->Status);
        $this->assertNotNull($response->Payment->RecurrentPayment);
        $this->assertNotNull($response->Payment->RecurrentPayment->RecurrentPaymentId);
        $this->assertNotNull($response->Payment->RecurrentPayment->NextRecurrency);
        $this->assertNotNull($response->Payment->RecurrentPayment->Interval);
        $this->assertNotNull($response->Payment->RecurrentPayment->EndDate);

        $endDate = date('Y-m-d', strtotime("+3 months", strtotime(date("Y-m-d"))));

        $recurrentResponse = $sut->changeRecurrencyEndDate($response->Payment->RecurrentPayment->RecurrentPaymentId, $endDate);

        $this->assertEquals(200, $recurrentResponse);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function changeRecurrencyInterval_returnsOk(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();

        $recurrentPayment = new RecurrentPaymentDataRequest();
        $recurrentPayment->AuthorizeNow = true;
        $recurrentPayment->EndDate = date('Y-m-d', strtotime("+3 months", strtotime(date("Y-m-d"))));
        $recurrentPayment->Interval = "Monthly";

        $request->Payment->RecurrentPayment = $recurrentPayment;

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);

        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $response->Payment->Status);
        $this->assertNotNull($response->Payment->RecurrentPayment);
        $this->assertNotNull($response->Payment->RecurrentPayment->RecurrentPaymentId);
        $this->assertNotNull($response->Payment->RecurrentPayment->NextRecurrency);
        $this->assertNotNull($response->Payment->RecurrentPayment->Interval);
        $this->assertNotNull($response->Payment->RecurrentPayment->EndDate);

        $recurrentResponse = $sut->changeRecurrencyInterval($response->Payment->RecurrentPayment->RecurrentPaymentId, RecurrencyInterval::Quarterly);

        $this->assertEquals(200, $recurrentResponse);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function changeRecurrencyDay_ReturnsOk(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();

        $recurrentPayment = new RecurrentPaymentDataRequest();
        $recurrentPayment->AuthorizeNow = true;
        $recurrentPayment->EndDate = date('Y-m-d', strtotime("+3 months", strtotime(date("Y-m-d"))));
        $recurrentPayment->Interval = "Monthly";

        $request->Payment->RecurrentPayment = $recurrentPayment;

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);

        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $response->Payment->Status);
        $this->assertNotNull($response->Payment->RecurrentPayment);
        $this->assertNotNull($response->Payment->RecurrentPayment->RecurrentPaymentId);
        $this->assertNotNull($response->Payment->RecurrentPayment->NextRecurrency);
        $this->assertNotNull($response->Payment->RecurrentPayment->Interval);
        $this->assertNotNull($response->Payment->RecurrentPayment->EndDate);

        $recurrentResponse = $sut->changeRecurrencyDay($response->Payment->RecurrentPayment->RecurrentPaymentId, 10);

        $this->assertEquals(200, $recurrentResponse);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function changeRecurrencyAmount_ReturnsOk(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();

        $recurrentPayment = new RecurrentPaymentDataRequest();
        $recurrentPayment->AuthorizeNow = true;
        $recurrentPayment->EndDate = date('Y-m-d', strtotime("+3 months", strtotime(date("Y-m-d"))));
        $recurrentPayment->Interval = "Monthly";

        $request->Payment->RecurrentPayment = $recurrentPayment;

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);

        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $response->Payment->Status);
        $this->assertNotNull($response->Payment->RecurrentPayment);
        $this->assertNotNull($response->Payment->RecurrentPayment->RecurrentPaymentId);
        $this->assertNotNull($response->Payment->RecurrentPayment->NextRecurrency);
        $this->assertNotNull($response->Payment->RecurrentPayment->Interval);
        $this->assertNotNull($response->Payment->RecurrentPayment->EndDate);

        $recurrentResponse = $sut->changeRecurrencyAmount($response->Payment->RecurrentPayment->RecurrentPaymentId, 15000);

        $this->assertEquals(200, $recurrentResponse);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function changeRecurrencyNextPaymentDate_ReturnsOk(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();

        $recurrentPayment = new RecurrentPaymentDataRequest();
        $recurrentPayment->AuthorizeNow = true;
        $recurrentPayment->EndDate = date('Y-m-d', strtotime("+3 months", strtotime(date("Y-m-d"))));
        $recurrentPayment->Interval = "Monthly";

        $request->Payment->RecurrentPayment = $recurrentPayment;

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);

        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $response->Payment->Status);
        $this->assertNotNull($response->Payment->RecurrentPayment);
        $this->assertNotNull($response->Payment->RecurrentPayment->RecurrentPaymentId);
        $this->assertNotNull($response->Payment->RecurrentPayment->NextRecurrency);
        $this->assertNotNull($response->Payment->RecurrentPayment->Interval);
        $this->assertNotNull($response->Payment->RecurrentPayment->EndDate);

        $recurrentResponse = $sut->changeRecurrencyNextPaymentDate($response->Payment->RecurrentPayment->RecurrentPaymentId, date('Y-m-d', strtotime("+1 months", strtotime(date("Y-m-d")))));

        $this->assertEquals(200, $recurrentResponse);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function changeRecurrencyPayment_ReturnsOk(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();

        $recurrentPayment = new RecurrentPaymentDataRequest();
        $recurrentPayment->AuthorizeNow = true;
        $recurrentPayment->EndDate = date('Y-m-d', strtotime("+3 months", strtotime(date("Y-m-d"))));
        $recurrentPayment->Interval = "Monthly";

        $request->Payment->RecurrentPayment = $recurrentPayment;

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);

        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $response->Payment->Status);
        $this->assertNotNull($response->Payment->RecurrentPayment);
        $this->assertNotNull($response->Payment->RecurrentPayment->RecurrentPaymentId);
        $this->assertNotNull($response->Payment->RecurrentPayment->NextRecurrency);
        $this->assertNotNull($response->Payment->RecurrentPayment->Interval);
        $this->assertNotNull($response->Payment->RecurrentPayment->EndDate);

        $card = new CreditCardData();
        $card->CardNumber = "1000100010001001";
        $card->Holder = "BJORN IRONSIDE";
        $card->ExpirationDate = "12/2021";
        $card->Brand = "Master";

        $payment = new PaymentDataRequest();
        $payment->Amount = 1000;
        $payment->Provider = "Simulado";
        $payment->Type = "CreditCard";
        $payment->Currency = "BRL";
        $payment->Country = "BRA";
        $payment->Installments = 1;
        $payment->SoftDescriptor = "Braspag SDK";
        $payment->CreditCard = $card;

        $recurrentResponse = $sut->changeRecurrencyPayment($response->Payment->RecurrentPayment->RecurrentPaymentId, $payment);

        $this->assertEquals(200, $recurrentResponse);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function deactivateRecurrency_ReturnsOk(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();

        $recurrentPayment = new RecurrentPaymentDataRequest();
        $recurrentPayment->AuthorizeNow = true;
        $recurrentPayment->EndDate = date('Y-m-d', strtotime("+3 months", strtotime(date("Y-m-d"))));
        $recurrentPayment->Interval = "Monthly";

        $request->Payment->RecurrentPayment = $recurrentPayment;

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);

        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $response->Payment->Status);
        $this->assertNotNull($response->Payment->RecurrentPayment);
        $this->assertNotNull($response->Payment->RecurrentPayment->RecurrentPaymentId);
        $this->assertNotNull($response->Payment->RecurrentPayment->NextRecurrency);
        $this->assertNotNull($response->Payment->RecurrentPayment->Interval);
        $this->assertNotNull($response->Payment->RecurrentPayment->EndDate);

        $recurrentResponse = $sut->deactivateRecurrency($response->Payment->RecurrentPayment->RecurrentPaymentId);

        $this->assertEquals(200, $recurrentResponse);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function reactivateRecurrency_ReturnsOk(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();

        $recurrentPayment = new RecurrentPaymentDataRequest();
        $recurrentPayment->AuthorizeNow = true;
        $recurrentPayment->EndDate = date('Y-m-d', strtotime("+3 months", strtotime(date("Y-m-d"))));
        $recurrentPayment->Interval = "Monthly";

        $request->Payment->RecurrentPayment = $recurrentPayment;

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);

        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $response->Payment->Status);
        $this->assertNotNull($response->Payment->RecurrentPayment);
        $this->assertNotNull($response->Payment->RecurrentPayment->RecurrentPaymentId);
        $this->assertNotNull($response->Payment->RecurrentPayment->NextRecurrency);
        $this->assertNotNull($response->Payment->RecurrentPayment->Interval);
        $this->assertNotNull($response->Payment->RecurrentPayment->EndDate);

        $recurrentResponse = $sut->reactivateRecurrency($response->Payment->RecurrentPayment->RecurrentPaymentId);

        $this->assertEquals(200, $recurrentResponse);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function getRecurrency_ReturnsOk(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();

        $recurrentPayment = new RecurrentPaymentDataRequest();
        $recurrentPayment->AuthorizeNow = true;
        $recurrentPayment->EndDate = date('Y-m-d', strtotime("+3 months", strtotime(date("Y-m-d"))));
        $recurrentPayment->Interval = "Monthly";

        $request->Payment->RecurrentPayment = $recurrentPayment;

        $sut = new PagadorClient($options);
        $response = $sut->createSale($request);

        $this->assertEquals(201, $response->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $response->Payment->Status);
        $this->assertNotNull($response->Payment->RecurrentPayment);
        $this->assertNotNull($response->Payment->RecurrentPayment->RecurrentPaymentId);
        $this->assertNotNull($response->Payment->RecurrentPayment->NextRecurrency);
        $this->assertNotNull($response->Payment->RecurrentPayment->Interval);
        $this->assertNotNull($response->Payment->RecurrentPayment->EndDate);

        $recurrentResponse = $sut->getRecurrency($response->Payment->RecurrentPayment->RecurrentPaymentId);

        $this->assertEquals(200, $recurrentResponse->HttpStatus);
        $this->assertNotNull($recurrentResponse->Customer);
        $this->assertNotNull($recurrentResponse->RecurrentPayment);
        $this->assertNotEmpty($recurrentResponse->RecurrentPayment->RecurrentTransactions);
    }

    #endregion

    #region General Tests

    /**
     * @test
     */
    public function constructor_whenEnvironmentIsProduction_returnsProductionUrls()
    {
        $credentials = new MerchantCredentials("33B6AC07-C48D-4F13-A5B9-D3516A378A0C", "d6Rb3OParKvLfzNrURzwcT0f1lzNazS1o19yP6Y8");
        $options = new PagadorClientOptions($credentials, Environment::PRODUCTION);

        $sut = new PagadorClient($options);

        $this->assertEquals(Endpoints::PagadorApiProduction, $sut->getUrl());
        $this->assertEquals(Endpoints::PagadorQueryApiProduction, $sut->getQueryUrl());
    }

    /**
     * @test
     */
    public function constructor_whenEnvironmentIsSandbox_returnsSandboxUrls()
    {
        $credentials = new MerchantCredentials("33B6AC07-C48D-4F13-A5B9-D3516A378A0C", "d6Rb3OParKvLfzNrURzwcT0f1lzNazS1o19yP6Y8");
        $options = new PagadorClientOptions($credentials, Environment::SANDBOX);

        $sut = new PagadorClient($options);

        $this->assertEquals(Endpoints::PagadorApiSandbox, $sut->getUrl());
        $this->assertEquals(Endpoints::PagadorQueryApiSandbox, $sut->getQueryUrl());
    }

    #endregion
}
