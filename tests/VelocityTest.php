<?php

namespace BraspagSdk\Tests;

use BraspagSdk\BraspagAuth\BraspagAuthClient;
use BraspagSdk\Common\ClientOptions;
use BraspagSdk\Common\Environment;
use BraspagSdk\Common\OAuthGrantType;
use BraspagSdk\Contracts\BraspagAuth\AccessTokenRequest;
use BraspagSdk\Contracts\Velocity\AddressData;
use BraspagSdk\Contracts\Velocity\AnalysisRequest;
use BraspagSdk\Contracts\Velocity\CardData;
use BraspagSdk\Contracts\Velocity\CustomerData;
use BraspagSdk\Contracts\Velocity\MerchantCredentials;
use BraspagSdk\Contracts\Velocity\PhoneData;
use BraspagSdk\Contracts\Velocity\TransactionData;
use BraspagSdk\Velocity\VelocityClient;
use BraspagSdk\Velocity\VelocityClientOptions;
use PHPUnit\Framework\TestCase;

final class VelocityTest extends TestCase
{
    public function dataProvider()
    {
        $transaction = new TransactionData();
        $transaction->OrderId = uniqid();
        $transaction->Date = date('Y-m-d H:i:s');
        $transaction->Amount = 1000;

        $card = new CardData();
        $card->Holder = "BJORN IRONSIDE";
        $card->Brand = "visa";
        $card->Number = "1000100010001000";
        $card->Expiration = "10/2025";

        $customer = new CustomerData();
        $customer->Name = "Bjorn Ironside";
        $customer->Identity = "76250252096";
        $customer->IpAddress = "127.0.0.1";
        $customer->Email = "bjorn.ironside@vikings.com.br";
        $customer->BirthDate = "1982-06-30";

        $phone = new PhoneData();
        $phone->Type = "Cellphone";
        $phone->Number = "999999999";
        $phone->DDI = "55";
        $phone->DDD = "11";
        array_push($customer->Phones, $phone);

        $billing = new AddressData();
        $billing->Street = "Alameda Xingu";
        $billing->Number = "512";
        $billing->Neighborhood = "Alphaville";
        $billing->City = "Barueri";
        $billing->State = "SP";
        $billing->Country = "BR";
        $billing->ZipCode = "06455-030";

        $customer->Billing = $billing;

        $request = new AnalysisRequest();
        $request->Transaction = $transaction;
        $request->Card = $card;
        $request->Customer = $customer;
        return [[$request]];
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param AnalysisRequest $request
     */
    public function performAnalysis_forValidRequest_returnsCreated(AnalysisRequest $request)
    {
        $authRequest = new AccessTokenRequest();
        $authRequest->GrantType = OAuthGrantType::ClientCredentials;
        $authRequest->ClientId = "5d85902e-592a-44a9-80bb-bdda74d51bce";
        $authRequest->ClientSecret = "mddRzd6FqXujNLygC/KxOfhOiVhlUr2kjKPsOoYHwhQ=";
        $authRequest->Scope = "VelocityApp";

        $clientOptions = new ClientOptions();
        $clientOptions->Environment = Environment::SANDBOX;
        $authClient = new BraspagAuthClient($clientOptions);
        $authResponse = $authClient->createAccessToken($authRequest);

        $this->assertEquals(200, $authResponse->HttpStatus);

        $credentials = new MerchantCredentials("94E5EA52-79B0-7DBA-1867-BE7B081EDD97", $authResponse->Token);
        $options = new VelocityClientOptions($credentials);

        $sut = new VelocityClient($options);
        $response = $sut->performAnalysis($request);

        $this->assertEquals(201, $response->HttpStatus);
        $this->assertNotNull($response->AnalysisResult);
        $this->assertEquals("Reject", $response->AnalysisResult->Status);
        $this->assertNotNull($response->Transaction);
        $this->assertNotNull($response->RequestId);
        $this->assertNull($response->ErrorDataCollection);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param AnalysisRequest $request
     */
    public function performAnalysis_forInvalidRequest_returnsBadRequest(AnalysisRequest $request)
    {
        $request->Transaction = null;
        $request->Card = null;
        $authRequest = new AccessTokenRequest();
        $authRequest->GrantType = OAuthGrantType::ClientCredentials;
        $authRequest->ClientId = "5d85902e-592a-44a9-80bb-bdda74d51bce";
        $authRequest->ClientSecret = "mddRzd6FqXujNLygC/KxOfhOiVhlUr2kjKPsOoYHwhQ=";
        $authRequest->Scope = "VelocityApp";

        $clientOptions = new ClientOptions();
        $clientOptions->Environment = Environment::SANDBOX;
        $authClient = new BraspagAuthClient($clientOptions);
        $authResponse = $authClient->createAccessToken($authRequest);

        $this->assertEquals(200, $authResponse->HttpStatus);

        $credentials = new MerchantCredentials("94E5EA52-79B0-7DBA-1867-BE7B081EDD97", $authResponse->Token);
        $options = new VelocityClientOptions($credentials);

        $sut = new VelocityClient($options);
        $response = $sut->performAnalysis($request);

        $this->assertEquals(400, $response->HttpStatus);
        $this->assertNull($response->AnalysisResult);
        $this->assertNull($response->Transaction);
        $this->assertNull($response->RequestId);
        $this->assertNotEmpty($response->ErrorDataCollection);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param AnalysisRequest $request
     */
    public function performAnalysis_forInvalidAccessToken_returnsCreated(AnalysisRequest $request)
    {
        $credentials = new MerchantCredentials("94E5EA52-79B0-7DBA-1867-BE7B081EDD97", "invalid_token");
        $options = new VelocityClientOptions($credentials);

        $sut = new VelocityClient($options);
        $response = $sut->performAnalysis($request);

        $this->assertEquals(401, $response->HttpStatus);
        $this->assertNull($response->AnalysisResult);
        $this->assertNull($response->Transaction);
        $this->assertNull($response->RequestId);
        $this->assertNotEmpty($response->ErrorDataCollection);
    }
}