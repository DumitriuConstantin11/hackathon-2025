<?php

declare(strict_types=1);

namespace App\Domain\Service;

class CBService {
    private array $categoryBudgets;
    public function __construct() {
        $json= $_ENV["EXPENSE_CATEGORIES"] ?? "{}";
        $this->categoryBudgets = json_decode($json, true) ?? [];
    }
    public function getCategory(): array {
        return array_keys($this->categoryBudgets);
    }
    public function getBudgets(): array {
        return $this->categoryBudgets;
    }
}