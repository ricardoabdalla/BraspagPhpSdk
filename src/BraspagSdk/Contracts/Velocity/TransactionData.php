<?php

namespace BraspagSdk\Contracts\Velocity;

class TransactionData
{
    public $Id;

    public $OrderId;

    /// Formato: YYYY-MM-DD HH:mm:SS.fff
    public $Date;

    public $Amount;
}