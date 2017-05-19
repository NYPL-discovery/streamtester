<?php
namespace NYPL\Services\Model\Response\SuccessResponse;

use NYPL\Services\Model\DataModel\StreamTestResultSummary;
use NYPL\Starter\Model\Response\SuccessResponse;

/**
 * @SWG\Definition(title="StreamTestResultsResponse", type="object")
 */
class StreamTestResultsResponse extends SuccessResponse
{
    /**
     * @SWG\Property
     * @var StreamTestResultSummary
     */
    public $data;
}
