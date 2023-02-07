<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Item;
use App\Entity\User;
use App\Entity\Warranty;
use App\Form\DefaultSettingType;
use App\Form\SelectCategoryType;
use App\Repository\UserRepository;
use App\Repository\WarrantyRepository;
use App\Service\ItemService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    private UserRepository $userRepository;
    private ItemService $itemService;
    private WarrantyRepository $warrantyRepository;

    public function __construct(UserRepository $userRepository, ItemService $itemService, WarrantyRepository $warrantyRepository)
    {
        $this->userRepository = $userRepository;
        $this->itemService = $itemService;
        $this->warrantyRepository = $warrantyRepository;
    }


    #[Route('/users/{id}', name: 'app_profile', requirements: ['id' => '\d+'])]
    #[IsGranted('access', 'user')]
    public function index(User $user, Request $request): Response
    {
        $form = $this->createForm(DefaultSettingType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $this->userRepository->save($user, true);
            $this->addFlash('success', 'User successfully updated!');
            return $this->redirectToRoute('app_set_locale', ['_locale' => $user->getPreferredLocale()]);
        }
        return $this->render('profile/index.html.twig', ['user' => $user, 'form' => $form
        ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }

   #[Route('/users/{id}/notifications', name: 'app_notifications', requirements: ['id' => '\d+'])]
   #[IsGranted('access', 'user')]
   public function showNotifications(User $user): Response
   {
        return $this->render('notification/index.html.twig', [
            'notClearedItems' => $this->itemService->findToBeNotifiedInAppOneUserByCleared($user->getId(), false),
            'clearedItems' => $this->itemService->findToBeNotifiedInAppOneUserByCleared($user->getId(), true)
        ]);
   }

    #[Route('/users/{userId}/notifications/{itemId}/clear', name: 'app_notification_clear', requirements: ['userId' => '\d+', 'itemId' => '\d+'])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('item', options: ['id' => 'itemId'])]
   public function clearNotificationItem(User $user, Item $item): Response
   {
        $this->itemService->clearNotification($item);
        return $this->redirectToRoute('app_notifications', ['id' => $user->getId()]);
   }



   #[Route('/users/{id}/notifications/clear', name: 'app_notifications_clear', requirements: ['id' => '\d+'])]
   #[IsGranted('access', 'user')]
   public function clearNotifications(User $user): Response
   {
       $items = $this->itemService->findToBeNotifiedInAppOneUserByCleared($user->getId(), false);
       /** @var Item $item */
       foreach ($items as $item)
       {
           $item->getWarranty()->setNotificationCleared(true);
           $this->warrantyRepository->save($item->getWarranty(), true);
       }
       return $this->redirectToRoute('app_notifications', ['id' => $user->getId()]);
   }



}