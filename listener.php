<?php
require __DIR__ . '/vendor/autoload.php';

use NYPL\Starter\Config;
use NYPL\Services\Listener;
use NYPL\Starter\APILogger;

Config::initialize(__DIR__ . '/config');

$listener = new Listener();

try {
    $numberRecords = $listener->process();

    APILogger::addInfo('Successfully added ' . $numberRecords . ' record(s).');
} catch (Throwable $exception) {
    APILogger::addError(
        $exception->getMessage(),
        $exception->getTrace()
    );
} catch (Exception $exception) {
    APILogger::addError(
        $exception->getMessage(),
        $exception->getTrace()
    );
}
