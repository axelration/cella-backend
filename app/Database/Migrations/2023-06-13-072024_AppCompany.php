<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class AppCompany extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'acm_id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'company_name' => [
                'type'      => 'VARCHAR',
                'constraint'=> 255,
                'null'      => false,
            ],
            'address' => [
                'type'      => 'TEXT',
                'null'      => true,
            ],
            'latitude' => [
                'type'      => 'FLOAT',
                'null'      => true,
            ],
            'longitude' => [
                'type'      => 'FLOAT',
                'null'      => true,
            ],
            'radius' => [
                'type'      => 'INT',
                'null'      => true,
            ],
            'mobile_phone' => [
                'type'       => 'VARCHAR',
                'constraint'=> 255,
                'null'      => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint'=> 255,
                'null'      => true,
            ],
            'cusr_id' => [
                'type'       => 'INT',
                'null'      => true,
            ],
            'ctime' => [
                'type'       => 'DATETIME',
                'null'      => true,
                'default'   => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'musr_id' => [
                'type'       => 'INT',
                'null'      => true,
            ],
            'mtime' => [
                'type'       => 'DATETIME',
                'null'      => true,
            ],
            'dusr_id' => [
                'type'       => 'INT',
                'null'      => true,
            ],
            'dtime' => [
                'type'       => 'DATETIME',
                'null'      => true,
            ],
            'is_deleted' => [
                'type'       => 'CHAR',
                'constraint' => '1',
                'default'   => new RawSql("'0'"),
                'null'      => true,
            ],
        ]);
        $this->forge->addKey('acm_id', true);
        $this->forge->createTable('app_company');
    }

    public function down()
    {
        $this->forge->dropTable('app_company');
    }
}
