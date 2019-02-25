<?php

namespace BraspagSdk\Contracts\Pagador;

class VoidResponse
{
    // List<ErrorData> 
    public $ErrorDataCollection;
    
    /// Código do status HTTP da requisição
    public $HttpStatus;
    
    /// Código retornado pelo provedor do meio de pagamento (adquirente e bancos)
    public $ProviderReturnCode;
    
    /// Mensagem retornada pelo provedor do meio de pagamento (adquirente e bancos)
    public $ProviderReturnMessage;
    
    /// Código de retorno da Operação
    public $ReasonCode;
    
    /// Mensagem de retorno da Operação
    public $ReasonMessage;
    
    /// Status da Transação
    public $Status;

    public static function fromJson($json)
    {
        $response = new VoidResponse();
        $jsonArray = json_decode($json);

        foreach ($jsonArray as $key => $val)
            if (property_exists(__CLASS__, $key))
                $response->$key = $val;

        return $response;
    }
}