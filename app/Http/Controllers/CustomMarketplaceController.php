<?php

namespace App\Http\Controllers;

use Botble\PluginManagement\Http\Controllers\MarketplaceController as BaseMarketplaceController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class CustomMarketplaceController extends BaseMarketplaceController
{
    public function list(Request $request): array|BaseHttpResponse
    {
        $request->merge(['type' => 'plugin']);

        try {
            $response = $this->marketplaceService->callApi('get', '/products', $request->input());

            if ($response instanceof JsonResponse) {
                $data = $response->getData(true);
            } else {
                $data = $response->json();
            }

            // Handle null or invalid response
            if (!$data || !is_array($data)) {
                return $this
                    ->httpResponse()
                    ->setError()
                    ->setMessage('Unable to connect to marketplace. Please check your internet connection or try again later.');
            }

            if (isset($data['error']) && $data['error']) {
                return $this
                    ->httpResponse()
                    ->setError()
                    ->setMessage($data['message'] ?? 'An error occurred while fetching marketplace data.');
            }

            // Check if data array exists
            if (!isset($data['data']) || !is_array($data['data'])) {
                return [
                    'data' => [],
                    'message' => 'No plugins available at the moment.',
                ];
            }

            $coreVersion = get_core_version();

            foreach ($data['data'] as $key => $item) {
                $data['data'][$key]['version_check'] = version_compare($coreVersion, $item['minimum_core_version'] ?? '1.0', '>=');
                $data['data'][$key]['humanized_last_updated_at'] = isset($item['last_updated_at']) 
                    ? Carbon::parse($item['last_updated_at'])->diffForHumans() 
                    : 'Unknown';
            }

            return $data;
        } catch (\Exception $e) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage('Marketplace is currently unavailable. Error: ' . $e->getMessage());
        }
    }

    public function listTheme(Request $request): array|BaseHttpResponse
    {
        $request->merge(['type' => 'theme']);

        try {
            $response = $this->marketplaceService->callApi('get', '/products', $request->input());

            if ($response instanceof JsonResponse) {
                $data = $response->getData(true);
            } else {
                $data = $response->json();
            }

            // Handle null or invalid response
            if (!$data || !is_array($data)) {
                return $this
                    ->httpResponse()
                    ->setError()
                    ->setMessage('Unable to connect to marketplace. Please check your internet connection or try again later.');
            }

            if (isset($data['error']) && $data['error']) {
                return $this
                    ->httpResponse()
                    ->setError()
                    ->setMessage($data['message'] ?? 'An error occurred while fetching marketplace data.');
            }

            // Check if data array exists
            if (!isset($data['data']) || !is_array($data['data'])) {
                return [
                    'data' => [],
                    'message' => 'No themes available at the moment.',
                ];
            }

            $coreVersion = get_core_version();

            foreach ($data['data'] as $key => $item) {
                $data['data'][$key]['version_check'] = version_compare($coreVersion, $item['minimum_core_version'] ?? '1.0', '>=');
                $data['data'][$key]['humanized_last_updated_at'] = isset($item['last_updated_at'])
                    ? Carbon::parse($item['last_updated_at'])->diffForHumans()
                    : 'Unknown';
            }

            return $data;
        } catch (\Exception $e) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage('Marketplace is currently unavailable. Error: ' . $e->getMessage());
        }
    }

    public function detail(string $id): JsonResponse|array|null
    {
        try {
            $response = $this->marketplaceService->callApi('get', '/products/' . $id);

            if ($response instanceof JsonResponse) {
                return $response;
            }

            $data = $response->json();

            // Handle null response
            if (!$data) {
                return response()->json([
                    'error' => true,
                    'message' => 'Unable to fetch plugin details.'
                ]);
            }

            return $data;
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Marketplace is currently unavailable.'
            ]);
        }
    }

    public function checkUpdate(): \Illuminate\Http\JsonResponse|array|null
    {
        try {
            $installedPlugins = $this->pluginService->getInstalledPluginIds();

            if (! $installedPlugins) {
                return response()->json();
            }

            $response = $this->marketplaceService->callApi('post', '/products/check-update', [
                'products' => $installedPlugins,
            ]);

            return $response instanceof \Illuminate\Http\JsonResponse ? $response : $response->json();
        } catch (\Exception $e) {
            // Graceful fallback
            return response()->json([
                'error' => false,
                'data' => [],
            ]);
        }
    }
}
