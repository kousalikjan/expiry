<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Item;
use App\Entity\ItemFile;
use App\Entity\User;
use App\Repository\ItemFileRepository;
use App\Service\UploaderHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ItemFileController extends AbstractController
{

    private ItemFileRepository $itemFileRepository;

    public function __construct(ItemFileRepository $itemFileRepository)
    {
        $this->itemFileRepository = $itemFileRepository;
    }


    #[Route('users/{userId}/categories/{catId}/items/{itemId}/files', name: 'app_item_add_file', methods: ['POST'])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('category', options: ['id' => 'catId'])]
    #[Entity('item', options: ['id' => 'itemId'])]
    public function uploadItemFile(User $user, Category $category, Item $item, Request $request, UploaderHelper $uploaderHelper): Response
    {
        $this->denyAccessUnlessGranted('access', $item);

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('item-file');

        // store file and get its filename
        $filename = $uploaderHelper->uploadFile($uploadedFile);

        $itemFile = new ItemFile($item);
        $itemFile->setFilename($filename);
        $itemFile->setOriginalFilename($uploadedFile->getClientOriginalName() ?? $filename);
        $itemFile->setMimeType($uploadedFile->getMimeType() ?? 'application/octet-stream');

        $this->itemFileRepository->save($itemFile, true);

        return $this->redirectToRoute('app_item_edit', [
            'userId' => $user->getId(),
            'catId' => $category->getId(),
            'itemId' => $item->getId()
        ]);
    }
}