<?php

use Slim\Routing\RouteCollectorProxy;
use App\Controllers\AuthController;
use App\Controllers\ExpenseController;
use App\Middleware\AuthMiddleware;

// Rota raiz - redirecionar para dashboard ou login
$app->get('/', function ($request, $response) {
    if (isset($_SESSION['user_id'])) {
        return $response->withHeader('Location', '/dashboard')->withStatus(302);
    }
    return $response->withHeader('Location', '/login')->withStatus(302);
});

// Rotas de autenticação (sem middleware)
$app->group('', function (RouteCollectorProxy $group) {
    $group->get('/login', [AuthController::class, 'loginForm']);
    $group->post('/login', [AuthController::class, 'login']);
    $group->get('/register', [AuthController::class, 'registerForm']);
    $group->post('/register', [AuthController::class, 'register']);
});

// Rota de logout
$app->post('/logout', [AuthController::class, 'logout']);

// Rotas protegidas (com middleware de autenticação)
$app->group('', function (RouteCollectorProxy $group) {
    // Dashboard
    $group->get('/dashboard', [ExpenseController::class, 'dashboard']);
    
    // Gastos
    $group->get('/expenses', [ExpenseController::class, 'index']);
    $group->get('/expenses/new', [ExpenseController::class, 'create']);
    $group->post('/expenses', [ExpenseController::class, 'store']);
    $group->get('/expenses/{id}/edit', [ExpenseController::class, 'edit']);
    $group->post('/expenses/{id}', [ExpenseController::class, 'update']);
    $group->post('/expenses/{id}/delete', [ExpenseController::class, 'delete']);
    
})->add(new AuthMiddleware());
