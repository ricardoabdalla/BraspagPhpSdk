<?php

namespace BraspagSdk\Contracts\Pagador;

class FraudAnalysisItemData
{
    public $GiftCategory;
    
    public $HostHedge;
    
    public $NonSensicalHedge;
    
    public $ObscenitiesHedge;
    
    public $PhoneHedge;
    
    public $TimeHedge;
    
    public $VelocityHedge;
    
    public $Name;
    
    public $Quantity;
    
    public $Sku;
    
    public $UnitPrice;
    
    public $Risk;
    
    public $Type;

    // FraudAnalysisPassengerData
    public $Passenger;

//    public function __construct()
//    {
//        $this->Passenger = new FraudAnalysisPassengerData();
//    }
}