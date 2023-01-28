<?php

namespace App\Controller;

use App\Repository\ItemRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class HomepageController extends AbstractController
{
    #[Route('/{_locale<%supported_locales%>}/', name: 'app_index')]
    public function index(TranslatorInterface $translator, Request $request): Response
    {
        dump($request->getLocale());
        return $this->render('index.html.twig');
    }

    #[Route('/')]
    public function indexNoLocale()
    {
        return $this->redirectToRoute('app_index', ['_locale' => 'en']);
    }

}