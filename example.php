<?php

include_once 'lib/SmartlingAPI.php';

$key = "";
$projectId = "";

$baseUrl = 'https://api.smartling.com/v1';
$fileUri = 'testing.xml';
$fileType = 'xml';
$newFileUri = 'newfile.xml';
$fileName = 'translated.xml';

$content = file_get_contents(realpath('./test.xml'));
$fileContentUri = "testing_content.xml";

$translationState = 'PUBLISHED';
$locale = 'ru-RU';
$locales_array = array('ru-RU');


//init api object
$api = new SmartlingAPI($baseUrl, $key, $projectId);

$params = array(
  'approved' => true,
);

$upload_params = new FileUploadParameterBuilder();
$upload_params->setFileUri($fileUri)
    ->setFileType($fileType)
    ->setLocalesToApprove($locales_array)
    // Error: response code -> VALIDATION_ERROR and message ->
    // Failed to convert property value of type java.lang.String to required type boolean
    // for property overwriteApprovedLocales; nested exception
    // is java.lang.IllegalArgumentException: Invalid boolean value []
    ->setOverwriteApprovedLocales(0) // Must be set 0 (not FALSE).
    ->setApproved(0)
    ->setCallbackUrl('http://test.com/smartling');
$upload_params = $upload_params->buildParameters();


//try to upload file
$result = $api->uploadFile('./test.xml', $upload_params);
var_dump($result);
echo "<br />This is a upload file<br />";

//try to upload content
$result = $api->uploadContent($content, $upload_params);
var_dump($result);
echo "<br />This is a upload content<br />";

//try to download file
$result = $api->downloadFile($fileUri, $locale);
var_dump($result);
echo "<br />This is a download file<br />";

//try to retrieve file status
$result = $api->getStatus($fileUri, $locale);
var_dump($result);
echo "<br />This is a get status<br />";


//try get files list
$result = $api->getList($locale);
var_dump($result);
echo "<br />This is a get list<br />";

//try to rename file
$result = $api->renameFile($fileUri, $newFileUri);
var_dump($result);
echo "<br />This is a rename file<br />";

//try to import
$result = $api->import($newFileUri, $fileType, $locale, './test.xml', true, $translationState);
var_dump($result);
echo "<br />This is a import file<br />";

//try to delete file
$result = $api->deleteFile($newFileUri);
var_dump($result);
echo "<br />This is delete file<br />";