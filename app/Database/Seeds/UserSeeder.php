<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'username'      => 'cella',
            'password'      => '$2y$10$XVr6sS7smzPonzOjIN1jzec9ye0UpkhEU7QA60iSj4F6BgRILrtQK',
            'fullname'      => 'Admin Cella',
            'mobile_phone'  => '1234',
            'email'         => 'admin@cella.id',
            'acm_id'        => '1',
            'agp_id'        => '1',
            'arl_id'        => '1',
        ];

        $this->db->table('app_user')->insert($data);
    }
}
