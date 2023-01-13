<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @method User getUser()
 */
class BaseController extends AbstractController
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    protected function checkOwner($id)
    {
        $user = $this->userRepository->find($id);
        if($user->getUserIdentifier() !== $this->getUser()->getUserIdentifier())
            throw $this->createAccessDeniedException();
    }

}