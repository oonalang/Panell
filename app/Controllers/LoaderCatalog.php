<?php

namespace App\Controllers;

use App\Libraries\LoaderCatalogFormatter;

final class LoaderCatalog extends BaseController
{
    public function index()
    {
        try {
            $rows = db_connect()
                ->table('loader_catalog')
                ->select('name, download_url, game_package, game_type, architecture, jni_key, version_name, logo_url, status')
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();
            $encoded = LoaderCatalogFormatter::encode(
                LoaderCatalogFormatter::payload($rows, site_url('connect'))
            );
        } catch (\Throwable $error) {
            log_message('error', 'Unable to serve loader catalog: {error}', [
                'error' => $error->getMessage(),
            ]);
            return $this->response->setStatusCode(503)
                ->setHeader('Cache-Control', 'no-store')
                ->setBody('Catalog unavailable');
        }

        return $this->response->setHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->setHeader('Cache-Control', 'no-store')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody($encoded);
    }
}
