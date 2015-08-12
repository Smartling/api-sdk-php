<?php

use Smartling\Api\HttpClientInterface;
use Smartling\Api\SmartlingApi;

/**
 * Test class for SmartlingAPI.
 * Generated by PHPUnit on 2013-10-17 at 14:51:02.
 */
class SmartlingAPITest extends PHPUnit_Framework_TestCase {

    /**
     * @var SmartlingAPI
     */
    protected $object;
    protected $_apiKey = 'TEST_API_KEY';
    protected $_projectId = 'TEST_PROJECT_ID';
    protected $conenction;
    

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->conenction = $this->getMockBuilder('Smartling\\Api\\HttpClientInterface')
          ->setMethods([
            'request',
            'setMethod',
            'requireUploadFile',
            'requireUploadContent',
            'setUri',
            'getContent',
            'getStatus',
            'getErrorMessage',
          ])
          ->disableOriginalConstructor()
          ->getMock();
        $this->conenction->expects($this->any())
          ->method('request')
          ->will($this->returnValue(TRUE));
        $this->conenction->expects($this->any())
          ->method('setMethod')
          ->will($this->returnSelf());
        $this->conenction->expects($this->any())
          ->method('requireUploadFile')
          ->will($this->returnSelf());
        $this->conenction->expects($this->any())
          ->method('requireUploadContent')
          ->will($this->returnSelf());
        $this->conenction->expects($this->any())
          ->method('setUri')
          ->will($this->returnSelf());
        $this->object = new SmartlingAPI(SmartlingAPI::SANDBOX_URL, $this->_apiKey, $this->_projectId, SmartlingApi::SANDBOX_MODE, $this->conenction);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }
    
    /**
     * 
     * @param object $object
     * @param string $methodName
     * @param array $parameters
     * @return string | null | int | object | bool | resource | float 
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @covers SmartlingAPI::uploadFile
     */
    public function testUploadFile() {
      $this->conenction->expects($this->any())
        ->method('getContent')
        ->will($this->returnValue('VALUE'));

      $this->conenction->expects($this->any())
        ->method('request')
        ->with(['custom_param' => 'custom_value', 'file' => 'resources/test.xml', 'apiKey' => $this->_apiKey, 'projectId' => $this->_projectId])
        ->will($this->returnValue(TRUE));

      $this->conenction->expects($this->any())
        ->method('requireUploadFile')
        ->with(TRUE)
        ->will($this->returnSelf());

      $this->conenction->expects($this->any())
        ->method('requireUploadContent')
        ->with(FALSE)
        ->will($this->returnSelf());

      $this->conenction->expects($this->any())
        ->method('setMethod')
        ->with(HttpClientInterface::REQUEST_TYPE_POST)
        ->will($this->returnSelf());

      $this->assertEquals('VALUE', $this->object->uploadFile('resources/test.xml', ['custom_param' => 'custom_value']));
    }

    /**
     * @covers SmartlingAPI::downloadFile
     */
    public function testDownloadFile() {
      $this->conenction->expects($this->any())
        ->method('getContent')
        ->will($this->returnValue('VALUE'));

      $this->conenction->expects($this->any())
        ->method('request')
        ->with(['custom_param' => 'custom_value', 'fileUri' => 'resources/test.xml', 'locale' => 'en-EN', 'apiKey' => $this->_apiKey, 'projectId' => $this->_projectId])
        ->will($this->returnValue(TRUE));

      $this->conenction->expects($this->any())
        ->method('requireUploadFile')
        ->with(FALSE)
        ->will($this->returnSelf());

      $this->conenction->expects($this->any())
        ->method('requireUploadContent')
        ->with(FALSE)
        ->will($this->returnSelf());

      $this->conenction->expects($this->any())
        ->method('setMethod')
        ->with(HttpClientInterface::REQUEST_TYPE_GET)
        ->will($this->returnSelf());

      $this->assertEquals('VALUE', $this->object->downloadFile('resources/test.xml', 'en-EN', ['custom_param' => 'custom_value']));
    }

    /**
     * @covers SmartlingAPI::getStatus
     * @todo Implement testGetStatus().
     */
    public function _testGetStatus() {
        // Remove the following lines when you implement this test.
        $this->assertNotEmpty(
                $this->object->getStatus('testing.xml', "ru_RU")
        );
        
        $this->assertInternalType(
                'string',
                $this->object->getStatus('testing.xml', "ru_RU")
                );
    }

    /**
     * @covers SmartlingAPI::getList
     * @todo Implement testGetList().
     */
    public function _testGetList() {
        
        $this->assertNotEmpty(
                $this->object->getList("ru_RU")
        );
        
        $this->assertInternalType(
                'string',
                $this->object->getList("ru_RU")
                );
    }

    /**
     * @covers SmartlingAPI::renameFile
     * @todo Implement testRenameFile().
     */
    public function _testRenameFile() {
       
        $this->assertNotEmpty(
                $this->object->renameFile('testing.xml', 'newTestFile.xml')
        );
        
        $this->assertInternalType(
                'string',
                $this->object->renameFile('testing.xml', 'newTestFile.xml')
                );
    }

    /**
     * @covers SmartlingAPI::getAuthorizedLocales
     */
    public function _testGetAuthorizedLocales() {

        $this->assertNotEmpty(
                $this->object->getAuthorizedLocales('testing.xml')
        );

        $this->assertInternalType(
                'string',
                $this->object->getAuthorizedLocales('testing.xml')
                );
    }

    /**
     * @covers SmartlingAPI::import
     */
    public function _testImport(){
        $this->assertNotEmpty(
                $this->object->import('translated.xml', 'xml', 'ru-RU', '../test.xml', true, 'PUBLISHED')
        );
        
        $this->assertInternalType(
                'string',
                $this->object->import('translated.xml', 'xml', 'ru-RU', '../test.xml', true, 'PUBLISHED')
                );
    }

    /**
     * @covers SmartlingAPI::deleteFile
     * @todo Implement testDeleteFile().
     */
    public function _testDeleteFile() {
        
        $this->assertNotEmpty(
                $this->object->deleteFile('newTestFile.xml')
        );
        
        $this->assertInternalType(
                'string',
                $this->object->deleteFile('newTestFile.xml')
                );
    }

    
    /**
     * @covers SmartlingAPI::uploadFile
     */
    public function _testUploadFileSuccess(){
        $this->object->uploadFile('../test.xml');
        $this->assertTrue(
                "SUCCESS" == $this->object->getCodeStatus()
                );
    }    
    
    /**
     * SmartlingAPI::getStatus
     */
    public function _testGetStatusSuccess(){
       $this->object->getStatus('testing.xml', "ru-RU");
        $this->assertTrue(
                "SUCCESS" == $this->object->getCodeStatus()
                );
    }
    
    /**
     * SmartlingAPI::getList 
     */
    public function _testGetListSuccess(){
        $this->object->getList("ru-RU");
        $this->assertTrue(
                "SUCCESS" == $this->object->getCodeStatus()
                );
    }
    
    /**
     * @covers SmartlingAPI::renameFile
     */
    public function _testRenameFileSuccess(){
        $this->object->renameFile('testing.xml', 'newTestFile.xml');
        $this->assertTrue(
                "SUCCESS" == $this->object->getCodeStatus()
                );
    }
    
    /**
     * @covers SmartlingAPI::deleteFile
     */
    public function _testDeleteFileSuccess(){
        $this->object->deleteFile('newTestFile.xml');
        $this->assertTrue(
                "SUCCESS" == $this->object->getCodeStatus()
                );
    }

    /**
     * @covers SmartlingAPI::getAuthorizedLocales
     */
    public function _testGetAuthorizedLocalesSuccess(){
        $this->object->getAuthorizedLocales('testing.xml');
        $this->assertTrue(
                "SUCCESS" == $this->object->getCodeStatus()
                );
    }

    /**
     * @covers SmartlingAPI::getLocaleList
     */
    public function _testGetLocaleListSuccess(){
        $this->object->getLocaleList();
        $this->assertTrue(
            "SUCCESS" == $this->object->getCodeStatus()
        );
    }

    /**
     * @covers SmartlingAPI::sendRequest
     */
    public function _testSendRequest(){
        
        //check response type
        $this->assertInternalType(
                'string',
                $this->invokeMethod($this->object, 'sendRequest', array(
                    '',
                    array(),
                    'POST'
                ))
                );
        
        //check not equals false
        $this->assertNotEquals(
                false,
                $this->invokeMethod($this->object, 'sendRequest', array(
                    '',
                    array(),
                    'POST'
                ))
                );
        
    }
    
    /**
     * @covers SmartlingAPI::getCodeStatus
     */
    public function _testGetCodeStatus(){
        
        $this->invokeMethod($this->object, 'sendRequest', array(
                    '',
                    array(),
                    'POST'
                ));
        
        //check response type
        $this->assertInternalType(
                'string',
                $this->object->getCodeStatus()
                );
        
        //not equals false
        $this->assertNotEquals(
                false,
                $this->object->getCodeStatus()
                );
    }
}