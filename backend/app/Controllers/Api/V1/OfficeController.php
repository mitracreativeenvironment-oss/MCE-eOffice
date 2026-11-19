<?php

namespace App\Controllers\Api\V1;

use App\Models\CharacterModel;
use App\Models\DivisionModel;
use App\Models\RoomModel;
use App\Models\TaskModel;
use App\Services\RouterService;
use CodeIgniter\HTTP\ResponseInterface;

class OfficeController extends BaseApiController
{
    /**
     * GET /api/v1/office/scene
     * Returns full 3D layout: floors, rooms, desks, characters with agent metadata
     */
    public function scene(): ResponseInterface
    {
        $characterModel = new CharacterModel();
        $roomModel      = new RoomModel();
        $divisionModel  = new DivisionModel();

        $divisions  = $divisionModel->findAll();
        $rooms      = $roomModel->findAll();
        $characters = $characterModel->getFullSceneCharacters();

        // Organize rooms by floor
        $floors = [
            1 => [
                'level'       => 1,
                'name'        => 'Public, Operations & Client Facing',
                'height_y'    => 0.0,
                'color'       => '#3B82F6',
                'description' => 'Lantai 1: Support, Sales, Marketing, and Paid Media teams',
            ],
            2 => [
                'level'       => 2,
                'name'        => 'Engineering & Deep Infrastructure',
                'height_y'    => 14.0,
                'color'       => '#10B981',
                'description' => 'Lantai 2: Core Engineering, Security SOC, QA Testing, GIS Hub',
            ],
            3 => [
                'level'       => 3,
                'name'        => 'Product, Design & Creative Studio',
                'height_y'    => 28.0,
                'color'       => '#EC4899',
                'description' => 'Lantai 3: Product Management, UI/UX Studio, Game Dev, Finance',
            ],
            4 => [
                'level'       => 4,
                'name'        => 'Strategy, Spatial & Innovation',
                'height_y'    => 42.0,
                'color'       => '#8B5CF6',
                'description' => 'Lantai 4: Academic Council, Spatial XR Lab, Project Management, Healthcare, Research',
            ],
        ];

        return $this->success([
            'floors'     => array_values($floors),
            'divisions'  => $divisions,
            'rooms'      => $rooms,
            'characters' => $characters,
            'summary'    => [
                'total_floors'     => 4,
                'total_divisions'  => count($divisions),
                'total_rooms'      => count($rooms),
                'total_characters' => count($characters),
            ],
        ], '3D Office Scene data loaded successfully.');
    }

    /**
     * GET /api/v1/office/stats
     * Returns operational status of the virtual office and 9Router gateway
     */
    public function stats(): ResponseInterface
    {
        $characterModel = new CharacterModel();
        $divisionModel  = new DivisionModel();
        $taskModel      = new TaskModel();
        $routerService  = new RouterService();

        $routerHealth = $routerService->checkHealth();

        return $this->success([
            'total_agents'     => $characterModel->countAllResults(),
            'total_divisions'  => $divisionModel->countAllResults(),
            'total_tasks'      => $taskModel->countAllResults(),
            'pending_tasks'    => $taskModel->where('status', 'pending')->countAllResults(),
            'completed_tasks'  => $taskModel->where('status', 'approved')->countAllResults(),
            'router_gateway'   => $routerHealth,
        ], 'Virtual Office Statistics.');
    }
}

