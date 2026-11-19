<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// ====================================================================
// MCE-eOffice REST API v1
// ====================================================================
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1'], static function ($routes) {
    // CORS Preflight
    $routes->options('(:any)', 'BaseApiController::options');

    // 3D Virtual Office & Spatial Scene
    $routes->get('office/scene', 'OfficeController::scene');
    $routes->get('office/stats', 'OfficeController::stats');

    // Agent Profiles & Divisions
    $routes->get('agents', 'AgentsController::index');
    $routes->get('agents/(:segment)', 'AgentsController::show/$1');
    $routes->get('divisions', 'AgentsController::divisions');

    // Chat Sessions & 9Router AI Inference
    $routes->post('characters/(:segment)/interact', 'ConversationsController::interact/$1');
    $routes->get('conversations/(:segment)/messages', 'ConversationsController::messages/$1');
    $routes->post('conversations/(:segment)/send', 'ConversationsController::send/$1');
    $routes->get('conversations/(:segment)/stream', 'ConversationsController::stream/$1');

    // Task Delegation & Deliverables Review
    $routes->get('tasks', 'TasksController::index');
    $routes->post('tasks', 'TasksController::create');
    $routes->get('tasks/(:segment)', 'TasksController::show/$1');
    $routes->post('tasks/(:segment)/approve', 'TasksController::approve/$1');
    $routes->post('tasks/(:segment)/reject', 'TasksController::reject/$1');

    // Admin Management Portal Endpoints
    $routes->get('admin/overview', 'AdminController::overview');
    $routes->get('admin/agents', 'AdminController::agents');
    $routes->put('admin/agents/(:segment)', 'AdminController::updateAgent/$1');
    $routes->put('agents/(:segment)', 'AdminController::updateAgent/$1');
    $routes->put('admin/divisions/(:segment)', 'AdminController::updateDivision/$1');
    $routes->put('divisions/(:segment)', 'AdminController::updateDivision/$1');
    $routes->get('admin/conversations', 'AdminController::conversations');
    $routes->get('admin/conversations/(:segment)/messages', 'AdminController::conversationMessages/$1');
    $routes->post('admin/test-inference', 'AdminController::testInference');
});
