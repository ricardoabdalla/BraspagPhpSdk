<?php
namespace BraspagSdk\CartaoProtegido;

use BraspagSdk\Common\Endpoints;
use BraspagSdk\Common\Environment;
use BraspagSdk\Common\Utilities;
use BraspagSdk\Contracts\CartaoProtegido\ErrorData;
use BraspagSdk\Contracts\CartaoProtegido\GetCreditCardRequest;
use BraspagSdk\Contracts\CartaoProtegido\GetCreditCardResponse;
use BraspagSdk\Contracts\CartaoProtegido\GetMaskedCreditCardRequest;
use BraspagSdk\Contracts\CartaoProtegido\GetMaskedCreditCardResponse;
use BraspagSdk\Contracts\CartaoProtegido\MerchantCredentials;
use InvalidArgumentException;

class CartaoProtegidoClient
{
    private $credentials;
    private $url;

    public function __construct(CartaoProtegidoClientOptions $options)
    {
        $this->credentials = $options->credentials;

        if ($options->Environment == Environment::PRODUCTION)
            $this->url = Endpoints::CartaoProtegidoProduction;
        else
            $this->url = Endpoints::CartaoProtegidoSandbox;
    }

    function getCreditCard(GetCreditCardRequest $request, MerchantCredentials $credentials = null)
    {
        if (empty($request) || !isset($request))
            throw new InvalidArgumentException("Request is null");

        if (empty($this->credentials) && empty($credentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $credentials;

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_URL, $this->url . "v2/cartaoprotegido.asmx");
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag-SDK-PHP');

            $headers = array(
                "Content-Type: text/xml",
                "cache-control: no-cache"
            );

            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            /* TODO: Corpo XML Soap */
            $body = "<soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">";
            $body .= "<soap:Body>";
            $body .= "<GetCreditCard xmlns=\"http://www.cartaoprotegido.com.br/WebService/\">";
            $body .= "<getCreditCardRequestWS>";

            if (!isset($request->RequestId) || empty($request->RequestId))
            {
                $requestId = Utilities::getGUID();
                $body .= "<RequestId>{$requestId}</RequestId>";
            }
            else
            {
                $body .= "<RequestId>{$request->RequestId}</RequestId>";
            }

            $body .= "<MerchantKey>{$currentCredentials->MerchantKey}</MerchantKey>";
            $body .= "<JustClickKey>{$request->JustClickKey}</JustClickKey>";
            $body .= "<JustClickAlias>{$request->JustClickAlias}</JustClickAlias>";
            $body .= "</getCreditCardRequestWS>";
            $body .= "</GetCreditCard>";
            $body .= "</soap:Body>";
            $body .= "</soap:Envelope>";

            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

            $httpResponse = curl_exec($curl);
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

        if (!empty($httpResponse) || isset($httpResponse))
        {
            $response = GetCreditCardResponse::fromXml($httpResponse);
            $response->HttpStatus = isset($statusCode) ? $statusCode : 0;
            return $response;
        }
        else
        {
            $errorResponse = new GetCreditCardResponse();
            $errorResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            $errorCollection = array();
            $errorData = new ErrorData();
            $errorData->Code = isset($curl_error) ? $curl_error : "unknown_error";
            $errorData->Message = isset($error_message) ? $error_message : "Unknown error";
            array_push($errorCollection, $errorData);
            $errorResponse->ErrorDataCollection = $errorCollection;
            return $errorResponse;
        }
    }

    function getMaskedCreditCard(GetMaskedCreditCardRequest $request, MerchantCredentials $credentials = null)
    {
        if (empty($request) || !isset($request))
            throw new InvalidArgumentException("Request is null");

        if (empty($this->credentials) && empty($credentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $credentials;

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_URL, $this->url . "v2/cartaoprotegido.asmx");
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag-SDK-PHP');

            $headers = array(
                "Content-Type: text/xml",
                "cache-control: no-cache"
            );

            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            /* TODO: Corpo XML Soap */
            $body = "<soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">";
            $body .= "<soap:Body>";
            $body .= "<GetMaskedCreditCard xmlns=\"http://www.cartaoprotegido.com.br/WebService/\">";
            $body .= "<getMaskedCreditCardRequestWS>";

            if (!isset($request->RequestId) || empty($request->RequestId))
            {
                $requestId = Utilities::getGUID();
                $body .= "<RequestId>{$requestId}</RequestId>";
            }
            else
            {
                $body .= "<RequestId>{$request->RequestId}</RequestId>";
            }

            $body .= "<MerchantKey>{$currentCredentials->MerchantKey}</MerchantKey>";
            $body .= "<JustClickKey>{$request->JustClickKey}</JustClickKey>";
            $body .= "<JustClickAlias>{$request->JustClickAlias}</JustClickAlias>";
            $body .= "</getMaskedCreditCardRequestWS>";
            $body .= "</GetMaskedCreditCard>";
            $body .= "</soap:Body>";
            $body .= "</soap:Envelope>";

            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

            $httpResponse = curl_exec($curl);
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

        if (!empty($httpResponse) || isset($httpResponse))
        {
            $response = GetMaskedCreditCardResponse::fromXml($httpResponse);
            $response->HttpStatus = isset($statusCode) ? $statusCode : 0;
            return $response;
        }
        else
        {
            $errorResponse = new GetMaskedCreditCardResponse();
            $errorResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            $errorCollection = array();
            $errorData = new ErrorData();
            $errorData->Code = isset($curl_error) ? $curl_error : "unknown_error";
            $errorData->Message = isset($error_message) ? $error_message : "Unknown error";
            array_push($errorCollection, $errorData);
            $errorResponse->ErrorDataCollection = $errorCollection;
            return $errorResponse;
        }
    }
}