<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/users/{id}', name: 'app_profile', requirements: ['id' => '\d+'])]
    public function index(User $user): Response
    {
        $this->denyAccessUnlessGranted('access', $user);
        return $this->render('profile/index.html.twig', ['user' => $user]);
    }
}