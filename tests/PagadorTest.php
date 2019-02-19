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
        $credentials = new MerchantCredentials("33B6AC07-C48D-4F13-A5B9-D3516A378A0C", "d6Rb3OParKvLfzNrURzwcT0f1lzNazS1o19yP6Y8");

        $pagadorClientOptions = new PagadorClientOptions($credentials, Environment::SANDBOX);
        $pagadorClientOptions->Environment = Environment::SANDBOX;

        $saleRequest = new SaleRequest();

        $sut = new PagadorClient($pagadorClientOptions);
        $result = $sut->CreateSale($saleRequest);
        $this->assertEquals(200, $result->HttpStatus);
    }
}
