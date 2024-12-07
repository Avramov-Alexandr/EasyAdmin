<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private UserService $userService;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, UserService $userService)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->userService = $userService;
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $users = $this->userRepository->findAll();

        return $this->render('admin/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function createUser(Request $request): Response
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $roles = $request->request->all('roles');

        if (!$username || !$password) {
            $this->addFlash('error', 'Имя пользователя и пароль обязательны.');
            return $this->redirectToRoute('admin_index');
        }

        $this->userService->createUser($username, $password, $roles);

        $this->addFlash('success', 'Пользователь успешно создан.');
        return $this->redirectToRoute('admin_index');
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['POST'])]
    public function deleteUser(int $id): Response
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'Пользователь не найден.');
            return $this->redirectToRoute('admin_index');
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->addFlash('success', 'Пользователь успешно удалён.');
        return $this->redirectToRoute('admin_index');
    }
}
