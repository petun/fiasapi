<?php


error_reporting(E_ALL);

require_once "FiasParser.class.php";
require_once "settings.php";

$p = new FiasParser($token, $key);

$streets = $p->allStreets(5200000700000);