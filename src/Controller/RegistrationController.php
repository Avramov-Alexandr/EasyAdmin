<?php
namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $username = $request->request->get('username');
            $password = $request->request->get('password');

            if (!$username || !$password) {
                $this->addFlash('error', 'Имя пользователя и пароль обязательны.');
                return $this->redirectToRoute('app_register');
            }

            try {
                // Регистрируем пользователя с ролью ROLE_USER
                $this->userService->createUser($username, $password, ['ROLE_USER']);
                $this->addFlash('success', 'Вы успешно зарегистрировались. Теперь вы можете войти.');

                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Ошибка регистрации: ' . $e->getMessage());
                return $this->redirectToRoute('app_register');
            }
        }

        return $this->render('registration/register.html.twig');
    }
}
