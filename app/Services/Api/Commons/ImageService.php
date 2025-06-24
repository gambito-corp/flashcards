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

    // Actualizar el método delete en tu ImageService.php

    /**
     * @throws \Exception
     */
    public function delete($fileName)
    {
        try {
            if (empty($fileName)) {
                Log::warning('Intento de eliminar archivo con nombre vacío');
                return false;
            }

            // Si es una URL completa, extraer solo el path
            if (str_contains($fileName, 'http')) {
                $parsedUrl = parse_url($fileName);
                $fileName = ltrim($parsedUrl['path'] ?? '', '/');
            }

            // Verificar si el archivo existe antes de intentar eliminarlo
            if (Storage::disk('s3')->exists($fileName)) {
                Storage::disk('s3')->delete($fileName);
                Log::info("Archivo eliminado exitosamente de S3: {$fileName}");
                return true;
            } else {
                Log::warning("Archivo no encontrado en S3: {$fileName}");
                return false;
            }

        } catch (\Exception $e) {
            Log::error("Error eliminando archivo de S3 ({$fileName}): " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Eliminar múltiples archivos de S3
     */
    public function deleteMultiple(array $fileNames)
    {
        $deletedCount = 0;
        $errors = [];

        foreach ($fileNames as $fileName) {
            try {
                if ($this->delete($fileName)) {
                    $deletedCount++;
                }
            } catch (\Exception $e) {
                $errors[] = "Error eliminando {$fileName}: " . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            Log::warning('Errores durante eliminación múltiple:', $errors);
        }

        return [
            'deleted_count' => $deletedCount,
            'errors' => $errors
        ];
    }


    // En ImageService.php, agregar método para obtener URL pública
    public function getPublicUrl($fileName)
    {
        try {
            if (empty($fileName)) {
                return null;
            }

            // Si ya es una URL completa, devolverla tal como está
            if (str_contains($fileName, 'http')) {
                return $fileName;
            }

            // Construir URL pública de S3
            $baseUrl = config('filesystems.disks.s3.url') ??
                'https://' . config('filesystems.disks.s3.bucket') . '.s3.' .
                config('filesystems.disks.s3.region') . '.amazonaws.com';

            return $baseUrl . '/' . ltrim($fileName, '/');

        } catch (\Exception $e) {
            Log::error("Error construyendo URL pública: " . $e->getMessage());
            return null;
        }
    }

// En ImageService.php
    public function getUrl($file)
    {
        try {
            if (empty($file)) {
                return null;
            }

            if (str_contains($file, 'http')) {
                return $file;
            }

            // ✅ USAR EL BUCKET CORRECTO
            $bucket = 'med-by-students';  // No pre-flashcard
            $region = 'sa-east-1';

            return "https://{$bucket}.s3.{$region}.amazonaws.com/{$file}";

        } catch (\Exception $e) {
            Log::error("Error construyendo URL pública: " . $e->getMessage());
            return null;
        }
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
