<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private $targetDirectory;
    private SluggerInterface $slugger;

    public function __construct($targetDirectory, SluggerInterface $slugger)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
    }

    public function upload(UploadedFile $file, int $userId, int $itemId)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory($userId, $itemId), $fileName);
        } catch (FileException $e) {
            //TODO: Asi trochu jinak
            dd($e);
        }

        return $fileName;
    }

    public function getTargetDirectory(int $userId, int $itemId)
    {
        return $this->targetDirectory
            .DIRECTORY_SEPARATOR
            .$userId
            .DIRECTORY_SEPARATOR
            .$itemId;
    }

}