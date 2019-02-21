<?php
/**
 * Created by PhpStorm.
 * User: ricardoabdalla
 * Date: 2019-02-20
 * Time: 17:07
 */

namespace BraspagSdk\Contracts\CartaoProtegido;


class BaseResponse
{
    public $CorrelationId;

    public $HttpStatus;

    public $ErrorDataCollection;
}