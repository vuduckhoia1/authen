<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends AbstractController
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/regist-account", name="create_user", methods={"POST"})
     */
    public function registAccount(Request $request, LoggerInterface $logger): JsonResponse
    {
        try {
            $params = json_decode($request->getContent(), true);
            if ($user = $this->userRepository->createUser($params)){
                $logger->info('Create User record:', $params);
            }
        } catch (Exception $e) {
            $logger->error($e->getMessage());
            return new Response('Error: ' . $e->getMessage(), 500);
        }
        return $this->json([
            'message' => 'Create Record successful',
            'user' => $user,
        ]);
    }

    
}
