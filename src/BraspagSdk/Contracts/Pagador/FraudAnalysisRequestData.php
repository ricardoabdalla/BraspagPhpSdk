<?php

namespace BraspagSdk\Contracts\Pagador;

class FraudAnalysisRequestData
{
    public $Sequence;
    
    public $SequenceCriteria;

    public $FingerPrintId;
    
    public $Provider;
    
    public $CaptureOnLowRisk;
    
    public $VoidOnHighRisk;
    
    public $TotalOrderAmount;

    // FraudAnalysisBrowserData
    public $Browser;

    // FraudAnalysisCartData
    public $Cart;

    // List<FraudAnalysisMerchantDefinedFieldsData>
    public $MerchantDefinedFields;

    // FraudAnalysisShippingData
    public $Shipping;

    // FraudAnalysisTravelData
    public $Travel;
}