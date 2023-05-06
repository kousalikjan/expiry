<?php

namespace App\Service;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ManipulatorInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

// Inspired and partly taken from: https://symfonycasts.com/screencast/symfony-uploads/uploading-private-files#creating-a-private-filesystem
class UploaderHelper
{
    public const ITEM_FILE = 'item_file';
    public const THUMBNAIL = 'thumbnails';
    private const MAX_WIDTH = 200;
    private const MAX_HEIGHT = 200;

    private Filesystem $filesystem;

    private SluggerInterface $slugger;

    private Imagine $imagine;

    public function __construct(Filesystem $filesystem, SluggerInterface $slugger)
    {
        $this->filesystem = $filesystem;
        $this->slugger = $slugger;
        $this->imagine = new Imagine();
    }

    /**
     * @throws FilesystemException
     */
    public function readStream(string $path)
    {
        return $this->filesystem->readStream($path);
    }

    /**
     * Saves a file using the Flysystem bundle
     *
     * @param UploadedFile $file file to be saved
     * @param bool $createThumbnail whether a thumbnail should be created
     * @return string filepath of the saved item
     * @throws FilesystemException
     */
    public function uploadFile(UploadedFile $file, bool $createThumbnail): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        $stream = fopen($file->getPathname(), 'r');

        $this->filesystem->writeStream(
            self::ITEM_FILE . DIRECTORY_SEPARATOR . $newFilename,
            $stream
        );

        if (is_resource($stream)) {
            fclose($stream);
        }

        $stream = fopen($file->getPathname(), 'r');

        if($createThumbnail)
        {
            $photo = $this->imagine->read($stream);
            $photo->thumbnail(new Box(
                min($photo->getSize()->getWidth() / 10, self::MAX_WIDTH),
               min($photo->getSize()->getHeight() / 10, self::MAX_HEIGHT)),
                ManipulatorInterface::THUMBNAIL_INSET|ManipulatorInterface::THUMBNAIL_FLAG_NOCLONE);
            $stream = fopen('php://temp', 'r+');
            fwrite($stream, $photo->get('jpg'));
            rewind($stream);
            $this->filesystem->writeStream(
                self::ITEM_FILE.DIRECTORY_SEPARATOR.self::THUMBNAIL.DIRECTORY_SEPARATOR.$newFilename,
                $stream
            );
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        // returns filename stored on our filesystem
        return $newFilename;
    }

    /**
     * @throws FilesystemException
     */
    public function deleteFile(string $path)
    {
        $this->filesystem->delete($path);
    }

}