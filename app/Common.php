<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the frameworks
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @link: https://codeigniter4.github.io/CodeIgniter4/
 */

/**
 * Run a query on a raw mysqli handle without letting a database error
 * escape as an exception.
 *
 * CodeIgniter's MySQLi driver raises
 * mysqli_report(MYSQLI_REPORT_ALL & ~MYSQLI_REPORT_INDEX) for the whole
 * request as soon as it opens its own connection. That turns a missing
 * table or an unknown column into a mysqli_sql_exception, which aborts
 * the request with a 500 error page instead of the JSON the game client
 * expects. The panel talks to the database through plain mysqli in a few
 * places (the /connect endpoint and the server settings view), so those
 * calls go through this helper and degrade to `false` instead of killing
 * the response.
 *
 * @param  mysqli|null $conn
 * @return mysqli_result|false
 */
function db_query($conn, string $sql)
{
    if (! $conn instanceof mysqli) {
        return false;
    }

    try {
        $result = @mysqli_query($conn, $sql);
    } catch (mysqli_sql_exception $e) {
        return false;
    }

    return $result === false ? false : $result;
}
