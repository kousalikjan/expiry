<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{


    public function __construct(private readonly UserRepository $userRepository)
    {

    }

    #[Route('/profile/{id}', name: 'app_profile', requirements: ['id' => '\d+'])]
    public function index(int $id): Response
    {
        $user = $this->userRepository->find($id);
        if($user->getUserIdentifier() !== $this->getUser()->getUserIdentifier())
            throw $this->createAccessDeniedException();
        return $this->render('profile/index.html.twig', ['user' => $user]);
    }

}