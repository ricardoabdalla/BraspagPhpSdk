<?php

namespace BraspagSdkExample;

use BraspagSdk\BraspagAuth\BraspagAuthClient;
use BraspagSdk\Common\Environment;
use BraspagSdk\Common\OAuthGrantType;
use BraspagSdk\Contracts\BraspagAuth\AccessTokenRequest;
use BraspagSdk\Common\ClientOptions;

require ("../BraspagSdk/Common/ClientOptions.php");
require ("../BraspagSdk/Common/Endpoints.php");
require ("../BraspagSdk/Common/Environment.php");
require ("../BraspagSdk/Common/OAuthGrantType.php");
require ("../BraspagSdk/Contracts/BraspagAuth/AccessTokenRequest.php");
require ("../BraspagSdk/BraspagAuth/BraspagAuthClient.php");

$clientOptions = new ClientOptions();
$clientOptions->Environment = Environment::SANDBOX;

$bpClient = new BraspagAuthClient($clientOptions);

$request = new AccessTokenRequest();

$request->ClientId = "5d85902e-592a-44a9-80bb-bdda74d51bce";
$request->ClientSecret = "mddRzd6FqXujNLygC/KxOfhOiVhlUr2kjKPsOoYHwhQ=";
$request->GrantType = OAuthGrantType::ClientCredentials;
$request->Scope = "VelocityApp";


$bpClient->CreateAccessToken($request);