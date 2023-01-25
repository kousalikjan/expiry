<?php

namespace App\Controller;

use App\Repository\ItemRepository;
use App\Repository\WarrantyRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    private WarrantyRepository $warrantyRepository;

    /**
     * @param WarrantyRepository $warrantyRepository
     */
    public function __construct(WarrantyRepository $warrantyRepository)
    {
        $this->warrantyRepository = $warrantyRepository;
    }


    #[Route('/', name: 'app_index')]
    public function homepage(): Response
    {
        return $this->render('emails/expiration.html.twig', ['warranties' => $this->warrantyRepository->findAll(), 'useremail' => 'kakaovnikk@gmail.com']);
    }
}