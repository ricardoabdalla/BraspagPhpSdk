<?php

namespace BraspagSdk\Contracts\Pagador;

class FraudAnalysisPassengerData
{
    public $Email;
    
    public $Identity;
    
    public $Name;
    
    public $Rating;
    
    public $Phone;
    
    public $Status;
    
    public $TicketNumber;
    
    public $FrequentFlyerNumber;

    // List<FraudAnalysisTravelLegsData>
    public $TravelLegs;

//    public function __construct()
//    {
//        $this->TravelLegs = new FraudAnalysisTravelLegsData();
//    }
}