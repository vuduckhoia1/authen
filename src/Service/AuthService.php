<?php
namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class AuthService
{
    private JWTTokenManagerInterface $jwtManager;
    private CacheInterface $redis;
    private UserRepository $userRepository;
    private LoggerInterface $logger;

    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        CacheInterface $redis,
        UserRepository $userRepository,
        LoggerInterface $logger)
    {
        $this->jwtManager = $jwtManager;
        $this->redis = $redis;
        $this->userRepository = $userRepository;
        $this->logger = $logger;
    }

    public function createUser($params): ?User
    {
        try {
            $user = $this->userRepository->createUser($params);
            $this->logger->info("Insert db successfully", $params);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return null;
        }
        return $user;
    }

    public function login(array $params): string
    {

    }
}