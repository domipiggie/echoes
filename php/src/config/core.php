<?php
error_reporting(E_ALL);
date_default_timezone_set('UTC');

//---JWT VARIABLES---
define('JWT_SECRET_KEY', 'verysecureandsecretkeythatiwrotehereforsecurity');
define('JWT_ALGORITHM', 'HS256');
define('JWT_ISSUER', 'http://localhost/');
define('JWT_AUDIENCE', 'http://localhost/');
define('JWT_EXPIRATION_TIME', 3600);