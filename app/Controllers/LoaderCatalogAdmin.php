<?php

namespace App\Controllers;

use App\Models\UserModel;

/** Owner-only catalog management. AdminFilter protects every route. */
final class LoaderCatalogAdmin extends BaseController
{
    public function index()
    {
        $db = db_connect();
        $entries = $db->table('loader_catalog')
            ->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')
            ->get()->getResultArray();
        $existingLicenseGames = $db->table('keys_code')
            ->distinct()->select('game')->orderBy('game', 'ASC')->get()->getResultArray();
        $editId = (int) ($this->request->getGet('edit') ?? 0);
        $selected = null;
        foreach ($entries as $entry) {
            if ((int) $entry['id'] === $editId) {
                $selected = $entry;
                break;
            }
        }

        return view('Admin/loader_catalog', [
            'title' => 'Loader catalog',
            'user' => (new UserModel())->getUser(session()->get('userid')),
            'entries' => $entries,
            'licenseGames' => $existingLicenseGames,
            'selected' => $selected,
        ]);
    }

    public function save()
    {
        $input = [
            'name' => trim((string) $this->request->getPost('name')),
            'game_type' => trim((string) $this->request->getPost('game_type')),
            'game_package' => trim((string) $this->request->getPost('game_package')),
            'architecture' => trim((string) $this->request->getPost('architecture')),
            'jni_key' => trim((string) $this->request->getPost('jni_key')),
            'download_url' => trim((string) $this->request->getPost('download_url')),
            'version_name' => trim((string) $this->request->getPost('version_name')),
            'logo_url' => trim((string) $this->request->getPost('logo_url')),
            'status' => trim((string) $this->request->getPost('status')),
            'sort_order' => (int) $this->request->getPost('sort_order'),
        ];
        $id = (int) $this->request->getPost('id');

        $invalid = ! (mb_strlen($input['name']) >= 1 && mb_strlen($input['name']) <= 120)
            || ! preg_match('/^[A-Za-z0-9_-]{1,32}$/D', $input['game_type'])
            || ! preg_match('/^[A-Za-z0-9._-]{2,190}$/D', $input['game_package'])
            || ! in_array($input['architecture'], ['32bit', '64bit'], true)
            || mb_strlen($input['jni_key']) > 255
            || mb_strlen($input['version_name']) > 50
            || ! self::validHttpsUrl($input['download_url'])
            || ($input['logo_url'] !== '' && ! self::validHttpsUrl($input['logo_url']))
            || ! in_array($input['status'], ['ACTIVE', 'MAINTENANCE', 'DISABLED'], true)
            || $input['sort_order'] < 0 || $input['sort_order'] > 100000;

        if ($invalid) {
            return redirect()->to(site_url('admin/loader-catalog'))
                ->with('catalog_error', 'Check the fields: HTTPS URLs, 32bit/64bit architecture, and a license game TYPE matching keys_code.game are required.');
        }

        try {
            $builder = db_connect()->table('loader_catalog');
            if ($id > 0) {
                if (! $builder->where('id', $id)->countAllResults()) {
                    return redirect()->to(site_url('admin/loader-catalog'))
                        ->with('catalog_error', 'Catalog entry no longer exists.');
                }
                $builder->where('id', $id)->update($input);
            } else {
                $builder->insert($input);
            }
        } catch (\Throwable $error) {
            log_message('error', 'Catalog entry save failed: {error}', [
                'error' => $error->getMessage(),
            ]);
            return redirect()->to(site_url('admin/loader-catalog'))
                ->with('catalog_error', 'Unable to save catalog entry. Check the database migration.');
        }

        return redirect()->to(site_url('admin/loader-catalog'))
            ->with('catalog_notice', 'Catalog entry saved.');
    }

    public function remove()
    {
        $id = (int) $this->request->getPost('id');
        if ($id < 1) {
            return redirect()->to(site_url('admin/loader-catalog'))
                ->with('catalog_error', 'Invalid catalog entry.');
        }
        try {
            db_connect()->table('loader_catalog')->where('id', $id)->delete();
        } catch (\Throwable $error) {
            log_message('error', 'Catalog entry delete failed: {error}', [
                'error' => $error->getMessage(),
            ]);
            return redirect()->to(site_url('admin/loader-catalog'))
                ->with('catalog_error', 'Unable to remove catalog entry.');
        }
        return redirect()->to(site_url('admin/loader-catalog'))
            ->with('catalog_notice', 'Catalog entry removed.');
    }

    private static function validHttpsUrl(string $url): bool
    {
        if (strlen($url) > 1024 || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }
        $parts = parse_url($url);
        return is_array($parts)
            && strtolower((string) ($parts['scheme'] ?? '')) === 'https'
            && ! empty($parts['host'])
            && ! isset($parts['user'])
            && ! isset($parts['pass']);
    }
}
