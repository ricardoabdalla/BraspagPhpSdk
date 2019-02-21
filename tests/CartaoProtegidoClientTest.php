<?php

namespace BraspagSdk\Tests;

use BraspagSdk\CartaoProtegido\CartaoProtegidoClient;
use BraspagSdk\CartaoProtegido\CartaoProtegidoClientOptions;
use BraspagSdk\Common\Environment;
use BraspagSdk\Common\Utilities;
use BraspagSdk\Contracts\CartaoProtegido\GetCreditCardRequest;
use BraspagSdk\Contracts\CartaoProtegido\GetMaskedCreditCardRequest;
use BraspagSdk\Contracts\CartaoProtegido\MerchantCredentials;
use BraspagSdk\Contracts\CartaoProtegido\SaveCreditCardRequest;
use PHPUnit\Framework\TestCase;

final class CartaoProtegidoClientTest extends TestCase
{
    /** @test */
    public function getCreditCardAsync_forValidToken_returnsCardData()
    {
        $request = new GetCreditCardRequest();
        $request->JustClickKey = "1ff03ed9-5f56-4ac6-bfb8-23b7a1aa55a7";
        $request->RequestId = Utilities::getGUID();

        $credentials = new MerchantCredentials("106c8a0c-89a4-4063-bf50-9e6c8530593b");
        $clientOptions = new CartaoProtegidoClientOptions($credentials, Environment::SANDBOX);

        $sut = new CartaoProtegidoClient($clientOptions);
        $response = $sut->getCreditCard($request);

        $this->assertEquals(200, $response->HttpStatus);
        $this->assertEquals("4539321573193671", $response->CardNumber);
        $this->assertEquals("453932******3671", $response->MaskedCardNumber);
        $this->assertEquals("06/2020", $response->CardExpiration);
        $this->assertEquals("TESTE TESTETESTE", $response->CardHolder);
        $this->assertEquals(strtolower($request->RequestId), strtolower($response->CorrelationId));
    }

    /** @test */
    public function getCreditCardAsync_forInvalidToken_returnsErrorMessage()
    {
        $request = new GetCreditCardRequest();
        $request->JustClickKey = "1ff03ed9-0000-0000-0000-23b700000000";
        $request->RequestId = Utilities::getGUID();

        $credentials = new MerchantCredentials("106c8a0c-89a4-4063-bf50-9e6c8530593b");
        $clientOptions = new CartaoProtegidoClientOptions($credentials, Environment::SANDBOX);

        $sut = new CartaoProtegidoClient($clientOptions);
        $response = $sut->getCreditCard($request);

        $this->assertEquals(200, $response->HttpStatus);
        $this->assertNotEmpty($response->ErrorDataCollection);
    }

    /** @test */
    public function getCreditCardAsync_forNullToken_returnsInternalServerError()
    {
        $request = new GetCreditCardRequest();
        $request->JustClickKey = null;
        $request->RequestId = Utilities::getGUID();

        $credentials = new MerchantCredentials("106c8a0c-89a4-4063-bf50-9e6c8530593b");
        $clientOptions = new CartaoProtegidoClientOptions($credentials, Environment::SANDBOX);

        $sut = new CartaoProtegidoClient($clientOptions);
        $response = $sut->getCreditCard($request);

        $this->assertEquals(500, $response->HttpStatus);
    }

    /** @test */
    public function getMaskedCreditCardAsync_forValidToken_returnsCardData()
    {
        $request = new GetMaskedCreditCardRequest();
        $request->JustClickKey = "1ff03ed9-5f56-4ac6-bfb8-23b7a1aa55a7";
        $request->RequestId = Utilities::getGUID();

        $credentials = new MerchantCredentials("106c8a0c-89a4-4063-bf50-9e6c8530593b");
        $clientOptions = new CartaoProtegidoClientOptions($credentials, Environment::SANDBOX);

        $sut = new CartaoProtegidoClient($clientOptions);
        $response = $sut->getMaskedCreditCard($request);

        $this->assertEquals(200, $response->HttpStatus);
        $this->assertEquals("453932******3671", $response->MaskedCardNumber);
        $this->assertEquals("06/2020", $response->CardExpiration);
        $this->assertEquals("TESTE TESTETESTE", $response->CardHolder);
        $this->assertEquals(strtolower($request->RequestId), strtolower($response->CorrelationId));
    }

    /** @test */
    public function getMaskedCreditCardAsync_forInvalidToken_returnsErrorMessage()
    {
        $request = new GetMaskedCreditCardRequest();
        $request->JustClickKey = "1ff03ed9-0000-0000-0000-23b700000000";
        $request->RequestId = Utilities::getGUID();

        $credentials = new MerchantCredentials("106c8a0c-89a4-4063-bf50-9e6c8530593b");
        $clientOptions = new CartaoProtegidoClientOptions($credentials, Environment::SANDBOX);

        $sut = new CartaoProtegidoClient($clientOptions);
        $response = $sut->getMaskedCreditCard($request);

        $this->assertEquals(200, $response->HttpStatus);
        $this->assertNotEmpty($response->ErrorDataCollection);
    }

    /** @test */
    public function getMaskedCreditCardAsync_forNullToken_returnsInternalServerError()
    {
        $request = new GetMaskedCreditCardRequest();
        $request->JustClickKey = null;
        $request->RequestId = Utilities::getGUID();

        $credentials = new MerchantCredentials("106c8a0c-89a4-4063-bf50-9e6c8530593b");
        $clientOptions = new CartaoProtegidoClientOptions($credentials, Environment::SANDBOX);

        $sut = new CartaoProtegidoClient($clientOptions);
        $response = $sut->getMaskedCreditCard($request);

        $this->assertEquals(500, $response->HttpStatus);
    }

    /** @test */
    public function saveCreditCardAsync_returnsJustClickToken()
    {
        $request = new SaveCreditCardRequest();
        $request->RequestId = Utilities::getGUID();
        $request->CustomerName = "Bjorn Ironside";
        $request->CustomerIdentification = "762.502.520-96";
        $request->CardHolder = "BJORN IRONSIDE";
        $request->CardExpiration = "10/2025";
        $request->CardNumber = "1000100010001000";
        $request->JustClickAlias = uniqid();

        $credentials = new MerchantCredentials("106c8a0c-89a4-4063-bf50-9e6c8530593b");
        $clientOptions = new CartaoProtegidoClientOptions($credentials, Environment::SANDBOX);

        $sut = new CartaoProtegidoClient($clientOptions);
        $response = $sut->saveCreditCard($request);

        $this->assertEquals(200, $response->HttpStatus);
        $this->assertEmpty($response->ErrorDataCollection);
        $this->assertNotNull($response->JustClickKey);
    }
}