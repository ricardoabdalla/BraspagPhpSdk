<?php

namespace BraspagSdk\Contracts\CartaoProtegido;

use Exception;

class SaveCreditCardResponse extends BaseResponse
{
    public $JustClickKey;

    public static function fromXml($xml)
    {
        $clean_xml = str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $xml);
        $object = simplexml_load_string($clean_xml);
        $response = new SaveCreditCardResponse();
        $response->populate($object);
        return $response;
    }

    public function populate($data)
    {
        try
        {
            $dataProps = get_object_vars($data->children()[0]->SaveCreditCardResponse[0]->SaveCreditCardResult);

            if (isset($dataProps['CorrelationId']))
                $this->CorrelationId = $dataProps['CorrelationId'];

            if (isset($dataProps['JustClickKey']))
                $this->JustClickKey = $dataProps['JustClickKey'];

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