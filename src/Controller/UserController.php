<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\User;
use App\Form\DefaultSettingType;
use App\Service\ItemService;
use App\Service\UserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    private UserService $userService;
    private ItemService $itemService;

    public function __construct(UserService $userService, ItemService $itemService)
    {
        $this->userService = $userService;
        $this->itemService = $itemService;
    }

    #[Route('/users/{id}', name: 'app_profile', requirements: ['id' => '\d+'])]
    #[IsGranted('access', 'user')]
    public function showProfile(User $user, Request $request): Response
    {
        $form = $this->createForm(DefaultSettingType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $this->userService->save($user, true);
            $this->addFlash('success', 'User successfully updated!');
            return $this->redirectToRoute('app_set_locale', ['_locale' => $user->getPreferredLocale()]);
        }
        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'form' => $form
        ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }

   #[Route('/users/{id}/notifications', name: 'app_notifications', requirements: ['id' => '\d+'])]
   #[IsGranted('access', 'user')]
   public function showNotifications(User $user, Request $request): Response
   {
       $request->getSession()->set('itemsUrl', $request->getRequestUri());

        return $this->render('notification/index.html.twig', [
            'notClearedItems' => $this->itemService->findToBeNotifiedInAppOneUserByCleared($user->getId(), false),
            'clearedItems' => $this->itemService->findToBeNotifiedInAppOneUserByCleared($user->getId(), true)
        ]);
   }

    #[Route('/users/{userId}/notifications/{itemId}/clear', name: 'app_notification_clear', requirements: ['userId' => '\d+', 'itemId' => '\d+'])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('item', options: ['id' => 'itemId'])]
    #[IsGranted('access', 'user')]
    #[IsGranted('access', 'item')]
   public function clearNotificationItem(User $user, Item $item): Response
   {
        $this->itemService->clearNotification($item);
        return $this->redirectToRoute('app_notifications', [
            'id' => $user->getId()
        ]);
   }

}