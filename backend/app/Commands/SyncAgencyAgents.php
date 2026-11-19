<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SyncAgencyAgents extends BaseCommand
{
    protected $group       = 'Agency';
    protected $name        = 'agency:sync';
    protected $description = 'Sync agency-agents catalog, divisions, and 3D office spatial desks into MySQL';

    public function run(array $params)
    {
        CLI::write("===> Starting Agency Agents & 3D Spatial Sync...", 'yellow');

        $db = \Config\Database::connect();

        // 1. Load divisions.json
        $divisionsPath = ROOTPATH . '../agency-agents/divisions.json';
        if (!file_exists($divisionsPath)) {
            $divisionsPath = FCPATH . '../../agency-agents/divisions.json';
        }

        if (!file_exists($divisionsPath)) {
            CLI::error("Divisions file not found at: {$divisionsPath}");
            return;
        }

        $divisionsData = json_decode(file_get_contents($divisionsPath), true);
        $divisionsMap = $divisionsData['divisions'] ?? [];

        // Floor mapping for the 18 divisions
        $floorMapping = [
            // Floor 1: Public, Operations & Client Facing
            'support'            => 1,
            'sales'              => 1,
            'marketing'          => 1,
            'paid-media'         => 1,
            // Floor 2: Engineering & Deep Infrastructure
            'engineering'        => 2,
            'security'           => 2,
            'testing'            => 2,
            'gis'                => 2,
            // Floor 3: Product, Design & Creative Studio
            'product'            => 3,
            'design'             => 3,
            'game-development'   => 3,
            'finance'            => 3,
            // Floor 4: Strategy, Spatial & Innovation
            'academic'           => 4,
            'spatial-computing'  => 4,
            'project-management' => 4,
            'research'           => 4,
            'healthcare'         => 4,
            'specialized'        => 4,
        ];

        CLI::write("Syncing 18 Divisions...", 'cyan');
        $now = date('Y-m-d H:i:s');

        foreach ($divisionsMap as $id => $info) {
            $floor = $floorMapping[$id] ?? 1;
            $existing = $db->table('divisions')->where('id', $id)->get()->getRowArray();
            $data = [
                'id'          => $id,
                'label'       => $info['label'] ?? ucfirst($id),
                'icon_name'   => $info['icon'] ?? 'Briefcase',
                'color_hex'   => $info['color'] ?? '#3B82F6',
                'floor_level' => $floor,
                'description' => "Divisi {$info['label']} yang beroperasi di Lantai {$floor}.",
                'updated_at'  => $now,
            ];

            if ($existing) {
                $db->table('divisions')->where('id', $id)->update($data);
            } else {
                $data['created_at'] = $now;
                $db->table('divisions')->insert($data);
            }
        }
        CLI::write("✓ Divisions synced (" . count($divisionsMap) . " divisions).", 'green');

        // 2. Load compiled agents.json
        $agentsPath = ROOTPATH . '../agency-agents/integrations/hermes/agency-agents-router/data/agents.json';
        if (!file_exists($agentsPath)) {
            $agentsPath = FCPATH . '../../agency-agents/integrations/hermes/agency-agents-router/data/agents.json';
        }

        if (!file_exists($agentsPath)) {
            CLI::error("Compiled agents.json not found at: {$agentsPath}");
            return;
        }

        $agentsList = json_decode(file_get_contents($agentsPath), true);
        if (!is_array($agentsList)) {
            CLI::error("Invalid agents.json structure.");
            return;
        }

        CLI::write("Syncing " . count($agentsList) . " Agent Profiles...", 'cyan');

        // Model tier mapping based on division / slug keywords
        $getModelTier = function ($division, $slug) {
            if ($division === 'security' || str_contains($slug, 'architect') || str_contains($slug, 'lead')) {
                return 'reasoning'; // Tier 1: Claude 3.7 Sonnet / DeepSeek R1
            }
            if ($division === 'engineering' || $division === 'game-development' || $division === 'gis') {
                return 'coding'; // Tier 2: DeepSeek Chat V3 / Claude 3.5
            }
            if ($division === 'design' || $division === 'spatial-computing') {
                return 'multimodal'; // Tier 3: Claude 3.5 Sonnet
            }
            if ($division === 'product' || $division === 'project-management' || $division === 'finance') {
                return 'planning'; // Tier 4: Claude 3.7 / Claude 3.5
            }
            return 'fast'; // Tier 5: Gemini 2.5 Flash / Fast LLM
        };

        $syncedAgents = 0;
        foreach ($agentsList as $agent) {
            $slug = $agent['slug'] ?? '';
            if (!$slug) continue;

            $divisionId = $agent['division'] ?? 'specialized';
            // Verify division exists in map
            if (!isset($divisionsMap[$divisionId])) {
                $divisionId = 'specialized';
            }

            $existing = $db->table('agent_profiles')->where('slug', $slug)->get()->getRowArray();
            $agentId = $existing['id'] ?? $this->generateUuid();

            $agentData = [
                'id'                   => $agentId,
                'division_id'          => $divisionId,
                'slug'                 => $slug,
                'name'                 => $agent['name'] ?? ucfirst($slug),
                'description'          => $agent['description'] ?? '',
                'color'                => $agent['color'] ?? ($divisionsMap[$divisionId]['color'] ?? '#3B82F6'),
                'emoji'                => $agent['emoji'] ?? '🤖',
                'vibe'                 => $agent['vibe'] ?? '',
                'source_path'          => $agent['source_path'] ?? '',
                'system_prompt'        => $agent['body'] ?? '',
                'preferred_model_tier' => $getModelTier($divisionId, $slug),
                'is_active'            => 1,
                'updated_at'           => $now,
            ];

            if ($existing) {
                $db->table('agent_profiles')->where('id', $agentId)->update($agentData);
            } else {
                $agentData['created_at'] = $now;
                $db->table('agent_profiles')->insert($agentData);
            }
            $syncedAgents++;
        }
        CLI::write("✓ Synced {$syncedAgents} Agent Profiles into MySQL.", 'green');

        // 3. Spatial Rooms & Desks Generation for 3D Office
        CLI::write("Generating 3D Spatial Layout (Rooms, Desks, Characters)...", 'cyan');

        // Clean up previous spatial records if needed or update existing
        $db->table('characters')->emptyTable();
        $db->table('office_desks')->emptyTable();
        $db->table('office_rooms')->emptyTable();

        // 4 Floors height: Floor 1: 0, Floor 2: 14, Floor 3: 28, Floor 4: 42
        $floorY = [1 => 0.0, 2 => 14.0, 3 => 28.0, 4 => 42.0];

        // Group agents by division
        $agentsByDivision = [];
        $allAgents = $db->table('agent_profiles')->orderBy('division_id', 'ASC')->orderBy('name', 'ASC')->get()->getResultArray();
        foreach ($allAgents as $a) {
            $agentsByDivision[$a['division_id']][] = $a;
        }

        // Room offsets per floor (grid 2x2 or 2x3 per floor)
        $divisionRoomOffsets = [
            // Floor 1 (4 divisions: 2x2 grid)
            'support'            => ['x' => -24.0, 'z' => -18.0],
            'sales'              => ['x' =>  24.0, 'z' => -18.0],
            'marketing'          => ['x' => -24.0, 'z' =>  18.0],
            'paid-media'         => ['x' =>  24.0, 'z' =>  18.0],
            // Floor 2 (4 divisions: 2x2 grid)
            'engineering'        => ['x' => -24.0, 'z' => -18.0],
            'security'           => ['x' =>  24.0, 'z' => -18.0],
            'testing'            => ['x' => -24.0, 'z' =>  18.0],
            'gis'                => ['x' =>  24.0, 'z' =>  18.0],
            // Floor 3 (4 divisions: 2x2 grid)
            'product'            => ['x' => -24.0, 'z' => -18.0],
            'design'             => ['x' =>  24.0, 'z' => -18.0],
            'game-development'   => ['x' => -24.0, 'z' =>  18.0],
            'finance'            => ['x' =>  24.0, 'z' =>  18.0],
            // Floor 4 (6 divisions: 3x2 grid)
            'academic'           => ['x' => -28.0, 'z' => -18.0],
            'spatial-computing'  => ['x' =>   0.0, 'z' => -18.0],
            'project-management' => ['x' =>  28.0, 'z' => -18.0],
            'research'           => ['x' => -28.0, 'z' =>  18.0],
            'healthcare'         => ['x' =>   0.0, 'z' =>  18.0],
            'specialized'        => ['x' =>  28.0, 'z' =>  18.0],
        ];

        $totalDesks = 0;
        $totalCharacters = 0;

        foreach ($divisionsMap as $divId => $divInfo) {
            $floor = $floorMapping[$divId] ?? 1;
            $roomId = $this->generateUuid();
            $roomOffset = $divisionRoomOffsets[$divId] ?? ['x' => 0.0, 'z' => 0.0];
            $yBase = $floorY[$floor] ?? 0.0;

            $roomData = [
                'id'              => $roomId,
                'division_id'     => $divId,
                'name'            => "Room " . ($divInfo['label'] ?? ucfirst($divId)),
                'floor_level'     => $floor,
                'position_x'      => $roomOffset['x'],
                'position_y'      => $yBase,
                'position_z'      => $roomOffset['z'],
                'camera_target_x' => $roomOffset['x'],
                'camera_target_y' => $yBase + 2.0,
                'camera_target_z' => $roomOffset['z'],
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
            $db->table('office_rooms')->insert($roomData);

            $divAgents = $agentsByDivision[$divId] ?? [];
            $cols = 4; // 4 desks per row in a room
            $spacingX = 4.5;
            $spacingZ = 4.5;

            foreach ($divAgents as $idx => $agent) {
                $col = $idx % $cols;
                $row = floor($idx / $cols);

                $deskX = $roomOffset['x'] + ($col - ($cols - 1) / 2) * $spacingX;
                $deskZ = $roomOffset['z'] + ($row * $spacingZ) - 6.0;
                $deskY = $yBase;

                $deskId = $this->generateUuid();
                $deskCode = strtoupper(substr($divId, 0, 3)) . "-F{$floor}-D" . ($idx + 1);

                $db->table('office_desks')->insert([
                    'id'         => $deskId,
                    'room_id'    => $roomId,
                    'desk_code'  => $deskCode,
                    'position_x' => round($deskX, 2),
                    'position_y' => round($deskY, 2),
                    'position_z' => round($deskZ, 2),
                    'rotation_y' => 0.0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $totalDesks++;

                // Create character tied to this agent and desk
                $charId = $this->generateUuid();
                $db->table('characters')->insert([
                    'id'                 => $charId,
                    'assigned_agent_id'  => $agent['id'],
                    'assigned_desk_id'   => $deskId,
                    'model_asset_path'   => 'procedural',
                    'current_status'     => 'idle',
                    'last_status_update' => $now,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);
                $totalCharacters++;
            }
        }

        CLI::write("✓ Generated " . count($divisionsMap) . " Office Rooms across 4 floors.", 'green');
        CLI::write("✓ Generated {$totalDesks} Spatial Workstation Desks.", 'green');
        CLI::write("✓ Allocated {$totalCharacters} 3D Agent Characters at workstations.", 'green');
        CLI::write("=====================================================", 'yellow');
        CLI::write("Sync complete! 3D Virtual Office & Agent Engine Ready.", 'green');
    }

    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variant
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
