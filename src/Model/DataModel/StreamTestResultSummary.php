<?php
namespace NYPL\Services\Model\DataModel;

use NYPL\Services\Model\DataModel;

/**
 * @SWG\Definition(type="object")
 */
class StreamTestResultSummary extends DataModel
{
    /**
     * @SWG\Property(example="4.1")
     * @var float
     */
    public $cumulativeSeconds = 0;

    /**
     * @SWG\Property
     * @var StreamTestResult[]
     */
    public $streams;

    /**
     * @return StreamTestResult[]
     */
    public function getStreams()
    {
        return $this->streams;
    }

    /**
     * @param StreamTestResult[] $streams
     */
    public function setStreams($streams)
    {
        $this->streams = $streams;
    }

    /**
     * @return float
     */
    public function getCumulativeSeconds()
    {
        return $this->cumulativeSeconds;
    }

    /**
     * @param float $cumulativeSeconds
     */
    public function setCumulativeSeconds($cumulativeSeconds)
    {
        $this->cumulativeSeconds = $cumulativeSeconds;
    }

    /**
     * @param StreamTestResult $streamTestResult
     */
    public function addStreamTestResult(StreamTestResult $streamTestResult)
    {
        $this->cumulativeSeconds += $streamTestResult->getFirstToLastSeconds();

        $this->streams[] = $streamTestResult;
    }
}
