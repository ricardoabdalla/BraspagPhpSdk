<?php

namespace BraspagSdk\Pagador;

use BraspagSdk\Contracts\Pagador\CaptureRequest;
use BraspagSdk\Contracts\Pagador\CaptureResponse;
use BraspagSdk\Contracts\Pagador\MerchantCredentials;
use BraspagSdk\Contracts\Pagador\PaymentIdResponse;
use BraspagSdk\Contracts\Pagador\SaleRequest;
use BraspagSdk\Contracts\Pagador\SaleResponse;
use BraspagSdk\Common\Endpoints;
use BraspagSdk\Common\Environment;
use BraspagSdk\Contracts\Pagador\VoidRequest;
use BraspagSdk\Contracts\Pagador\VoidResponse;
use InvalidArgumentException;
use Exception;

class PagadorClient
{
    private $credentials;
    private $url;
    private $queryUrl;

    public function __construct(PagadorClientOptions $pagadorClientOptions)
    {
        $this->credentials = $pagadorClientOptions->credentials;

        if ($pagadorClientOptions->Environment == Environment::PRODUCTION)
        {
            $this->url = Endpoints::PagadorApiProduction;
            $this->queryUrl = Endpoints::PagadorQueryApiProduction;
        }
        else
        {
            $this->url = Endpoints::PagadorApiSandbox;
            $this->queryUrl = Endpoints::PagadorQueryApiSandbox;
        }
    }

