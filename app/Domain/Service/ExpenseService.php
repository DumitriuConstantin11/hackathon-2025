<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\Expense;
use App\Domain\Entity\User;
use App\Domain\Repository\ExpenseRepositoryInterface;
use DateTimeImmutable;
use Psr\Http\Message\UploadedFileInterface;

class ExpenseService
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
    ) {}

    public function list(User $user, int $year, int $month, int $pageNumber, int $pageSize): array
    {
        $startDate=new DateTimeImmutable("$year-$month-01");
        $endDate=new DateTimeImmutable("$year-$month-31");
        return $this->expenses->findByUserIdDateRange(
            $user->id,
            $startDate,
            $endDate,
            $pageNumber,
            $pageSize
        );

    }

    public function listExpenditureYears(User $user): array {
        return $this->expenses->listExpenditureYears($user);
    }

    public function create(
        User $user,
        float $amount,
        string $description,
        DateTimeImmutable $date,
        string $category,
    ): void {
        $amountCents = (int)round($amount * 100);
        $expense = new Expense(null, $user->id, $date, $category, $amountCents, $description);
        $this->expenses->save($expense);
    }

    public function findById(int $id): Expense {
        return $this->expenses->find($id);
    }

    public function update(
        Expense $expense,
        float $amount,
        string $description,
        DateTimeImmutable $date,
        string $category,
    ): void {
        $expense->amountCents = (int)round($amount * 100);
        $expense->description = $description;
        $expense->date = $date;
        $expense->category = $category;
        $this->expenses->save($expense);
    }

    public function delete(int $id): void {
        $this->expenses->delete($id);
    }

    public function count(User $user, int $year, int $month): int {
        return $this->expenses->countBy([
            'user_id' => $user->id,
            'year' => $year,
            'month' => $month
        ]);
    }

    public function importFromCsv(User $user, UploadedFileInterface $csvFile): int
    {
        return 0; // number of imported rows
    }
}
