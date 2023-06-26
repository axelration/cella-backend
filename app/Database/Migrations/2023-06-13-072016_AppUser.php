<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class AppUser extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'usr_id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'username' => [
                'type'      => 'VARCHAR',
                'constraint'=> 255,
                'null'      => false,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint'=> 255,
                'null' => false,
            ],
            'fullname' => [
                'type'       => 'VARCHAR',
                'constraint'=> 255,
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
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint'=> 255,
                'null'      => true,
            ],
            'acm_id' => [
                'type'       => 'INT',
                'null'      => true,
            ],
            'agp_id' => [
                'type'       => 'INT',
                'null'      => true,
            ],
            'arl_id' => [
                'type'       => 'INT',
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
        $this->forge->addKey('usr_id', true);
        $this->forge->createTable('app_user');
    }

    public function down()
    {
        $this->forge->dropTable('app_user');
    }
}
