<?php

use App\VaultFile;
use Illuminate\Database\Seeder;

class VaultFilesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            'user_id' => 2,
            'file_name' => 'example',
            'stored_file_name' => 'example',
            'file_extension' => '.txt',
            'file_size' => 175,
            'file_size_v' => 175,
            'ip_address' => '127.0.0.1',
            'currently_shared' => false
        ];

        $vaultNote = VaultFile::create($data);
        $vaultNote->save();

    }
}
