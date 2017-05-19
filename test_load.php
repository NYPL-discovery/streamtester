<?php
require __DIR__ . '/vendor/autoload.php';

use NYPL\Starter\Config;
use NYPL\Services\AvroDeserializer;
use NYPL\Services\SchemaClient;
use NYPL\Starter\APILogger;

Config::initialize(__DIR__ . '/config');

$streamName = 'SierraBibPostRequest';

$kinesisClient = new \Aws\Kinesis\KinesisClient([
    'version' => 'latest',
    'region'  => Config::get('AWS_DEFAULT_REGION'),
    'credentials' => [
        'key' => Config::get('AWS_ACCESS_KEY_ID'),
        'secret' => Config::get('AWS_SECRET_ACCESS_KEY'),
        'token' => Config::get('AWS_SESSION_TOKEN')
    ]
]);

$data = json_decode(
    file_get_contents('sample_event_3.json'),
    true
);

$putRecords = [];

$schema = SchemaClient::getSchema($streamName);

foreach ($data as $record) {
    $io = new \AvroStringIO();
    $writer = new \AvroIODatumWriter($schema->getAvroSchema());
    $encoder = new \AvroIOBinaryEncoder($io);

    $writer->write(
        AvroDeserializer::deserializeWithSchema($schema, base64_decode($record['kinesis']['data'])),
        $encoder
    );

    $putRecords[] = [
        'Data' => $io->string(),
        'PartitionKey' => uniqid()
    ];
}

$kinesisClient->putRecords([
        'Records' => $putRecords,
        'StreamName' => $streamName,
    ]
);

APILogger::addInfo('Successfully put ' . count($putRecords) . ' record(s) in ' . $streamName . '.');
