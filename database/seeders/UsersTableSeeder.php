<?php

namespace Database\Seeders;

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeders.
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
            'password' => bcrypt("cd9d8743b80501a5ccd6f9c83d2ed0141356d8b2daf93890cc4bdb86625cebc4"),
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
            'password' => bcrypt("cd9d8743b80501a5ccd6f9c83d2ed0141356d8b2daf93890cc4bdb86625cebc4"),
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
            'password' => bcrypt("cd9d8743b80501a5ccd6f9c83d2ed0141356d8b2daf93890cc4bdb86625cebc4"),
            'is_admin' => true,
            'ip_address' => request()->ip()
        ];
        $admin = User::create($credentials);
        $token = $admin->createToken('VaultSec_token')->accessToken;
        Storage::disk('local')->append('test_tokens.txt',
            $credentials['email'] . ' : ' . $token);
        $admin->save();

        $credentials = [
            'first_name' => "Nu",
            'last_name' => "Nu",
            'email' => "nu@nu.com",
            'password' => bcrypt("cd9d8743b80501a5ccd6f9c83d2ed0141356d8b2daf93890cc4bdb86625cebc4"),
            'is_admin' => false,
            'ip_address' => request()->ip()
        ];
        $user = User::create($credentials);
//        $token = $user->createToken('VaultSec_token')->accessToken;
//        Storage::disk('local')->append('test_tokens.txt',
//            $credentials['email'] . ' : ' . $token);
        $user->save();
    }
}
