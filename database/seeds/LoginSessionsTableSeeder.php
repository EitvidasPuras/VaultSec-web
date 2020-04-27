<?php

use App\LoginSession;
use Illuminate\Database\Seeder;

class LoginSessionsTableSeeder extends Seeder
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
            'currently_active' => true,
        ];
        $session = LoginSession::create($data);
        $session->save();
    }
}
