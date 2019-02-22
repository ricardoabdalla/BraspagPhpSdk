<?php

namespace BraspagSdk\Contracts\Pagador;

class CustomerData
{
    public $Name;

    public $Identity;

    public $IdentityType;

    public $Email;

    public $Birthdate;

    public $Mobile;

    public $Phone;

    public $Address;

    public $DeliveryAddress;

//    public function __construct()
//    {
//        $this->Address = new AddressData();
//        $this->DeliveryAddress = new AddressData();
//    }
}