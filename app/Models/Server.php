<?php

namespace App\Models;

use CodeIgniter\Model;

class Server extends Model
{
    /*=================================================================*/

    protected $table      = 'modname';
    protected $allowedFields = ['modname'];

    public function __construct()
    {
        parent::__construct();

        // Installs created from the bundled SQL script may still keep the mod
        // name in the legacy `mod_settings` table. Use whichever exists.
        try {
            if (! $this->db->tableExists('modname') && $this->db->tableExists('mod_settings')) {
                $this->table = 'mod_settings';
            }
        } catch (\Throwable $e) {
            // Keep the default table when the database cannot be reached.
        }
    }
}
