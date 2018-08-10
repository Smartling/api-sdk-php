<?php

namespace Smartling\Tests\Unit;

use Smartling\AuthApi\AuthTokenProvider;
use Smartling\Submissions\Params\CreateSubmissionParams;
use Smartling\Submissions\Params\SearchSubmissionsParams;
use Smartling\Submissions\Params\UpdateSubmissionParams;
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

        $createParams = (new CreateSubmissionParams())
            ->setOriginalAssetId(['a' => $time])
            ->setTitle(vsprintf('Submission %s', [$time]))
            ->setFileUri(vsprintf('/posts/hello-world_1_%s_post.xml', [$time]))
            ->setOriginalLocale('en-US');

        $response = $this->submissionsApi->createSubmission(self::BUCKET_NAME, $createParams);

        self::assertArraySubset($createParams->exportToArray(), $response);
        self::assertArrayHasKey('submission_uid', $response);
    }

    /**
     * @covers \Smartling\Submissions\SubmissionsApi::updateSubmission
     */
    public function testUpdateSubmission()
    {
        $time = (string)microtime(true);

        $createParams = (new CreateSubmissionParams())
            ->setOriginalAssetId(['a' => $time])
            ->setTitle(vsprintf('Submission %s', [$time]))
            ->setFileUri(vsprintf('/posts/hello-world_1_%s_post.xml', [$time]))
            ->setOriginalLocale('en-US');

        $response = $this->submissionsApi->createSubmission(self::BUCKET_NAME, $createParams);

        self::assertArraySubset($createParams->exportToArray(), $response);
        self::assertArrayHasKey('submission_uid', $response);

        $submissionUid = $response['submission_uid'];

        $updateParams = (new UpdateSubmissionParams())
            ->setTitle('Submission UPDATED');


        $updateResponse = $this->submissionsApi->updateSubmission(self::BUCKET_NAME, $submissionUid, $updateParams);

        self::assertArraySubset($updateParams->exportToArray(), $updateResponse);
        self::assertArrayHasKey('submission_uid', $updateResponse);
        self::assertEquals($submissionUid, $updateResponse['submission_uid']);
    }

    /**
     * @covers \Smartling\Submissions\SubmissionsApi::getSubmission
     */
    public function testGetSubmission()
    {
        $time = (string)microtime(true);

        $createParams = (new CreateSubmissionParams())
            ->setOriginalAssetId(['a' => $time])
            ->setTitle(vsprintf('Submission %s', [$time]))
            ->setFileUri(vsprintf('/posts/hello-world_1_%s_post.xml', [$time]))
            ->setOriginalLocale('en-US');

        $response = $this->submissionsApi->createSubmission(self::BUCKET_NAME, $createParams);

        self::assertArraySubset($createParams->exportToArray(), $response);
        self::assertArrayHasKey('submission_uid', $response);

        $submissionUid = $response['submission_uid'];

        $getResponsePositive = $this->submissionsApi->getSubmission(self::BUCKET_NAME, $submissionUid);

        self::assertArraySubset($createParams->exportToArray(), $getResponsePositive);
        self::assertArrayHasKey('submission_uid', $getResponsePositive);
    }

    /**
     * @covers \Smartling\Submissions\SubmissionsApi::searchSubmissions
     */
    public function testSearchSubmissions()
    {
        $time = (string)microtime(true);

        $createParams = (new CreateSubmissionParams())
            ->setOriginalAssetId(['a' => $time])
            ->setTitle(vsprintf('Submission %s', [$time]))
            ->setFileUri(vsprintf('/posts/hello-world_1_%s_post.xml', [$time]))
            ->setOriginalLocale('en-US');

        $response = $this->submissionsApi->createSubmission(self::BUCKET_NAME, $createParams);

        self::assertArraySubset($createParams->exportToArray(), $response);
        self::assertArrayHasKey('submission_uid', $response);

        $submissionUid = $response['submission_uid'];

        $searchResponseEmpty = $this->submissionsApi->searchSubmissions(self::BUCKET_NAME,
            (new SearchSubmissionsParams())->setFileUri(vsprintf('%%%s%%', [md5($time)]))
        );

        self::assertTrue(is_array($searchResponseEmpty));
        self::assertArrayHasKey('items', $searchResponseEmpty);
        $items = $searchResponseEmpty['items'];
        self::assertTrue(is_array($items));
        self::assertTrue(0 === count($items));

        $searchResponse = $this->submissionsApi->searchSubmissions(self::BUCKET_NAME,
            (new SearchSubmissionsParams())->setFileUri(vsprintf('%%%s%%', [$time]))
        );

        self::assertTrue(is_array($searchResponse));
        self::assertArrayHasKey('items', $searchResponse);
        $items = $searchResponse['items'];
        self::assertTrue(is_array($items));
        self::assertTrue(1 === count($items));
        self::assertTrue($submissionUid === $items[0]['submission_uid']);
    }

}
