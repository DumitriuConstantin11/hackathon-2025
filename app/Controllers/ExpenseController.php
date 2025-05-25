<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Entity\User;
use App\Domain\Service\CBService;
use App\Domain\Service\ExpenseService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class ExpenseController extends BaseController
{
    private const PAGE_SIZE = 20;

    public function __construct(
        Twig $view,
        private readonly ExpenseService $expenseService,
        private readonly CBService $cbService,
    ) {
        parent::__construct($view);
    }

    public function index(Request $request, Response $response): Response
    {
        // TODO: implement this action method to display the expenses page

        // Hints:
        // - use the session to get the current user ID
        // - use the request query parameters to determine the page number and page size
        // - use the expense service to fetch expenses for the current user

        // parse request parameters

        $year = (int)($request->getQueryParams()['year'] ?? date("Y"));
        $month = (int)($request->getQueryParams()['month'] ?? date("m"));


        $userId = $_SESSION["user_id"]; // TODO: obtain logged-in user ID from session
        $user = new User($userId, $_SESSION["username"], "", new \DateTimeImmutable());
        $years=$this->expenseService->listExpenditureYears($user);
        $page = (int)($request->getQueryParams()['page'] ?? 1);
        $pageSize = (int)($request->getQueryParams()['pageSize'] ?? self::PAGE_SIZE);

        $expenses = $this->expenseService->list($user,$year, $month, $page, $pageSize);


        return $this->render($response, 'expenses/index.twig', [
            'expenses' => $expenses,
            'year' => $year,
            'month' => $month,
            'page' => $page,
            'pageSize' => $pageSize,
            'years' => $years,
        ]);
    }

    public function create(Request $request, Response $response): Response
    {
        $categories = $this->cbService->getCategory();
        $today= (new \DateTimeImmutable())->format('Y-m-d');
        // TODO: implement this action method to display the create expense page

        // Hints:
        // - obtain the list of available categories from configuration and pass to the view

        return $this->render($response, 'expenses/create.twig', ['categories' => $categories, 'date' => $today]);
    }

    public function store(Request $request, Response $response): Response
    {
        // TODO: implement this action method to create a new expense
        $data = $request->getParsedBody();
        $userId= $_SESSION["user_id"];
        $username= $_SESSION["username"];
        $dateInput=$data["date"];
        $date = new \DateTimeImmutable($dateInput);
        $now = new \DateTimeImmutable();


        $amount = floatval($data['amount']);
        $description = trim($data['description']);

        $category = $data['category'];

        $errors = [];
        if($amount<=0){
            $errors["amount"] = "Amount trebuie sa fie mai mare ca 0";
        }
        if(empty($description)){
           $errors["description"] = "Trebuie scrisa o descriere";
        }
        if(!$category){
            $errors["category"] = "Trebuie aleasa o categorie";
        }
        if($date>$now){
            $errors["date"] = "Datele nu pot fi in viitor";
        }
        if(!empty($errors)){
            $categories = $this->cbService->getCategory();
            return $this->render($response, 'expenses/create.twig', ['errors' => $errors, 'categories' => $categories, 'amount' => $amount, 'description' => $description, 'category' => $category, 'date' => $dateInput]);
        }

        $user= new User($userId, $username, "", new \DateTimeImmutable());

        $this->expenseService->create($user, $amount, $description, $date, $category);
        // Hints:
        // - use the session to get the current user ID
        // - use the expense service to create and persist the expense entity
        // - rerender the "expenses.create" page with included errors in case of failure
        // - redirect to the "expenses.index" page in case of success




        return $response->withHeader('Location', '/expenses')->withStatus(302);


    }

    public function edit(Request $request, Response $response, array $routeParams): Response
    {
        $expenseId= (int)$routeParams["id"];
        $userId= $_SESSION["user_id"];
        $expense = $this->expenseService->findById($expenseId);

        if($expense->userId !== $userId){
            $response->getBody()->write("Acces neautorizat pentru acest expense");
            return $response->withStatus(403);
        }
        $categories = $this->cbService->getCategory();
        return $this->render($response, 'expenses/edit.twig', ['expense' => $expense, 'categories' => $categories]);
        // TODO: implement this action method to display the edit expense page

        // Hints:
        // - obtain the list of available categories from configuration and pass to the view
        // - load the expense to be edited by its ID (use route params to get it)
        // - check that the logged-in user is the owner of the edited expense, and fail with 403 if not


    }

    public function update(Request $request, Response $response, array $routeParams): Response
    {
        $expenseId= (int)$routeParams["id"];
        $userId= $_SESSION["user_id"];
        $expense = $this->expenseService->findById($expenseId);
        if($expense->userId !== $userId){
            $response->getBody()->write("Acces neautorizat pentru acest expense");
            return $response->withStatus(403);
        }
        $data = $request->getParsedBody();
        $amount = floatval($data['amount']);
        $description = trim($data['description']);
        $category = trim($data['category']);
        $dateInput=$data["date"];
        $date = new \DateTimeImmutable($dateInput);
        $now = new \DateTimeImmutable();

        $errors = [];
        if($amount<=0){
            $errors["amount"] = "Amount trebuie sa fie mai mare ca 0";
        }
        if(empty($description)){
            $errors["description"] = "Trebuie scrisa o descriere";
        }
        if(!$category){
            $errors["category"] = "Trebuie aleasa o categorie";
        }
        if($date>$now){
            $errors["date"] = "Datele nu pot fi in viitor";
        }
        if(!empty($errors)){
            $categories = $this->cbService->getCategory();
            return $this->render($response, 'expenses/edit.twig', ['errors' => $errors, 'categories' => $categories, 'amount' => $amount, 'description' => $description, 'category' => $category, 'date' => $dateInput]);
        }
        $this->expenseService->update($expense, $amount, $description,$date, $category);
        return $response->withHeader('Location', '/expenses')->withStatus(302);
        // TODO: implement this action method to update an existing expense

        // Hints:
        // - load the expense to be edited by its ID (use route params to get it)
        // - check that the logged-in user is the owner of the edited expense, and fail with 403 if not
        // - get the new values from the request and prepare for update
        // - update the expense entity with the new values
        // - rerender the "expenses.edit" page with included errors in case of failure
        // - redirect to the "expenses.index" page in case of success


    }

    public function destroy(Request $request, Response $response, array $routeParams): Response
    {
        $expenseId= (int)$routeParams["id"];
        $userId= $_SESSION["user_id"];
        $expense = $this->expenseService->findById($expenseId);
        if($expense->userId !== $userId){
            $response->getBody()->write("Acces neautorizat pentru acest expense");
            return $response->withStatus(403);
        }
        $this->expenseService->delete($expenseId);
        return $response->withHeader('Location', '/expenses')->withStatus(302);
        // TODO: implement this action method to delete an existing expense

        // - load the expense to be edited by its ID (use route params to get it)
        // - check that the logged-in user is the owner of the edited expense, and fail with 403 if not
        // - call the repository method to delete the expense
        // - redirect to the "expenses.index" page

        return $response;
    }
}
