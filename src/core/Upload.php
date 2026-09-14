<?php
declare(strict_types=1);

/**
 * Upload - Manejo seguro de archivos subidos (imágenes de evidencias y recursos).
 */
final class Upload
{
    public const ENTREGAS   = 'public/uploads/entregas';
    public const PERFILES   = 'public/uploads/profiles';
    public const RECURSOS   = 'public/uploads/recursos';

    /**
     * Valida y guarda un archivo subido.
     *
     * @param array  $file      Elemento de $_FILES.
     * @param string $subdir    subdirectorio dentro de public/uploads (constantes de la clase).
     * @param bool   $requiereImagen Si true, exige imagen válida (jpg/png/gif) dentro de tamaños aceptados.
     *
     * @return array{ok: bool, path?: string, error?: string}
     */
    public static function save(array $file, string $subdir, bool $requiereImagen = true): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'No se pudo subir el archivo o no se seleccionó ninguno.'];
        }

        $maxSize = (int)Config::get('uploads.max_file_size', 5 * 1024 * 1024);
        if ((int)$file['size'] > $maxSize) {
            $mb = round($maxSize / 1048576, 1);
            return ['ok' => false, 'error' => "El archivo excede el tamaño máximo de {$mb} MB."];
        }

        $finfo   = new finfo(FILEINFO_MIME_TYPE);
        $mime    = (string)$finfo->file($file['tmp_name']);
        $allowed = Config::get('uploads.allowed_extensions', []);

        $allExts = array_values(array_unique(array_merge(
            $allowed['images'] ?? [],
            $allowed['documents'] ?? [],
            $allowed['videos'] ?? []
        )));

        // Mapeo mime -> extensiones permitidas
        $mimeMap = [
            'jpeg'    => 'images', 'png'     => 'images', 'gif'     => 'images',
            'pdf'     => 'documents', 'msword' => 'documents',
            'vnd.openxmlformats-officedocument.wordprocessingml.document' => 'documents',
            'vnd.ms-powerpoint' => 'documents',
            'vnd.openxmlformats-officedocument.presentationml.presentation' => 'documents',
            'vnd.ms-excel' => 'documents',
            'vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'documents',
            'mp4'     => 'videos', 'webm'    => 'videos', 'ogg'     => 'videos',
        ];

        $mimeMain = explode('/', $mime);
        $mimeKey  = count($mimeMain) === 2 ? $mimeMain[1] : '';
        $grupo    = $mimeMap[$mimeKey] ?? (($mimeMain[0] ?? '') . 's');

        if ($requiereImagen && !in_array($mime, ['image/jpeg', 'image/png', 'image/gif'], true)) {
            return ['ok' => false, 'error' => 'Solo se permiten imágenes (JPG, PNG o GIF).'];
        }

        if (!empty($allowed[$grupo])) {
            $ext = self::extensionOf($mime);
            if (!in_array($ext, $allowed[$grupo], true)) {
                return ['ok' => false, 'error' => 'El tipo de archivo no está permitido.'];
            }
        }

        // Validación de dimensiones para imágenes
        if ($requiereImagen || in_array($mime, ['image/jpeg', 'image/png', 'image/gif'], true)) {
            $size = @getimagesize($file['tmp_name']);
            if ($size === false) {
                return ['ok' => false, 'error' => 'El archivo no es una imagen válida.'];
            }
            $minW = (int)Config::get('uploads.image_validation.min_width', 800);
            $minH = (int)Config::get('uploads.image_validation.min_height', 600);
            $imgW = (int)Config::get('uploads.image_validation.max_width', 4096);
            $imgH = (int)Config::get('uploads.image_validation.max_height', 4096);
            if ($size[0] < $minW || $size[1] < $minH) {
                return ['ok' => false, 'error' => "La imagen debe tener al menos {$minW}x{$minH} píxeles."];
            }
            if ($size[0] > $imgW || $size[1] > $imgH) {
                return ['ok' => false, 'error' => "La imagen supera el tamaño máximo de {$imgW}x{$imgH} píxeles."];
            }
        }

        $subdir = trim($subdir, '/');
        $root   = WEBQUEST_ROOT . '/' . $subdir;
        if (!is_dir($root)) {
            @mkdir($root, 0777, true);
        }

        $ext      = self::extensionOf($mime);
        $filename = date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $target   = $root . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return ['ok' => false, 'error' => 'No se pudo guardar el archivo en el servidor.'];
        }

        return ['ok' => true, 'path' => $subdir . '/' . $filename];
    }

    private static function extensionOf(string $mime): string
    {
        $map = [
            'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif',
            'application/pdf' => 'pdf', 'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/ogg' => 'ogg',
        ];
        return $map[$mime] ?? 'bin';
    }
}