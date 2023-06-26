<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class AppRole extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'arl_id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
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
                'default'   => '0',
                'null'      => true,
            ],
        ]);
        $this->forge->addKey('arl_id', true);
        $this->forge->createTable('app_role');
    }

    public function down()
    {
        $this->forge->dropTable('app_role');
    }
}
