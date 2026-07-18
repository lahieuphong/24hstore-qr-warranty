<?php

namespace App\Livewire;

use App\Exceptions\BackendUnavailableException;
use App\Exceptions\WarrantyNotFoundException;
use App\Services\WarrantyApiClient;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.public')]
#[Title('Tra cứu bảo hành')]
class Home extends Component
{
    public string $imei = '';

    /** @var array<string, mixed>|null */
    public ?array $product = null;

    public string $lookupError = '';

    public bool $backendUnavailable = false;

    public function search(WarrantyApiClient $client): void
    {
        $this->imei = mb_strtoupper((string) preg_replace('/\s+/', '', trim($this->imei)));
        $this->product = null;
        $this->lookupError = '';
        $this->backendUnavailable = false;

        $this->validate([
            'imei' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9._\-]+$/'],
        ], [
            'imei.required' => 'Vui lòng nhập IMEI.',
            'imei.regex' => 'IMEI không đúng định dạng.',
        ]);

        try {
            $this->product = $client->findByImei($this->imei);
        } catch (WarrantyNotFoundException $exception) {
            $this->lookupError = $exception->getMessage();
        } catch (BackendUnavailableException $exception) {
            $this->backendUnavailable = true;
            $this->lookupError = $exception->getMessage();
        }
    }

    public function render(): View
    {
        return view('livewire.home');
    }
}
