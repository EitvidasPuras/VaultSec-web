<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Storage::disk('local')->delete('test_tokens.txt');

        $credentials = [
            'first_name' => "ExampleFirst",
            'last_name' => "ExampleLast",
            'email' => "Example@example.com",
            'password' => bcrypt("123456789*aA"),
            'is_admin' => false,
            'ip_address' => request()->ip()
        ];
        $user = User::create($credentials);
        $token = $user->createToken('VaultSec_token')->accessToken;
        Storage::disk('local')->append('test_tokens.txt',
            $credentials['email'] . ' : ' . $token);
        $user->save();

        $credentials = [
            'first_name' => "Tom",
            'last_name' => "Brady",
            'email' => "tom@example.com",
            'password' => bcrypt("123456789*aA"),
            'is_admin' => false,
            'ip_address' => request()->ip(),
            'login_session_id' => 1
        ];
        $user = User::create($credentials);
        $token = $user->createToken('VaultSec_token')->accessToken;
        Storage::disk('local')->append('test_tokens.txt',
            $credentials['email'] . ' : ' . $token);
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
        $token = $admin->createToken('VaultSec_token')->accessToken;
        Storage::disk('local')->append('test_tokens.txt',
            $credentials['email'] . ' : ' . $token);
        $admin->save();
    }
}
