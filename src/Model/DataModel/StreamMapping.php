<?php
namespace NYPL\Services\Model\DataModel;

use NYPL\Services\Model\DataModel;
use NYPL\Starter\Model\ModelTrait\CacheCreateTrait;
use NYPL\Starter\Model\ModelTrait\CacheReadTrait;
use NYPL\Starter\Model\ModelTrait\CacheTrait;
use NYPL\Starter\Model\ModelTrait\CacheUpdateTrait;
use NYPL\Starter\Model\ModelTrait\TranslateTrait;

class StreamMapping extends DataModel
{
    use TranslateTrait, CacheTrait, CacheCreateTrait, CacheReadTrait, CacheUpdateTrait;

    /**
     * @var string
     */
    public $id = '';

    /**
     * @var string
     */
    public $testName = '';

    /**
     * @var string
     */
    public $eventSourceArn = '';

    /**
     * @var string
     */
    public $streamName = '';

    /**
     * @var float
     */
    public $firstRecordTime = 0;

    /**
     * @var float
     */
    public $lastRecordTime = 0;

    /**
     * @var int
     */
    public $count = 0;

    public function getIdFields()
    {
        return ["id"];
    }

    public function getIdKey()
    {
        return "StreamMapping";
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTestName()
    {
        return $this->testName;
    }

    /**
     * @param mixed $testName
     */
    public function setTestName($testName)
    {
        $this->testName = $testName;
    }

    /**
     * @return string
     */
    public function getEventSourceArn()
    {
        return $this->eventSourceArn;
    }

    /**
     * @param string $eventSourceArn
     */
    public function setEventSourceArn($eventSourceArn)
    {
        $this->eventSourceArn = $eventSourceArn;
    }

    /**
     * @param string $eventSourceArn
     *
     * @return string
     */
    public function getIdFromEventSourceArn($eventSourceArn = '') {
        return md5($eventSourceArn);
    }

    /**
     * @return string
     */
    public function getStreamName()
    {
        return $this->streamName;
    }

    /**
     * @param string $streamName
     */
    public function setStreamName($streamName)
    {
        $this->streamName = $streamName;
    }

    /**
     * @return string
     */
    public function getStreamRecordKeyName()
    {
        return 'StreamRecord:' .
            $this->getStreamName() . ':' .
            'Set';
    }

    /**
     * @return string
     */
    public function getStreamRecordListKeyName()
    {
        return 'StreamRecord:' .
            $this->getStreamName() . ':' .
            'List';
    }

    /**
     * @return float
     */
    public function getFirstRecordTime()
    {
        return $this->firstRecordTime;
    }

    /**
     * @param float $firstRecordTime
     */
    public function setFirstRecordTime($firstRecordTime)
    {
        $this->firstRecordTime = (float) $firstRecordTime;
    }

    /**
     * @return float
     */
    public function getLastRecordTime()
    {
        return $this->lastRecordTime;
    }

    /**
     * @param float $lastRecordTime
     */
    public function setLastRecordTime($lastRecordTime)
    {
        $this->lastRecordTime = (float) $lastRecordTime;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }
}
