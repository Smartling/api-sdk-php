<?php

namespace Smartling\Tests\Functional;

use PHPUnit_Framework_TestCase;
use Smartling\AuthApi\AuthTokenProvider;
use Smartling\Context\Params\UploadContextParameters;
use Smartling\Exceptions\SmartlingApiException;
use Smartling\Context\ContextApi;

/**
 * Test class for Project API examples.
 */
class ContextApiFunctionalTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var ContextApi
     */
    private $contextApi;

    /**
     * Test mixture.
     */
    public function setUp() {
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
        $this->contextApi = ContextApi::create($authProvider, $projectId);
    }

    /**
     * Test for upload context.
     */
    public function testUploadContext() {
        try {
            $params = new UploadContextParameters();
            $params->setContextFileUri('tests/resources/context.html');
            $params->setName('test_context.html');
            $result = $this->contextApi->uploadContext($params);

            $this->assertArrayHasKey('contextUid', $result);
            $this->assertArrayHasKey('contextType', $result);
            $this->assertArrayHasKey('name', $result);
            $this->assertArrayHasKey('created', $result);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test for match context.
     */
    public function testMatchContext() {
        try {
            $params = new UploadContextParameters();
            $params->setContextFileUri('tests/resources/context.html');
            $params->setName('test_context.html');
            $contextInfo = $this->contextApi->uploadContext($params);
            $result = $this->contextApi->matchContext($contextInfo['contextUid']);

            $this->assertArrayHasKey('matchId', $result);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

}
