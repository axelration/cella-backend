<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class AppAttendance extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'att_id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'type' => [
                'type'      => 'CHAR',
                'constraint'=> 1,
                'default'   => new RawSql("'1'"),
                'null'      => false,
            ],
            'check_time' => [
                'type'      => 'DATETIME',
                'null'      => false,
            ],
            'latitude' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'longitude' => [
                'type'       => 'FLOAT',
                'null'      => true,
            ],
            'device_id' => [
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
        $this->forge->addKey('att_id', true);
        $this->forge->createTable('app_attendance');
    }

    public function down()
    {
        $this->forge->dropTable('app_attendance');
    }
}
