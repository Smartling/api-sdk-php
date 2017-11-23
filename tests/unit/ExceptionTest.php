<?php

namespace Smartling\Tests;
use Smartling\Exceptions\SmartlingApiException;

/**
 * Test class for Smartling\Exceptions\SmartlingApiException.
 */
class ExceptionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Check exception's constructor with string error passed in.
     */
    public function testConstructorStringMessage() {
        $exceptionMessage = 'Exception message';
        $e = new SmartlingApiException($exceptionMessage);

        $this->assertTrue(is_string($e->getMessage()));
        $this->assertNotEmpty($e->getMessage());
        $this->assertSame($exceptionMessage, $e->getMessage());
    }

    /**
     * Check exception's constructor with array of errors passed in.
     */
    public function testConstructorArrayMessage() {
        $exceptionArray = [
            'errors' => [
                [
                    'key' => 'error_key',
                    'message' => 'Error message.',
                    'details' => [
                        'errorId' => 'error_id',
                    ],
                ],
            ],
        ];
        $e = new SmartlingApiException($exceptionArray);

        $this->assertTrue(is_string($e->getMessage()));
        $this->assertNotEmpty($e->getMessage());
        $this->assertSame(print_r($exceptionArray, TRUE), $e->getMessage());
    }

}
