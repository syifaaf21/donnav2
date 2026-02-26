<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class DocSpaceService
{
    protected string $baseUrl;
    protected string $email;
    protected string $password;
    protected string $folderId;

    public function __construct()
    {
        $this->baseUrl  = rtrim(config('onlyoffice.docspace_url'), '/');
        $this->email    = config('onlyoffice.docspace_email');
        $this->password = config('onlyoffice.docspace_password');
        $this->folderId = config('onlyoffice.docspace_folder_id');
    }

    /**
     * Get Bearer auth token (untuk REST API calls)
     * di-cache 8 jam
     */
    public function getToken(): string
    {
        return Cache::remember('docspace_token', now()->addHours(8), function () {
            $response = Http::acceptJson()
                ->post("{$this->baseUrl}/api/2.0/authentication", [
                    'UserName' => $this->email,
                    'Password' => $this->password,
                ]);

            if (!$response->successful()) {
                throw new \Exception('DocSpace auth gagal: ' . $response->body());
            }

            return $response->json('response.token');
        });
    }

    /**
     * Get asc_auth_key — ini yang dibutuhkan DocSpace JavaScript SDK untuk embed.
     * Berbeda dengan Bearer token, ini adalah cookie session value.
     * di-cache 8 jam (sama dengan session DocSpace)
     */
    public function getAscAuthKey(): string
    {
        return Cache::remember('docspace_asc_auth_key', now()->addHours(8), function () {
            $response = Http::acceptJson()
                ->withOptions(['cookies' => true]) // penting: tangkap cookie
                ->post("{$this->baseUrl}/api/2.0/authentication", [
                    'UserName' => $this->email,
                    'Password' => $this->password,
                ]);

            if (!$response->successful()) {
                throw new \Exception('DocSpace auth gagal: ' . $response->body());
            }

            // Coba ambil dari response body dulu (beberapa versi DocSpace return ini)
            $token = $response->json('response.token');

            // asc_auth_key biasanya sama dengan token di DocSpace API response
            // namun ada juga di Set-Cookie header
            $cookies = $response->cookies(); // GuzzleHttp\Cookie\CookieJar atau array

            // Coba ambil dari cookie jika ada
            if (method_exists($cookies, 'toArray')) {
                foreach ($cookies->toArray() as $cookie) {
                    if (($cookie['Name'] ?? '') === 'asc_auth_key') {
                        return $cookie['Value'];
                    }
                }
            }

            // Fallback: pakai token dari body (biasanya works untuk DocSpace cloud)
            return $token;
        });
    }

    /**
     * Generate shared/external link untuk file tertentu.
     * Link ini bisa dipakai untuk embed editor tanpa perlu autentikasi user.
     * Return: share token string
     */
    public function getOrCreateFileShareToken(string $docspaceFileId): string
    {
        $cacheKey = "docspace_share_token_{$docspaceFileId}";

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($docspaceFileId) {
            $token = $this->getToken();

            // Cek apakah sudah ada external link
            $response = Http::withToken($token)
                ->get("{$this->baseUrl}/api/2.0/files/file/{$docspaceFileId}/share");

            if ($response->successful()) {
                $shares = $response->json('response') ?? [];
                foreach ($shares as $share) {
                    // Cari share dengan access level editor (2) atau full access (1)
                    if (isset($share['sharedTo']['shareLink']) && in_array($share['access'], [1, 2])) {
                        // Extract token dari URL share link
                        $shareUrl = $share['sharedTo']['shareLink'];
                        parse_str(parse_url($shareUrl, PHP_URL_QUERY), $params);
                        if (!empty($params['key'])) {
                            return $params['key'];
                        }
                    }
                }
            }

            // Buat share link baru jika belum ada
            $createResponse = Http::withToken($token)
                ->put("{$this->baseUrl}/api/2.0/files/file/{$docspaceFileId}/share", [
                    'share' => [
                        [
                            'ShareTo' => 'everyone',  // atau user ID / email
                            'Access'  => 2,           // 1=FullAccess, 2=Editor, 9=Comment
                        ]
                    ],
                    'message'     => '',
                    'notify'      => false,
                    'sharingMessage' => '',
                ]);

            if (!$createResponse->successful()) {
                throw new \Exception('Gagal membuat share link: ' . $createResponse->body());
            }

            $shares = $createResponse->json('response') ?? [];
            foreach ($shares as $share) {
                if (isset($share['sharedTo']['shareLink'])) {
                    $shareUrl = $share['sharedTo']['shareLink'];
                    parse_str(parse_url($shareUrl, PHP_URL_QUERY), $params);
                    if (!empty($params['key'])) {
                        return $params['key'];
                    }
                }
            }

            throw new \Exception('Share token tidak ditemukan di response DocSpace');
        });
    }

    /**
     * Upload file dari Laravel storage ke DocSpace
     */
    public function uploadFile(string $localPath, string $fileName): array
    {
        $token    = $this->getToken();
        $fullPath = storage_path('app/public/' . $localPath);

        if (!file_exists($fullPath)) {
            throw new \Exception("File tidak ditemukan di storage: {$localPath}");
        }

        $response = Http::withToken($token)
            ->attach('file', fopen($fullPath, 'r'), $fileName)
            ->post("{$this->baseUrl}/api/2.0/files/{$this->folderId}/upload");

        if (!$response->successful()) {
            if ($response->status() === 401) {
                Cache::forget('docspace_token');
                Cache::forget('docspace_asc_auth_key');
                $token = $this->getToken();
                $response = Http::withToken($token)
                    ->attach('file', fopen($fullPath, 'r'), $fileName)
                    ->post("{$this->baseUrl}/api/2.0/files/{$this->folderId}/upload");
            }

            if (!$response->successful()) {
                throw new \Exception('Upload DocSpace gagal: ' . $response->body());
            }
        }

        $data = $response->json('response');

        return [
            'file_id'   => (string) $data['id'],
            'folder_id' => (string) ($data['folderId'] ?? $this->folderId),
        ];
    }

    /**
     * Download file dari DocSpace dan simpan ke Laravel storage
     */
    public function downloadAndSave(string $docspaceFileId, string $localPath): bool
    {
        $token = $this->getToken();

        $infoResponse = Http::withToken($token)
            ->get("{$this->baseUrl}/api/2.0/files/file/{$docspaceFileId}");

        if (!$infoResponse->successful()) {
            throw new \Exception('Gagal ambil info file dari DocSpace');
        }

        $viewUrl = $infoResponse->json('response.viewUrl');

        if (!$viewUrl) {
            throw new \Exception('URL download tidak tersedia dari DocSpace');
        }

        $content  = Http::withToken($token)->get($viewUrl)->body();
        $fullPath = storage_path('app/public/' . $localPath);
        $dir      = dirname($fullPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($fullPath, $content);

        return true;
    }

    /**
     * Get view URL for a file
     */
    public function getFileViewUrl(string $docspaceFileId): ?string
    {
        $token = $this->getToken();

        $infoResponse = Http::withToken($token)
            ->get("{$this->baseUrl}/api/2.0/files/file/{$docspaceFileId}");

        if (!$infoResponse->successful()) {
            return null;
        }

        return $infoResponse->json('response.viewUrl');
    }

    /**
     * Hapus file dari DocSpace
     */
    public function deleteFile(string $docspaceFileId): bool
    {
        $token = $this->getToken();

        $response = Http::withToken($token)
            ->delete("{$this->baseUrl}/api/2.0/files/file/{$docspaceFileId}");

        return $response->successful();
    }
}
