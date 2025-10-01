<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_categories extends CI_Migration {

    public function up() {
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'user_id' => [
                'type' => 'INT',
                'unsigned' => TRUE,
                'null' => TRUE,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => '120',
            ],
            'type' => [
                'type' => 'ENUM("income","expense")',
                'default' => 'expense'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => TRUE,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => TRUE,
            ],
        ]);

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('categories');

        // Add default categories
        $default_categories = [
            ['user_id' => NULL, 'name' => 'Salary', 'type' => 'income', 'created_at' => date('Y-m-d H:i:s')],
            ['user_id' => NULL, 'name' => 'Freelance', 'type' => 'income', 'created_at' => date('Y-m-d H:i:s')],
            ['user_id' => NULL, 'name' => 'Investment', 'type' => 'income', 'created_at' => date('Y-m-d H:i:s')],
            ['user_id' => NULL, 'name' => 'Gift', 'type' => 'income', 'created_at' => date('Y-m-d H:i:s')],
            ['user_id' => NULL, 'name' => 'Food', 'type' => 'expense', 'created_at' => date('Y-m-d H:i:s')],
            ['user_id' => NULL, 'name' => 'Transportation', 'type' => 'expense', 'created_at' => date('Y-m-d H:i:s')],
            ['user_id' => NULL, 'name' => 'Entertainment', 'type' => 'expense', 'created_at' => date('Y-m-d H:i:s')],
            ['user_id' => NULL, 'name' => 'Utilities', 'type' => 'expense', 'created_at' => date('Y-m-d H:i:s')],
            ['user_id' => NULL, 'name' => 'Rent', 'type' => 'expense', 'created_at' => date('Y-m-d H:i:s')],
            ['user_id' => NULL, 'name' => 'Healthcare', 'type' => 'expense', 'created_at' => date('Y-m-d H:i:s')]
        ];
        
        $this->db->insert_batch('categories', $default_categories);
    }

    public function down() {
        $this->dbforge->drop_table('categories');
    }
}