# Braspag SDK para PHP

SDK para integração simplificada nos serviços da plataforma [Braspag](http://www.braspag.com.br/#solucoes)

| Develop | Master 
|---|---|
| [![Build Status](https://braspag.visualstudio.com/Innovation/_apis/build/status/Braspag-PHP-SDK?branchName=develop)](https://braspag.visualstudio.com/Innovation/_build/latest?definitionId=573&branchName=develop) | [![Build Status](https://braspag.visualstudio.com/Innovation/_apis/build/status/Braspag-PHP-SDK?branchName=master)](https://braspag.visualstudio.com/Innovation/_build/latest?definitionId=573&branchName=master) 

> Para documentação completa das APIs e manuais, acesse [http://braspag.github.io/](http://braspag.github.io/)

## Índice

- [Features](#features)
- [Dependências](#dependencias)
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

## Dependências

* PHP >= 5.6.37
* ext-curl
* ext-json

## Instalação

Caso já possua um arquivo `composer.json`, adicione a seguinte dependência ao seu projeto:

```
"require": {
    "braspag/braspag-php-sdk": "*"
}
```

Com a dependência adicionada ao `composer.json`, execute o comando:

```
composer install
```

De forma alternativa, a instalação pode ser realizada executando o comando abaixo diretamente em seu terminal:

```
composer require braspag/braspag-php-sdk
```

## Exemplos de Uso

### Pagador

Para criar uma transação utilizando cartão de crédito:

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

Para criar uma transação utilizando cartão de débito:

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
$request->Payment->Type = "DebitCard";
$request->Payment->Currency = "BRL";
$request->Payment->Country = "BRA";
$request->Payment->Amount = 150000;
$request->Payment->Installments = 1;
$request->Payment->SoftDescriptor = "Braspag SDK";
$request->Payment->ReturnUrl = "http://www.sualoja.com/url-de-retorno";
$request->Payment->Authenticate = true;
$request->Payment->DebitCard = new DebitCardData();
$request->Payment->DebitCard->CardNumber = "4485623136297301";
$request->Payment->DebitCard->Holder = "BJORN IRONSIDE";
$request->Payment->DebitCard->ExpirationDate = "12/2025";
$request->Payment->DebitCard->SecurityCode = "123";
$request->Payment->DebitCard->Brand = "visa";

/* Obtenção do resultado da operação */
$response = $pagadorClient->createSale($request);
```

Para criar uma transação utilizando boleto registrado:

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
$request->Payment->Type = "Boleto";
$request->Payment->Currency = "BRL";
$request->Payment->Country = "BRA";
$request->Payment->Amount = 150000;
$request->Payment->BoletoNumber = "2017091101";
$request->Payment->Assignor = "Braspag";
$request->Payment->Demonstrative = "Texto demonstrativo";
$request->Payment->ExpirationDate = "2019-03-20";
$request->Payment->Identification = "11017523000167";
$request->Payment->Instructions = "Aceitar somente até a data de vencimento.";

/* Obtenção do resultado da operação */
$response = $pagadorClient->createSale($request);
```

### Cartão Protegido

Para salvar um cartão de crédito em um cofre PCI:

```php
/* Criação do Cliente Cartão Protegido */
$credentials = new MerchantCredentials("CHAVE_DA_LOJA");
$options = new CartaoProtegidoClientOptions($credentials, Environment::SANDBOX);
$cartaoProtegidoClient = new CartaoProtegidoClient($options);

/* Preenchimento do objeto SaveCreditCardRequest */
$request = new SaveCreditCardRequest();
$request->CustomerName = "Bjorn Ironside";
$request->CustomerIdentification = "762.502.520-96";
$request->CardHolder = "BJORN IRONSIDE";
$request->CardExpiration = "10/2025";
$request->CardNumber = "1000100010001000";

/* Obtenção do resultado da operação */
$response = $cartaoProtegidoClient->saveCreditCard($request);
```

Para obter os dados de um cartão de crédito previamente salvo em cofre PCI:

```php
/* Criação do Cliente Cartão Protegido */
$credentials = new MerchantCredentials("CHAVE_DA_LOJA");
$options = new CartaoProtegidoClientOptions($credentials, Environment::SANDBOX);
$cartaoProtegidoClient = new CartaoProtegidoClient($options);

/* Preenchimento do objeto GetCreditCardRequest */
$request = new GetCreditCardRequest();
$request->JustClickKey = "CREDITCARD_TOKEN";

/* Obtenção do resultado da operação */
$response = $cartaoProtegidoClient->getCreditCard($request);
```

### Velocity

Análise de uma transação com o Velocity:

```php
/* Criação do Token de Acesso OAUTH via Braspag Auth */
$authRequest = new AccessTokenRequest();
$authRequest->GrantType = OAuthGrantType::ClientCredentials;
$authRequest->ClientId = "CLIENT_ID";
$authRequest->ClientSecret = "CLIENT_SECRET";
$authRequest->Scope = "VelocityApp";

$clientOptions = new ClientOptions();
$clientOptions->Environment = Environment::SANDBOX;
$braspagAuthClient = new BraspagAuthClient($clientOptions);

/* Obtenção do Token de Acesso */
$authResponse = $braspagAuthClient->createAccessToken($authRequest);

/* Criação do Cliente Velocity */
$credentials = new MerchantCredentials("MERCHANT_ID", $authResponse->Token);
$options = new VelocityClientOptions($credentials);
$velocityClient = new VelocityClient($options);

/* Preenchimento do objeto AnalysisRequest */
$transaction = new TransactionData();
$transaction->OrderId = uniqid();
$transaction->Date = date('Y-m-d H:i:s');
$transaction->Amount = 1000;

$card = new CardData();
$card->Holder = "BJORN IRONSIDE";
$card->Brand = "visa";
$card->Number = "1000100010001000";
$card->Expiration = "10/2025";

$customer = new CustomerData();
$customer->Name = "Bjorn Ironside";
$customer->Identity = "76250252096";
$customer->IpAddress = "127.0.0.1";
$customer->Email = "bjorn.ironside@vikings.com.br";
$customer->BirthDate = "1982-06-30";

$phone = new PhoneData();
$phone->Type = "Cellphone";
$phone->Number = "999999999";
$phone->DDI = "55";
$phone->DDD = "11";
array_push($customer->Phones, $phone);

$billing = new AddressData();
$billing->Street = "Alameda Xingu";
$billing->Number = "512";
$billing->Neighborhood = "Alphaville";
$billing->City = "Barueri";
$billing->State = "SP";
$billing->Country = "BR";
$billing->ZipCode = "06455-030";
$customer->Billing = $billing;

$request = new AnalysisRequest();
$request->Transaction = $transaction;
$request->Card = $card;
$request->Customer = $customer;

/* Obtenção do resultado da operação */
$response = $velocityClient->performAnalysis($request);
```