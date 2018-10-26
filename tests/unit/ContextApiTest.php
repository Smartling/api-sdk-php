<?php

namespace Smartling\Tests;

use Smartling\Context\ContextApi;
use Smartling\Context\Params\MatchContextParameters;
use Smartling\Context\Params\MissingResourcesParameters;
use Smartling\Context\Params\UploadContextParameters;
use Smartling\Tests\Unit\ApiTestAbstract;
use Smartling\Context\Params\UploadResourceParameters;


/**
 * Test class for Smartling\Context\ContextApi.
 */
class ContextApiTest extends ApiTestAbstract
{

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->prepareContextApiMock();
    }

    private function prepareContextApiMock()
    {
        $this->object = $this->getMockBuilder('Smartling\Context\ContextApi')
            ->setMethods(['readFile'])
            ->setConstructorArgs([
                $this->projectId,
                $this->client,
                null,
                ContextApi::ENDPOINT_URL,
            ])
            ->getMock();

        $this->object->expects(self::any())
            ->method('readFile')
            ->willReturn($this->streamPlaceholder);

        $this->invokeMethod(
            $this->object,
            'setAuth',
            [
                $this->authProvider
            ]
        );
    }

    /**
     * @covers \Smartling\Context\ContextApi::uploadContext
     */
    public function testUploadContext() {
        $params = new UploadContextParameters();
        $params->setContent('./tests/resources/context.html');
        $params->setName('test_context.html');
        $endpointUrl = vsprintf('%s/%s/contexts', [
            ContextApi::ENDPOINT_URL,
            $this->projectId
        ]);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('post', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                    'X-SL-Context-Source' => $this->invokeMethod($this->object, 'getXSLContextSourceHeader'),
                ],
                'exceptions' => FALSE,
                'multipart' => [
                    [
                        'name' => 'content',
                        'contents' => $this->streamPlaceholder,
                    ],
                    [
                        'name' => 'name',
                        'contents' => 'test_context.html',
                    ],
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->uploadContext($params);
    }

    /**
     * @covers \Smartling\Context\ContextApi::uploadContext
     */
    public function testMatchContext() {
        $fileUri = './tests/resources/context.html';
        $contextUid = 'someContextUid';

        $params = new MatchContextParameters();
        $params->setContentFileUri($fileUri);

        $endpointUrl = vsprintf('%s/%s/contexts/%s/match/async', [
            ContextApi::ENDPOINT_URL,
            $this->projectId,
            $contextUid
        ]);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('post', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                    'X-SL-Context-Source' => $this->invokeMethod($this->object, 'getXSLContextSourceHeader'),
              ],
              'exceptions' => FALSE,
              'json' => [
                  'contentFileUri' => $fileUri,
              ],
            ])
            ->willReturn($this->responseMock);

        $this->object->matchContext($contextUid, $params);
    }

    /**
     * @covers \Smartling\Context\ContextApi::uploadAndMatchContext
     */
    public function testUploadAndMatchContext() {
        $fileUri = './tests/resources/context.html';

        $matchParams = new MatchContextParameters();
        $matchParams->setContentFileUri($fileUri);

        $params = new UploadContextParameters();
        $params->setContent($fileUri);
        $params->setMatchParams($matchParams);

        $endpointUrl = vsprintf('%s/%s/contexts/upload-and-match-async', [
            ContextApi::ENDPOINT_URL,
            $this->projectId
        ]);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('post', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                    'X-SL-Context-Source' => $this->invokeMethod($this->object, 'getXSLContextSourceHeader'),
                ],
                'exceptions' => FALSE,
                'multipart' => [
                    [
                        'name' => 'content',
                        'contents' => $this->streamPlaceholder,
                    ],
                    [
                        'name' => 'matchParams',
                        'contents' => '{"contentFileUri":".\/tests\/resources\/context.html"}',
                        'headers' => [
                            'Content-Type' => 'application/json',
                        ]
                    ],
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->uploadAndMatchContext($params);
    }

    /**
     * @covers \Smartling\Context\ContextApi::getMissingResources
     */
    public function testGetMatchStatus() {
        $matchId = 'test_match_id';
        $endpointUrl = vsprintf('%s/%s/match/%s', [
            ContextApi::ENDPOINT_URL,
            $this->projectId,
            $matchId,
        ]);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('get', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                    'X-SL-Context-Source' => $this->invokeMethod($this->object, 'getXSLContextSourceHeader'),
                ],
                'exceptions' => FALSE,
                'query' => [],
            ])
            ->willReturn($this->responseMock);

        $this->object->getMatchStatus($matchId);
    }

    /**
     * @covers \Smartling\Context\ContextApi::getMissingResources
     */
    public function testGetMissingResources() {
        $offset = 'some_offset';
        $params = new MissingResourcesParameters();
        $params->setOffset($offset);
        $endpointUrl = vsprintf('%s/%s/missing-resources', [
            ContextApi::ENDPOINT_URL,
            $this->projectId
        ]);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('get', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                    'X-SL-Context-Source' => $this->invokeMethod($this->object, 'getXSLContextSourceHeader'),
                ],
                'exceptions' => FALSE,
                'query' => [
                    'offset' => $offset,
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->getMissingResources($params);
    }

    /**
     * @covers \Smartling\Context\ContextApi::uploadResource
     */
    public function testUploadResource() {
        $resourceId = 'some_resource_id';
        $params = new UploadResourceParameters();
        $params->setFile('./tests/resources/test.png');
        $endpointUrl = vsprintf('%s/%s/resources/%s', [
            ContextApi::ENDPOINT_URL,
            $this->projectId,
            $resourceId,
        ]);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('put', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                    'X-SL-Context-Source' => $this->invokeMethod($this->object, 'getXSLContextSourceHeader'),
                ],
                'exceptions' => FALSE,
                'multipart' => [
                    [
                        'name' => 'resource',
                        'contents' => $this->streamPlaceholder,
                    ],
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->uploadResource($resourceId, $params);
    }

    /**
     * @covers \Smartling\Context\ContextApi::renderContext
     */
    public function testRenderContext() {
        $contextUid = 'someContextUid';
        $endpointUrl = vsprintf('%s/%s/contexts/%s/render', [
            ContextApi::ENDPOINT_URL,
            $this->projectId,
            $contextUid
        ]);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('post', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                    'X-SL-Context-Source' => $this->invokeMethod($this->object, 'getXSLContextSourceHeader'),
                ],
                'exceptions' => FALSE,
                'form_params' => [],
            ])
            ->willReturn($this->responseMock);

        $this->object->renderContext($contextUid);
    }

}
