<?php
namespace NYPL\Services;

use GuzzleHttp\Client;
use NYPL\Starter\AvroLoader;
use NYPL\Starter\Config;

class SchemaClient
{
    /**
     * @var Client
     */
    protected static $client;

    /**
     * @var array
     */
    protected static $schemaCache = [];

    /**
     * @return Client
     */
    public static function getClient()
    {
        if (!self::$client) {
            self::setClient(
                new Client()
            );
        }

        return self::$client;
    }

    /**
     * @param Client $client
     */
    public static function setClient($client)
    {
        self::$client= $client;
    }

    /**
     * @param string $streamName
     *
     * @return Schema
     */
    public static function getSchema($streamName = '')
    {
        if (isset(self::$schemaCache[$streamName])) {
            return self::$schemaCache[$streamName];
        }

        AvroLoader::load();

        $response = json_decode(
            self::getClient()->get(Config::get('SCHEMA_BASE_URL') . '/' . $streamName)->getBody(),
            true
        );

        $schema = new Schema(
            $streamName,
            0,
            \AvroSchema::parse($response['data']['schema']),
            $response['data']['schemaObject']
        );

        self::$schemaCache[$streamName] = $schema;

        return $schema;
    }
}
