<?php

use Framework\Framework;

require __DIR__ . "/vendor/autoload.php";

define("DIRECTORY_ROOT", __DIR__);

Framework::setConfigApp();

require_once("./application/routes.php");