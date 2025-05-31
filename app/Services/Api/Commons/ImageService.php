<?php

namespace App\Services\Api\Commons;

use App\Enums\StorageFolder;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class ImageService
{
    public function __construct()
    {
    }

    public function upload($file, $path, $name)
    {
        return $file->storeAs($path, $name, 'new_s3');
    }

    /**
     * @throws \Exception
     */
    public function delete($fileName)
    {
        try {
            // Si usas AWS S3
            Storage::disk('s3')->delete($fileName);

            // O si usas otro storage
            // Storage::delete($fileName);

            return true;
        } catch (\Exception $e) {
            Log::error('Error eliminando archivo de S3: ' . $e->getMessage());
            throw $e;
        }
    }


    public function getUrl($file)
    {
    }

    public function getBase64($file)
    {
    }

    public function setName($file, $type, $userId)
    {
        $user = User::find($userId);
        $folderName = '/' . $user->id;
        $storageEnum = StorageFolder::fromType($type);
        if ($storageEnum === null) {
            throw new \InvalidArgumentException("Tipo de archivo no soportado: $type");
        }
        return $storageEnum->path() . $folderName;
    }
}
