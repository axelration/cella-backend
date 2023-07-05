<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class AppGroup extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'agp_id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint'=> 255,
                'null'      => true,
            ],
            'acm_id' => [
                'type'       => 'INT',
                'null'      => true,
            ],
            'address' => [
                'type'      => 'TEXT',
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
            'check_in_limit' => [
                'type'       => 'TIME',
                'default'   => new RawSql("'08:30:00'"),
                'null'      => true,
            ],
            'check_out_limit' => [
                'type'       => 'TIME',
                'default'   => new RawSql("'17:00:00'"),
                'null'      => true,
            ],
            'check_in_enable' => [
                'type'       => 'TIME',
                'default'   => new RawSql("'06:00:00'"),
                'null'      => true,
            ],
            'check_out_enable' => [
                'type'       => 'TIME',
                'default'   => new RawSql("'13:00:00'"),
                'null'      => true,
            ],
            'check_in_disable' => [
                'type'       => 'TIME',
                'default'   => new RawSql("'09:30:00'"),
                'null'      => true,
            ],
            'check_out_disable' => [
                'type'       => 'TIME',
                'default'   => new RawSql("'23:00:00'"),
                'null'      => true,
            ],
        ]);
        $this->forge->addKey('agp_id', true);
        $this->forge->createTable('app_group');
    }

    public function down()
    {
        $this->forge->dropTable('app_group');
    }
}
