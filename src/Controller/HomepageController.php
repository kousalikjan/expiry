<?php

namespace App\Controller;

use App\Repository\ItemRepository;
use App\Repository\WarrantyRepository;
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
    #[Route('/', name: 'app_index')]
    public function index(ItemRepository $itemRepository): Response
    {
        $user = $this->getUser();
        return $this->render('index.html.twig', ['notificationsCount' =>
            $user ? sizeof($itemRepository->findNotifiedItems($user->getId())) : 0]);
    }

    #[Route('/set-locale/{_locale<%supported_locales%>}', name: 'app_set_locale')]
    public function setLocale(Request $request, string $_locale): Response
    {
        $request->getSession()->set('_locale', $_locale);
        $referer = $request->headers->get('referer');
        if(!$referer)
            return $this->redirectToRoute('app_index');
        return $this->redirect($referer);
    }

}