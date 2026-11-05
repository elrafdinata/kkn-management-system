<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Membuat satu akun administrator dari nilai di .env.
     */
    public function run(): void
    {
        $name = config('kkn.admin.name');
        $username = config('kkn.admin.username');
        $password = config('kkn.admin.password');

        if (! $name || ! $username || ! $password) {
            $this->command->error('KKN_ADMIN_NAME, KKN_ADMIN_USERNAME, dan KKN_ADMIN_PASSWORD harus diisi di .env.');

            return;
        }

        User::updateOrCreate(
            ['username' => $username],
            [
                'name' => $name,
                'password' => $password,
                'role' => 'admin',
                'schedule_category' => null,
                'is_active' => true,
            ],
        );

        $this->command->info('Akun administrator siap.');
    }
}
