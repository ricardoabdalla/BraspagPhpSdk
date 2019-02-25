<?php
/**
 * Created by PhpStorm.
 * User: rabdalla
 * Date: 2019-02-25
 * Time: 15:19
 */

namespace BraspagSdk\Contracts\Velocity;


class MerchantCredentials
{
    public $MerchantId;

    public $AccessToken;

    /**
     * MerchantCredentials constructor.
     * @param $MerchantId
     * @param $AccessToken
     */
    public function __construct($MerchantId, $AccessToken)
    {
        $this->MerchantId = $MerchantId;
        $this->AccessToken = $AccessToken;
    }
}