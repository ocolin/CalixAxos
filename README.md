# Calix Axos

## What is it?

A lightweight HTTP REST client for Calix AXOS Northbound interface.

No worrying about HTTP and authentication. Just provide an endpoint path and the payload data.

---

## Table of Contents

- [What is it?](#What-is-it)
- [Installation](#Installation)
- [Requirements](#Requirements)
- [Instantiation](#Instantiation)
  - [Using Environment variables](#Using-Environment-variables)
  - [Using Constructor arguments](#Using-Constructor-arguments)
  - [Options](#Options)
- [Response](#Response)
- [Request Methods](#Request-Methods)
  - [Parameters](#Parameters)
  - [Path replacement](#Path-Replacement)
  - [GET Method](#GET-Method)
  - [POST Method](#POST-Method)
  - [PUT Method](#PUT-Method)
  - [DELETE Method](#DELETE-Method)
  - [Request Method](#Request-Method)

---

## Installation

```
composer require ocolin/calix-axos
```
---
## Requirements

- php ^8.3
- guzzlehttp/guzzle ^7.10
- ocolin/global-type ^2.0

---

## Instantiation

The client can be configured either via constructor arguments or environment parameters. 

| Argument Name |Environment Name| Type   | Description                     |
|---------------|----------------|--------|---------------------------------|
| $host         |SMX_AXOS_HOST| string | Hostname/IP, port, and URI path |
| $usernmae     |SMX_AXOS_USERNAME|string| Username of account to use      |
| $password     |SMX_AXOS_PASSWORD|string| Password of account to use      |

### Using Environment variables

```php
// Setting manually for demonstration
$_ENV['SMX_AXOS_HOST'] = 'https://smx.servername.com:18443/rest/v1/';
$_ENV['SMX_AXOS_USERNAME'] = 'api_user';
$_ENV['SMX_AXOS_PASSWORD'] = 'password1234';

$client = new Ocolin\CalixAxos\Client();
```

### Using Constructor arguments

The constructor takes a DTO called Config. 

```php
$client = new Ocolin\CalixAxos\Client(
    client: new Ocolin\CalixAxos\Config(
            host: 'https://smx.servername.com:18443/rest/v1/',
        username: 'api_user',
        password: 'password1234'
    )
);
```

### Options

The Config object can also take an options parameter for setting guzzle options such as HTTP timeout, verifying SSL, etc.

Default Options:
- timeout: 20 sec
- verify: false

```php
$client = new Ocolin\CalixAxos\Client(
    client: new Ocolin\CalixAxos\Config(
        options: [
            'timeout' => 60,
            'verify'  => true  
        ]
    )
);
```
---

## Response

The client will response with a data object containing the following properties:

| Name          | Type          | Description                  |
|---------------|---------------|------------------------------|
| status        | integer       | HTTP response status code    |
| statusMessage | string        | HTTP response status message |
| headers       | array         | HTTP response headers        |
| body          | array\|object | API response payload         |

---

## Request Methods

The Calix API allows four HTTP methods, each is a function on the client:

| Method | Function |Description|
|--------|----------|-----------|
| GET    | get()    |Retrieve existing resource(s)|
| POST   | post()   |Create a new resource|
| PUT    | put()    |Modify an existing resource|
| DELETE | delete() |Delete an existing resource|

### Parameters

|Name| Type          | Description                              |
|----|---------------|------------------------------------------|
|endpoint| string        | The URI of an API endpoint               |
|method| string        | The HTTP method (only used in request()) |
|query| array\|object | Both path and URI query parameters       |
|body| array\|object | Payload from API server                  |

### Path Replacement

Any parameters in the query argument with names that match a variable token in the endpoint path name will swap those token names with their value. This allows you to past the endpoint URI as is from the docs and replace them automatically. See the GET method example below.


### GET Method

```php
$response = $client->get(
    endpoint: '/config/device/{device-name}/ont',
       query: [ 
            'device-name' => 'OLT-NAME',
            'ont-id'      => 777
       ]
);
```

### POST Method

```php
$response = $client->post(
    endpoint: '/config/device/{device-name}/ont',
       query: [ 'device-name' => 'OLT-NAME' ],
        body: [
            'ont-id'         => 777,
            'ont-reg-id'     => 777,
            'ont-type'       => 'Residential',
            'ont-profile-id' => '844G'
        ]   
);
```

### PUT Method

```php
$response = $client->put(
    endpoint: '/config/device/{device-name}/ont',
       query: [ 'device-name' => 'OLT-NAME' ],
        body: [
            'ont-id'         => 777,
            'ont-reg-id'     => 777,
            'ont-type'       => 'Business',
            'ont-profile-id' => '844G'
        ]   
);
```

### DELETE Method

```php
$response = $client->delete(
    endpoint: '/config/device/{device-name}/ont',
       query: [ 
            'device-name' => 'OLT-NAME',
            'ont-id'      => 777
       ]
);
```

### Request Method

There is a request method which lets you manually specify the HTTP method to use rather than a specific method function.

```php
$response = $client->request(
    endpoint: '/config/device/{device-name}/ont',
      method: 'POST',
       query: [ 'device-name' => 'OLT-NAME' ],
        body: [
            'ont-id'         => 777,
            'ont-reg-id'     => 777,
            'ont-type'       => 'Business',
            'ont-profile-id' => '844G'
        ]   
);
```