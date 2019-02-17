<?php

namespace BraspagSdk\Tests;

use BraspagSdk\BraspagAuth\BraspagAuthClient;
use BraspagSdk\Common\ClientOptions;
use BraspagSdk\Common\Environment;
use BraspagSdk\Common\OAuthGrantType;
use BraspagSdk\Contracts\BraspagAuth\AccessTokenRequest;
use PHPUnit\Framework\TestCase;

final class BraspagAuthTest extends TestCase
{
    /** @test */
    public function createAccessToken_forValidCredentials_returnsAccessToken()
    {
        $request = new AccessTokenRequest();
        $request->ClientId = "5d85902e-592a-44a9-80bb-bdda74d51bce";
        $request->ClientSecret = "mddRzd6FqXujNLygC/KxOfhOiVhlUr2kjKPsOoYHwhQ=";
        $request->GrantType = OAuthGrantType::ClientCredentials;
        $request->Scope = "VelocityApp";

        $clientOptions = new ClientOptions();
        $clientOptions->Environment = Environment::SANDBOX;

        $sut = new BraspagAuthClient($clientOptions);
        $result = $sut->CreateAccessToken($request);
        $this->assertEquals(200, $result->HttpStatus);
        $this->assertNotNull($result->Token);
    }
}
