<?php
namespace NYPL\Services\Model\DataModel\StreamRecord;

use NYPL\Services\Model\DataModel\StreamRecord;
use NYPL\Services\Model\DataModel\StreamRecordKey\ResourceKey;

class Item extends StreamRecord
{
    /**
     * @return ResourceKey
     */
    public function getStreamRecordKey()
    {
        return new ResourceKey(
            $this->getData()['nyplSource'],
            $this->getData()['nyplType'],
            $this->getData()['id']
        );
    }
}
