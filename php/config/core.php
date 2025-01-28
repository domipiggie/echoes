<?php
error_reporting(E_ALL);
date_default_timezone_set('UTC');

//---JWT VARIABLES---
define('SECRET_KEY', 'verysecureandsecretkeythatiwrotehereforsecurity');
define('ALGORITHM', 'HS256');
define('ISSUER', 'http://localhost/');
define('AUDIENCE', 'http://localhost/');