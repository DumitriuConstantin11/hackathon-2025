<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Expense;
use App\Domain\Entity\User;
use App\Domain\Repository\ExpenseRepositoryInterface;
use DateTimeImmutable;
use Exception;
use PDO;

class PdoExpenseRepository implements ExpenseRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
    ) {}

    /**
     * @throws Exception
     */
    public function find(int $id): ?Expense
    {
        $query = 'SELECT * FROM expenses WHERE id = :id';
        $statement = $this->pdo->prepare($query);
        $statement->execute(['id' => $id]);
        $data = $statement->fetch();
        if (false === $data) {
            return null;
        }

        return $this->createExpenseFromData($data);
    }

    public function save(Expense $expense): void
    {
        if($expense->id === null) {
            $query = "INSERT INTO expenses (user_id, date, category, amount_cents, description) VALUES (?,?,?,?,?)";
            $params= [
                $expense->userId,
                $expense->date->format('Y-m-d'),
                $expense->category,
                $expense->amountCents,
                $expense->description,
            ];
        } else {
            $query = "UPDATE expenses SET date = ?, category = ?, amount_cents = ?, description = ? WHERE id = ? AND user_id = ?";
            $params = [
                $expense->date->format('Y-m-d'),
                $expense->category,
                $expense->amountCents,
                $expense->description,
                $expense->id,
                $expense->userId
            ];
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM expenses WHERE id=?');
        $statement->execute([$id]);
    }

    public function findBy(array $criteria, int $from, int $limit): array
    {
        return [];
    }


    public function countBy(array $criteria): int
    {
        $year=(int)$criteria['year'];
        $month=(int)$criteria['month'];
        $start= new \DateTimeImmutable($year . '-' . str_pad((string)$month, 2, '0', STR_PAD_LEFT) . '-01');
        $primZi= $start->modify('+1 month');
        $end= $primZi->modify('-1 day');
        $query= "SELECT COUNT(*) FROM expenses WHERE user_id = ? and date BETWEEN ? AND ?";
        $params=[
            $criteria['user_id'],
            $start->format('Y-m-d'),
            $end->format('Y-m-d'),
        ];
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function listExpenditureYears(User $user): array
    {
        $query = "SELECT date FROM expenses WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['user_id' => $user->id]);
        $years=[];
        foreach ($stmt->fetchAll() as $row) {
            $date = new DateTimeImmutable($row['date']);
            $years[] = $date->format('Y');
        }
        $years = array_unique($years);

        return $years;
    }

    public function sumAmountsByCategory(array $criteria): array
    {
        $year= (int)$criteria['year'];
        $month= (int)$criteria['month'];
        $start= new \DateTimeImmutable($year . '-' . str_pad((string)$month, 2, '0', STR_PAD_LEFT) . '-01');
        $primZi= $start->modify('+1 month');
        $end= $primZi->modify('-1 day');
        $query= "SELECT category, SUM(amount_cents) as total FROM expenses WHERE user_id = ? AND date BETWEEN ? AND ? GROUP BY category";
        $params=[
            $criteria['user_id'],
            $start->format('Y-m-d'),
            $end->format('Y-m-d')
        ];
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function averageAmountsByCategory(array $criteria): array
    {
        $year= (int)$criteria['year'];
        $month= (int)$criteria['month'];
        $start= new \DateTimeImmutable($year . '-' . str_pad((string)$month, 2, '0', STR_PAD_LEFT) . '-01');
        $primZi= $start->modify('+1 month');
        $end= $primZi->modify('-1 day');
        $query= "SELECT category, AVG(amount_cents) as avg FROM expenses WHERE user_id = ? AND date BETWEEN ? AND ? GROUP BY category";
        $params=[
            $criteria['user_id'],
            $start->format('Y-m-d'),
            $end->format('Y-m-d')
        ];
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function sumAmounts(array $criteria): float
    {
        $year= (int)$criteria['year'];
        $month= (int)$criteria['month'];
        $start= new \DateTimeImmutable($year . '-' . str_pad((string)$month, 2, '0', STR_PAD_LEFT) . '-01');
        $primZi= $start->modify('+1 month');
        $end= $primZi->modify('-1 day');
        $query= "SELECT SUM(amount_cents) as total FROM expenses WHERE user_id = ? And date BETWEEN ? AND ?";
        $params=[
            $criteria['user_id'],
            $start->format('Y-m-d'),
            $end->format('Y-m-d')
        ];
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return (float)$stmt->fetchColumn();
    }

    /**
     * @throws Exception
     */
    private function createExpenseFromData(mixed $data): Expense
    {
        return new Expense(
            $data['id'],
            $data['user_id'],
            new DateTimeImmutable($data['date']),
            $data['category'],
            $data['amount_cents'],
            $data['description'],
        );
    }

    public function findByUserIdDateRange(int $userId, DateTimeImmutable $startDate, DateTimeImmutable $endDate, int $page, int $pageSize): array {
        $offset=($page-1)*$pageSize;
        $query = "SELECT * from expenses WHERE user_id = :user_id AND date BETWEEN :start_date AND :end_date
                   ORDER BY date DESC
                   LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($query);
        $params=[
            ':user_id' => $userId,
            ':start_date' => $startDate->format('Y-m-d'),
            ':end_date' => $endDate->format('Y-m-d'),
            ':limit' => $pageSize,
            ':offset' => $offset,
        ];
        $stmt->execute($params);
        $results = $stmt->fetchAll();
        return array_map([$this, 'createExpenseFromData'], $results);
    }
}
