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
            ->to('kakaovnikk@gmail.com')
            ->subject("Hello from code")
            ->text('Hello ' . $this->getUser()->getUserIdentifier() . ' this has been send from code!');

        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface $e)
        {
            dd($e);
        }

        return $this->redirectToRoute('app_index');
    }
}