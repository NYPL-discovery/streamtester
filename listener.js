const spawn = require('child_process').spawnSync;

const libraryPath = process.env['LD_LIBRARY_PATH'];

exports.handler = function(event, context) {
    var headers = {
        LD_LIBRARY_PATH: libraryPath
    };

    // console.log('CONTEXT');
    // console.log(context);
    // console.log('EVENT');
    // console.log(event);
    // console.log('HEADERS');
    // console.log(headers);
    // console.log('RECORDS');
    // console.log(JSON.stringify(event.Records));

    var log = {
        message: 'Starting processing',
        numberRecords: event.Records.length,
        streamArn: event.Records[0].eventSourceARN
    };

    var options = {
        input: JSON.stringify(event.Records),
        env: Object.assign(process.env, headers)
    };

    console.log(JSON.stringify(log));

    const php = spawn('./php-cgi', ['listener.php'], options);

    if (php.stderr.length) {
        php.stderr.toString().split("\n").map(function (message) {
            if (message.trim().length) console.log(message);
        });
    }

    context.succeed({
        body: php.stdout.toString()
    });
};
