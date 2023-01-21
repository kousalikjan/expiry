<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/users/{id}', name: 'app_profile', requirements: ['id' => '\d+'])]
    #[IsGranted('access', 'user')]
    public function index(User $user): Response
    {
        return $this->render('profile/index.html.twig', ['user' => $user]);
    }
}