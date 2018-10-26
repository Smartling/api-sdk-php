<?php

namespace Smartling\Tests\Functional;

use PHPUnit_Framework_TestCase;
use Smartling\AuthApi\AuthTokenProvider;
use Smartling\Context\Params\MatchContextParameters;
use Smartling\Context\Params\UploadContextParameters;
use Smartling\Context\Params\UploadResourceParameters;
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
            $params->setContent('tests/resources/context.html');
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
            $fileUri = 'tests/resources/context.html';

            $params = new UploadContextParameters();
            $params->setContent($fileUri);
            $params->setName('test_context.html');
            $contextInfo = $this->contextApi->uploadContext($params);

            $params = new MatchContextParameters();
            $params->setContentFileUri($fileUri);

            $result = $this->contextApi->matchContext($contextInfo['contextUid'], $params);

            $this->assertArrayHasKey('matchId', $result);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test for upload and match context.
     */
    public function testUploadAndMatchContext() {
        try {
            $fileUri = 'tests/resources/context.html';

            $matchParams = new MatchContextParameters();
            $matchParams->setContentFileUri($fileUri);

            $params = new UploadContextParameters();
            $params->setContent($fileUri);
            $params->setMatchParams($matchParams);
            $params->setName('test_context.html');

            $result = $this->contextApi->uploadAndMatchContext($params);

            $this->assertArrayHasKey('matchId', $result);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test for get missing resources.
     */
    public function testGetMissingResources() {
        try {
            $result = $this->contextApi->getMissingResources();

            $this->assertArrayHasKey('items', $result);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test for get all missing resources.
     */
    public function testGetAllMissingResources() {
        try {
            $result = $this->contextApi->getAllMissingResources();

            $this->assertArrayHasKey('items', $result);
            $this->assertArrayHasKey('all', $result);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test for render context.
     */
    public function testRenderContext() {
        try {
            $params = new UploadContextParameters();
            $params->setContent('tests/resources/context.html');
            $params->setName('test_context.html');
            $contextInfo = $this->contextApi->uploadContext($params);
            $result = $this->contextApi->renderContext($contextInfo['contextUid']);

            $this->assertTrue($result);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

}
