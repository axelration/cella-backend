<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MainSeeder extends Seeder
{
    public function run()
    {
        $this->call('RoleSeeder');
        $this->call('CompanySeeder');
        $this->call('GroupSeeder');
        $this->call('UserSeeder');
    }
}
