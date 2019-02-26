<?php

namespace BraspagSdk\Contracts\Pagador;

abstract class RecurrencyInterval
{
    const Monthly = 1;
    const Bimonthly = 2;
    const Quarterly = 4;
    const SemiAnnual = 6;
    const Annual = 12;
}