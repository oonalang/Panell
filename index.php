<?php

// Compatibility front controller for shared hosts whose webroot cannot be
// pointed at public/. Prefer configuring the document root to public/.
use CodeIgniter\Boot;
use Config\Paths;

if (version_compare(PHP_VERSION, '8.2', '<')) {
    http_response_code(503);
    exit('PHP 8.2 or later is required.');
}

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
chdir(FCPATH);
require FCPATH . 'app/Config/Paths.php';
$paths = new Paths();
if (! is_file($paths->systemDirectory . '/Boot.php')) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Application dependencies missing. Install the PHP 8.2+ Composer dependencies before deploying.');
}
require $paths->systemDirectory . '/Boot.php';
exit(Boot::bootWeb($paths));
