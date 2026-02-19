<?php

namespace App\Services;

use Botble\PluginManagement\Services\MarketplaceService as BaseMarketplaceService;
use Botble\Base\Supports\Core;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CustomMarketplaceService extends BaseMarketplaceService
{
    public function __construct(?string $url = null, ?string $token = null)
    {
        $core = Core::make()->getCoreFileData();

        $this->url = rtrim(
            $url ?? Arr::get($core, 'marketplaceUrl', config('packages.plugin-management.general.marketplace_url', 'https://marketplace.botble.com')),
            '/'
        );

        $this->token = $token ?? Arr::get($core, 'marketplaceToken', config('packages.plugin-management.general.marketplace_token', 'guest-token'));

        $this->publishedPath = storage_path('app/marketplace');

        $this->productId = Arr::get($core, 'productId', config('packages.plugin-management.general.marketplace_product_id', ''));

        $this->licenseUrl = rtrim(
            Arr::get($core, 'apiUrl', config('packages.plugin-management.general.marketplace_license_url', 'https://api.botble.com')),
            '/'
        );

        $this->licenseApiKey = Arr::get($core, 'apiKey', config('packages.plugin-management.general.marketplace_license_api_key', ''));

        // BYPASS: Set default values if empty, instead of throwing exception
        if (! $this->url) {
            $this->url = 'https://marketplace.botble.com';
        }

        if (! $this->token) {
            $this->token = 'guest-token';
        }
    }

    // Use public requests when token is not provided or is a guest token
    protected function request(): \Illuminate\Http\Client\PendingRequest
    {
        $request = \Illuminate\Support\Facades\Http::asJson()
            ->acceptJson()
            ->withoutVerifying()
            ->connectTimeout(100)
            ->timeout(300);

        if ($this->token && $this->token !== 'guest-token') {
            $request = $request->withHeaders([
                'Authorization' => 'Token ' . $this->token,
            ]);
        }

        return $request;
    }

    // Graceful fallback for missing endpoints - always use local catalog for marketplace listing
    public function callApi(string $method, string $path, array $request = []): \Illuminate\Http\JsonResponse|\Illuminate\Http\Client\Response
    {
        $catalog = $this->loadCatalog();

        // List products - always use local catalog
        if ($path === '/products') {
            $type = $request['type'] ?? null;
            $items = $catalog['data'] ?? [];
            if ($type) {
                $items = array_values(array_filter($items, fn($i) => ($i['type'] ?? null) === $type));
            }
            return response()->json([
                'error' => false,
                'data' => $items,
            ]);
        }

        // Product detail - always use local catalog
        if (Str::startsWith($path, '/products/')) {
            $id = Str::after($path, '/products/');
            $id = Str::before($id, '/');
            $item = collect($catalog['data'] ?? [])->firstWhere('id', $id);
            if ($item) {
                return response()->json([
                    'error' => false,
                    'data' => $item,
                ]);
            }
            return response()->json([
                'error' => true,
                'message' => 'Product not found in local catalog.'
            ], 404);
        }

        // Check updates - always use local catalog
        if ($path === '/products/check-update') {
            $installed = $request['products'] ?? [];
            $available = collect($catalog['data'] ?? [])
                ->mapWithKeys(fn($i) => [$i['id'] => $i['version'] ?? '1.0.0'])
                ->all();

            $updates = [];
            foreach ($installed as $id => $version) {
                if (isset($available[$id]) && version_compare($available[$id], $version, '>')) {
                    $updates[$id] = $available[$id];
                }
            }

            return response()->json([
                'error' => false,
                'data' => $updates,
            ]);
        }

        // For other endpoints, try remote
        try {
            return parent::callApi($method, $path, $request);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Endpoint unavailable: ' . $path
            ], 503);
        }
    }

    protected function loadCatalog(): array
    {
        $path = storage_path('app/marketplace/plugins.json');
        if (! File::exists($path)) {
            return ['data' => []];
        }
        $json = File::get($path);
        $data = json_decode($json, true);
        return is_array($data) ? $data : ['data' => []];
    }

    public function beginInstall(string $id, string $name, ?\Botble\PluginManagement\Services\PluginService $pluginService = null): bool|\Illuminate\Http\JsonResponse
    {
        // Try remote download first
        try {
            return parent::beginInstall($id, $name, $pluginService);
        } catch (\Exception $e) {
            // Attempt local install
            $catalog = $this->loadCatalog();
            $item = collect($catalog['data'] ?? [])->firstWhere('id', $id);
            if (! $item) {
                return response()->json([
                    'error' => true,
                    'message' => 'Product not found.'
                ], 404);
            }

            $storageTempPath = $this->publishedPath . '/' . $id;
            File::ensureDirectoryExists($storageTempPath, 0775);
            File::cleanDirectory($storageTempPath);

            $zipPath = $item['local_zip'] ? storage_path('app/marketplace/packages/' . $item['local_zip']) : null;

            if ($zipPath && File::exists($zipPath)) {
                // Copy zip to temp then extract and install
                $destination = $storageTempPath . '/' . $name . '.zip';
                File::copy($zipPath, $destination);
                $this->extractFile($storageTempPath, $name);
                $this->copyToPath($storageTempPath, plugin_path($name));
                return true;
            }

            if (! empty($item['download_url'])) {
                $response = \Illuminate\Support\Facades\Http::get($item['download_url']);
                if ($response->failed()) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Could not download plugin package.'
                    ], 400);
                }
                $destination = $storageTempPath . '/' . $name . '.zip';
                File::put($destination, $response->body());
                $this->extractFile($storageTempPath, $name);
                $this->copyToPath($storageTempPath, plugin_path($name));
                return true;
            }

            return response()->json([
                'error' => true,
                'message' => 'Package not available locally. Provide a zip in storage/app/marketplace/packages or configure a download_url.'
            ], 400);
        }
    }
}
