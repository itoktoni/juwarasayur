<?php

namespace Database\Seeders;

use App\Enums\UserTypeEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Akun demo bisnis sayur (Sayurin).
     * Password default: SEED_USER_PASSWORD env atau "password123".
     * Mencakup 4 role × 4 type + customer bawahan affiliator (reference_id berantai).
     */
    public function run(): void
    {
        $password = env('SEED_USER_PASSWORD', 'password123');

        // --- 1. Backoffice (role internal, type=user) ---
        $backoffice = [
            ['name' => 'Developer', 'email' => 'developer@sayur.test', 'role' => 'developer', 'type' => UserTypeEnum::USER, 'phone' => '081200000001'],
            ['name' => 'Admin Gudang', 'email' => 'admin@sayur.test', 'role' => 'admin', 'type' => UserTypeEnum::USER, 'phone' => '081200000002'],
            ['name' => 'Editor Konten', 'email' => 'editor@sayur.test', 'role' => 'editor', 'type' => UserTypeEnum::USER, 'phone' => '081200000003'],
            ['name' => 'Kasir Toko', 'email' => 'kasir@sayur.test', 'role' => 'user', 'type' => UserTypeEnum::USER, 'phone' => '081200000004'],
        ];
        foreach ($backoffice as $row) {
            $this->upsertUser($row, $password);
        }

        // --- 2. Affiliator (butuh reference_id=null; fee & rekening untuk withdraw demo) ---
        $affiliator = $this->upsertUser([
            'name' => 'Andi Affiliator',
            'email' => 'affiliator@sayur.test',
            'role' => 'user',
            'type' => UserTypeEnum::AFFILIATOR,
            'phone' => '081500000001',
            'fee' => 2.5,
            'bank_name' => 'BCA',
            'bank_account_name' => 'Andi Affiliator',
            'bank_account_no' => '1234567890',
        ], $password);

        $affiliator2 = $this->upsertUser([
            'name' => 'Rina Affiliator',
            'email' => 'affiliator2@sayur.test',
            'role' => 'user',
            'type' => UserTypeEnum::AFFILIATOR,
            'phone' => '081500000002',
            'fee' => 3.0,
            'bank_name' => 'BRI',
            'bank_account_name' => 'Rina Affiliator',
            'bank_account_no' => '9876543210',
        ], $password);

        // --- 3. Reseller (belanja untuk diri sendiri dengan harga diskon) ---
        $this->upsertUser([
            'name' => 'Pak Hari Reseller',
            'email' => 'reseller@sayur.test',
            'role' => 'user',
            'type' => UserTypeEnum::RESELLER,
            'phone' => '081300000001',
            'fee' => 5.0,
        ], $password);

        // --- 4. Customer bawahan affiliator (reference_id berantai) ---
        $customersAff1 = [
            ['name' => 'Ibu Wati', 'email' => 'customer1@sayur.test', 'phone' => '081600000001'],
            ['name' => 'Pak Darmo', 'email' => 'customer2@sayur.test', 'phone' => '081600000002'],
        ];
        foreach ($customersAff1 as $row) {
            $this->upsertUser(array_merge($row, [
                'role' => 'user',
                'type' => UserTypeEnum::CUSTOMER,
                'reference_id' => $affiliator->id,
            ]), $password);
        }

        $customersAff2 = [
            ['name' => 'Ibu Lestari', 'email' => 'customer3@sayur.test', 'phone' => '081600000003'],
        ];
        foreach ($customersAff2 as $row) {
            $this->upsertUser(array_merge($row, [
                'role' => 'user',
                'type' => UserTypeEnum::CUSTOMER,
                'reference_id' => $affiliator2->id,
            ]), $password);
        }

        // --- 5. Customer publik (reference_id null) ---
        foreach ([
            ['name' => 'Budi Santoso', 'email' => 'budi@sayur.test', 'phone' => '081400000001'],
            ['name' => 'Siti Aminah', 'email' => 'siti@sayur.test', 'phone' => '081400000002'],
        ] as $row) {
            $this->upsertUser(array_merge($row, [
                'role' => 'user',
                'type' => UserTypeEnum::CUSTOMER,
            ]), $password);
        }
    }

    /**
     * Upsert user + set kolom opsional (fee, bank_*, reference_id) dan verified_at.
     * @param  array<string,mixed>  $row
     */
    private function upsertUser(array $row, string $password): User
    {
        $user = User::firstOrNew(['email' => $row['email']]);
        $user->name = $row['name'];
        $user->role = $row['role'];
        $user->type = $row['type'];
        $user->phone = $row['phone'] ?? null;
        $user->password = bcrypt($password);
        $user->verified_at = now();
        $user->email_verified_at = now();

        // Kolom opsional — set hanya jika tersedia di row
        foreach (['reference_id', 'fee', 'bank_name', 'bank_account_name', 'bank_account_no', 'consignasi'] as $col) {
            if (array_key_exists($col, $row)) {
                $user->{$col} = $row[$col];
            }
        }

        $user->save();

        return $user;
    }
}
