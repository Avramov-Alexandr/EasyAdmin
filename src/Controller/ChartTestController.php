<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChartTestController extends AbstractController
{
    #[Route('/chart-test', name: 'chart_test')]
    public function index(): Response
    {
        return $this->render('chart_test/index.html.twig');
    }
}

