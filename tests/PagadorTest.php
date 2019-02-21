<?php

namespace BraspagSdk\Tests;

use BraspagSdk\Contracts\Pagador\AddressData;
use BraspagSdk\Contracts\Pagador\AvsData;
use BraspagSdk\Contracts\Pagador\CreditCardData;
use BraspagSdk\Contracts\Pagador\CustomerData;
use BraspagSdk\Contracts\Pagador\MerchantCredentials;
use BraspagSdk\Contracts\Pagador\PaymentDataRequest;
use BraspagSdk\Contracts\Pagador\TransactionStatus;
use BraspagSdk\Pagador\PagadorClientOptions;
use BraspagSdk\Contracts\Pagador\SaleRequest;
use BraspagSdk\Pagador\PagadorClient;
use PHPUnit\Framework\TestCase;
use function Sodium\add;

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
    public function createSale_forValidCredentials_returnsSaleResponse(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();

        $sut = new PagadorClient($options);
        $response = $sut->CreateSale($request);
        $this->assertEquals(http_response_code(201), $response->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $response->Payment->Status);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function CreateSaleAsync_ForValidCreditCardWithAutomaticCapture_ReturnsPaymentConfirmed(SaleRequest $request, PagadorClientOptions $options)
    {
        $request->MerchantOrderId = uniqid();

        $request->Payment->Capture = true;

        $sut = new PagadorClient($options);
        $response = $sut->CreateSale($request);
        $this->assertEquals(http_response_code(201), $response->HttpStatus);
        $this->assertEquals(TransactionStatus::PaymentConfirmed, $response->Payment->Status);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param SaleRequest $request
     * @param PagadorClientOptions $options
     */
    public function CreateSaleAsync_WithFullCustomerData_ReturnsAuthorized(SaleRequest $request, PagadorClientOptions $options)
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
        $address->Street = "Av. Marechal Camara";
        $address->Number = "160";
        $address->Complement = "sala 934";
        $address->District = "Centro";
        $address->City = "Rio de Janeiro";
        $address->State = "RJ";
        $address->Country = "Brasil";
        $address->ZipCode = "20020-080";

        $request->Customer->DeliveryAddress = $deliveryAddress;

        $request->Customer->Birthdate = "1982-06-30";
        $request->Customer->Mobile = "(55) 11 99999-9999";
        $request->Customer->Phone = "(55) 11 9999-9999";

        $sut = new PagadorClient($options);
        $response = $sut->CreateSale($request);
        $this->assertEquals(http_response_code(201), $response->HttpStatus);
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
    public function CreateSaleAsync_WithAvsAnalysis_ReturnsAuthorized(SaleRequest $request, PagadorClientOptions $options)
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
        $response = $sut->CreateSale($request);
        $this->assertEquals(http_response_code(201), $response->HttpStatus);
        $this->assertEquals(TransactionStatus::Authorized, $response->Payment->Status);
        $this->assertNotNull($response->Payment->CreditCard->Avs);
        $this->assertEquals("S", $response->Payment->CreditCard->Avs->ReturnCode);
        $this->assertEquals(3, $response->Payment->CreditCard->Avs->Status);
    }
}
