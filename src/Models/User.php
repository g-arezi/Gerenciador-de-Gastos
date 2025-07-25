<?php

namespace App\Models;

class User
{
    private string $dataFile;
    
    public function __construct()
    {
        $this->dataFile = __DIR__ . '/../../data/users.json';
        $this->ensureDataFile();
    }
    
    private function ensureDataFile(): void
    {
        if (!file_exists($this->dataFile)) {
            $dir = dirname($this->dataFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents($this->dataFile, json_encode([]));
        }
    }
    
    public function create(string $username, string $password, string $email): bool
    {
        $users = $this->getAll();
        
        // Verificar se usuário já existe
        foreach ($users as $user) {
            if ($user['username'] === $username || $user['email'] === $email) {
                return false;
            }
        }
        
        $newUser = [
            'id' => uniqid(),
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'email' => $email,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $users[] = $newUser;
        return $this->saveAll($users);
    }
    
    public function authenticate(string $username, string $password): ?array
    {
        $users = $this->getAll();
        
        foreach ($users as $user) {
            if (($user['username'] === $username || $user['email'] === $username) 
                && password_verify($password, $user['password'])) {
                // Remover senha do retorno
                unset($user['password']);
                return $user;
            }
        }
        
        return null;
    }
    
    public function findById(string $id): ?array
    {
        $users = $this->getAll();
        
        foreach ($users as $user) {
            if ($user['id'] === $id) {
                unset($user['password']);
                return $user;
            }
        }
        
        return null;
    }
    
    private function getAll(): array
    {
        $content = file_get_contents($this->dataFile);
        return json_decode($content, true) ?: [];
    }
    
    private function saveAll(array $users): bool
    {
        return file_put_contents($this->dataFile, json_encode($users, JSON_PRETTY_PRINT)) !== false;
    }
}
