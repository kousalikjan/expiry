<?php

namespace App\Factory;

use App\Entity\Item;
use App\Entity\ItemFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ItemFileFactory
{
    public static function createItemFile(Item $item, string $filename, UploadedFile $uploadedFile): ItemFile
    {
        $itemFile = new ItemFile($item);
        $itemFile->setFilename($filename);
        $itemFile->setThumbnail(preg_match('/image\/*/', $uploadedFile->getMimeType()));
        $itemFile->setOriginalFilename($uploadedFile->getClientOriginalName() ?? $filename);
        $itemFile->setMimeType($uploadedFile->getMimeType() ?? 'application/octet-stream');
        return $itemFile;
    }
}