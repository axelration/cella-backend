<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run()
    {
        $data = [
            'company_name'  => 'PT Cella Teknologi Jaya',
            'address'       => 'Jl. Tebet Barat Raya, Jakarta Selatan',
            'email'         => 'cella@cella.id',
            'mobile_phone'  => '123456',
        ];

        $this->db->table('app_company')->insert($data);
    }
}
