<?php

namespace App\Service;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploaderHelper
{
    const ITEM_FILE = 'item_file';

    private Filesystem $filesystem;

    private SluggerInterface $slugger;

    public function __construct(Filesystem $filesystem, SluggerInterface $slugger)
    {
        $this->filesystem = $filesystem;
        $this->slugger = $slugger;
    }

    public function uploadFile(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        $stream = fopen($file->getPathname(), 'r');

        try {
            $this->filesystem->writeStream(
                self::ITEM_FILE . '/' . $newFilename,
                $stream
            );
        } catch (FilesystemException $e)
        {
            throw new \Exception("Could not write uploaded file %s", $newFilename);
        }

        if (is_resource($stream)) {
            fclose($stream);
        }

        // returns filename stored on our filesystem
        return $newFilename;
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