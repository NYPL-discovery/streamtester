<?php
namespace NYPL\Services\Model\DataModel;

use NYPL\Services\Model\DataModel;

abstract class StreamRecordKey extends DataModel
{
    /**
     * @return string
     */
    abstract public function transformStreamRecordKey();
}
