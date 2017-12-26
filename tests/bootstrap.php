<?php

date_default_timezone_set('UTC');
ini_set('session.use_cookies', 0);

$autoloader = require(dirname(__DIR__).'/vendor/autoload.php');
$autoloader->addPsr4('Bizurkur\\Bitty\\Tests\\', __DIR__);
