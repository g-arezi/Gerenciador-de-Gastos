<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Models\User;

class AuthController
{
    private User $userModel;
    private Twig $view;
    
    public function __construct(Twig $view)
    {
        $this->userModel = new User();
        $this->view = $view;
    }
    
    public function loginForm(Request $request, Response $response): Response
    {
        if (isset($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }
        
        return $this->view->render($response, 'auth/login.html');
    }
    
    public function registerForm(Request $request, Response $response): Response
    {
        if (isset($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }
        
        return $this->view->render($response, 'auth/register.html');
    }
    
    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            return $this->view->render($response, 'auth/login.html', [
                'error' => 'Usuário e senha são obrigatórios'
            ]);
        }
        
        $user = $this->userModel->authenticate($username, $password);
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }
        
        return $this->view->render($response, 'auth/login.html', [
            'error' => 'Credenciais inválidas'
        ]);
    }
    
    public function register(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';
        
        // Validações
        $errors = [];
        
        if (empty($username)) {
            $errors[] = 'Nome de usuário é obrigatório';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email válido é obrigatório';
        }
        
        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Senha deve ter pelo menos 6 caracteres';
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = 'Senhas não coincidem';
        }
        
        if (!empty($errors)) {
            return $this->view->render($response, 'auth/register.html', [
                'errors' => $errors,
                'old' => $data
            ]);
        }
        
        // Criar usuário
        if ($this->userModel->create($username, $password, $email)) {
            return $this->view->render($response, 'auth/login.html', [
                'success' => 'Conta criada com sucesso! Faça login.'
            ]);
        }
        
        return $this->view->render($response, 'auth/register.html', [
            'error' => 'Usuário ou email já existem',
            'old' => $data
        ]);
    }
    
    public function logout(Request $request, Response $response): Response
    {
        session_destroy();
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}
