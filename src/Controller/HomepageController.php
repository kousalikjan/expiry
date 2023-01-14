<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function homepage(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route('/email')]
    public function sendEmail(MailerInterface $mailer): Response
    {

        $email = (new Email())
            ->from('code@expiry.live')
            ->to('kakaovnikk@gmail.com')
            ->subject("Funguje to!")
            ->text('Sending emails is fun again!');

        $mailer->send($email);

        return $this->redirectToRoute('app_index');
    }
}