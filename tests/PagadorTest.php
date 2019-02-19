<?php

namespace BraspagSdk\Tests;

use BraspagSdk\Common\Environment;
use BraspagSdk\Contracts\Pagador\MerchantCredentials;
use BraspagSdk\Pagador\PagadorClientOptions;
use BraspagSdk\Contracts\Pagador\SaleRequest;
use BraspagSdk\Pagador\PagadorClient;
use PHPUnit\Framework\TestCase;

final class PagadorClientTest extends TestCase
{
    /** @test */
    public function createSale_forValidCredentials_returnsSaleResponse()
    {
        $credentials = new MerchantCredentials("4b83f3c7-7787-47d1-8a62-568f71f5ab02", "ROJBOAQUNCBHWPUETLDQIFYHBYRCIIFKAMGEGTXB");

        $pagadorClientOptions = new PagadorClientOptions($credentials, Environment::SANDBOX);
        $pagadorClientOptions->Environment = Environment::SANDBOX;

        $saleRequest = new SaleRequest();

        $sut = new PagadorClient($pagadorClientOptions);
        $result = $sut->CreateSale($saleRequest);
        $this->assertEquals(200, $result->HttpStatus);
    }
}
