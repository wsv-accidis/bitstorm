<?php
/*
 * Database connection settings
 */
define('__DB_SERVER', 'localhost');
define('__DB_USERNAME', '');
define('__DB_PASSWORD', '');
define('__DB_DATABASE', '');

/*
 * General settings
 */
// Peer announce interval (Seconds)
define('__INTERVAL', 1800);
// Time out if peer is this late to re-announce (Seconds)
define('__TIMEOUT', 120);
// Minimum announce interval (Seconds) - most clients obey this, but not all
define('__INTERVAL_MIN', 60);
// By default, never encode more than this number of peers in a single request
define('__MAX_PPR', 20);

/*
 * Whitelisting
 */
define('__WHITELIST_ENABLED', false);
// Each element is an infohash of the torrent, in hexadecimal format
$_whiteList = array();

/*
 * PHP error reporting (for debugging)
 */
//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
//error_reporting(E_ALL);
