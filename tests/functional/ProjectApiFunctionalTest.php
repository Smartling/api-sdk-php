<?php

namespace Smartling\Tests\Functional;

use PHPUnit_Framework_TestCase;
use Smartling\AuthApi\AuthTokenProvider;
use Smartling\Exceptions\SmartlingApiException;
use Smartling\Project\ProjectApi;

/**
 * Test class for Project API examples.
 */
class ProjectApiFunctionalTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var ProjectApi
     */
    private $projectApi;

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
        $this->projectApi = ProjectApi::create($authProvider, $projectId);
    }

    /**
     * Test for project details.
     */
    public function testProjectDetails() {
        try {
            $result = $this->projectApi->getProjectDetails();

            $this->assertArrayHasKey('projectId', $result);
            $this->assertArrayHasKey('projectName', $result);
            $this->assertArrayHasKey('accountUid', $result);
            $this->assertArrayHasKey('archived', $result);
            $this->assertArrayHasKey('projectTypeCode', $result);
            $this->assertArrayHasKey('projectTypeDisplayValue', $result);
            $this->assertArrayHasKey('targetLocales', $result);
            $this->assertArrayHasKey('sourceLocaleId', $result);
            $this->assertArrayHasKey('sourceLocaleDescription', $result);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

}
