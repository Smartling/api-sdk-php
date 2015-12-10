<?php

include_once 'vendor/autoload.php';

use GuzzleHttp\Client;
use Smartling\Auth\AuthTokenProvider;


$client = new Client(['base_uri' => $baseUrl, 'debug' => TRUE]);
$api = AuthTokenProvider::create('userIdentifier', 'secretKey');

$result = $api->getAccessToken();
print (string) $result->getBody();
echo "\nThis is a upload file\n";