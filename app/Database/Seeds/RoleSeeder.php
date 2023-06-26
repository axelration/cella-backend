<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $data = array(
            ['name' => 'admin'], 
            ['name' => 'employee']
        );

        $this->db->table('app_role')->insertBatch($data);
    }
}
