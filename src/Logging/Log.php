<?php

namespace EdLugz\Tanda\Logging;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Logger;
use InvalidArgumentException;

final class Log
{
    /**
     * All the available debug levels.
     *
     * @var array<string, Level>
     */
    private static array $levels = [
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
     * @param array $options
     *
     * @return array
     */
    public static function enable(array $options): array
    {
        $level = self::getLogLevel();

        $handler = new Logger(
            'Tanda',
            [new RotatingFileHandler(storage_path('logs/tanda.log'), 30, $level)]
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
     * @return Level
     */
    private static function getLogLevel(): Level
    {
        $level = strtoupper(config('tanda.logs.level', 'DEBUG'));

        return self::$levels[$level] ?? throw new InvalidArgumentException('Invalid log level: ' . $level);
    }
}
