<?php

use App\VaultNote;
use Illuminate\Database\Seeder;

class VaultNotesTableSeeder extends Seeder
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
            'title' => 'TestMe',
            'text' => 'Some sample notes for these very secure notes',
            'color' => '#0362fc',
            'font_size' => 12,
            'ip_address' => '127.0.0.1',
            'currently_shared' => false
        ];
        $vaultNote = VaultNote::create($data);
        $vaultNote->save();

        $data = [
            'user_id' => 1,
            'title' => 'RestRate',
            'text' => 'Throwback',
            'color' => '#fc0303',
            'font_size' => 18,
            'ip_address' => '127.0.0.1',
            'currently_shared' => false
        ];
        $vaultNote = VaultNote::create($data);
        $vaultNote->save();
    }
}
