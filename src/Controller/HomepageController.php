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
    #[Route('/{_locale}', name: 'app_index', requirements: ['_locale' => 'en|cs'], defaults: ['_locale' => 'en'])]
    public function homepage(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route('/email')]
    public function sendEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->to('cabaktom@fit.cvut.cz')
            ->subject("Ahoj Tomáší")
            ->text('Zašel bych na boulder:(');

        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface $e)
        {
            dd($e);
        }

        return $this->redirectToRoute('app_index');
    }
}