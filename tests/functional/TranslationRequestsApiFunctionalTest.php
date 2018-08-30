<?php

namespace Smartling\Tests\Unit;

use Smartling\AuthApi\AuthTokenProvider;
use Smartling\TranslationRequests\Params\CreateTranslationRequestParams;
use Smartling\TranslationRequests\Params\SearchTranslationRequestParams;
use Smartling\TranslationRequests\Params\UpdateTranslationRequestParams;
use Smartling\TranslationRequests\TranslationRequestsApi;

class TranslationRequestsApiFunctionalTest extends \PHPUnit_Framework_TestCase
{
    const BUCKET_NAME = 'tst-bucket';

    /**
     * @var TranslationRequestsApi
     */
    private $translationRequestsApi;

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
        $this->translationRequestsApi = TranslationRequestsApi::create($authProvider, $projectId);
    }


    /**
     * @covers \Smartling\TranslationRequests\TranslationRequestsApi::createTranslationRequest
     */
    public function testCreateTranslationRequest()
    {
        $time = (string)microtime(true);

        $createParams = (new CreateTranslationRequestParams())
            ->setOriginalAssetKey(['a' => $time])
            ->setTitle(vsprintf('Submission %s', [$time]))
            ->setFileUri(vsprintf('/posts/hello-world_1_%s_post.xml', [$time]))
            ->setOriginalLocaleId('en-US');

        $response = $this->translationRequestsApi->createTranslationRequest(self::BUCKET_NAME, $createParams);

        self::assertArraySubset($createParams->exportToArray(), $response);
        self::assertArrayHasKey('translationRequestUid', $response);
    }

    /**
     * @covers \Smartling\TranslationRequests\TranslationRequestsApi::updateTranslationRequest
     */
    public function testUpdateTranslationRequest()
    {
        $time = (string)microtime(true);

        $createParams = (new CreateTranslationRequestParams())
            ->setOriginalAssetKey(['a' => $time])
            ->setTitle(vsprintf('Submission %s', [$time]))
            ->setFileUri(vsprintf('/posts/hello-world_1_%s_post.xml', [$time]))
            ->setOriginalLocaleId('en-US');

        $response = $this->translationRequestsApi->createTranslationRequest(self::BUCKET_NAME, $createParams);

        self::assertArraySubset($createParams->exportToArray(), $response);
        self::assertArrayHasKey('translationRequestUid', $response);

        $translationRequestUid = $response['translationRequestUid'];

        $updateParams = (new UpdateTranslationRequestParams())
            ->setTitle('Submission UPDATED');


        $updateResponse = $this->translationRequestsApi->updateTranslationRequest(self::BUCKET_NAME, $translationRequestUid, $updateParams);

        self::assertArraySubset($updateParams->exportToArray(), $updateResponse);
        self::assertArrayHasKey('translationRequestUid', $updateResponse);
        self::assertEquals($translationRequestUid, $updateResponse['translationRequestUid']);
    }

    /**
     * @covers \Smartling\TranslationRequests\TranslationRequestsApi::getTranslationRequest
     */
    public function testGetTranslationRequest()
    {
        $time = (string)microtime(true);

        $createParams = (new CreateTranslationRequestParams())
            ->setOriginalAssetKey(['a' => $time])
            ->setTitle(vsprintf('Submission %s', [$time]))
            ->setFileUri(vsprintf('/posts/hello-world_1_%s_post.xml', [$time]))
            ->setOriginalLocaleId('en-US');

        $response = $this->translationRequestsApi->createTranslationRequest(self::BUCKET_NAME, $createParams);

        self::assertArraySubset($createParams->exportToArray(), $response);
        self::assertArrayHasKey('translationRequestUid', $response);

        $translationRequestUid = $response['translationRequestUid'];

        $getResponsePositive = $this->translationRequestsApi->getTranslationRequest(self::BUCKET_NAME, $translationRequestUid);

        self::assertArraySubset($createParams->exportToArray(), $getResponsePositive);
        self::assertArrayHasKey('translationRequestUid', $getResponsePositive);
    }

    /**
     * @covers \Smartling\TranslationRequests\TranslationRequestsApi::searchTranslationRequests
     */
    public function testSearchTranslationRequests()
    {
        $time = (string)microtime(true);

        $createParams = (new CreateTranslationRequestParams())
            ->setOriginalAssetKey(['a' => $time])
            ->setTitle(vsprintf('Submission %s', [$time]))
            ->setFileUri(vsprintf('/posts/hello-world_1_%s_post.xml', [$time]))
            ->setOriginalLocaleId('en-US');

        $response = $this->translationRequestsApi->createTranslationRequest(self::BUCKET_NAME, $createParams);

        self::assertArraySubset($createParams->exportToArray(), $response);
        self::assertArrayHasKey('translationRequestUid', $response);

        $translationRequestUid = $response['translationRequestUid'];

        $searchResponseEmpty = $this->translationRequestsApi->searchTranslationRequests(self::BUCKET_NAME,
            (new SearchTranslationRequestParams())->setFileUri(vsprintf('%%%s%%', [md5($time)]))
        );

        self::assertTrue(is_array($searchResponseEmpty));
        self::assertArrayHasKey('items', $searchResponseEmpty);
        $items = $searchResponseEmpty['items'];
        self::assertTrue(is_array($items));
        self::assertTrue(0 === count($items));

        $searchResponse = $this->translationRequestsApi->searchTranslationRequests(self::BUCKET_NAME,
            (new SearchTranslationRequestParams())->setFileUri(vsprintf('%%%s%%', [$time]))
        );

        self::assertTrue(is_array($searchResponse));
        self::assertArrayHasKey('items', $searchResponse);
        $items = $searchResponse['items'];
        self::assertTrue(is_array($items));
        self::assertTrue(1 === count($items));
        self::assertTrue($translationRequestUid === $items[0]['translationRequestUid']);
    }

}
