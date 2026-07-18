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
#[Title('Thông tin bảo hành')]
class WarrantyLookup extends Component
{
    public string $token;

    /** @var array<string, mixed>|null */
    public ?array $product = null;

    public int $state = 200;

    public string $message = '';

    public function mount(string $token, WarrantyApiClient $client): void
    {
        $this->token = $token;

        try {
            $this->product = $client->findByToken($token);
        } catch (WarrantyNotFoundException $exception) {
            $this->state = 404;
            $this->message = $exception->getMessage();
        } catch (BackendUnavailableException $exception) {
            $this->state = 503;
            $this->message = $exception->getMessage();
        }
    }

    public function reload(WarrantyApiClient $client): void
    {
        $this->state = 200;
        $this->message = '';

        try {
            $this->product = $client->findByToken($this->token);
        } catch (WarrantyNotFoundException $exception) {
            $this->product = null;
            $this->state = 404;
            $this->message = $exception->getMessage();
        } catch (BackendUnavailableException $exception) {
            $this->product = null;
            $this->state = 503;
            $this->message = $exception->getMessage();
        }
    }

    public function render(): View
    {
        return view('livewire.warranty-lookup');
    }
}
