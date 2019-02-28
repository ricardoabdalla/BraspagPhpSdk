<?php

namespace BraspagSdk\Contracts\Velocity;

class PhoneData
{
    /// Valores possíveis: 'Phone', 'Workphone', 'Cellphone'
    public $Type;

    public $DDI;

    public $DDD;

    public $Number;

    public $NExtension;
}