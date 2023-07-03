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
            'check_in_time' => '0830',
            'check_out_time'=> '1700',
        ];

        $this->db->table('app_group')->insert($data);
    }
}
