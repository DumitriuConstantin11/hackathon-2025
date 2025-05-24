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
        // TODO: implement this and call from controller to obtain paginated list of expenses
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
        // TODO: implement this to create a new expense entity, perform validation, and persist
        $amountCents = (int)round($amount * 100);
        // TODO: here is a code sample to start with
        $expense = new Expense(null, $user->id, $date, $category, $amountCents, $description);
        $this->expenses->save($expense);
    }

    public function update(
        Expense $expense,
        float $amount,
        string $description,
        DateTimeImmutable $date,
        string $category,
    ): void {
        // TODO: implement this to update expense entity, perform validation, and persist
    }

    public function importFromCsv(User $user, UploadedFileInterface $csvFile): int
    {
        // TODO: process rows in file stream, create and persist entities
        // TODO: for extra points wrap the whole import in a transaction and rollback only in case writing to DB fails

        return 0; // number of imported rows
    }
}
