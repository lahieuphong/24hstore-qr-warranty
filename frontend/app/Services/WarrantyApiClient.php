<?php

namespace App\Services;

use App\Exceptions\BackendUnavailableException;
use App\Exceptions\WarrantyNotFoundException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Throwable;

class WarrantyApiClient
{
    /** @return array<string, mixed> */
    public function findByToken(string $token): array
    {
        return $this->get('warranties/'.rawurlencode($token));
    }

    /** @return array<string, mixed> */
    public function findByImei(string $imei): array
    {
        return $this->get('warranties/search', ['imei' => $imei]);
    }

    /** @param array<string, scalar> $query
     * @return array<string, mixed>
     */
    private function get(string $path, array $query = []): array
    {
        try {
            $response = $this->client()->get($path, $query);
        } catch (ConnectionException $exception) {
            throw new BackendUnavailableException('Không thể kết nối máy chủ dữ liệu.', previous: $exception);
        } catch (Throwable $exception) {
            throw new BackendUnavailableException('Máy chủ dữ liệu đang tạm thời gián đoạn.', previous: $exception);
        }

        if ($response->notFound()) {
            throw new WarrantyNotFoundException('Không tìm thấy thông tin bảo hành.');
        }

        if ($response->status() === 422) {
            $message = (string) collect($response->json('errors', []))->flatten()->first();
            throw new WarrantyNotFoundException($message ?: 'Thông tin tra cứu không hợp lệ.');
        }

        if ($response->failed()) {
            throw new BackendUnavailableException('Máy chủ dữ liệu chưa sẵn sàng. Vui lòng thử lại sau.');
        }

        $data = $response->json('data');

        if (! is_array($data)) {
            throw new BackendUnavailableException('Phản hồi từ máy chủ dữ liệu không hợp lệ.');
        }

        return $data;
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl((string) config('services.backend.api_url'))
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('services.backend.timeout', 8))
            ->connectTimeout(3)
            ->retry(2, 250, throw: false)
            ->withHeaders([
                'X-Frontend-Service' => '24hstore-warranty-lookup',
            ]);
    }
}
