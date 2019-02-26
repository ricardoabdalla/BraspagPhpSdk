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

    public function __construct()
    {
        $this->AnalysisResult = new AnalysisResultData();
        $this->Transaction = new TransactionData();
        $this->ErrorDataCollection = array();
        $this->EmailageResult = new EmailageResultData();
        $this->CredilinkResult = new CredilinkResultData();
    }

    public static function fromJson($json)
    {
        $response = new AnalysisResponse();
        $jsonArray = json_decode($json);

        foreach ($jsonArray as $key => $val)
            if (property_exists(__CLASS__, $key))
                $response->$key = $val;

        return $response;
    }
}