<?php

namespace App\Libraries;

/**
 * Preserves the ASTRAL loader's Base64(JSON) catalog wire format.
 * Not an authorization token: every field in this public response is visible.
 */
final class LoaderCatalogFormatter
{
    public static function payload(array $rows, string $connectUrl): array
    {
        $entries = [];
        foreach ($rows as $row) {
            $status = strtoupper((string) ($row['status'] ?? 'DISABLED'));
            if (! in_array($status, ['ACTIVE', 'MAINTENANCE'], true)) {
                continue;
            }

            $entries[] = [
                'NAME' => (string) $row['name'],
                'DOWNLOAD' => (string) $row['download_url'],
                'URL' => $connectUrl,
                'ARCH' => (string) $row['architecture'],
                'KEY' => (string) $row['jni_key'],
                'GAME' => (string) $row['game_package'],
                'TYPE' => (string) $row['game_type'],
                'STATUS' => $status,
                'VERSION' => (string) ($row['version_name'] ?? ''),
                'LOGO' => (string) ($row['logo_url'] ?? ''),
            ];
        }

        // This value matches the current Android app (versionCode=1).
        // A higher value requires a coordinated client release/update URL.
        return [
            'META' => ['APP_VERSION_CODE' => 1],
            'DATA' => $entries,
        ];
    }

    public static function encode(array $payload): string
    {
        return base64_encode(json_encode(
            $payload,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ));
    }
}
