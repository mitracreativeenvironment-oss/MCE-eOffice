<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOfficeEcosystemTables extends Migration
{
    public function up()
    {
        // 1. DIVISIONS TABLE
        $this->forge->addField([
            'id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'icon_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Briefcase',
            ],
            'color_hex' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => '#3B82F6',
            ],
            'floor_level' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 1,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('divisions', true);

        // 2. AGENT PROFILES TABLE
        $this->forge->addField([
            'id' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
            ],
            'division_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'unique'     => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'color' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => '#3B82F6',
            ],
            'emoji' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => '🤖',
            ],
            'vibe' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'source_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'system_prompt' => [
                'type' => 'LONGTEXT',
            ],
            'preferred_model_tier' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'coding',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('division_id');
        $this->forge->addForeignKey('division_id', 'divisions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('agent_profiles', true);

        // 3. OFFICE ROOMS TABLE
        $this->forge->addField([
            'id' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
            ],
            'division_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'floor_level' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 1,
            ],
            'position_x' => [
                'type'    => 'FLOAT',
                'default' => 0.0,
            ],
            'position_y' => [
                'type'    => 'FLOAT',
                'default' => 0.0,
            ],
            'position_z' => [
                'type'    => 'FLOAT',
                'default' => 0.0,
            ],
            'camera_target_x' => [
                'type'    => 'FLOAT',
                'default' => 0.0,
            ],
            'camera_target_y' => [
                'type'    => 'FLOAT',
                'default' => 0.0,
            ],
            'camera_target_z' => [
                'type'    => 'FLOAT',
                'default' => 0.0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('floor_level');
        $this->forge->createTable('office_rooms', true);

        // 4. OFFICE DESKS TABLE
        $this->forge->addField([
            'id' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
            ],
            'room_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
            ],
            'desk_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'position_x' => [
                'type'    => 'FLOAT',
                'default' => 0.0,
            ],
            'position_y' => [
                'type'    => 'FLOAT',
                'default' => 0.0,
            ],
            'position_z' => [
                'type'    => 'FLOAT',
                'default' => 0.0,
            ],
            'rotation_y' => [
                'type'    => 'FLOAT',
                'default' => 0.0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('room_id');
        $this->forge->createTable('office_desks', true);

        // 5. CHARACTERS TABLE
        $this->forge->addField([
            'id' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
            ],
            'assigned_agent_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
                'null'       => true,
            ],
            'assigned_desk_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
                'null'       => true,
            ],
            'model_asset_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'default'    => 'procedural',
            ],
            'current_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'idle',
            ],
            'last_status_update' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('assigned_agent_id');
        $this->forge->addKey('assigned_desk_id');
        $this->forge->createTable('characters', true);

        // 6. AGENT TASKS TABLE
        $this->forge->addField([
            'id' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
            ],
            'user_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
                'default'    => 'guest-user',
            ],
            'lead_agent_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'pending',
            ],
            'brief_input' => [
                'type' => 'LONGTEXT',
            ],
            'final_deliverable' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'review_feedback' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('lead_agent_id');
        $this->forge->createTable('agent_tasks', true);

        // 7. CONVERSATIONS TABLE
        $this->forge->addField([
            'id' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
            ],
            'agent_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
            ],
            'character_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
                'null'       => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'default'    => 'Conversation',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('agent_id');
        $this->forge->createTable('conversations', true);

        // 8. CONVERSATION MESSAGES TABLE
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'conversation_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
            ],
            'sender_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20, // 'user', 'agent', 'system'
            ],
            'content' => [
                'type' => 'LONGTEXT',
            ],
            'model_used' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'tokens_prompt' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'tokens_completion' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('conversation_id');
        $this->forge->createTable('conversation_messages', true);
    }

    public function down()
    {
        $this->forge->dropTable('conversation_messages', true);
        $this->forge->dropTable('conversations', true);
        $this->forge->dropTable('agent_tasks', true);
        $this->forge->dropTable('characters', true);
        $this->forge->dropTable('office_desks', true);
        $this->forge->dropTable('office_rooms', true);
        $this->forge->dropTable('agent_profiles', true);
        $this->forge->dropTable('divisions', true);
    }
}

