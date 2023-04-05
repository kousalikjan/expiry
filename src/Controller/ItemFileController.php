<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Item;
use App\Entity\ItemFile;
use App\Entity\User;
use App\Factory\ItemFileFactory;
use App\Repository\ItemFileRepository;
use App\Service\ItemFileService;
use App\Service\UploaderHelper;
use League\Flysystem\FilesystemException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ItemFileController extends AbstractController
{

    private ItemFileService $itemFileService;

    public function __construct(ItemFileService $itemFileService)
    {
        $this->itemFileService = $itemFileService;
    }

    #[Route('users/{userId}/categories/{catId}/items/{itemId}/files/{redirect}', name: 'app_item_file_edit_redirect', requirements: ['userId' => '\d+', 'catId' => '\d+', 'itemId' => '\d+'], defaults: ['redirect' => null], methods: ['GET'])]
    #[Route('users/{userId}/categories/{catId}/items/{itemId}/files', name: 'app_item_file_edit', requirements: ['userId' => '\d+', 'catId' => '\d+', 'itemId' => '\d+'], methods: ['GET'])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('category', options: ['id' => 'catId'])]
    #[Entity('item', options: ['id' => 'itemId'])]
    #[IsGranted('access', 'user')]
    #[IsGranted('access', 'category')]
    #[IsGranted('access', 'item')]
    public function list(User $user, Category $category, Item $item, ?bool $redirect): Response
    {
        return $this->render('file/list.html.twig', [
            'user' => $user,
            'category' => $category,
            'item' => $item,
            'redirect' => $redirect]);
    }

    #[Route('users/{userId}/categories/{catId}/items/{itemId}/files', name: 'app_item_file_add', requirements: ['userId' => '\d+', 'catId' => '\d+', 'itemId' => '\d+'], methods: ['POST'])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('category', options: ['id' => 'catId'])]
    #[Entity('item', options: ['id' => 'itemId'])]
    #[IsGranted('access', 'user')]
    #[IsGranted('access', 'category')]
    #[IsGranted('access', 'item')]
    public function uploadItemFile(User $user, Category $category, Item $item, Request $request, UploaderHelper $uploaderHelper, ValidatorInterface $validator): Response
    {
        // Inspired and partly taken from: https://symfonycasts.com/screencast/symfony-uploads/mime-type-validation#requiring-the-file
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('item-file');

        $violations = $validator->validate(
            $uploadedFile,
            [
                new NotBlank([
                'message' => 'Please select a file to upload!'
            ]),
                new File([
                'maxSize' => '15M',
                'mimeTypes' => [
                    'image/*',
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ],
                'mimeTypesMessage' => 'Invalid mime type!',
            ])]
        );

        if($violations->count() > 0)
        {
            /** @var ConstraintViolation $violation */
            $violation = $violations[0];
            $this->addFlash('file_error', $violation->getMessage());

            return $this->redirectToRoute('app_item_file_edit_redirect', [
                'userId' => $user->getId(),
                'catId' => $category->getId(),
                'itemId' => $item->getId(),
                'redirect'=> $request->query->get('redirect'),
            ]);
        }

        // store file and get its filename
        try
        {
            $filename = $uploaderHelper->uploadFile($uploadedFile, preg_match('/image\/*/', $uploadedFile->getMimeType()));
        }
        catch (FilesystemException $e)
        {
            $this->addFlash('file_error', 'Unexpected error has occurred while saving the file!');
            return $this->redirectToRoute('app_item_file_edit_redirect', [
                'userId' => $user->getId(),
                'catId' => $category->getId(),
                'itemId' => $item->getId(),
                'redirect'=> $request->query->get('redirect'),
            ]);
        }

        $itemFile = ItemFileFactory::createItemFile($item, $filename, $uploadedFile);
        $this->itemFileService->save($itemFile, true);
        $this->addFlash('file_success', 'File successfully uploaded!');

        return $this->redirectToRoute('app_item_file_edit_redirect', [
            'userId' => $user->getId(),
            'catId' => $category->getId(),
            'itemId' => $item->getId(),
            'redirect'=> $request->query->get('redirect'),
        ]);
    }

    #[Route('users/{userId}/categories/{catId}/items/{itemId}/files/{fileId}/delete', name: 'app_item_file_delete', requirements: ['userId' => '\d+', 'catId' => '\d+', 'itemId' => '\d+', 'fileId' => '\d+'],  methods: ['GET'])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('category', options: ['id' => 'catId'])]
    #[Entity('item', options: ['id' => 'itemId'])]
    #[Entity('file', options: ['id' => 'fileId'])]
    #[IsGranted('access', 'user')]
    #[IsGranted('access', 'category')]
    #[IsGranted('access', 'item')]
    #[IsGranted('access', 'file')]
    public function deleteItemFile(User $user, Category $category, Item $item, ItemFile $file, UploaderHelper $uploaderHelper, Request $request): Response
    {
        $this->itemFileService->remove($file, true);
        try
        {
            $uploaderHelper->deleteFile($file->getItemFilePath());
            if($file->isThumbnail())
                $uploaderHelper->deleteFile($file->getItemFileThumbnailPath());
        }
        catch (FilesystemException $e)
        {
            $this->addFlash('file_error', 'Unexpected error has occurred while deleting the file!');
        }

        return $this->redirectToRoute('app_item_file_edit_redirect', [
            'userId' => $user->getId(),
            'catId' => $category->getId(),
            'itemId' => $item->getId(),
            'redirect'=> $request->query->get('redirect'),
        ]);
    }

    #[Route('users/{userId}/categories/{catId}/items/{itemId}/files/{fileId}/download', name: 'app_item_file_download', requirements: ['userId' => '\d+', 'catId' => '\d+', 'itemId' => '\d+', 'fileId' => '\d+'], methods: ['GET'])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('category', options: ['id' => 'catId'])]
    #[Entity('item', options: ['id' => 'itemId'])]
    #[Entity('file', options: ['id' => 'fileId'])]
    #[IsGranted('access', 'user')]
    #[IsGranted('access', 'category')]
    #[IsGranted('access', 'item')]
    #[IsGranted('access', 'file')]
    public function downloadItemFile(User $user, Category $category, Item $item, ItemFile $file, UploaderHelper $uploaderHelper, Request $request): Response
    {
        // Inspired and partly taken from: https://symfonycasts.com/screencast/symfony-uploads/file-streaming#content-disposition-forcing-download
        $response = new StreamedResponse(function () use ($file, $uploaderHelper, $request) {
            $outputStream = fopen('php://output', 'wb');

            if($request->query->get('thumbnail'))
                $fileStream = $uploaderHelper->readStream($file->getItemFileThumbnailPath());
            else
                $fileStream = $uploaderHelper->readStream($file->getItemFilePath());
            stream_copy_to_stream($fileStream, $outputStream);
        });

        $response->headers->set('Content-Type', $file->getMimeType());

        if($request->query->get('save'))
        {
            $disposition = HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                $file->getOriginalFilename(),
                "expiry-file"
            );
            $response->headers->set('Content-Disposition', $disposition);
        }

        return $response;
    }

    #[Route('users/{userId}/categories/{catId}/items/{itemId}/files/{fileId}/thumbnail', name: 'app_item_thumbnail', requirements: ['userId' => '\d+', 'catId' => '\d+', 'itemId' => '\d+', 'fileId' => '-?\d+'])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('category', options: ['id' => 'catId'])]
    #[Entity('item', options: ['id' => 'itemId'])]
    #[IsGranted('access', 'user')]
    #[IsGranted('access', 'category')]
    #[IsGranted('access', 'item')]
    public function getItemThumbnail(User $user, Category $category, Item $item, int $fileId, UploaderHelper $uploaderHelper): Response
    {
        if($fileId === -1)
            return $this->file('img/no-image.png');

        $file = $this->itemFileService->find($fileId);
        $this->denyAccessUnlessGranted('access', $file);

        $response = new StreamedResponse(function () use ($uploaderHelper, $file)
        {
            $outputStream = fopen('php://output', 'wb');
            $fileStream = $uploaderHelper->readStream($file->getItemFileThumbnailPath());
            stream_copy_to_stream($fileStream, $outputStream);
        });
        $response->headers->set('Content-Type', $file->getMimeType());
        return $response;
    }
}