<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(VaultPasswordsTableSeeder::class);
        $this->call(LoginSessionsTableSeeder::class);
        // $this->call(UserSeeder::class);
    }
}
