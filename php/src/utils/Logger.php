<?php

namespace Utils;

class Logger
{
    const LEVEL_DEBUG = 'debug';
    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';

    protected $logFile;
    protected $minLevel;
    protected $levels = [
        self::LEVEL_DEBUG => 0,
        self::LEVEL_INFO => 1,
        self::LEVEL_WARNING => 2,
        self::LEVEL_ERROR => 3
    ];

    public function __construct($logFile = null, $minLevel = self::LEVEL_INFO)
    {
        $this->logFile = $logFile ?: __DIR__ . '/../../logs/websocket.log';
        $this->minLevel = $minLevel;

        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    public function log($message, $level = self::LEVEL_INFO)
    {
        if ($this->levels[$level] < $this->levels[$this->minLevel]) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[$timestamp] [$level] $message" . PHP_EOL;

        file_put_contents($this->logFile, $formattedMessage, FILE_APPEND);

        echo $formattedMessage;
    }

    public function debug($message)
    {
        $this->log($message, self::LEVEL_DEBUG);
    }
    public function info($message)
    {
        $this->log($message, self::LEVEL_INFO);
    }
    public function warning($message)
    {
        $this->log($message, self::LEVEL_WARNING);
    }
    public function error($message)
    {
        $this->log($message, self::LEVEL_ERROR);
    }
}
