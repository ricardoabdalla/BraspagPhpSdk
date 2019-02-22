<?php

namespace BraspagSdk\Contracts\Pagador;

class FraudAnalysisTravelData
{
    public $JourneyType;
    
    public $DepartureDateTime;

    public $Passengers;

    public function __construct()
    {
        $this->Passengers = new FraudAnalysisPassengerData();
    }
}