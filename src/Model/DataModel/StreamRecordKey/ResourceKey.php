<?php
namespace NYPL\Services\Model\DataModel\StreamRecordKey;

use NYPL\Services\Model\DataModel\StreamRecordKey;

class ResourceKey extends StreamRecordKey
{
    public $nyplSource = '';

    public $nyplType = '';

    public $id = '';

    /**
     * @param string $nyplSource
     * @param string $nyplType
     * @param string $id
     */
    public function __construct($nyplSource, $nyplType, $id)
    {
        $this->setNyplSource($nyplSource);

        $this->setNyplType($nyplType);

        $this->setId($id);
    }

    public function transformStreamRecordKey()
    {
        return $this->getNyplSource() . '-' . $this->getNyplType() . '-' . $this->getId();
    }

    /**
     * @return string
     */
    public function getNyplSource()
    {
        return $this->nyplSource;
    }

    /**
     * @param string $nyplSource
     */
    public function setNyplSource($nyplSource)
    {
        $this->nyplSource = $nyplSource;
    }

    /**
     * @return string
     */
    public function getNyplType()
    {
        return $this->nyplType;
    }

    /**
     * @param string $nyplType
     */
    public function setNyplType($nyplType)
    {
        $this->nyplType = $nyplType;
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
}
