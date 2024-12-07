<?php
namespace App\Controller;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[Route('/create-user', name: 'create_user', methods: ['GET', 'POST'])]
    public function createUser(Request $request): Response
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $roles = $request->request->all('roles');

        if (!$username || !$password) {
            return $this->json(['error' => 'Имя пользователя и пароль обязательны.'], 400);
        }

        $this->userService->createUser($username, $password, $roles);

        return $this->json(['message' => "Пользователь '$username' успешно создан."]);
    }
}
