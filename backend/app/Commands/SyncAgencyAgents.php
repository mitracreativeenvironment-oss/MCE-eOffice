<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SyncAgencyAgents extends BaseCommand
{
    protected $group       = 'Agency';
    protected $name        = 'agency:sync';
    protected $description = 'Sync agency-agents catalog, divisions, and 3D Coastal Office Campus into MySQL';

    public function run(array $params)
    {
        CLI::write("===> Starting Strategic Coastal Campus & Agent Sync...", 'yellow');

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

        // Divisional building layout across the 4 Campus Zones:
        // - Zone 1: Front Boulevard (facing Highway)
        // - Zone 2: Tech & Defense Quad
        // - Zone 3: Creative & Business Plaza
        // - Zone 4: Beachfront Oceanfront Promenade
        $campusBuildings = [
            // Zone 1: Front Avenue (Near Highway, Z = +28)
            'marketing'          => ['zone' => 'Front Avenue',         'x' => -34.0, 'z' =>  28.0, 'label' => 'Marketing Creative Pavilion'],
            'support'            => ['zone' => 'Front Avenue',         'x' => -11.0, 'z' =>  28.0, 'label' => 'Customer Care & Welcome Center'],
            'sales'              => ['zone' => 'Front Avenue',         'x' =>  11.0, 'z' =>  28.0, 'label' => 'Global Sales Executive Lounge'],
            'paid-media'         => ['zone' => 'Front Avenue',         'x' =>  34.0, 'z' =>  28.0, 'label' => 'Paid Media & Growth Tower'],

            // Zone 2: Tech & Defense Quad (Z = +10)
            'engineering'        => ['zone' => 'Tech & Defense Quad',  'x' => -38.0, 'z' =>  10.0, 'label' => 'Core Engineering Cyber Tower'],
            'security'           => ['zone' => 'Tech & Defense Quad',  'x' => -11.0, 'z' =>  10.0, 'label' => 'Security Operations Bunker'],
            'testing'            => ['zone' => 'Tech & Defense Quad',  'x' =>  11.0, 'z' =>  10.0, 'label' => 'QA & Stress Proving Lab'],
            'gis'                => ['zone' => 'Tech & Defense Quad',  'x' =>  34.0, 'z' =>  10.0, 'label' => 'Geospatial & Satellite Dome'],

            // Zone 3: Creative & Business Plaza (Z = -10)
            'product'            => ['zone' => 'Creative & Business',  'x' => -34.0, 'z' => -10.0, 'label' => 'Product Discovery Atrium'],
            'design'             => ['zone' => 'Creative & Business',  'x' => -11.0, 'z' => -10.0, 'label' => 'Avant-Garde Design Pavilion'],
            'game-development'   => ['zone' => 'Creative & Business',  'x' =>  11.0, 'z' => -10.0, 'label' => 'Interactive Game Studio'],
            'finance'            => ['zone' => 'Creative & Business',  'x' =>  34.0, 'z' => -10.0, 'label' => 'Finance & Wealth Tower'],

            // Zone 4: Beachfront Promenade (Ocean View, Z = -31)
            'academic'           => ['zone' => 'Beachfront Promenade', 'x' => -40.0, 'z' => -31.0, 'label' => 'Academic Rotunda Library'],
            'research'           => ['zone' => 'Beachfront Promenade', 'x' => -24.0, 'z' => -31.0, 'label' => 'Quantum Research Laboratory'],
            'spatial-computing'  => ['zone' => 'Beachfront Promenade', 'x' =>  -8.0, 'z' => -31.0, 'label' => 'Spatial Computing XR Lab'],
            'specialized'        => ['zone' => 'Beachfront Promenade', 'x' =>   8.0, 'z' => -31.0, 'label' => 'Strategic Innovation Spire'],
            'healthcare'         => ['zone' => 'Beachfront Promenade', 'x' =>  26.0, 'z' => -31.0, 'label' => 'Biophilic Healthcare Pavilion'],
            'project-management' => ['zone' => 'Beachfront Promenade', 'x' =>  42.0, 'z' => -31.0, 'label' => 'Project Management Command'],
        ];

        CLI::write("Syncing 18 Campus Divisions...", 'cyan');
        $now = date('Y-m-d H:i:s');

        foreach ($divisionsMap as $id => $info) {
            $building = $campusBuildings[$id] ?? ['zone' => 'Central Campus', 'x' => 0.0, 'z' => 0.0, 'label' => ucfirst($id)];
            $existing = $db->table('divisions')->where('id', $id)->get()->getRowArray();
            $data = [
                'id'          => $id,
                'label'       => $info['label'] ?? ucfirst($id),
                'icon_name'   => $info['icon'] ?? 'Briefcase',
                'color_hex'   => $info['color'] ?? '#3B82F6',
                'floor_level' => 1,
                'description' => "Gedung Kantor {$building['label']} di zona {$building['zone']}.",
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

        $getModelTier = function ($division, $slug) {
            if ($division === 'security' || str_contains($slug, 'architect') || str_contains($slug, 'lead')) {
                return 'reasoning';
            }
            if ($division === 'engineering' || $division === 'game-development' || $division === 'gis') {
                return 'coding';
            }
            if ($division === 'design' || $division === 'spatial-computing') {
                return 'multimodal';
            }
            if ($division === 'product' || $division === 'project-management' || $division === 'finance') {
                return 'planning';
            }
            return 'fast';
        };

        $syncedAgents = 0;
        foreach ($agentsList as $agent) {
            $slug = $agent['slug'] ?? '';
            if (!$slug) continue;

            $divisionId = $agent['division'] ?? 'specialized';
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

        // 3. Spatial Campus Buildings & Workstations Generation
        CLI::write("Generating 18 Campus Standalone Buildings & Agent Workstations...", 'cyan');

        $db->table('characters')->emptyTable();
        $db->table('office_desks')->emptyTable();
        $db->table('office_rooms')->emptyTable();

        $agentsByDivision = [];
        $allAgents = $db->table('agent_profiles')->orderBy('division_id', 'ASC')->orderBy('name', 'ASC')->get()->getResultArray();
        foreach ($allAgents as $a) {
            $agentsByDivision[$a['division_id']][] = $a;
        }

        $totalDesks = 0;
        $totalCharacters = 0;

        foreach ($divisionsMap as $divId => $divInfo) {
            $b = $campusBuildings[$divId] ?? ['zone' => 'Central Campus', 'x' => 0.0, 'z' => 0.0, 'label' => ucfirst($divId)];
            $roomId = $this->generateUuid();

            $roomData = [
                'id'              => $roomId,
                'division_id'     => $divId,
                'name'            => "Gedung {$b['label']}",
                'floor_level'     => 1,
                'position_x'      => $b['x'],
                'position_y'      => 0.0,
                'position_z'      => $b['z'],
                'camera_target_x' => $b['x'],
                'camera_target_y' => 3.5,
                'camera_target_z' => $b['z'],
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
            $db->table('office_rooms')->insert($roomData);

            $divAgents = $agentsByDivision[$divId] ?? [];
            $count = count($divAgents);

            // Determine columns based on headcount
            if ($count <= 6) {
                $cols = 3;
            } elseif ($count <= 12) {
                $cols = 4;
            } elseif ($count <= 24) {
                $cols = 6;
            } elseif ($count <= 45) {
                $cols = 7;
            } else {
                $cols = 9; // For 59 (Specialized) and 66 (Engineering)
            }

            $rows = (int) ceil($count / $cols);

            // Proportional terrace dimension based on team size
            $t = min(1.0, max(0.0, ($count - 5) / (66 - 5)));
            $terraceWidth = 13.0 + pow($t, 0.7) * 21.0;
            $terraceDepth = 12.0 + pow($t, 0.7) * 18.0;

            $spacingX = ($terraceWidth * 0.82) / $cols;
            $spacingZ = min(2.4, ($terraceDepth * 0.55) / max(1, $rows));

            foreach ($divAgents as $idx => $agent) {
                $col = $idx % $cols;
                $row = (int) floor($idx / $cols);

                // Cluster desks proportionally on the open-air/glass terrace of the building
                $deskX = $b['x'] + ($col - ($cols - 1) / 2.0) * $spacingX;
                $deskZ = $b['z'] + ($terraceDepth * 0.12) + ($row - ($rows - 1) / 2.0) * $spacingZ;
                $deskY = 0.42;

                $deskId = $this->generateUuid();
                $deskCode = strtoupper(substr($divId, 0, 3)) . "-B" . ($idx + 1);

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

        CLI::write("✓ Generated 18 Standalone Divisional Buildings across the Campus.", 'green');
        CLI::write("✓ Generated {$totalDesks} Spatial Workstations.", 'green');
        CLI::write("✓ Allocated {$totalCharacters} 3D Agent Characters in their divisional buildings.", 'green');
        CLI::write("=====================================================", 'yellow');
        CLI::write("Campus Spatial Sync complete! Coastal Office Complex Ready.", 'green');
    }

    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
