<?php
namespace NYPL\Services;

use NYPL\Services\Model\DataModel\StreamMapping;
use NYPL\Services\Model\DataModel\StreamRecord;
use NYPL\Starter\APILogger;

class Listener
{
    /**
     * @var array
     */
    protected $records = [];

    /**
     * Listener constructor.
     */
    public function __construct()
    {
        register_shutdown_function('NYPL\Services\Listener::fatalHandler');
    }

    public static function fatalHandler()
    {
        $error = error_get_last();

        if ($error !== null) {
            error_log(
                json_encode([
                    'message' => $error['message'],
                    'level' => 400,
                    'level_name' => 'ERROR'
                ])
            );
        }
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

    protected function initializeRecords()
    {
        APILogger::addInfo('Decoding buffer using file_get_contents()');

        $buffer = file_get_contents('php://stdin');

        $this->setRecords(json_decode($buffer, true));

        if (!$this->getRecords()) {
            APILogger::addError(
                'Error decoding buffer',
                ['json_error' => json_last_error(), 'buffer' => $buffer]
            );
        }
    }

    public function process()
    {
        $this->initializeRecords();

        APILogger::addInfo('Processing ' . count($this->getRecords()) . ' record(s).');

        $addCount = 0;

        if ($this->getRecords()) {
            foreach ($this->getRecords() as $record) {
                try {
                    $streamMapping = new StreamMapping();
                    $streamMapping->read($streamMapping->getIdFromEventSourceArn($record['eventSourceARN']));

                    if (!$streamMapping->getFirstRecordTime()) {
                        $streamMapping->setFirstRecordTime(microtime(true));
                    }

                    $streamMapping->setLastRecordTime(microtime(true));
                    $streamMapping->setCount($streamMapping->getCount() + 1);

                    $streamMapping->update();

                    APILogger::addInfo(
                        'Processing record for ' . $streamMapping->getTestName() . ' in ' .
                        $streamMapping->getStreamName() . ' stream.'
                    );

                    $schema = SchemaClient::getSchema($streamMapping->getStreamName());

                    APILogger::addInfo(
                        'Got schema for ' . $streamMapping->getStreamName(),
                        (array) $schema->getSchema()
                    );

                    APILogger::addInfo(
                        'Decoding data with schema in '. $streamMapping->getStreamName(),
                        ['data' => base64_decode($record['kinesis']['data'])]
                    );

                    $data = AvroDeserializer::deserializeWithSchema(
                        $schema,
                        base64_decode($record['kinesis']['data'])
                    );

                    APILogger::addInfo(
                        'Decoded data with schema in ' . $streamMapping->getStreamName(),
                        (array) $data
                    );

                    $streamRecordName = '\NYPL\Services\Model\DataModel\StreamRecord\\' . $streamMapping->getStreamName();

                    if (!class_exists($streamRecordName)) {
                        APILogger::addInfo('Data received', $data);

                        throw new \InvalidArgumentException(
                            'Mapping class for stream name (' . $streamMapping->getStreamName() . ') does not exist'
                        );
                    }

                    /**
                     * @var StreamRecord $streamRecord
                     */
                    $streamRecord = new $streamRecordName($data, $streamMapping);

                    $addCount += $streamRecord->create();
                } catch (\Exception $exception) {
                    APILogger::addError($exception->getMessage(), (array) $exception);
                }
            }
        }

        return $addCount;
    }
}
