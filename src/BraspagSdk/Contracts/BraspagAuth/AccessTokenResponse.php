<?php

namespace BraspagSdk\Contracts\BraspagAuth;

class AccessTokenResponse
{
    public $HttpStatus;

    public $Token;

    public $ExpiresIn;

    public $Error;

    public $ErrorDescription;

    public static function fromJson($json)
    {
        $object = json_decode($json);
        $response = new AccessTokenResponse();
        $response->populate($object);
        return $response;
    }

    public function populate(\stdClass $data)
    {
        $dataProps = get_object_vars($data);

        if (isset($dataProps['access_token'])) {
            $this->Token = $dataProps['access_token'];
        }

        if (isset($dataProps['expires_in'])) {
            $this->ExpiresIn = $dataProps['expires_in'];
        }

        if (isset($dataProps['error']))
            $this->Error = $dataProps['error'];

        if (isset($dataProps['error_description']))
            $this->ErrorDescription = $dataProps['error_description'];
    }
}