    function createSale(SaleRequest $saleRequest, MerchantCredentials $merchantCredentials = null)
    {
        if (empty($saleRequest))
            throw new InvalidArgumentException("Sale request is null");

        if (empty($this->credentials) && empty($merchantCredentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $merchantCredentials;

        if (empty($currentCredentials->MerchantId))
            throw new InvalidArgumentException("Invalid credentials: MerchantId is null");

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_URL, $this->url . "v2/sales");
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag PHP SDK');
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $headers = array(
                "Content-Type: application/json",
                "User-Agent: Braspag PHP SDK",
                "MerchantId: $currentCredentials->MerchantId",
                "MerchantKey: $currentCredentials->MerchantKey",
                "RequestId: " . uniqid(),
                "cache-control: no-cache"
            );

            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            $saleRequestJson = json_encode($saleRequest);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $saleRequestJson);

            $response = curl_exec($curl);

            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            curl_close($curl);
        }
        catch (Exception $e)
        {
            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
                E_USER_ERROR
            );
        }

        if (!empty($response))
        {
            $jsonResponse = SaleResponse::fromJson($response);
            $jsonResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            return $jsonResponse;
        }
        else
        {
            $errorResponse = new SaleResponse();
            $errorResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            $errorResponse->ErrorDataCollection->Code = isset($curl_error) ? $curl_error : "unknown_error";
            $errorResponse->ErrorDataCollection->Message = isset($error_message) ? $error_message : "Unknown error";
            return $errorResponse;
        }
    }

    function capture(CaptureRequest $captureRequest, MerchantCredentials $merchantCredentials = null)
    {
        if (empty($captureRequest))
            throw new InvalidArgumentException("Capture request is null");

        if (empty($this->credentials) && empty($merchantCredentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $merchantCredentials;

        if (empty($currentCredentials->MerchantId))
            throw new InvalidArgumentException("Invalid credentials: MerchantId is null");

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try {
            $curl = curl_init();

            $captureUrl = $this->url . "v2/sales/$captureRequest->PaymentId/capture?amount=";

            if (isset($captureRequest->Amount))
                $captureUrl .= $captureRequest->Amount;
            else
                $captureUrl .= "0&ServiceTaxAmount=";

            if (isset($captureRequest->ServiceTaxAmount))
                $captureUrl .= $captureRequest->ServiceTaxAmount;
            else
                $captureUrl .= "0";

            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_URL, $captureUrl);
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag PHP SDK');
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $headers = array(
                "Content-Type: application/json",
                "User-Agent: Braspag PHP SDK",
                "MerchantId: $currentCredentials->MerchantId",
                "MerchantKey: $currentCredentials->MerchantKey",
                "RequestId: " . uniqid(),
                "cache-control: no-cache"
            );

            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            $saleRequestJson = json_encode($captureRequest);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $saleRequestJson);

            $response = curl_exec($curl);

            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            curl_close($curl);
        }
        catch (Exception $e)
        {
            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
                E_USER_ERROR
            );
        }

        if (!empty($response))
        {
            $jsonResponse = CaptureResponse::fromJson($response);
            $jsonResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            return $jsonResponse;
        }
        else
        {
            $errorResponse = new CaptureResponse();
            $errorResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            $errorResponse->ErrorDataCollection->Code = isset($curl_error) ? $curl_error : "unknown_error";
            $errorResponse->ErrorDataCollection->Message = isset($error_message) ? $error_message : "Unknown error";
            return $errorResponse;
        }
    }

    function void(VoidRequest $voidRequest, MerchantCredentials $merchantCredentials = null)
    {
        if (empty($voidRequest))
            throw new InvalidArgumentException("Void request is null");

        if (empty($this->credentials) && empty($merchantCredentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $merchantCredentials;

        if (empty($currentCredentials->MerchantId))
            throw new InvalidArgumentException("Invalid credentials: MerchantId is null");

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try {
            $curl = curl_init();

            $voidUrl = $this->url . "v2/sales/$voidRequest->PaymentId/void?amount=";

            if (isset($voidRequest->Amount))
                $voidUrl .= $voidRequest->Amount;
            else
                $voidUrl .= "0&ServiceTaxAmount=";

            if (isset($voidRequest->ServiceTaxAmount))
                $voidUrl .= $voidRequest->ServiceTaxAmount;
            else
                $voidUrl .= "0";

            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_URL, $voidUrl);
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag PHP SDK');
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $headers = array(
                "Content-Type: application/json",
                "User-Agent: Braspag PHP SDK",
                "MerchantId: $currentCredentials->MerchantId",
                "MerchantKey: $currentCredentials->MerchantKey",
                "RequestId: " . uniqid(),
                "cache-control: no-cache"
            );

            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            $saleRequestJson = json_encode($voidRequest);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $saleRequestJson);

            $response = curl_exec($curl);

            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            curl_close($curl);
        }
        catch (Exception $e)
        {
            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
                E_USER_ERROR
            );
        }

        if (!empty($response))
        {
            $jsonResponse = VoidResponse::fromJson($response);
            $jsonResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            return $jsonResponse;
        }
        else
        {
            $errorResponse = new VoidResponse();
            $errorResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            $errorResponse->ErrorDataCollection->Code = isset($curl_error) ? $curl_error : "unknown_error";
            $errorResponse->ErrorDataCollection->Message = isset($error_message) ? $error_message : "Unknown error";
            return $errorResponse;
        }
    }

    function get($paymentId, MerchantCredentials $merchantCredentials = null)
    {
        if (empty($paymentId))
            throw new InvalidArgumentException("PaymentId is null");

        if (empty($this->credentials) && empty($merchantCredentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $merchantCredentials;

        if (empty($currentCredentials->MerchantId))
            throw new InvalidArgumentException("Invalid credentials: MerchantId is null");

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_URL, $this->queryUrl . "v2/sales/$paymentId/");
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag PHP SDK');
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $headers = array(
                "Content-Type: application/json",
                "User-Agent: Braspag PHP SDK",
                "MerchantId: $currentCredentials->MerchantId",
                "MerchantKey: $currentCredentials->MerchantKey",
                "RequestId: " . uniqid(),
                "cache-control: no-cache"
            );

            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($curl);

            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            curl_close($curl);
        }
        catch (Exception $e)
        {
            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
                E_USER_ERROR
            );
        }

        if (!empty($response))
        {
            $jsonResponse = SaleResponse::fromJson($response);
            $jsonResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            return $jsonResponse;
        }
        else
        {
            $errorResponse = new SaleResponse();
            $errorResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            $errorResponse->ErrorDataCollection->Code = isset($curl_error) ? $curl_error : "unknown_error";
            $errorResponse->ErrorDataCollection->Message = isset($error_message) ? $error_message : "Unknown error";
            return $errorResponse;
        }
    }

    function getByOrderId($orderId, MerchantCredentials $merchantCredentials = null)
    {
        if (empty($orderId))
            throw new InvalidArgumentException("OrderId is null");

        if (empty($this->credentials) && empty($merchantCredentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $merchantCredentials;

        if (empty($currentCredentials->MerchantId))
            throw new InvalidArgumentException("Invalid credentials: MerchantId is null");

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_URL, $this->queryUrl . "v2/sales?merchantOrderId=$orderId");
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag PHP SDK');
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $headers = array(
                "Content-Type: application/json",
                "User-Agent: Braspag PHP SDK",
                "MerchantId: $currentCredentials->MerchantId",
                "MerchantKey: $currentCredentials->MerchantKey",
                "RequestId: " . uniqid(),
                "cache-control: no-cache"
            );

            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($curl);

            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            curl_close($curl);
        }
        catch (Exception $e)
        {
            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
                E_USER_ERROR
            );
        }

        if (!empty($response))
        {
            $jsonResponse = PaymentIdResponse::fromJson($response);
            $jsonResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            return $jsonResponse;
        }
        else
        {
            $errorResponse = new PaymentIdResponse();
            $errorResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            return $errorResponse;
        }
    }

}