<?php

/*
|--------------------------------------------------------------------------
| cPanel production front controller
|--------------------------------------------------------------------------
|
| This is Laravel's stock public/index.php with its two __DIR__-relative
| requires rewritten to absolute paths. On the server, public_html/index.php
| is a COPY of this file (not a symlink to public/index.php), so it lives
| one directory shallower than Laravel expects (public_html/ vs
| inzra-app/public/) — the relative "../vendor" and "../bootstrap" paths
| would resolve to the account home directory instead of the app directory.
| See .cpanel.yml and DEPLOYMENT.md for how this gets copied into place.
|
*/

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

const INZRA_APP_PATH = '/home/seoweb/inzra-app';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = INZRA_APP_PATH.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require INZRA_APP_PATH.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once INZRA_APP_PATH.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
