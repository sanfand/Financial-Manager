<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_transactions extends CI_Migration {

    public function up() {
        $this->dbforge->add_field([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'user_id' => [
                'type' => 'INT',
                'unsigned' => TRUE,
            ],
            'category_id' => [
                'type' => 'INT',
                'unsigned' => TRUE,
                'null' => TRUE,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
            ],
            'type' => [
                'type' => 'ENUM("income","expense")',
            ],
            'occurred_at' => [
                'type' => 'DATE',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => TRUE,
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
        $this->dbforge->create_table('transactions');

        // Foreign keys
        $this->db->query('ALTER TABLE `transactions` ADD CONSTRAINT `fk_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE;');
        $this->db->query('ALTER TABLE `transactions` ADD CONSTRAINT `fk_transactions_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL;');
    }

    public function down() {
        $this->dbforge->drop_table('transactions');
    }
}