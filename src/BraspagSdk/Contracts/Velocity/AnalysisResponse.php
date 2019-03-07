<?php

namespace BraspagSdk\Contracts\Velocity;

class AnalysisResponse
{
    public $HttpStatus;

    public $AnalysisResult;

    public $Transaction;

    public $ErrorDataCollection;

    public $EmailageResult;

    public $CredilinkResult;

    public $RequestId;

    public static function fromJson($json)
    {
        $response = new AnalysisResponse();
        $jsonArray = json_decode($json);

        foreach ($jsonArray as $key => $val)
            if (property_exists(__CLASS__, $key))
                $response->$key = $val;

        return $response;
    }

    public static function fromErrorJson($json)
    {
        $jsonArray = json_decode($json);
        $errorCollection = array();

        foreach ($jsonArray as $key => $val)
        {
            $errorData = new ErrorData();
            $errorData->Field = $jsonArray[$key]->Field;
            $errorData->ErrorCode = $jsonArray[$key]->ErrorCode;
            $errorData->Message = $jsonArray[$key]->Message;
            array_push($errorCollection, $errorData);
        }

        $response = new AnalysisResponse();
        $response->ErrorDataCollection = $errorCollection;
        return $response;
    }

    public static function fromText($message)
    {
        $response = new AnalysisResponse();
        $errorCollection = array();
        $errorData = new ErrorData();
        $errorData->Message = $message;
        array_push($errorCollection, $errorData);
        $response->ErrorDataCollection = $errorCollection;
        return $response;
    }
}