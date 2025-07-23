<?php

namespace EdLugz\Tanda\Logging;

use Exception;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Logger;

class Log
{
    /**
     * All the available debug levels.
     *
     * @var array
     */
    protected static array $levels = [
        'DEBUG'     => Level::Debug,
        'INFO'      => Level::Info,
        'NOTICE'    => Level::Notice,
        'WARNING'   => Level::Warning,
        'ERROR'     => Level::Error,
        'CRITICAL'  => Level::Critical,
        'ALERT'     => Level::Alert,
        'EMERGENCY' => Level::Emergency,
    ];

    /**
     * Set up the logging requirements for the Guzzle package.
     *
     * @param $options
     *
     * @throws Exception
     *
     * @return array
     */
    public static function enable($options) : array
    {
        $level = self::getLogLevel();

        $handler = new Logger(
            'Tanda',
            [
                new RotatingFileHandler(storage_path('logs/tanda/tanda.log'), 30, $level),
            ]
        );

        $stack = HandlerStack::create();
        $stack->push(
            Middleware::log(
                $handler,
                new MessageFormatter('{method} {uri} HTTP/{version} {req_body} RESPONSE: {code} - {res_body}')
            )
        );

        $options['handler'] = $stack;

        return $options;
    }

    /**
     * Determine the log level specified in the configurations.
     *
     * @throws Exception
     *
     * @return mixed
     */
    protected static function getLogLevel(): mixed
    {
        $level = strtoupper(config('tanda.logs.level'));

        if (array_key_exists($level, self::$levels)) {
            return self::$levels[$level];
        }

        throw new Exception('Debug level not recognized');
    }
}