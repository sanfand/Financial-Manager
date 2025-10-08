<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_tokens extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'user_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'null' => FALSE
            ),
            'token' => array(
                'type' => 'VARCHAR',
                'constraint' => 128,
                'null' => FALSE
            ),
            'created_at' => array(
                'type' => 'DATETIME',
                'null' => FALSE
            ),
            'expires_at' => array(
                'type' => 'DATETIME',
                'null' => FALSE
            ),
            'is_valid' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => FALSE,
                'default' => 1
            )
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('user_id');
        $this->dbforge->add_key('token');
        $this->dbforge->create_table('tokens');
        // Add FK after table creation
        $this->db->query('ALTER TABLE tokens ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
    }

    public function down() {
        $this->dbforge->drop_table('tokens');
    }
}