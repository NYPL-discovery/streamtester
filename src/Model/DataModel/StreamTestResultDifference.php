<?php
namespace NYPL\Services\Model\DataModel;

use NYPL\Services\Model\DataModel;

/**
 * @SWG\Definition(type="object")
 */
class StreamTestResultDifference extends DataModel
{
    /**
     * @SWG\Property(example="130")
     * @var int
     */
    public $count = 0;

    /**
     * @SWG\Property()
     * @var string[]
     */
    public $records = [];

    /**
     * @param array $records
     */
    public function __construct(array $records)
    {
        $this->setCount(count($records));

        $this->setRecords($records);
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
}
