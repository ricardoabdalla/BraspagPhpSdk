<?php

namespace BraspagSdk\Contracts\Pagador;

class SaleResponse
{
    public $HttpStatus;

    public $Error;

    public $ErrorDescription;

    public $MerchantOrderId;

    public $Customer;

    public static function fromJson($json)
    {
        $object = json_decode($json);
        $response = new SaleResponse();
        $response->populate($object);
        return $response;
    }

    public function populate(\stdClass $data)
    {
        $dataProps = get_object_vars($data);
        $this->Customer = new CustomerData();

        if (isset($dataProps['MerchantOrderId']))
            $this->MerchantOrderId = $dataProps['MerchantOrderId'];

        if (isset($dataProps['Customer']->Name))
            $this->Customer->Name = $dataProps["Customer"]->Name;
    }
}