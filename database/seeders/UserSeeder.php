<?php

namespace Database\Seeders;

use App\Enums\UserTypeEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Akun demo bisnis sayur (Sayurin).
     * Semua password default: password123
     */
    public function run(): void
    {
        $users = [
            // Backoffice
            ['name' => 'Developer', 'email' => 'developer@sayur.test', 'role' => 'developer', 'type' => UserTypeEnum::USER, 'phone' => '081200000001'],
            ['name' => 'Admin Gudang', 'email' => 'admin@sayur.test', 'role' => 'admin', 'type' => UserTypeEnum::USER, 'phone' => '081200000002'],
            ['name' => 'Kasir Toko', 'email' => 'kasir@sayur.test', 'role' => 'user', 'type' => UserTypeEnum::USER, 'phone' => '081200000003'],

            // Reseller
            ['name' => 'Warung Bu Sari', 'email' => 'reseller@sayur.test', 'role' => 'user', 'type' => UserTypeEnum::RESELLER, 'phone' => '081300000001'],

            // Customer end-user
            ['name' => 'Budi Santoso', 'email' => 'budi@sayur.test', 'role' => 'user', 'type' => UserTypeEnum::CUSTOMER, 'phone' => '081400000001'],
            ['name' => 'Siti Aminah', 'email' => 'siti@sayur.test', 'role' => 'user', 'type' => UserTypeEnum::CUSTOMER, 'phone' => '081400000002'],
        ];

        foreach ($users as $row) {
            $this->upsertUser($row);
        }
    }

    private function upsertUser(array $row): void
    {
        $password = env('SEED_USER_PASSWORD', 'password123');

        $user = User::firstOrNew(['email' => $row['email']]);
        $user->name = $row['name'];
        $user->role = $row['role'];
        $user->type = $row['type'];
        $user->phone = $row['phone'] ?? null;
        $user->password = bcrypt($password);
        $user->verified_at = now();
        $user->email_verified_at = now();
        $user->save();

        // Reseller menjadi reference bagi dirinya sendiri
        if ($row['type'] === UserTypeEnum::RESELLER && empty($user->reference_id)) {
            $user->update(['reference_id' => $user->id]);
        }
    }
}
