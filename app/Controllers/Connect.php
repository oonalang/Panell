<?php

namespace App\Controllers;

use App\Models\KeysModel;

/**
 * The licence endpoint the game client talks to.
 *
 * The client (VARIANT4, src/main/jni/Includes/Jni_Stuff.h) posts
 * `game`, `user_key` and `serial` to /connect and then reads exactly
 * three fields out of the answer:
 *
 *   result["status"]        must be the boolean true
 *   result["data"]["token"] must be a string
 *   result["data"]["rng"]   must be a number
 *   result["data"]["EXP"]   must be a string
 *
 * and compares the token against
 * md5("<game>-<user_key>-<serial>-Vm8Lk7Uj2JmsjCPVPVjrLa7zgfx3uz9E").
 * Anything else - a missing field, a null, a number where a string is
 * expected - throws inside the client and the login fails even though
 * the key is valid, so the response shape is part of the contract.
 */
class Connect extends BaseController
{
    protected $model, $game, $uKey, $sDev;
    protected $maintenance, $staticWords, $dbReady;

    public function __construct()
    {
        $conn = $this->db();

        $this->dbReady = $conn !== null;
        $this->model   = new KeysModel();
//=================================================
        $userDetails1 = $this->dbReady ? $this->fetchRow('select * from onoff where id=1', $conn) : null;
//=================================================
        $this->maintenance = (strtolower((string) ($userDetails1['status'] ?? '')) === 'on');
//=================================================
        $this->staticWords = 'Vm8Lk7Uj2JmsjCPVPVjrLa7zgfx3uz9E';
    }

    /**
     * Open the panel's database connection.
     *
     * conn.php lives in the project root, so it resolves whether the
     * panel is deployed from the project root or from the /public folder.
     */
    protected function db(): ?\mysqli
    {
        include(ROOTPATH . 'conn.php');

        return isset($conn) && $conn instanceof \mysqli ? $conn : null;
    }

    /**
     * Run a query and return the first row as an array.
     *
     * A missing table, a lost connection or bad SQL returns null instead
     * of raising an error, so the client always receives a JSON answer
     * it can display instead of an HTML error page.
     */
    protected function fetchRow(string $sql, ?\mysqli $conn = null): ?array
    {
        if ($conn === null) {
            $conn = $this->db();
        }

        $result = db_query($conn, $sql);

        if ($result === false) {
            return null;
        }

        $row = mysqli_fetch_assoc($result);
        mysqli_free_result($result);

        return $row ?: null;
    }

    /**
     * The panel keeps two database configurations: conn.php (raw mysqli)
     * and the CodeIgniter group in .env, which the models use. When they
     * point at different databases the licence is looked up in one of them
     * while the mod name is read from the other, and the client is told the
     * key is wrong even though it is perfectly valid.
     */
    protected function databasesMatch(\mysqli $conn): bool
    {
        $result = db_query($conn, 'select database() as db');
        $row    = $result ? mysqli_fetch_assoc($result) : null;

        if (! $row || empty($row['db'])) {
            // The connection could not tell us anything - do not block the login.
            return true;
        }

        try {
            $ciDatabase = (string) \Config\Database::connect()->getDatabase();
        } catch (\Throwable $e) {
            return true;
        }

        return $ciDatabase === '' || $ciDatabase === $row['db'];
    }

    /**
     * The mod name lives in `modname`; installs created from the bundled
     * SQL script may still keep it in the legacy `mod_settings` table.
     */
    protected function fetchModName(?\mysqli $conn = null): string
    {
        $row = $this->fetchRow('select modname from modname where id=1', $conn)
            ?? $this->fetchRow('select modname from mod_settings where id=1', $conn);

        $name = trim((string) ($row['modname'] ?? ''));

        return $name !== '' ? $name : BASE_NAME_FULL;
    }

