<?php

namespace BraspagSdkExample;

require ("../../src/BraspagSdk/Common/ClientOptions.php");
require ("../../src/BraspagSdk/Common/Environment.php");
require ("../../src/BraspagSdk/Common/Endpoints.php");
require ("../../src/BraspagSdk/Common/OAuthGrantType.php");
require ("../../src/BraspagSdk/BraspagAuth/BraspagAuthClient.php");
require ("../../src/BraspagSdk/Contracts/BraspagAuth/AccessTokenRequest.php");
require ("../../src/BraspagSdk/Contracts/BraspagAuth/AccessTokenResponse.php");

use BraspagSdk\Common\Environment;
use BraspagSdk\Common\ClientOptions;
use BraspagSdk\Common\OAuthGrantType;
use BraspagSdk\BraspagAuth\BraspagAuthClient;
use BraspagSdk\Contracts\BraspagAuth\AccessTokenRequest;

$clientOptions = new ClientOptions();
$clientOptions->Environment = Environment::SANDBOX;

$bpClient = new BraspagAuthClient($clientOptions);

$request = new AccessTokenRequest();

$request->ClientId = "5d85902e-592a-44a9-80bb-bdda74d51bce";
$request->ClientSecret = "mddRzd6FqXujNLygC/KxOfhOiVhlUr2kjKPsOoYHwhQ=";
$request->GrantType = OAuthGrantType::ClientCredentials;
$request->Scope = "VelocityApp";


$response = $bpClient->createAccessToken($request);