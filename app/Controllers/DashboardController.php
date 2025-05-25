<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Entity\User;
use App\Domain\Service\AlertGenerator;
use App\Domain\Service\ExpenseService;
use App\Domain\Service\MonthlyService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class DashboardController extends BaseController
{
    public function __construct(
        Twig $view,
        private readonly AlertGenerator $alertGenerator,
        private readonly MonthlyService $monthlyService,
    )
    {
        parent::__construct($view);
    }

    public function index(Request $request, Response $response): Response
    {
        $userId= $_SESSION['user_id'];
        $username = $_SESSION['username'];
        $user= new User($userId, $username, "", new \DateTimeImmutable());
        $queryParams = $request->getQueryParams();
        $year= isset($queryParams['year']) ? (int)$queryParams['year'] : (int)date("Y");
        $month= isset($queryParams['month']) ? (int)$queryParams['month'] : (int)date("m");
        $totalMonth= $this->monthlyService->computeTotalExpenditure($user, $year, $month);
        $totalCategory= $this->monthlyService->computePerCategoryTotals($user, $year, $month);
        $averageCategory= $this->monthlyService->computePerCategoryAverages($user, $year, $month);
        $maxTotal= !empty($totalCategory) ? max($totalCategory) : 1;
        $maxAverage= !empty($averageCategory) ? max($averageCategory) : 1;
        $alert= $this->alertGenerator->generate($user, $year, $month);
        $years= $this->monthlyService->listYears($user);
        return $this->render($response, 'dashboard.twig', [
            'username'=>$username,
            'year'=>$year,
            'month'=>$month,
            'totalMonth'=>$totalMonth,
            'totalCategory'=>$totalCategory,
            'averageCategory'=>$averageCategory,
            'maxTotal'=>$maxTotal,
            'maxAverage'=>$maxAverage,
            'alert'=>$alert,
            'years'=>$years,
        ]);
    }
}
