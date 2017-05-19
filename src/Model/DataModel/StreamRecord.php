<?php
namespace NYPL\Services\Model\DataModel;

use NYPL\Services\Model\DataModel;
use NYPL\Starter\Cache;
use NYPL\Starter\Config;

abstract class StreamRecord extends DataModel
{
    /**
     * @return StreamRecordKey
     */
    abstract public function getStreamRecordKey();

    /**
     * @var string
     */
    public $id = '';

    /**
     * @var array
     */
    public $data = [];

    /**
    public $streamTest;

    /**
     * @var StreamMapping
     */
    public $streamMapping;

    /**
     * @param array $data
     * @param StreamMapping $streamMapping
     */
    public function __construct(array $data = [], StreamMapping $streamMapping)
    {
        if ($data) {
            $this->setData($data);
        }

        if ($streamMapping) {
            $this->setStreamMapping($streamMapping);
        }
    }

    /**
     * @return StreamMapping
     */
    public function getStreamMapping()
    {
        return $this->streamMapping;
    }

    /**
     * @param StreamMapping $streamMapping
     */
    public function setStreamMapping($streamMapping)
    {
        $this->streamMapping = $streamMapping;
    }


    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getStreamRecordKey()->transformStreamRecordKey();
    }

    /**
     * @return int
     */
    public function create()
    {
        $expiration = (int) Config::get('CACHE_DEFAULT_EXPIRATION');

        $numberUpdates = Cache::getCache()->sAdd(
            $this->getStreamMapping()->getStreamRecordKeyName(),
            $this->getId()
        );

        Cache::getCache()->expire(
            $this->getStreamMapping()->getStreamRecordKeyName(),
            $expiration
        );

        Cache::getCache()->rPush(
            $this->getStreamMapping()->getStreamRecordListKeyName(),
            $this->getId()
        );

        Cache::getCache()->expire(
            $this->getStreamMapping()->getStreamRecordListKeyName(),
            $expiration
        );

        return $numberUpdates;
    }
}
