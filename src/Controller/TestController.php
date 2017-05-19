<?php
namespace NYPL\Services\Controller;

use Aws\Lambda\LambdaClient;
use NYPL\Services\Model\DataModel\StreamTest;
use NYPL\Services\Model\DataModel\StreamMapping;
use NYPL\Services\Model\DataModel\StreamTestResult;
use NYPL\Services\Model\DataModel\StreamTestResultDifference;
use NYPL\Services\Model\DataModel\StreamTestResultSummary;
use NYPL\Services\Model\Response\SuccessResponse\StreamTestResultsResponse;
use NYPL\Starter\Cache;
use NYPL\Starter\Config;
use NYPL\Starter\Controller;

/**
 * @SWG\Tag(
 *   name="streamtests",
 *   description="Stream Test API"
 * )
 */
final class TestController extends Controller
{
    /**
     * @var LambdaClient
     */
    protected $lambdaClient;

    /**
     * @return LambdaClient
     */
    public function getLambdaClient()
    {
        if (!$this->lambdaClient) {
            $this->setLambdaClient(
                new LambdaClient([
                    'version' => 'latest',
                    'region'  => Config::get('AWS_DEFAULT_REGION'),
                    'credentials' => [
                        'key' => Config::get('AWS_ACCESS_KEY_ID'),
                        'secret' => Config::get('AWS_SECRET_ACCESS_KEY'),
                        'token' => Config::get('AWS_SESSION_TOKEN')
                    ]
                ])
            );
        }

        return $this->lambdaClient;
    }

    /**
     * @param LambdaClient $lambdaClient
     */
    public function setLambdaClient($lambdaClient)
    {
        $this->lambdaClient = $lambdaClient;
    }

    protected function deleteMapping($streamArn = '')
    {
        $mappings = $this->getLambdaClient()->listEventSourceMappings([
            'FunctionName' => Config::get('STREAM_TEST_LISTENER_FUNCTION'),
            'EventSourceArn' => $streamArn
        ]);

        if (is_array($mappings->get('EventSourceMappings'))) {
            foreach ($mappings->get('EventSourceMappings') as $mapping) {
                $this->getLambdaClient()->deleteEventSourceMapping([
                    'UUID' => $mapping['UUID']
                ]);
            }
        }
    }

    /**
     * @SWG\Post(
     *     path="/v0.1/stream-tests/start",
     *     summary="Start a Stream Test",
     *     tags={"streamtests"},
     *     operationId="startTest",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="StreamTest",
     *         in="body",
     *         description="",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/StreamTest")
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Successful operation",
     *         @SWG\Schema(ref="#/definitions/StreamTest")
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Bad request",
     *         @SWG\Schema(ref="#/definitions/ErrorResponse")
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="Generic server error",
     *         @SWG\Schema(ref="#/definitions/ErrorResponse")
     *     ),
     *     security={
     *         {
     *             "api_auth": {"openid api"}
     *         }
     *     }
     * )
     */
    public function startTest()
    {
        $streamTest = new StreamTest($this->getRequest()->getParsedBody());
        $streamTest->create(true);

        foreach ($streamTest->getStreamMappings() as $streamMapping) {
            $streamMapping->create(true);

            Cache::getCache()->del($streamMapping->getStreamRecordKeyName());
            Cache::getCache()->del($streamMapping->getStreamRecordListKeyName());

            $this->deleteMapping($streamMapping->getEventSourceArn());

            $this->getLambdaClient()->createEventSourceMapping([
                'EventSourceArn' => $streamMapping->getEventSourceArn(),
                'FunctionName' => Config::get('STREAM_TEST_LISTENER_FUNCTION'),
                'Enabled' => true,
                'BatchSize' => (int) Config::get('STREAM_TEST_BATCH_SIZE'),
//                'StartingPosition' => Config::get('STREAM_TEST_STARTING_POSITION'),
                'StartingPosition' => 'AT_TIMESTAMP',
                'StartingPositionTimestamp' => date('c')
            ]);
        }

        return $this->getResponse()
            ->withJson($streamTest)
            ->withStatus(201);
    }

