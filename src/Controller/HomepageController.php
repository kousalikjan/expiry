<?php

namespace App\Controller;

use App\Service\ItemService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(Request $request, ItemService $itemService): Response
    {
        $showTutorial = true;
        $userId = $this->getUser()?->getId();
        if($userId !== null)
            $showTutorial = $itemService->getUserItemsCount($userId) === 0;

        $request->getSession()->set('itemsUrl', $request->getRequestUri());
        return $this->render('index.html.twig', [
            'showTutorial' => $showTutorial
        ]);
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