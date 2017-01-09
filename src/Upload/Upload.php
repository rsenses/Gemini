<?php

namespace App\Upload;

use Psr\Http\Message\UploadedFileInterface;

class Upload
{
    public static function image(UploadedFileInterface $file, $key, $slug, $uploadPath, $uploadedUrl)
    {
        if ($file->getSize()) {
            if ($file->getError() === UPLOAD_ERR_OK) {
                // if (!in_array($file->getClientMediaType(), ['image/png', 'image/gif', 'image/jpeg'])) {
                //     throw new Exception("La imagen debe ser jpg, gif o png, inténtelo de nuevo, por favor.", 1);
                // }
                // if ($file->getSize() > 500000) {
                //     throw new Exception("La imagen no debe superar los 500Kb, inténtelo de nuevo, por favor.", 1);
                // }

                $fileName = $key.'-'.$slug.'.'.pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);

                $file->moveTo($uploadPath.$fileName);

                return $uploadedUrl.$fileName;
            } else {
                throw new Exception("Hubo un error al subir la imagen, inténtelo de nuevo, por favor.", 1);
            }
        }
    }
}
