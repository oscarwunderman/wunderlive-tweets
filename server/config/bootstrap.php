<?php
@session_start();

ini_set('display_errors',2);
error_reporting(E_ALL);

set_time_limit(0);

include dirname(__FILE__).'/conf.php';
include dirname(__FILE__).'/functions.php';
require dirname(__FILE__).'/../../vendor/autoload.php';


