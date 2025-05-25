<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Entity\User;
use App\Domain\Service\AlertGenerator;
use App\Domain\Service\ExpenseService;
use App\Domain\Service\MonthlySummaryService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class DashboardController extends BaseController
{
    public function __construct(
        Twig $view,
        // TODO: add necessary services here and have them injected by the DI container
        private readonly ExpenseService $expenseService,
        private readonly AlertGenerator $alertGenerator,
        private readonly MonthlySummaryService $monthlySummaryService,
    )
    {
        parent::__construct($view);
    }

    public function index(Request $request, Response $response): Response
    {
        // TODO: parse the request parameters
        // TODO: load the currently logged-in user
        // TODO: get the list of available years for the year-month selector
        // TODO: call service to generate the overspending alerts for current month
        // TODO: call service to compute total expenditure per selected year/month
        // TODO: call service to compute category totals per selected year/month
        // TODO: call service to compute category averages per selected year/month
        $userId= $_SESSION['user_id'];
        $username = $_SESSION['username'];
        $user= new User($userId, $username, "", new \DateTimeImmutable());
        $queryParams = $request->getQueryParams();
        $year= isset($queryParams['year']) ? (int)$queryParams['year'] : (int)date("Y");
        $month= isset($queryParams['month']) ? (int)$queryParams['month'] : (int)date("m");
        $totalMonth= $this->monthlySummaryService->computeTotalExpenditure($user, $year, $month);
        $totalCategory= $this->monthlySummaryService->computePerCategoryTotals($user, $year, $month);
        $averageCategory= $this->monthlySummaryService->computePerCategoryAverages($user, $year, $month);
        $maxTotal= !empty($totalCategory) ? max($totalCategory) : 1;
        $maxAverage= !empty($averageCategory) ? max($averageCategory) : 1;
        $alert= $this->alertGenerator->generate($user, $year, $month);
        $years= $this->monthlySummaryService->listYears($user);
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
