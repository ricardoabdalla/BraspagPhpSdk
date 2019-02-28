<?php

namespace BraspagSdk\Contracts\Velocity;


class AnalysisResultData
{
    public $Score;

    public $Status;

    public $RejectReasons;

    public $AcceptByWhiteList;

    public $RejectByBlackList;

    public function __construct()
    {
        $this->RejectReasons = array();
    }
}