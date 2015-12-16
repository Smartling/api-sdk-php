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
use Smartling\SmartlingFileApi;

use Smartling\Params\DownloadFileParameters;
use Smartling\Params\ListFilesParameters;

if (!empty($_GET['user-id']) || !empty($_GET['secret-key']) || !empty($_GET['project-id'])) {
  $projectId = $_GET['project-id'];
  $userSecretKey = $_GET['secret-key'];
  $userIdentifier = $_GET['user-id'];
} else {
  $projectId = '';
  $userSecretKey = '';
  $userIdentifier = '';
}

$fileName = 'test.xml';
$fileUri = 'tests/resources/test.xml';
$fileRealPath = realpath($fileUri);
$fileType = 'xml';
$newFileName = 'new_test_file.xml';
$retrievalType = 'pseudo';
$content = file_get_contents(realpath($fileUri));
$fileContentUri = "testing_content.xml";
$translationState = 'PUBLISHED';
$locale = 'ru-RU';
$locales_array = array('ru-RU');

$api = SmartlingFileApi::create($projectId, $userIdentifier, $userSecretKey);

$result = $api->uploadFile($fileRealPath, $fileName, $fileType);
var_dump($result);
echo "\nThis is a upload file\n";

//try to download file
$params = new DownloadFileParameters();
$params->setRetrievalType($retrievalType);
$result = $api->downloadFile($fileName, $locale, $params);
var_dump($result);
echo "\nThis is a download file\n";

$result = $api->getStatus($fileName, $locale);
var_dump($result);
echo "\nThis is a get status\n";

$result = $api->getAuthorizedLocales($fileName);
var_dump($result);
echo "\nThis is a get authorized locales\n";

$params = new ListFilesParameters();
$params
  ->setFileTypes('xml')
  ->setUriMask('test')
  ->setLimit(5);
$result = $api->getList($params);
var_dump($result);
echo "\nThis is a get list\n";

$result = $api->renameFile($fileName, $newFileName);
var_dump($result);
$api->renameFile($newFileName, $fileName);
echo "\nThis is a rename file\n";

$result = $api->import($locale, $fileName, $fileType, $fileRealPath, $translationState, TRUE);
var_dump($result);
echo "\nThis is a import file\n";

$result = $api->deleteFile($fileName);
var_dump($result);
echo "\nThis is delete file\n";