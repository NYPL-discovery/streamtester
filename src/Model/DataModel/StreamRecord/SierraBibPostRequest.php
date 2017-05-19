<?php
namespace NYPL\Services\Model\DataModel\StreamRecord;

use NYPL\Services\Model\DataModel\StreamRecord;
use NYPL\Services\Model\DataModel\StreamRecordKey\ResourceKey;

class SierraBibPostRequest extends StreamRecord
{
    /**
     * @return ResourceKey
     */
    public function getStreamRecordKey()
    {
        return new ResourceKey(
            'sierra-nypl',
            'bib',
            $this->getData()['id']
        );
    }
}
