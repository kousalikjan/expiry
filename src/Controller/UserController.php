<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Form\DefaultSettingType;
use App\Form\SelectCategoryType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    #[Route('/users/{id}', name: 'app_profile', requirements: ['id' => '\d+'])]
    #[IsGranted('access', 'user')]
    public function index(User $user, Request $request): Response
    {
        $form = $this->createForm(DefaultSettingType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $this->userRepository->save($user, true);
            $this->addFlash('success', 'User successfully updated!');
        }
        return $this->render('profile/index.html.twig', ['user' => $user, 'form' => $form]);
    }
}