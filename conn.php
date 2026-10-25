<?php

/**
 * Legacy MySQLi connection used by existing controllers and root scripts.
 * Values are shared with the CodeIgniter database.default.* configuration.
 * Populate .env on the host, never hardcode the account password here.
 */
$setting = static function (string $name, string $fallback = ''): string {
    if (function_exists('env')) {
        $value = env('database.default.' . $name, null);
        if ($value !== null && $value !== '') {
            return (string) $value;
        }
    }
    $value = getenv('DB_' . strtoupper($name));
    if ($value !== false && $value !== '') {
        return (string) $value;
    }
    // Standalone legacy scripts do not bootstrap CodeIgniter. Read the same
    // private .env configuration, rather than keeping another password copy.
    static $fileConfig = null;
    if ($fileConfig === null) {
        $path = __DIR__ . DIRECTORY_SEPARATOR . '.env';
        $fileConfig = is_readable($path) ? (parse_ini_file($path, false, INI_SCANNER_RAW) ?: []) : [];
    }
    $key = 'database.default.' . $name;
    if (isset($fileConfig[$key]) && $fileConfig[$key] !== '') {
        return trim((string) $fileConfig[$key], " \t\r\n\"'");
    }
    return $fallback;
};
$servername = $setting('hostname', 'localhost');
$dbname = $setting('database');
$username = $setting('username');
$password = $setting('password');
$port = (int) $setting('port', '3306');
$conn = false;

if ($dbname !== '' && $username !== '' && $password !== '' && extension_loaded('mysqli')) {
    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @mysqli_connect($servername, $username, $password, $dbname, $port);
    if ($conn) {
        mysqli_set_charset($conn, 'utf8mb4');
    }
}

$currentDomain = $_SERVER['SERVER_NAME'] ?? '';
$BaseURL = $currentDomain !== '' ? 'https://' . $currentDomain . '/' : '/';