    /**
     * Register a device against a key, or report that the device slots are full.
     *
     * @return array|bool ['devices' => 'serial,serial'] on a new device,
     *                     true when the serial is already bound, false when full
     */
    protected function checkDevicesAdd($serial, $devices, $max_dev)
    {
        $lsDevice = array_filter(explode(',', (string) $devices), static function ($d) {
            return $d !== '';
        });
        $cDevices = count($lsDevice);
        $serialOn = in_array($serial, $lsDevice, true);

        if ($serialOn) {
            return true;
        }

        if ($cDevices < $max_dev) {
            $lsDevice[] = $serial;

            return ['devices' => reduce_multiples(implode(',', $lsDevice), ',', true)];
        }

        return false;
    }

    /**
     * The empty payload the client expects next to a "status": false
     * answer, so that it can read the fields without hitting a
     * type error.
     */
    protected function emptyData(): array
    {
        return [
            'real'         => '',
            'token'        => '',
            'modname'      => BASE_NAME_FULL,
            'mod_status'   => '',
            'credit'       => '',
            'ESP'          => false,
            'Item'         => false,
            'AIM'          => false,
            'SilentAim'    => false,
            'BulletTrack'  => false,
            'Floating'     => false,
            'Memory'       => false,
            'Setting'      => false,
            'EXP'          => '',
            'device'       => 0,
            'MOD_NAME'     => BASE_NAME_FULL,
            'MOD_STATUS'   => '',
            'FLOTING_TEST' => '',
            'BHATIA_EXP'   => null,
            'BHATIA_SLOT'  => 0,
            'rng'          => 0,
        ];
    }

    protected function fail(string $reason)
    {
        return $this->response->setJSON([
            'status' => false,
            'reason' => $reason,
            'data'   => $this->emptyData(),
        ]);
    }

    public function index()
    {
        if ($this->request->getPost()) {
            return $this->index_post();
        }

        $nata = [
            'web_info' => [
                '_client' => BASE_NAME_FULL,
                'license' => "5G7B3F8J2H\n",
                'version' => '3.0.0',
            ],
            'web__dev' => [
                'author'  => 'Zia Dev',
                'Channel' => 'https://t.me/Shaiaintshyy',
                'Group'   => 'https://t.me/Shaiaintshyy',
            ],
        ];

        if ($this->clientWantsHtml()) {
            return view('Connect/endpoint', [
                'info'        => $nata,
                'maintenance' => $this->maintenance,
            ]);
        }

        return $this->response->setJSON($nata);
    }

    protected function clientWantsHtml()
    {
        $accept = $this->request->getHeaderLine('Accept');

        return $accept !== ''
            && strpos($accept, 'text/html') !== false
            && strpos($accept, 'application/json') === false;
    }

