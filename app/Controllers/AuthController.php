<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Service\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

class AuthController extends BaseController
{
    public function __construct(
        Twig $view,
        private AuthService $authService,
        private LoggerInterface $logger,
    ) {
        parent::__construct($view);
    }

    public function showRegister(Request $request, Response $response): Response
    {
        $this->logger->info('Register page requested');

        return $this->render($response, 'auth/register.twig');
    }

    public function register(Request $request, Response $response): Response
    {

        $data = $request->getParsedBody();
        $username= trim($data['username']);
        $password= trim($data['password']);
        $passwordConfirm= trim($data['password_confirm']);
        $isErrors= "";
        if($password !== $passwordConfirm){
            $isErrors .= "Parolele nu sunt la fel";
            return $this->render($response, 'auth/register.twig', ['errors' => $isErrors]);
        }
        try {
            $this->authService->register($username, $password);
            return $response->withHeader('Location', '/login')->withStatus(302);
        } catch (\InvalidArgumentException $exc) {
            $isErrors = $exc->getMessage();
            return $this->render($response, 'auth/register.twig', ["errors" => $isErrors]);
        }
    }

    public function showLogin(Request $request, Response $response): Response
    {
        return $this->render($response, 'auth/login.twig');
    }

    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $username= trim($data['username']);
        $password= $data['password'];

        $isErrors= "";

        if(!$this->authService->attempt($username, $password)){
            $isErrors="Username sau parola invalide";
            return $this->render($response, 'auth/login.twig', ["errors" => $isErrors]);
        }

        return $response->withHeader('Location', '/')->withStatus(302);
    }

    public function logout(Request $request, Response $response): Response
    {
        $_SESSION=[];
        session_destroy();
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}
