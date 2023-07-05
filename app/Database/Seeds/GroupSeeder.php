<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class GroupSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'name'          => 'Cella',
            'acm_id'        => '1',
            'address'       => 'Jl. Tebet Barat Raya, Jakarta Selatan',
            'latitude'      => '-6.238351433334787',
            'longitude'     => '106.85262438789536',
            'radius'        => '500',
            'check_in_limit' => '08:30:00',
            'check_out_limit'=> '17:00:00',
            'check_in_enable' => '06:00:00',
            'check_out_enable'=> '13:00:00',
            'check_in_disable' => '09:30:00',
            'check_out_disable'=> '23:00:00',
        ];

        $this->db->table('app_group')->insert($data);
    }
}
