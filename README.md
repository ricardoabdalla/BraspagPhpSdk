# Braspag SDK para PHP

SDK para integração simplificada nos serviços da plataforma [Braspag](http://www.braspag.com.br/#solucoes)

| Develop | Master 
|---|---|
| [![Build Status](https://braspag.visualstudio.com/Innovation/_apis/build/status/Braspag-PHP-SDK?branchName=develop)](https://braspag.visualstudio.com/Innovation/_build/latest?definitionId=573&branchName=develop) | [![Build Status](https://braspag.visualstudio.com/Innovation/_apis/build/status/Braspag-PHP-SDK?branchName=master)](https://braspag.visualstudio.com/Innovation/_build/latest?definitionId=573&branchName=master) 

> Para documentação completa das APIs e manuais, acesse [http://braspag.github.io/](http://braspag.github.io/)

## Índice

- [Features](#features)
- [Instalação](#instalacao)
- [Exemplos de Uso](#exemplos-de-uso)
    - [Pagador](#pagador)
    - [Cartão Protegido](#cartao-protegido)
    - [Velocity](#velocity)
    
## Features

* Instalação simplificada utilizando [Composer](https://getcomposer.org/), sem necessidade de arquivos de configuração
* Endpoints Braspag já configurados no pacote
* Seleção de ambientes Sandbox ou Production
* Client para a API Braspag Auth (Obtenção de tokens de acesso)
* Client para a API de pagamentos Recorrentes
* Client para a API do Pagador (Autorização, Captura, Cancelamento/Estorno, Consulta)
* Client para a API do Cartão Protegido (Salvar cartão, Recuperar cartão, Invalidar cartão)
* Client para a API de análises do Velocity

## Instalação

Caso já possua um arquivo `composer.json`, adicione a seguinte dependência ao seu projeto:

```xml
"require": {
    "braspag/braspag-php-sdk": "*"
}
```

Com a dependência adicionada ao `composer.json`, execute o comando:

```xml
composer install
```

De forma alternativa, a instalação pode ser realizada executando o comando abaixo diretamente em seu terminal:

```xml
composer require braspag/braspag-php-sdk
```

## Exemplos de Uso

### Pagador

Para criar uma transação com cartão de crédito:

```php
/* Criação do Cliente Pagador */
$credentials = new MerchantCredentials("ID_DA_LOJA", "CHAVE_DA_LOJA");
$options = new PagadorClientOptions($credentials, Environment::SANDBOX);
$pagadorClient = new PagadorClient($options);

/* Preenchimento do objeto SaleRequest */
$request = new SaleRequest();
$request->MerchantOrderId = "123456789";
$request->Customer = new CustomerData();
$request->Customer->Name = "Bjorn Ironside";
$request->Customer->Identity = "762.502.520-96";
$request->Customer->IdentityType = "CPF";
$request->Customer->Email = "bjorn.ironside@vikings.com.br";
$request->Payment = new PaymentDataRequest();
$request->Payment->Provider = "Simulado";
$request->Payment->Type = "CreditCard";
$request->Payment->Currency = "BRL";
$request->Payment->Country = "BRA";
$request->Payment->Amount = 150000;
$request->Payment->Installments = 1;
$request->Payment->SoftDescriptor = "Braspag SDK";
$request->Payment->CreditCard = new CreditCardData();
$request->Payment->CreditCard->CardNumber = "4485623136297301";
$request->Payment->CreditCard->Holder = "BJORN IRONSIDE";
$request->Payment->CreditCard->ExpirationDate = "12/2025";
$request->Payment->CreditCard->SecurityCode = "123";
$request->Payment->CreditCard->Brand = "visa";

/* Obtenção do resultado da operação */
$response = $pagadorClient->createSale($request);
```