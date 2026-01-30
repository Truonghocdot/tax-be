<?php

namespace Database\Seeders;

use App\Models\Bank;
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
            $data = $response->json()['data'];
            foreach ($data as $bank) {
                Bank::insert([
                    'name' => $bank['name'],
                    'code' => $bank['code'],
                    'bin' => $bank['bin'],
                    'logo' => $bank['logo'],
                    'short_name' => $bank['short_name'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        } catch (\Exception $e) {
        }
    }
}
