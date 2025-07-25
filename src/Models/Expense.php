<?php

namespace App\Models;

class Expense
{
    private string $dataFile;
    
    public function __construct()
    {
        $this->dataFile = __DIR__ . '/../../data/expenses.json';
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
    
    public function create(string $userId, array $data): bool
    {
        $expenses = $this->getAll();
        
        $newExpense = [
            'id' => uniqid(),
            'user_id' => $userId,
            'description' => $data['description'],
            'amount' => (float) $data['amount'],
            'category' => $data['category'],
            'date' => $data['date'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $expenses[] = $newExpense;
        return $this->saveAll($expenses);
    }
    
    public function update(string $id, string $userId, array $data): bool
    {
        $expenses = $this->getAll();
        
        foreach ($expenses as &$expense) {
            if ($expense['id'] === $id && $expense['user_id'] === $userId) {
                $expense['description'] = $data['description'];
                $expense['amount'] = (float) $data['amount'];
                $expense['category'] = $data['category'];
                $expense['date'] = $data['date'];
                $expense['updated_at'] = date('Y-m-d H:i:s');
                return $this->saveAll($expenses);
            }
        }
        
        return false;
    }
    
    public function delete(string $id, string $userId): bool
    {
        $expenses = $this->getAll();
        
        foreach ($expenses as $key => $expense) {
            if ($expense['id'] === $id && $expense['user_id'] === $userId) {
                unset($expenses[$key]);
                return $this->saveAll(array_values($expenses));
            }
        }
        
        return false;
    }
    
    public function findByUserId(string $userId): array
    {
        $expenses = $this->getAll();
        
        return array_filter($expenses, function($expense) use ($userId) {
            return $expense['user_id'] === $userId;
        });
    }
    
    public function findById(string $id, string $userId): ?array
    {
        $expenses = $this->getAll();
        
        foreach ($expenses as $expense) {
            if ($expense['id'] === $id && $expense['user_id'] === $userId) {
                return $expense;
            }
        }
        
        return null;
    }
    
    public function getByDateRange(string $userId, string $startDate, string $endDate): array
    {
        $expenses = $this->findByUserId($userId);
        
        return array_filter($expenses, function($expense) use ($startDate, $endDate) {
            return $expense['date'] >= $startDate && $expense['date'] <= $endDate;
        });
    }
    
    public function getTotalByCategory(string $userId): array
    {
        $expenses = $this->findByUserId($userId);
        $totals = [];
        
        foreach ($expenses as $expense) {
            $category = $expense['category'];
            if (!isset($totals[$category])) {
                $totals[$category] = 0;
            }
            $totals[$category] += $expense['amount'];
        }
        
        return $totals;
    }
    
    private function getAll(): array
    {
        $content = file_get_contents($this->dataFile);
        return json_decode($content, true) ?: [];
    }
    
    private function saveAll(array $expenses): bool
    {
        return file_put_contents($this->dataFile, json_encode($expenses, JSON_PRETTY_PRINT)) !== false;
    }
}
