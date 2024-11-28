<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class AuthController extends AbstractController
{
    private $userRepository, $jwtManager;

    public function __construct(UserRepository $userRepository, JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
        $this->userRepository = $userRepository;
    }

    #[Route('/regist-account', name: 'create_user', methods: ['POST'])]
    public function registAccount(Request $request, LoggerInterface $logger): JsonResponse
    {
        try {
            $params = $request->request->all();
            if ($user = $this->userRepository->createUser($params)){
                $logger->info('Create User record:', $params);
            }
        } catch (Exception $e) {
            $logger->error($e->getMessage());
            return $this->json('Error: ' . $e->getMessage());
        }
        return $this->json([
            'message' => 'Create Record successful',
            'user' => $params,
        ]);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request, LoggerInterface $logger): JsonResponse
    {
        try {
            $params = $request->request->all();
            $user = $this->userRepository->findUserByEmail($params['email']);

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
}
