<?php
namespace NYPL\Services\Model\DataModel;

use NYPL\Services\Model\DataModel;

/**
 * @SWG\Definition(type="object")
 */
class StreamTestResult extends DataModel
{
    /**
     * @SWG\Property(example="Bib")
     * @var string
     */
    public $streamName = '';

    /**
     * @SWG\Property(example="511")
     * @var int
     */
    public $totalCount = 0;

    /**
     * @SWG\Property(example="130")
     * @var int
     */
    public $uniqueCount = 0;

    /**
     * @SWG\Property()
     * @var string[]
     */
    private $records = [];

    /**
     * @SWG\Property(example=1493730547.0994)
     * @var float
     */
    public $firstRecordTime = 0;

    /**
     * @SWG\Property(example=1493730547.0994)
     * @var float
     */
    public $lastRecordTime = 0;

    /**
     * @SWG\Property(example=555.0994)
     * @var float
     */
    public $firstToLastSeconds = 0;

    /**
     * @SWG\Property(example=2.3)
     * @var float
     */
    public $averageSecondsPerRecord = 0;

    /**
     * @SWG\Property
     * @var StreamTestResultDifference
     */
    public $differenceFromPreviousStream;

    /**
     * @SWG\Property
     * @var StreamTestResultDifference
     */
    public $differenceFromFirstStream = [];

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
     * @return int
     */
    public function getUniqueCount()
    {
        return $this->uniqueCount;
    }

    /**
     * @param int $uniqueCount
     */
    public function setUniqueCount($uniqueCount)
    {
        $this->uniqueCount = $uniqueCount;
    }

    /**
     * @return array
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * @param array $records
     */
    public function setRecords($records)
    {
        $this->records = $records;
    }

    /**
     * @return int
     */
    public function getFirstRecordTime()
    {
        return $this->firstRecordTime;
    }

    /**
     * @param int $firstRecordTime
     */
    public function setFirstRecordTime($firstRecordTime)
    {
        $this->firstRecordTime = $firstRecordTime;
    }

    /**
     * @return int
     */
    public function getLastRecordTime()
    {
        return $this->lastRecordTime;
    }

    /**
     * @param int $lastRecordTime
     */
    public function setLastRecordTime($lastRecordTime)
    {
        if ($this->getFirstRecordTime()) {
            $this->setFirstToLastSeconds($lastRecordTime - $this->getFirstRecordTime());
        }

        $this->lastRecordTime = $lastRecordTime;
    }

    /**
     * @return StreamTestResultDifference
     */
    public function getDifferenceFromPreviousStream()
    {
        return $this->differenceFromPreviousStream;
    }

    /**
     * @param StreamTestResultDifference $differenceFromPreviousStream
     */
    public function setDifferenceFromPreviousStream(
        $differenceFromPreviousStream
    ) {
        $this->differenceFromPreviousStream = $differenceFromPreviousStream;
    }

    /**
     * @return StreamTestResultDifference
     */
    public function getDifferenceFromFirstStream()
    {
        return $this->differenceFromFirstStream;
    }

    /**
     * @param StreamTestResultDifference $differenceFromFirstStream
     */
    public function setDifferenceFromFirstStream($differenceFromFirstStream)
    {
        $this->differenceFromFirstStream = $differenceFromFirstStream;
    }

    /**
     * @return float
     */
    public function getFirstToLastSeconds()
    {
        return $this->firstToLastSeconds;
    }

    /**
     * @param float $firstToLastSeconds
     */
    public function setFirstToLastSeconds($firstToLastSeconds)
    {
        if ($this->getUniqueCount()) {
            $this->setAverageSecondsPerRecord($firstToLastSeconds / $this->getUniqueCount());
        }

        $this->firstToLastSeconds = (float) $firstToLastSeconds;
    }

    /**
     * @return mixed
     */
    public function getAverageSecondsPerRecord()
    {
        return $this->averageSecondsPerRecord;
    }

    /**
     * @param mixed $averageSecondsPerRecord
     */
    public function setAverageSecondsPerRecord($averageSecondsPerRecord)
    {
        $this->averageSecondsPerRecord = (float) $averageSecondsPerRecord;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @param int $totalCount
     */
    public function setTotalCount($totalCount)
    {
        $this->totalCount = (int) $totalCount;
    }
}
