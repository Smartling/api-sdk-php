<?php

namespace Smartling\Tests\Functional;

use PHPUnit_Framework_TestCase;
use Smartling\AuthApi\AuthTokenProvider;
use Smartling\Exceptions\SmartlingApiException;
use Smartling\ProgressTracker\Params\CreateRecordParameters;
use Smartling\ProgressTracker\ProgressTrackerApi;

/**
 * Test class for Progress Tracker API examples.
 */
class ProgressTrackerApiFunctionalTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var ProgressTrackerApi
     */
    private $progressTraclerApi;

    /**
     * Test mixture.
     */
    public function setUp()
    {
        $projectId = getenv('project_id');
        $userIdentifier = getenv('user_id');
        $userSecretKey = getenv('user_key');

        if (
            empty($projectId) ||
            empty($userIdentifier) ||
            empty($userSecretKey)
        ) {
            $this->fail('Missing required parameters');
        }

        $authProvider = AuthTokenProvider::create($userIdentifier, $userSecretKey);
        $this->progressTraclerApi = ProgressTrackerApi::create($authProvider, $projectId);
    }

    /**
     * Tests for create record.
     */
    public function testCreateRecord()
    {
        try {
            $params = new CreateRecordParameters();
            $params->setTtl(15);
            $params->setData([
              "foo" => "bar"
            ]);
            $result = $this->progressTraclerApi->createRecord("space", "object", $params);

            $this->assertArrayHasKey('recordId', $result);
        } catch (SmartlingApiException $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * Test for get token.
     */
    public function testGetToken()
    {
        try {
            $result = $this->progressTraclerApi->getToken(getenv("account_uid"));

            $this->assertArrayHasKey('token', $result);
            $this->assertArrayHasKey('config', $result);
            $this->assertArrayHasKey('apiKey', $result['config']);
            $this->assertArrayHasKey('authDomain', $result['config']);
            $this->assertArrayHasKey('databaseURL', $result['config']);
            $this->assertArrayHasKey('projectId', $result['config']);
            $this->assertArrayHasKey('storageBucket', $result['config']);
            $this->assertArrayHasKey('messagingSenderId', $result['config']);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

}