    public function index_post()
    {
        $conn = $this->db();

        if ($conn === null) {
            return $this->fail('Panel database unavailable, try again later. Contact: @Christdmm');
        }

        if (! $this->databasesMatch($conn)) {
            return $this->fail('Panel database configuration mismatch: conn.php and .env point at different databases. Contact: @Christdmm');
        }

        $isMT = $this->maintenance;
        $game = $this->request->getPost('game');
        $uKey = $this->request->getPost('user_key');
        $sDev = $this->request->getPost('serial');

        $form_rules = [
            'game'     => 'required|alpha_dash',
            'user_key' => 'required|alpha_dash',
            'serial'   => 'required|alpha_dash',
        ];

        if (! $this->validate($form_rules)) {
            return $this->fail('Bad Parameter, Contact: @Christdmm');
        }

        if ($isMT) {
            $userDetails1 = $this->fetchRow('select * from onoff where id=1', $conn) ?? [];

            return $this->fail((string) ($userDetails1['myinput'] ?? '') ?: 'Panel is under maintenance, try again later.');
        }

        if (! $game or ! $uKey or ! $sDev) {
            return $this->fail('Invalid Parameter, Contact: @Christdmm');
        }

        $time   = new \CodeIgniter\I18n\Time;
        $model  = $this->model;

        try {
            $findKey = $model->getKeysGame(['user_key' => $uKey, 'game' => $game]);
        } catch (\Throwable $e) {
            // The MySQLi driver raises the raw mysqli_sql_exception, not a
            // CodeIgniter exception, and the client cannot read a 500 page.
            return $this->fail('Panel database unavailable, try again later. Contact: @Christdmm');
        }

        if (! $findKey) {
            return $this->fail('Incorrect Key, Contact: @Christdmm');
        }

        if ($findKey->status != 1) {
            return $this->fail('Suspended Key, Contact: @Christdmm');
        }

        $id_keys  = $findKey->id_keys;
        $duration = (int) $findKey->duration;
        $expired  = $findKey->expired_date;
        $max_dev  = (int) $findKey->max_devices;
        $devices  = $findKey->devices;

        if ($duration < 1) {
            $duration = 1;
        }

        if ($max_dev < 1) {
            $max_dev = 1;
        }

        // A key that was never used has no expiry yet: start the clock now.
        $expiry = $expired;

        if (! $expiry) {
            $setExpired = $time::now()->addDays($duration);

            try {
                $model->update($id_keys, ['expired_date' => $setExpired]);
            } catch (\Throwable $e) {
                return $this->fail('Panel database unavailable, try again later. Contact: @Christdmm');
            }

            $expiry = $setExpired->toDateTimeString();
        } elseif (! $time::now()->isBefore($expiry)) {
            return $this->fail('Expired Key, Contact: @Christdmm');
        }

        $modName        = $this->fetchModName($conn);
        $userDetails3   = $this->fetchRow('select * from _ftext where id=1', $conn) ?? [];
//=================================================
        $safeKey        = mysqli_real_escape_string($conn, $uKey);
        $userDetails4   = $this->fetchRow("select expired_date from keys_code where user_key='{$safeKey}'", $conn) ?? [];
//=================================================
        $ModFeatureStatus = $this->fetchRow('select * from Feature where id=1', $conn) ?? [];
//=================================================
        $rngcnt         = $time->getTimestamp();
//=================================================
        $devicesAdd     = $this->checkDevicesAdd($sDev, $devices, $max_dev);

        if ($devicesAdd === false) {
            return $this->fail('Max Device Reached, Contact: @Christdmm');
        }

        if (is_array($devicesAdd)) {
            try {
                $model->update($id_keys, $devicesAdd);
            } catch (\Throwable $e) {
                return $this->fail('Panel database unavailable, try again later. Contact: @Christdmm');
            }
        }

        // ? game-user_key-serial-word di line 15
        $real = "$game-$uKey-$sDev-$this->staticWords";

        $data = [
            'status' => true,
            'data'   => [
                'real'         => $real,
                'token'        => md5($real),
                'modname'      => $modName,
                'mod_status'   => (string) ($userDetails3['_status'] ?? ''),
                'credit'       => (string) ($userDetails3['_ftext'] ?? ''),
                'ESP'          => (($ModFeatureStatus['ESP'] ?? 'false') == 'true') ? true : false,
                'Item'         => (($ModFeatureStatus['Item'] ?? 'false') == 'true') ? true : false,
                'AIM'          => (($ModFeatureStatus['AIM'] ?? 'false') == 'true') ? true : false,
                'SilentAim'    => (($ModFeatureStatus['SilentAim'] ?? 'false') == 'true') ? true : false,
                'BulletTrack'  => (($ModFeatureStatus['BulletTrack'] ?? 'false') == 'true') ? true : false,
                'Floating'     => (($ModFeatureStatus['Floating'] ?? 'false') == 'true') ? true : false,
                'Memory'       => (($ModFeatureStatus['Memory'] ?? 'false') == 'true') ? true : false,
                'Setting'      => (($ModFeatureStatus['Setting'] ?? 'false') == 'true') ? true : false,

                'EXP'          => (string) ($userDetails4['expired_date'] ?? $expiry),
                'device'       => $max_dev,
                'MOD_NAME'     => $modName,
                'MOD_STATUS'   => (string) ($userDetails3['_status'] ?? ''),
                'FLOTING_TEST' => (string) ($userDetails3['_ftext'] ?? ''),
                'BHATIA_EXP'   => $expired,
                'BHATIA_SLOT'  => $max_dev,
                'rng'          => $rngcnt,
            ],
        ];

        return $this->response->setJSON($data);
    }
}
