<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\User;
use App\Domain\Repository\ExpenseRepositoryInterface;

class AlertGenerator
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
        private readonly CBService $cbService,
    ) {}
    public function generate(User $user, int $year, int $month): array
    {
        $total=$this->expenses->sumAmountsByCategory([
            "user_id" => $user->id,
            "year" => $year,
            "month" => $month,
        ]);
        $alert=[];
        foreach ($total as $category => $totalCents) {
            $ttl=$totalCents/100;
            $budget= $this->cbService->getBudgetForCategory($category);
            if($ttl > $budget) {
                $diff=$ttl-$budget;
                $alert[]=sprintf("Buget depasit la categoria %s: %.2f € cheltuit din %.2f € buget alocat. Ai trecut peste cu %.2f €", $category, $ttl, $budget, $diff);
            }
        }

        return $alert;
    }
}
