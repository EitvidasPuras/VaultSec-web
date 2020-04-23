<?php

use App\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $credentials = [
            'first_name' => "ExampleFirst",
            'last_name' => "ExampleLast",
            'email' => "Example@example.com",
            'password' => bcrypt("123456789*aA"),
            'is_admin' => false,
            'ip_address' => request()->ip()
        ];
        $user = User::create($credentials);
        $user->createToken('VaultSec_token')->accessToken;
        $user->save();

        $credentials = [
            'first_name' => "AdminFirst",
            'last_name' => "AdminLast",
            'email' => "Admin@example.com",
            'password' => bcrypt("123456789*aA"),
            'is_admin' => true,
            'ip_address' => request()->ip()
        ];
        $admin = User::create($credentials);
        $admin->createToken('VaultSec_token')->accessToken;
        $admin->save();
    }
}
