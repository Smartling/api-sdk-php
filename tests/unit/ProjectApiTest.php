<?php

namespace Smartling\Tests\Unit;

use Smartling\Project\ProjectApi;

/**
 * Test class for Smartling\Project\ProjectsApi.
 */
class ProjectApiTest extends ApiTestAbstract
{
    /**
     * @covers \Smartling\Project\ProjectApi::getProjectDetails
     */
    public function testGetProjectDetails()
    {
        $endpointUrl = vsprintf(
            '%s/%s/',
            [
                ProjectApi::ENDPOINT_URL,
                $this->projectId
            ]
        );

        $this->client->expects($this->any())
            ->method('request')
            ->with('get', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => false,
                'query' => [],
            ])
            ->willReturn($this->responseMock);

        $this->object->getProjectDetails();
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->prepareProjectApiMock();
    }

    private function prepareProjectApiMock()
    {
        $this->object = $this->getMockBuilder('Smartling\Project\ProjectApi')
            ->setMethods(NULL)
            ->setConstructorArgs([
                $this->projectId,
                $this->client,
                null,
                ProjectApi::ENDPOINT_URL,
            ])
            ->getMock();

        $this->invokeMethod(
            $this->object,
            'setAuth',
            [
                $this->authProvider
            ]
        );
    }
}
