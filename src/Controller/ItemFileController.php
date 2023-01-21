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
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ItemFileController extends AbstractController
{

    private ItemFileRepository $itemFileRepository;

    public function __construct(ItemFileRepository $itemFileRepository)
    {
        $this->itemFileRepository = $itemFileRepository;
    }


    #[Route('users/{userId}/categories/{catId}/items/{itemId}/files', name: 'app_item_file_add', methods: ['POST'])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('category', options: ['id' => 'catId'])]
    #[Entity('item', options: ['id' => 'itemId'])]
    public function uploadItemFile(User $user, Category $category, Item $item, Request $request, UploaderHelper $uploaderHelper, ValidatorInterface $validator): Response
    {
        $this->denyAccessUnlessGranted('access', $item);

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('item-file');

        $violations = $validator->validate(
            $uploadedFile,
            [
                new NotBlank([
                'message' => 'Please select a file to upload!'
            ]),
                new File([
                'maxSize' => '5M',
                'mimeTypes' => [
                    'image/*',
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ],
                'mimeTypesMessage' => 'The mime type of the file is invalid!'
            ])]
        );

        if($violations->count() > 0) {
            /** @var ConstraintViolation $violation */
            $violation = $violations[0];
            $this->addFlash('error', $violation->getMessage());

            return $this->redirectToRoute('app_item_edit', [
                'userId' => $user->getId(),
                'catId' => $category->getId(),
                'itemId' => $item->getId()
            ]);
        }


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

    #[Route('users/{userId}/categories/{catId}/items/{itemId}/files/{fileId}/download', name: 'app_item_file_download', methods: ['GET'])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('category', options: ['id' => 'catId'])]
    #[Entity('item', options: ['id' => 'itemId'])]
    #[Entity('file', options: ['id' => 'fileId'])]
    public function downloadItemFile(User $user, Category $category, Item $item, ItemFile $file, UploaderHelper $uploaderHelper): Response
    {
        $this->denyAccessUnlessGranted('access', $item);

        $response = new StreamedResponse(function () use ($file, $uploaderHelper) {
            $outputStream = fopen('php://output', 'wb');
            $fileStream = $uploaderHelper->readStream($file->getItemFilePath());
            stream_copy_to_stream($fileStream, $outputStream);
        });

        $response->headers->set('Content-Type', $file->getMimeType());
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $file->getOriginalFilename(),
            "expiry-file"
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    #[Route('users/{userId}/categories/{catId}/items/{itemId}/files/{fileId}/delete', name: 'app_item_file_delete', methods: ['GET'])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('category', options: ['id' => 'catId'])]
    #[Entity('item', options: ['id' => 'itemId'])]
    #[Entity('file', options: ['id' => 'fileId'])]
    public function deleteItemFile(User $user, Category $category, Item $item, ItemFile $file, UploaderHelper $uploaderHelper): Response
    {
        $this->denyAccessUnlessGranted('access', $item);

        $this->itemFileRepository->remove($file, true);
        $uploaderHelper->deleteFile($file->getItemFilePath());

        return $this->redirectToRoute('app_item_edit', [
            'userId' => $user->getId(),
            'catId' => $category->getId(),
            'itemId' => $item->getId()
        ]);
    }


}