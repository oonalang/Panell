<?php

/*
 * Shim for deployments where the document root is the /public folder.
 *
 * The shared connection file lives in the project root, but the application
 * includes it as `conn.php` (working directory) and as FCPATH . 'conn.php'
 * (front controller directory). When index.php is served from /public neither
 * path resolves and every request that touches the database dies with a fatal
 * error. Requiring the root file keeps both include styles working, whether
 * the panel is deployed from the project root or from /public.
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'conn.php';
