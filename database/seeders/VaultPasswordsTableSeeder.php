<?php

namespace Database\Seeders;

use App\VaultPassword;
use Illuminate\Database\Seeder;

class VaultPasswordsTableSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            'user_id' => 2,
            'title' => 'TestMe',
            'website_name' => 'www.example.com',
            'login' => 'TestMyUsername',
            'password' => 'TestMyExcryptedPassword',
            'category' => 'Unassigned',
            'ip_address' => '127.0.0.1',
            'currently_shared' => false
        ];
        $vaultPassword = VaultPassword::create($data);
        $vaultPassword->save();

        $data = [
            'user_id' => 2,
            'title' => 'TestMeme',
            'website_name' => 'www.example.com',
            'login' => 'TomBrady',
            'password' => '123456',
            'category' => 'Banking',
            'ip_address' => '127.0.0.1',
            'currently_shared' => false
        ];
        $vaultPassword = VaultPassword::create($data);
        $vaultPassword->save();

        $data = [
            'user_id' => 1,
            'title' => 'Example',
            'website_name' => 'www.example.com',
            'login' => 'Example',
            'password' => 'password',
            'category' => 'Shopping',
            'ip_address' => '127.0.0.1',
            'currently_shared' => false
        ];
        $vaultPassword = VaultPassword::create($data);
        $vaultPassword->save();
    }
}
