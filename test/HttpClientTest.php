<?php
require_once dirname(__FILE__) . '/../lib/HttpClient.php';
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HttpClient
 *
 * @author snail
 */
class HttpClientTest extends PHPUnit_Framework_TestCase {
   
    /**
     * @var Calculator
     */
    protected $object;
    
    /**
     * @var host
     */    
    protected $_host = 'https://sandbox-api.smartling.com/v1/';
    
    protected $_port = 443;
    
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new HttpClient($this->_host, $this->_port);        
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }
    
    /**
     * @covers HttpClient::request
     * @todo Implement testRequest().
     */
    public function testRequest(){
        $this->object->setUseGzip(true);
        
        //request without error
        $this->assertTrue(
                $this->object->request());
                
        $object = new HttpClient('http://dummyhost/', 443);
        $object->setUseGzip(true);
        
        //request with error
        $this->assertFalse(                
                $object->request()
                );
    } 
    
    /**
     * @covers HttpClient::GetContent
     * @todo Implement testGetContentEqualsString().
     */
    public function testGetContentEqualsString(){
        $this->object->request();
        $this->assertInternalType(
                'string',
                $this->object->getContent()                               
                );
    }
    
    /**
     * @covers HttpClient::GetContent
     * @todo Implement testGetContentNotEmpty().
     */
    public function testGetContentNotEmpty(){
        $this->object->request();
        $this->assertNotEmpty(
                $this->object->getContent()                
                );
    }
    
    /**
     * @covers HttpClient::setRequest
     * @todo Implement testSetRequestData().
     */
    public function testSetRequestData(){
        try{
            $this->object->setRequestData(20);
        } catch (Exception $e){
            return $this->assertEquals('Uncorrect parameters data type', $e->getMessage());
        }
         $this->fail('An exception should be thrown if the argument is not string or array or object');
    }
    
    /**
     * @covers HttpClient::setRequest
     * @todo Implement testSetRequestDataInternalType().
     */
    public function testSetRequestDataInternalType(){
        $this->assertInternalType(
                'object',
                $this->object->setRequestData(array('id' => 20))
                );
    }
    
    /**
     * @covers HttpClient::_connection
     * @todo Implement testConnection().
     */
    public function testConnectionSuccess(){
        $this->assertInternalType(
                'resource',
                $this->invokeMethod($this->object, '_connect')
                );
        
    }
    
    /**
     * @covers HttpClient::_builtRequest()
     * @todo Implement testBuildRequest().
     */
    public function testBuildRequest(){
        $this->object->setRequestData(array('id' => 20))
                     ->setMethod(HttpClient::REQUEST_TYPE_POST);                     
        
        //check type
        $this->assertInternalType(
                'string',
                $this->invokeMethod($this->object, '_buildRequest')
                );
        
        //check empty
        $this->assertNotEmpty(
                $this->invokeMethod($this->object, '_buildRequest')
                );
        
        $this->object->setMethod(HttpClient::REQUEST_TYPE_GET);
        
        //check empty in get request
         $this->assertNotEmpty(
                $this->invokeMethod($this->object, '_buildRequest')
                );
    }
    
    /**
     * @covers HttpClient::_buildQuery
     * @todo Implement testBuildQuery().
     */
    public function testBuildQuery(){
        $this->object->setMethod(HttpClient::REQUEST_TYPE_POST)
                     ->setNeedUploadFile(true);
        
        //check empty
        $this->assertNotEmpty(
                $this->invokeMethod($this->object, '_buildQuery', array(array('file' => '../test.xml')))
                );
        
        //check empty in get request
        $this->object->setMethod(HttpClient::REQUEST_TYPE_GET);
        
        $this->assertNotEmpty(
                $this->invokeMethod($this->object, '_buildQuery', array(array('id' => 20)))
                );
        
        // check null if uncorrect data type
        $this->assertNull(
                $this->invokeMethod($this->object, '_buildQuery', array(array()))
                );
        
        $this->assertNull(
                $this->invokeMethod($this->object, '_buildQuery', array("test"))
                );
    }
    
        
    /**
     * @covers HttpClient::getStatus
     * @todo Implement testGetStatus().
     */
    public function testGetStatus(){
        $this->object->request();
       
        //check result type
        $this->assertInternalType(
                'string',
                $this->object->getStatus()
                );
        
        //must be less than 600
        $this->assertLessThan(
                '600',
                $this->object->getStatus()
                );
    }
    
    /**
     * @covers HttpClient::setNeedUploadFile
     * @todo Implement testSetNeedUploadFile().
     */
    public function testSetNeedUploadFile(){
        $this->assertInternalType(
                'object',
                $this->object->setNeedUploadFile("test")
                );       
    }    
}

?>
