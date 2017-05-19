<?php
namespace NYPL\Services\Model\DataModel;

use Aws\Kinesis\KinesisClient;
use NYPL\Services\Model\DataModel;
use NYPL\Starter\APIException;
use NYPL\Starter\Config;
use NYPL\Starter\Model\ModelTrait\CacheCreateTrait;
use NYPL\Starter\Model\ModelTrait\CacheReadTrait;
use NYPL\Starter\Model\ModelTrait\CacheTrait;
use NYPL\Starter\Model\ModelTrait\TranslateTrait;

/**
 * @SWG\Definition(type="object")
 */
class StreamTest extends DataModel
{
    use TranslateTrait, CacheTrait, CacheCreateTrait, CacheReadTrait;

    /**
     * @SWG\Property(example="BibTest")
     * @var string
     */
    public $id;

    /**
     * @SWG\Property
     * @var string[]
     */
    public $streams = [];

    /**
     * @var StreamMapping[]
     */
    private $streamMappings = [];

    /**
     * @var KinesisClient
     */
    private $kinesisClient;

    public function getIdFields()
    {
        return ["id"];
    }

    public function getIdKey()
    {
        return "StreamTest";
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return \string[]
     */
    public function getStreams()
    {
        return $this->streams;
    }

    /**
     * @return KinesisClient
     */
    public function getKinesisClient()
    {
        if (!$this->kinesisClient) {
            $this->setKinesisClient(
                new KinesisClient([
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

        return $this->kinesisClient;
    }

    /**
     * @param KinesisClient $kinesisClient
     */
    public function setKinesisClient($kinesisClient)
    {
        $this->kinesisClient = $kinesisClient;
    }

    /**
     * @param string $streamName
     *
     * @return string
     * @throws APIException
     */
    protected function getStreamArn($streamName = '')
    {
        try {
            $stream = $this->getKinesisClient()->describeStream([
                'StreamName' => $streamName
            ]);

            return $stream->get('StreamDescription')['StreamARN'];
        } catch (\Exception $exception) {
            throw new APIException('Stream specified (' . $streamName . ') was not found.', null, 0, null, 400);
        }
    }

    /**
     * @return array
     */
    protected function getStreamArns()
    {
        $streamArns = [];

        foreach ($this->getStreams() as $streamName) {
            $streamArns[$streamName] = $this->getStreamArn($streamName);
        }

        return $streamArns;
    }

    /**
     * @param string|\string[] $streams
     */
    public function setStreams($streams)
    {
        if (is_string($streams)) {
            $streams = json_decode($streams, true);
        }

        $this->streams = $streams;
    }

    /**
     * @return StreamMapping[]
     */
    public function getStreamMappings()
    {
        if (!$this->streamMappings) {
            $this->initializeStreamMappings();
        }

        return $this->streamMappings;
    }

    /**
     * @param StreamMapping[] $streamMappings
     */
    public function setStreamMappings($streamMappings)
    {
        $this->streamMappings = $streamMappings;
    }

    public function addStreamMapping(StreamMapping $streamMapping)
    {
        $this->streamMappings[] = $streamMapping;
    }

    protected function initializeStreamMappings()
    {
        $streamArns = $this->getStreamArns();

        foreach ($this->getStreams() as $streamName) {
            $streamMapping = new StreamMapping();

            $streamMapping->setId($streamMapping->getIdFromEventSourceArn($streamArns[$streamName]));
            $streamMapping->setTestName($this->getId());
            $streamMapping->setEventSourceArn($streamArns[$streamName]);
            $streamMapping->setStreamName($streamName);

            $this->addStreamMapping($streamMapping);
        }
    }

    /**
     * @param array|string $data
     *
     * @return StreamMapping[]
     */
    public function translateStreamMappings($data)
    {
        return $this->translateArray($data, new StreamMapping(), true);
    }
}
