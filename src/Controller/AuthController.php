<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\AuthService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class AuthController extends AbstractController
{
    private $authService, $jwtManager;

    public function __construct(AuthService $authService, JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
        $this->authService = $authService;
    }

    #[Route('/regist-account', name: 'create_user', methods: ['POST'])]
    public function registAccount(Request $request, LoggerInterface $logger): JsonResponse
    {
        $params = $request->request->all();
        if(!$user = $this->authService->createUser($params)) {
            return $this->json([
                'message' => 'Regist failure'
            ]);
        }

        return $this->json([
            'message' => 'Create Record successful',
        ]);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request, LoggerInterface $logger): JsonResponse
    {
        try {
            $params = $request->request->all();
            $login = $this->authService->login($params);
            // $user = $this->authService->findUserByEmail($params['email']);

            if (!$user) {
                $logger->info('User not exist!', [$params['email']]);
                return $this->json('Account not exist!');
            }
            if (password_verify($params['password'], $user->getPassword())) {
                $msg = 'Login success!';
                $token = $this->jwtManager->create($user);
                $logger->info($msg, [$params['email']]);

            } else {
                $msg = 'Password invalid!';
                $logger->info($msg, [$params['email']]);
            }
            return $this->json($msg);
        } catch (Exception $e) {
            $logger->error($e->getMessage());
            return $this->json('Error: ' . $e->getMessage());
        }
    }

    private function getCacheKey($token): string
    {
        return sprintf('auth_user_%d', $token);
    }
}
