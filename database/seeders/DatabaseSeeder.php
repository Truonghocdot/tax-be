<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedBank();
    }

    private function seedBank(): void
    {
        try {
            $response = Http::get('https://api.vietqr.io/v2/banks');
            dd($response->json()['data']);
        } catch (\Exception $e) {
        }
    }
}
