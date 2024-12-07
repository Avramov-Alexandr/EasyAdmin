<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestTwigController extends AbstractController
{
    #[Route('/test-email-crud', name: 'test_email_crud')]
    public function testEmailCrud(): Response
    {
        return $this->render('admin/test2_crud.html.twig', [
            'test_variable' => 'Hello from the controller!',
        ]);
    }
}
