<?php

namespace BraspagSdk\Contracts\CartaoProtegido;

use Exception;

class GetMaskedCreditCardResponse extends BaseResponse
{
    public $CardHolder;

    public $CardExpiration;

    public $MaskedCardNumber;

    public static function fromXml($xml)
    {
        $clean_xml = str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $xml);
        $object = simplexml_load_string($clean_xml);
        $response = new GetMaskedCreditCardResponse();
        $response->populate($object);
        return $response;
    }

    public function populate($data)
    {
        try
        {
            $dataProps = get_object_vars($data->children()[0]->GetMaskedCreditCardResponse[0]->GetMaskedCreditCardResult);

            if (isset($dataProps['CorrelationId']))
                $this->CorrelationId = $dataProps['CorrelationId'];

            if (isset($dataProps['CardHolder']))
                $this->CardHolder = $dataProps['CardHolder'];

            if (isset($dataProps['CardExpiration']))
                $this->CardExpiration = $dataProps['CardExpiration'];

            if (isset($dataProps['MaskedCardNumber']))
                $this->MaskedCardNumber = $dataProps['MaskedCardNumber'];

            if (isset($dataProps['ErrorReportCollection']))
            {
                $errorCollection = array();

                foreach ($dataProps['ErrorReportCollection'] as $errorReport => $value) {
                    $errorData = new ErrorData();
                    $errorData->Code = (string)$value->ErrorCode;
                    $errorData->Message = (string)$value->ErrorMessage;
                    array_push($errorCollection, $errorData);
                }

                $this->ErrorDataCollection = $errorCollection;
            }
        }
        catch (Exception $e)
        {
            $errorCollection = array();
            $errorData = new ErrorData();
            $errorData->Message = "Error parsing data: ".$e->getMessage();
            array_push($errorCollection, $errorData);
            $this->ErrorDataCollection = $errorCollection;
        }
    }
}