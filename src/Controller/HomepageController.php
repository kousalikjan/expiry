<?php

namespace App\Controller;

use App\Repository\ItemRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    private ItemRepository $itemRepository;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }


    #[Route('/{_locale}', name: 'app_index', requirements: ['_locale' => 'en|cs'], defaults: ['_locale' => 'en'])]
    public function homepage(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route('/email')]
    public function sendEmail(MailerInterface $mailer): Response
    {
        $email = (new TemplatedEmail())
            ->to(new Address('kakaovnikk@gmail.com'))
            ->subject('Thanks for signing up!')

            // path of the Twig template to render
            ->htmlTemplate('emails/expiration.html.twig')

            // pass variables (name => value) to the template
            ->context([
                'item' => $this->itemRepository->find(1),
            ])
        ;

        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface $e)
        {
            dd($e);
        }

        return $this->redirectToRoute('app_index');
    }
}