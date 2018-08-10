<?php

namespace Smartling\Tests\Unit;

use Smartling\AuthApi\AuthTokenProvider;
use Smartling\Submissions\Params\SearchSubmissionsParams;
use Smartling\Submissions\SubmissionsApi;

class SubmissionsApiFunctionalTest extends \PHPUnit_Framework_TestCase
{
    const BUCKET_NAME = 'tst-bucket';

    /**
     * @var SubmissionsApi
     */
    private $submissionsApi;

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
        $this->submissionsApi = SubmissionsApi::create($authProvider, $projectId);
    }


    /**
     * @covers \Smartling\Submissions\SubmissionsApi::createSubmission
     */
    public function testCreateSubmission()
    {
        $time = (string)microtime(true);

        $testRequestBody = [
            'original_asset_id' => ['a' => $time],
            'title' => vsprintf('Submission %s', [$time]),
            'fileUri' => vsprintf('/posts/hello-world_1_%s_post.xml', [$time]),
            'original_locale' => 'en-US'
        ];

        $response = $this->submissionsApi->createSubmission(self::BUCKET_NAME, $testRequestBody);

        self::assertArraySubset($testRequestBody, $response);
        self::assertArrayHasKey('submission_uid', $response);
    }

    /**
     * @covers \Smartling\Submissions\SubmissionsApi::updateSubmission
     */
    public function testUpdateSubmission()
    {
        $time = (string)microtime(true);

        $testRequestBody = [
            'original_asset_id' => ['a' => $time],
            'title' => vsprintf('Submission %s', [$time]),
            'fileUri' => vsprintf('/posts/hello-world_1_%s_post.xml', [$time]),
            'original_locale' => 'en-US'
        ];

        $response = $this->submissionsApi->createSubmission(self::BUCKET_NAME, $testRequestBody);

        self::assertArraySubset($testRequestBody, $response);
        self::assertArrayHasKey('submission_uid', $response);

        $submissionUid = $response['submission_uid'];

        $testUpdateBody = [
            'title' => 'Submission UPDATED',
        ];

        $updateResponse = $this->submissionsApi->updateSubmission(self::BUCKET_NAME, $submissionUid, $testUpdateBody);

        self::assertArraySubset($testUpdateBody, $updateResponse);
        self::assertArrayHasKey('submission_uid', $updateResponse);
        self::assertEquals($submissionUid, $updateResponse['submission_uid']);
    }

    /**
     * @covers \Smartling\Submissions\SubmissionsApi::getSubmission
     */
    public function testGetSubmission()
    {
        $time = (string)microtime(true);

        $testRequestBody = [
            'original_asset_id' => ['a' => $time],
            'title' => vsprintf('Submission %s', [$time]),
            'fileUri' => vsprintf('/posts/hello-world_1_%s_post.xml', [$time]),
            'original_locale' => 'en-US'
        ];

        $response = $this->submissionsApi->createSubmission(self::BUCKET_NAME, $testRequestBody);

        self::assertArraySubset($testRequestBody, $response);
        self::assertArrayHasKey('submission_uid', $response);

        $submissionUid = $response['submission_uid'];

        $getResponsePositive = $this->submissionsApi->getSubmission(self::BUCKET_NAME, $submissionUid);

        self::assertArraySubset($testRequestBody, $getResponsePositive);
        self::assertArrayHasKey('submission_uid', $getResponsePositive);

        $getResponseNegative = $this->submissionsApi->getSubmission(self::BUCKET_NAME, md5($submissionUid));

        self::assertEquals([], $getResponseNegative);

    }

    /**
     * @covers \Smartling\Submissions\SubmissionsApi::searchSubmissions
     */
    public function testSearchSubmissions()
    {
        $time = (string)microtime(true);


        $testRequestBody = [
            'original_asset_id' => ['a' => $time, 'b' => 'c'],
            'title' => vsprintf('Submission %s', [$time]),
            'fileUri' => vsprintf('/posts/hello-world_1_%s_post.xml', [$time]),
            'original_locale' => 'en-US'
        ];

        $response = $this->submissionsApi->createSubmission(self::BUCKET_NAME, $testRequestBody);

        self::assertArraySubset($testRequestBody, $response);
        self::assertArrayHasKey('submission_uid', $response);

        $submissionUid = $response['submission_uid'];

        $searchResponse = $this->submissionsApi->searchSubmissions(self::BUCKET_NAME,
            (new SearchSubmissionsParams())
                ->setFileUri(vsprintf('%%%s%%', [$time]))
        );

        self::assertTrue(is_array($searchResponse));
        self::assertArrayHasKey('items', $searchResponse);
        $items = $searchResponse['items'];
        self::assertTrue(is_array($items));
        self::assertTrue(1 === count($items));
        $item = reset($items);

        self::assertArraySubset($testRequestBody, $item);
        self::assertArrayHasKey('submission_uid', $item);
        self::assertEquals($submissionUid, $item['submission_uid']);
    }

}
