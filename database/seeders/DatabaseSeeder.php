<?php

namespace Database\Seeders;

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
        $this->call(LoginSessionsTableSeeder::class);
        $this->call(VaultPasswordsTableSeeder::class);
        $this->call(VaultNotesTableSeeder::class);
        $this->call(VaultFilesTableSeeder::class);
        // $this->call(UserSeeder::class);
    }
}
