<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run()
    {
        $data = [
            'company_name'  => 'cella',
            'address'       => 'Jl. Tebet Barat Raya, Jakarta Selatan',
            'latitude'      => '-6.238351433334787',
            'longitude'     => '106.85262438789536',
            'radius'        => '500',
            'email'         => 'cella@cella.id',
            'mobile_phone'  => '123456',
        ];

        $this->db->table('app_company')->insert($data);
    }
}
