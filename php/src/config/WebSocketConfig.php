<?php

namespace Config;

class WebSocketConfig
{
    const PORT = 8080;
    const SECURE_PORT = 8080;
    const HOST = '0.0.0.0';

    const SSL_CERT_PATH = '';
    const SSL_KEY_PATH = '';

    const HEARTBEAT_INTERVAL = 30; // seconds
    const CONNECTION_TIMEOUT = 60; // seconds

    const RATE_LIMIT_WINDOW = 60; // seconds
    const RATE_LIMIT_MAX_REQUESTS = 100;

    const LOG_FILE = '';
    const LOG_LEVEL = 'info'; // debug, info, warning, error

    const AUTH_TIMEOUT = 10; // seconds
}
