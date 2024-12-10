<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StimulusTestController extends AbstractController
{
    #[Route('/stimulus-test', name: 'stimulus_test')]
    public function index(): Response
    {
        return $this->render('stimulus_test.html.twig');
    }
}
