<?php

namespace BraspagSdk\Pagador;

use BraspagSdk\Contracts\Pagador\CaptureRequest;
use BraspagSdk\Contracts\Pagador\CaptureResponse;
use BraspagSdk\Contracts\Pagador\CustomerData;
use BraspagSdk\Contracts\Pagador\ErrorData;
use BraspagSdk\Contracts\Pagador\MerchantCredentials;
use BraspagSdk\Contracts\Pagador\PaymentDataRequest;
use BraspagSdk\Contracts\Pagador\PaymentIdResponse;
use BraspagSdk\Contracts\Pagador\RecurrentDataResponse;
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

    public function __construct(PagadorClientOptions $options)
    {
        $this->credentials = $options->Credentials;

        if ($options->Environment == Environment::PRODUCTION) {
            $this->url = Endpoints::PagadorApiProduction;
            $this->queryUrl = Endpoints::PagadorQueryApiProduction;
        } else {
            $this->url = Endpoints::PagadorApiSandbox;
            $this->queryUrl = Endpoints::PagadorQueryApiSandbox;
        }
    }

    function getUrl() {
        return $this->url;
    }

    function getQueryUrl() {
        return $this->queryUrl;
    }

    function getCredentials() {
        return $this->credentials;
    }

    function createSale(SaleRequest $request, MerchantCredentials $credentials = null)
    {
        if (empty($request))
            throw new InvalidArgumentException("Sale request is null");

        if (empty($this->credentials) && empty($credentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $credentials;

        if (empty($currentCredentials->MerchantId))
            throw new InvalidArgumentException("Invalid credentials: MerchantId is null");

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_URL, $this->url . "v2/sales");
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag-PHP-SDK');
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "MerchantId: $currentCredentials->MerchantId",
                "MerchantKey: $currentCredentials->MerchantKey",
                "RequestId: " . uniqid(),
                "cache-control: no-cache"));

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

        if (!empty($httpResponse))
        {
            $response = SaleResponse::fromJson($httpResponse);
            $response->HttpStatus = isset($statusCode) ? $statusCode : 0;
            return $response;
        }
        else
        {
            $errorResponse = new SaleResponse();
            $errorResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            $errorResponse->Customer = null;
            $errorResponse->Payment = null;
            $errorData = new ErrorData();
            $errorData->Code = "unknown_error";
            $errorData->Message = "Unknown error";
            array_push($errorResponse->ErrorDataCollection, $errorData);
            return $errorResponse;
        }
    }

    function capture(CaptureRequest $request, MerchantCredentials $credentials = null)
    {
        if (empty($request))
            throw new InvalidArgumentException("Capture request is null");

        if (empty($this->credentials) && empty($credentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $credentials;

        if (empty($currentCredentials->MerchantId))
            throw new InvalidArgumentException("Invalid credentials: MerchantId is null");

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_URL, $this->url . "v2/sales/$request->PaymentId/capture?" . http_build_query($request));
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag-PHP-SDK');
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, "");
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "MerchantId: $currentCredentials->MerchantId",
                "MerchantKey: $currentCredentials->MerchantKey",
                "RequestId: " . uniqid(),
                "cache-control: no-cache"));

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

        if (!empty($httpResponse))
        {
            $response = CaptureResponse::fromJson($httpResponse);
            $response->HttpStatus = isset($statusCode) ? $statusCode : 0;
            return $response;
        }
        else
        {
            $errorResponse = new CaptureResponse();
            $errorResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            $errorData = new ErrorData();
            $errorData->Code = "unknown_error";
            $errorData->Message = "Unknown error";
            array_push($errorResponse->ErrorDataCollection, $errorData);
            return $errorResponse;
        }
    }

    function void(VoidRequest $request, MerchantCredentials $credentials = null)
    {
        if (empty($request))
            throw new InvalidArgumentException("Void request is null");

        if (empty($this->credentials) && empty($credentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $credentials;

        if (empty($currentCredentials->MerchantId))
            throw new InvalidArgumentException("Invalid credentials: MerchantId is null");

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_URL, $this->url . "v2/sales/$request->PaymentId/void?" . http_build_query($request));
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag-PHP-SDK');
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, "");
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "MerchantId: $currentCredentials->MerchantId",
                "MerchantKey: $currentCredentials->MerchantKey",
                "RequestId: " . uniqid(),
                "cache-control: no-cache"
            ));

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

        if (!empty($httpResponse))
        {
            $response = VoidResponse::fromJson($httpResponse);
            $response->HttpStatus = isset($statusCode) ? $statusCode : 0;
            return $response;
        }
        else
        {
            $errorResponse = new VoidResponse();
            $errorResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            $errorData = new ErrorData();
            $errorData->Code = "unknown_error";
            $errorData->Message = "Unknown error";
            array_push($errorResponse->ErrorDataCollection, $errorData);
            return $errorResponse;
        }
    }

    function get($paymentId, MerchantCredentials $credentials = null)
    {
        if (empty($paymentId))
            throw new InvalidArgumentException("PaymentId is null");

        if (empty($this->credentials) && empty($credentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $credentials;

        if (empty($currentCredentials->MerchantId))
            throw new InvalidArgumentException("Invalid credentials: MerchantId is null");

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try 
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_URL, $this->queryUrl . "v2/sales/$paymentId/");
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag-PHP-SDK');
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "MerchantId: $currentCredentials->MerchantId",
                "MerchantKey: $currentCredentials->MerchantKey",
                "RequestId: " . uniqid(),
                "cache-control: no-cache"));

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

        if (!empty($httpResponse))
        {
            $response = SaleResponse::fromJson($httpResponse);
            $response->HttpStatus = isset($statusCode) ? $statusCode : 0;
            return $response;
        }
        else
        {
            $errorResponse = new SaleResponse();
            $errorResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            $errorData = new ErrorData();
            $errorData->Code = "unknown_error";
            $errorData->Message = "Unknown error";
            array_push($errorResponse->ErrorDataCollection, $errorData);
            return $errorResponse;
        }
    }

    function getByOrderId($orderId, MerchantCredentials $credentials = null)
    {
        if (empty($orderId))
            throw new InvalidArgumentException("OrderId is null");

        if (empty($this->credentials) && empty($credentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $credentials;

        if (empty($currentCredentials->MerchantId))
            throw new InvalidArgumentException("Invalid credentials: MerchantId is null");

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try 
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_URL, $this->queryUrl . "v2/sales?merchantOrderId=$orderId");
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag-PHP-SDK');
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "MerchantId: $currentCredentials->MerchantId",
                "MerchantKey: $currentCredentials->MerchantKey",
                "RequestId: " . uniqid(),
                "cache-control: no-cache"
            ));

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

        if (!empty($httpResponse))
        {
            $response = PaymentIdResponse::fromJson($httpResponse);
            $response->HttpStatus = isset($statusCode) ? $statusCode : 0;
            return $response;
        }
        else
        {
            $errorResponse = new PaymentIdResponse();
            $errorResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            return $errorResponse;
        }
    }

    public function changeRecurrencyCustomer($recurrencyId, CustomerData $customer, MerchantCredentials $credentials = null)
    {
        if (empty($recurrencyId))
            throw new InvalidArgumentException("RecurrentPaymentId is null");

        if (empty($customer))
            throw new InvalidArgumentException("Customer is null");

        if (empty($this->credentials) && empty($credentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $credentials;

        if (empty($currentCredentials->MerchantId))
            throw new InvalidArgumentException("Invalid credentials: MerchantId is null");

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try 
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_URL, $this->url . "v2/recurrentpayment/$recurrencyId/customer");
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag-PHP-SDK');
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($customer));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "MerchantId: $currentCredentials->MerchantId",
                "MerchantKey: $currentCredentials->MerchantKey",
                "RequestId: " . uniqid(),
                "cache-control: no-cache"));

            curl_exec($curl);
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

        return isset($statusCode) ? $statusCode : 0;
    }

    public function changeRecurrencyEndDate($recurrencyId, $endDate, MerchantCredentials $credentials = null)
    {
        if (empty($recurrencyId))
            throw new InvalidArgumentException("RecurrentPaymentId is null");

        if (empty($endDate))
            throw new InvalidArgumentException("EndDate is null");

        if (empty($this->credentials) && empty($credentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $credentials;

        if (empty($currentCredentials->MerchantId))
            throw new InvalidArgumentException("Invalid credentials: MerchantId is null");

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try 
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_URL, $this->url . "v2/recurrentpayment/$recurrencyId/enddate");
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag-PHP-SDK');
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($endDate));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "MerchantId: $currentCredentials->MerchantId",
                "MerchantKey: $currentCredentials->MerchantKey",
                "RequestId: " . uniqid(),
                "cache-control: no-cache"));

            curl_exec($curl);
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

        return isset($statusCode) ? $statusCode : 0;
    }

    public function changeRecurrencyInterval($recurrencyId, $interval, MerchantCredentials $credentials = null)
    {
        if (empty($recurrencyId))
            throw new InvalidArgumentException("RecurrentPaymentId is null");

        if (empty($interval))
            throw new InvalidArgumentException("Interval is null");

        if (empty($this->credentials) && empty($credentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $credentials;

        if (empty($currentCredentials->MerchantId))
            throw new InvalidArgumentException("Invalid credentials: MerchantId is null");

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try 
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_URL, $this->url . "v2/recurrentpayment/$recurrencyId/interval");
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag-PHP-SDK');
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($interval));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "MerchantId: $currentCredentials->MerchantId",
                "MerchantKey: $currentCredentials->MerchantKey",
                "RequestId: " . uniqid(),
                "cache-control: no-cache"));

            curl_exec($curl);
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

        return isset($statusCode) ? $statusCode : 0;
    }

    public function changeRecurrencyDay($recurrencyId, $day, MerchantCredentials $credentials = null)
    {
        if (empty($recurrencyId))
            throw new InvalidArgumentException("RecurrentPaymentId is null");

        if (empty($day))
            throw new InvalidArgumentException("Day is null");

        if (empty($this->credentials) && empty($credentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $credentials;

        if (empty($currentCredentials->MerchantId))
            throw new InvalidArgumentException("Invalid credentials: MerchantId is null");

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try 
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_URL, $this->url . "v2/recurrentpayment/$recurrencyId/recurrencyday");
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag-PHP-SDK');
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($day));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "MerchantId: $currentCredentials->MerchantId",
                "MerchantKey: $currentCredentials->MerchantKey",
                "RequestId: " . uniqid(),
                "cache-control: no-cache"));

            curl_exec($curl);
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

        return isset($statusCode) ? $statusCode : 0;
    }

    public function changeRecurrencyAmount($recurrencyId, $amount, MerchantCredentials $credentials = null)
    {
        if (empty($recurrencyId))
            throw new InvalidArgumentException("RecurrentPaymentId is null");

        if (empty($amount))
            throw new InvalidArgumentException("Amount is null");

        if (empty($this->credentials) && empty($credentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $credentials;

        if (empty($currentCredentials->MerchantId))
            throw new InvalidArgumentException("Invalid credentials: MerchantId is null");

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_URL, $this->url . "v2/recurrentpayment/$recurrencyId/amount");
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag-PHP-SDK');
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($amount));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "MerchantId: $currentCredentials->MerchantId",
                "MerchantKey: $currentCredentials->MerchantKey",
                "RequestId: " . uniqid(),
                "cache-control: no-cache"));

            curl_exec($curl);
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

        return isset($statusCode) ? $statusCode : 0;
    }

    public function changeRecurrencyNextPaymentDate($recurrencyId, $nextPaymentDate, MerchantCredentials $credentials = null)
    {
        if (empty($recurrencyId))
            throw new InvalidArgumentException("RecurrentPaymentId is null");

        if (empty($nextPaymentDate))
            throw new InvalidArgumentException("NextPaymentDate is null");

        if (empty($this->credentials) && empty($credentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $credentials;

        if (empty($currentCredentials->MerchantId))
            throw new InvalidArgumentException("Invalid credentials: MerchantId is null");

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try 
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_URL, $this->url . "v2/recurrentpayment/$recurrencyId/nextPaymentDate");
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag-PHP-SDK');
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($nextPaymentDate));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "MerchantId: $currentCredentials->MerchantId",
                "MerchantKey: $currentCredentials->MerchantKey",
                "RequestId: " . uniqid(),
                "cache-control: no-cache"));

            curl_exec($curl);
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

        return isset($statusCode) ? $statusCode : 0;
    }

    public function changeRecurrencyPayment($recurrencyId, PaymentDataRequest $payment, MerchantCredentials $credentials = null)
    {
        if (empty($recurrencyId))
            throw new InvalidArgumentException("RecurrentPaymentId is null");

        if (empty($payment))
            throw new InvalidArgumentException("PaymentData is null");

        if (empty($this->credentials) && empty($credentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $credentials;

        if (empty($currentCredentials->MerchantId))
            throw new InvalidArgumentException("Invalid credentials: MerchantId is null");

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try
        {
            $payment->ExternalAuthentication = null;
            $payment->FraudAnalysis = null;
            $payment->RecurrentPayment = null;
            $payment->Wallet = null;
            $payment->ExtraDataCollection = null;
            $payment->DebitCard = null;
            $payment = (object) array_filter((array) $payment);

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_URL, $this->url . "v2/RecurrentPayment/$recurrencyId/Payment");
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag-PHP-SDK');
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payment));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "MerchantId: $currentCredentials->MerchantId",
                "MerchantKey: $currentCredentials->MerchantKey",
                "RequestId: " . uniqid(),
                "cache-control: no-cache"));

            curl_exec($curl);
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

        return isset($statusCode) ? $statusCode : 0;
    }

    public function deactivateRecurrency($recurrencyId, MerchantCredentials $credentials = null)
    {
        if (empty($recurrencyId))
            throw new InvalidArgumentException("RecurrentPaymentId is null");

        if (empty($this->credentials) && empty($credentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $credentials;

        if (empty($currentCredentials->MerchantId))
            throw new InvalidArgumentException("Invalid credentials: MerchantId is null");

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_URL, $this->url . "v2/recurrentpayment/$recurrencyId/deactivate");
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag-PHP-SDK');
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, "");
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "MerchantId: $currentCredentials->MerchantId",
                "MerchantKey: $currentCredentials->MerchantKey",
                "RequestId: " . uniqid(),
                "cache-control: no-cache"));

            curl_exec($curl);
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

        return isset($statusCode) ? $statusCode : 0;
    }

    public function reactivateRecurrency($recurrencyId, MerchantCredentials $credentials = null)
    {
        if (empty($recurrencyId))
            throw new InvalidArgumentException("RecurrentPaymentId is null");

        if (empty($this->credentials) && empty($credentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $credentials;

        if (empty($currentCredentials->MerchantId))
            throw new InvalidArgumentException("Invalid credentials: MerchantId is null");

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_URL, $this->url . "v2/recurrentpayment/$recurrencyId/reactivate");
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag-PHP-SDK');
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, "");
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "MerchantId: $currentCredentials->MerchantId",
                "MerchantKey: $currentCredentials->MerchantKey",
                "RequestId: " . uniqid(),
                "cache-control: no-cache"));

            curl_exec($curl);
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

        return isset($statusCode) ? $statusCode : 0;
    }

    function getRecurrency($recurrencyId, MerchantCredentials $credentials = null)
    {
        if (empty($recurrencyId))
            throw new InvalidArgumentException("PaymentId is null");

        if (empty($this->credentials) && empty($credentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $credentials;

        if (empty($currentCredentials->MerchantId))
            throw new InvalidArgumentException("Invalid credentials: MerchantId is null");

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_URL, $this->queryUrl . "v2/recurrentpayment/$recurrencyId/");
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag-PHP-SDK');
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "MerchantId: $currentCredentials->MerchantId",
                "MerchantKey: $currentCredentials->MerchantKey",
                "RequestId: " . uniqid(),
                "cache-control: no-cache"));

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

        if (!empty($httpResponse))
        {
            $response = RecurrentDataResponse::fromJson($httpResponse);
            $response->HttpStatus = isset($statusCode) ? $statusCode : 0;
            return $response;
        }
        else
        {
            $errorResponse = new RecurrentDataResponse();
            $errorResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            return $errorResponse;
        }
    }
}