    /**
     * @SWG\Post(
     *     path="/v0.1/stream-tests/end/{id}",
     *     summary="End a Stream Test",
     *     tags={"streamtests"},
     *     operationId="endTest",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="string",
     *         format="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Bad request",
     *         @SWG\Schema(ref="#/definitions/ErrorResponse")
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="Generic server error",
     *         @SWG\Schema(ref="#/definitions/ErrorResponse")
     *     ),
     *     security={
     *         {
     *             "api_auth": {"openid api"}
     *         }
     *     }
     * )
     */
    public function endTest($id)
    {
        $streamTest = new StreamTest();

        $streamTest->read($id);

        foreach ($streamTest->getStreamMappings() as $streamMapping) {
            $this->deleteMapping($streamMapping->getEventSourceArn());
        }

        return $this->getResponse()
            ->withStatus(200);
    }

    /**
     * @SWG\Get(
     *     path="/v0.1/stream-tests/tests/{id}/results",
     *     summary="Get a Stream Test Results",
     *     tags={"streamtests"},
     *     operationId="getResults",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="string",
     *         format="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful operation",
     *         @SWG\Schema(ref="#/definitions/StreamTestResultsResponse")
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Not found",
     *         @SWG\Schema(ref="#/definitions/ErrorResponse")
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="Generic server error",
     *         @SWG\Schema(ref="#/definitions/ErrorResponse")
     *     )
     * )
     */
    public function getResults($id = '')
    {
        $resultSummary = new StreamTestResultSummary();

        $streamTest = new StreamTest();
        $streamTest->read($id);

        /**
         * @var StreamMapping $previousStreamMapping
         */
        $previousStreamMapping = null;

        /**
         * @var StreamMapping $firstStreamMapping
         */
        $firstStreamMapping = $streamTest->getStreamMappings()[0];

        /**
         * @var StreamMapping $finalStreamMapping
         */
        $finalStreamMapping = $streamTest->getStreamMappings()[
            count($streamTest->getStreamMappings()) - 1
        ];

        foreach ($streamTest->getStreamMappings() as $streamMapping) {
            try {
                $streamMapping->read($streamMapping->getId());
            } catch (\Exception $exception) {
            }

            $streamTestResult = new StreamTestResult();

            $streamTestResult->setStreamName($streamMapping->getStreamName());

            $streamTestResult->setTotalCount(
                Cache::getCache()->lLen($streamMapping->getStreamRecordListKeyName())
            );

            $streamTestResult->setUniqueCount(
                Cache::getCache()->sCard($streamMapping->getStreamRecordKeyName())
            );

//            $streamTestResult->setRecords(
//                Cache::getCache()->sGetMembers($streamMapping->getStreamRecordKeyName())
//            );

            $streamTestResult->setFirstRecordTime($streamMapping->getFirstRecordTime());

            $streamTestResult->setLastRecordTime($streamMapping->getLastRecordTime());

            if ($previousStreamMapping) {
                $streamTestResult->setDifferenceFromPreviousStream(
                    new StreamTestResultDifference(
                        array_slice(
                            Cache::getCache()->sDiff(
                                $previousStreamMapping->getStreamRecordKeyName(),
                                $streamMapping->getStreamRecordKeyName()
                            ),
                            0,
                            1000
                        )
                    )
                );
            }

            if ($streamMapping === $finalStreamMapping) {
                $streamTestResult->setDifferenceFromFirstStream(
                    new StreamTestResultDifference(
                        array_slice(
                            Cache::getCache()->sDiff(
                                $firstStreamMapping->getStreamRecordKeyName(),
                                $finalStreamMapping->getStreamRecordKeyName()
                            ),
                            0,
                            1000
                        )
                    )
                );
            }

            $resultSummary->addStreamTestResult($streamTestResult);

            $previousStreamMapping = $streamMapping;
        }

        return $this->getResponse()
            ->withJson(
                new StreamTestResultsResponse($resultSummary),
                200,
                JSON_PRETTY_PRINT
            );
    }
}
