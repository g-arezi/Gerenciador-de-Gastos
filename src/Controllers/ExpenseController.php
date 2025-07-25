<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Models\Expense;

class ExpenseController
{
    private Expense $expenseModel;
    private Twig $view;
    
    public function __construct(Twig $view)
    {
        $this->expenseModel = new Expense();
        $this->view = $view;
    }
    
    public function dashboard(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'];
        $expenses = $this->expenseModel->findByUserId($userId);
        $totalsByCategory = $this->expenseModel->getTotalByCategory($userId);
        
        // Ordenar por data mais recente
        usort($expenses, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        // Calcular total geral
        $totalAmount = array_sum(array_column($expenses, 'amount'));
        
        return $this->view->render($response, 'dashboard.html', [
            'expenses' => array_slice($expenses, 0, 10), // Últimas 10
            'totalsByCategory' => $totalsByCategory,
            'totalAmount' => $totalAmount,
            'user' => $_SESSION['username']
        ]);
    }
    
    public function index(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'];
        $expenses = $this->expenseModel->findByUserId($userId);
        
        // Ordenar por data mais recente
        usort($expenses, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $this->view->render($response, 'expenses/index.html', [
            'expenses' => $expenses
        ]);
    }
    
    public function create(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'expenses/form.html', [
            'title' => 'Novo Gasto',
            'action' => '/expenses'
        ]);
    }
    
    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $userId = $_SESSION['user_id'];
        
        // Validações
        $errors = [];
        
        if (empty($data['description'])) {
            $errors[] = 'Descrição é obrigatória';
        }
        
        if (empty($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
            $errors[] = 'Valor deve ser um número positivo';
        }
        
        if (empty($data['category'])) {
            $errors[] = 'Categoria é obrigatória';
        }
        
        if (empty($data['date'])) {
            $errors[] = 'Data é obrigatória';
        }
        
        if (!empty($errors)) {
            return $this->view->render($response, 'expenses/form.html', [
                'title' => 'Novo Gasto',
                'action' => '/expenses',
                'errors' => $errors,
                'old' => $data
            ]);
        }
        
        if ($this->expenseModel->create($userId, $data)) {
            return $response->withHeader('Location', '/expenses')->withStatus(302);
        }
        
        return $this->view->render($response, 'expenses/form.html', [
            'title' => 'Novo Gasto',
            'action' => '/expenses',
            'error' => 'Erro ao salvar gasto',
            'old' => $data
        ]);
    }
    
    public function edit(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $userId = $_SESSION['user_id'];
        
        $expense = $this->expenseModel->findById($id, $userId);
        
        if (!$expense) {
            return $response->withHeader('Location', '/expenses')->withStatus(302);
        }
        
        return $this->view->render($response, 'expenses/form.html', [
            'title' => 'Editar Gasto',
            'action' => "/expenses/{$id}",
            'expense' => $expense,
            'method' => 'PUT'
        ]);
    }
    
    public function update(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $data = $request->getParsedBody();
        $userId = $_SESSION['user_id'];
        
        // Validações (mesmo que store)
        $errors = [];
        
        if (empty($data['description'])) {
            $errors[] = 'Descrição é obrigatória';
        }
        
        if (empty($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
            $errors[] = 'Valor deve ser um número positivo';
        }
        
        if (empty($data['category'])) {
            $errors[] = 'Categoria é obrigatória';
        }
        
        if (empty($data['date'])) {
            $errors[] = 'Data é obrigatória';
        }
        
        if (!empty($errors)) {
            $expense = $this->expenseModel->findById($id, $userId);
            return $this->view->render($response, 'expenses/form.html', [
                'title' => 'Editar Gasto',
                'action' => "/expenses/{$id}",
                'method' => 'PUT',
                'errors' => $errors,
                'expense' => $expense
            ]);
        }
        
        if ($this->expenseModel->update($id, $userId, $data)) {
            return $response->withHeader('Location', '/expenses')->withStatus(302);
        }
        
        return $response->withHeader('Location', '/expenses')->withStatus(302);
    }
    
    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $userId = $_SESSION['user_id'];
        
        $this->expenseModel->delete($id, $userId);
        
        return $response->withHeader('Location', '/expenses')->withStatus(302);
    }
}
