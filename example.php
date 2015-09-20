<?php

/**
 * @file
 * Contains example of Smartling API usage.
 *
 * How to use:
 * run "php example.php --api-key={API_KEY} --product-id={PRODUCT_ID}" in console
 * or open page in browser using url
 * http://localhost/example.php?api-key={API_KEY}&product-id={PRODUCT_ID}
 */

include_once 'vendor/autoload.php';

use Smartling\SmartlingApi;
use GuzzleHttp\Client;

if (!empty($_GET['api-key']) || !empty($_GET['product-id'])) {
  $key = $_GET['api-key'];
  $projectId = $_GET['product-id'];
}
else {
  foreach ($argv as $param) {
    if (strpos($param, '--api-key') === 0) {
      $key = substr($param, 10);
    }
    elseif (strpos($param, '--product-id') === 0) {
      $projectId = substr($param, 13);
    }
  }
}

if (empty($key) || empty($projectId)) {
  die('You have to specify Api Key and Product Id');
}

$baseUrl = 'https://api.smartling.com/v1/';
$fileName = 'test.xml';
$fileUri = 'test/resources/test.xml';
$fileRealPath = realpath($fileUri);
$fileType = 'xml';
$newFileName = 'new_test_file.xml';
$retrievalType = 'pseudo';

$content = file_get_contents(realpath($fileUri));
$fileContentUri = "testing_content.xml";

$translationState = 'PUBLISHED';
$locale = 'ru-RU';
$locales_array = array('ru-RU');

$client = new Client(['base_uri' => $baseUrl, 'debug' => TRUE]);
$api = new SmartlingApi($baseUrl, $key, $projectId, $client);

$result = $api->uploadFile($fileRealPath, $fileName, $fileType);
print (string) $result->getBody();
echo "\nThis is a upload file\n";

////try to download file
$result = $api->downloadFile($fileName, $locale, $retrievalType);
print (string) $result->getBody();
echo "\nThis is a download file\n";

$result = $api->getStatus($fileName, $locale);
print (string) $result->getBody();
echo "\nThis is a get status\n";

$result = $api->getAuthorizedLocales($fileName);
print (string) $result->getBody();
echo "\nThis is a get authorized locales\n";

$result = $api->getList($locale, ['limit' => 5, 'fileTypes' => 'xml', 'uriMask' => 'test']);
print (string) $result->getBody();
echo "\nThis is a get list\n";

$result = $api->renameFile($fileName, $newFileName);
print (string) $result->getBody();
$api->renameFile($newFileName, $fileName);
echo "\nThis is a rename file\n";

$result = $api->import($fileName, $fileType, $locale, $fileRealPath, $translationState, ['overwrite' => TRUE]);
print (string) $result->getBody();
echo "\nThis is a import file\n";

$result = $api->deleteFile($fileName);
print (string) $result->getBody();
echo "\nThis is delete file\n";

$result = $api->getLocaleList();
print (string) $result->getBody();
echo "\nThis is the list of project locales\n";
