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
     * @param array  $file            Elemento de $_FILES.
     * @param string $subdir          subdirectorio dentro de public/uploads (constantes de la clase).
     * @param bool   $requiereImagen  Si true, exige imagen válida dentro de tamaños aceptados.
     * @param array  $opciones        Sobrescribe los límites por defecto: max_file_size, mimes,
     *                                extensions, min_width, min_height, max_width, max_height,
     *                                mime_error, min_size_error.
     *
     * @return array{ok: bool, path?: string, error?: string}
     */
    public static function save(array $file, string $subdir, bool $requiereImagen = true, array $opciones = []): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'No se pudo subir el archivo o no se seleccionó ninguno.'];
        }

        $maxSize = (int)($opciones['max_file_size'] ?? Config::get('uploads.max_file_size', 5 * 1024 * 1024));
        if ((int)$file['size'] > $maxSize) {
            $mb = round($maxSize / 1048576, 1);
            return ['ok' => false, 'error' => "El archivo excede el tamaño máximo de {$mb} MB."];
        }

        $finfo   = new finfo(FILEINFO_MIME_TYPE);
        $mime    = (string)$finfo->file($file['tmp_name']);
        $allowed = Config::get('uploads.allowed_extensions', []);

        // Mapeo mime -> grupo de extensiones permitidas
        $mimeMap = [
            'jpeg'    => 'images', 'png'     => 'images', 'gif'     => 'images',
            'webp'    => 'images', 'bmp'     => 'images',
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

        $mimesPermitidos = $opciones['mimes'] ?? ($requiereImagen ? ['image/jpeg', 'image/png', 'image/gif'] : null);
        if ($mimesPermitidos !== null && !in_array($mime, $mimesPermitidos, true)) {
            return ['ok' => false, 'error' => $opciones['mime_error'] ?? 'Solo se permiten imágenes (JPG, PNG o GIF).'];
        }

        $extensiones = $opciones['extensions'] ?? ($allowed[$grupo] ?? []);
        if (!empty($extensiones)) {
            $ext = self::extensionOf($mime);
            if (!in_array($ext, $extensiones, true)) {
                return ['ok' => false, 'error' => 'El tipo de archivo no está permitido.'];
            }
        }

        // Validación de dimensiones para imágenes (max_width/max_height en 0 = sin tope)
        if (str_starts_with($mime, 'image/')) {
            $size = @getimagesize($file['tmp_name']);
            if ($size === false) {
                return ['ok' => false, 'error' => 'El archivo no es una imagen válida.'];
            }
            $minW = (int)($opciones['min_width'] ?? Config::get('uploads.image_validation.min_width', 800));
            $minH = (int)($opciones['min_height'] ?? Config::get('uploads.image_validation.min_height', 600));
            $imgW = (int)($opciones['max_width'] ?? Config::get('uploads.image_validation.max_width', 4096));
            $imgH = (int)($opciones['max_height'] ?? Config::get('uploads.image_validation.max_height', 4096));
            if ($size[0] < $minW || $size[1] < $minH) {
                return [
                    'ok' => false,
                    'error' => $opciones['min_size_error'] ?? 'Tu foto es demasiado pequeña para leer tu trabajo. Tómala desde más cerca, con buena luz y apuntando de frente a tu libreta.',
                ];
            }
            if ($imgW > 0 && $imgH > 0 && ($size[0] > $imgW || $size[1] > $imgH)) {
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

    /**
     * Guarda la fotografía de evidencia de la libreta con criterios flexibles:
     * fotos grandes, capturas de pantalla y fotos pequeñas del teléfono se aceptan.
     */
    public static function saveEvidence(array $file): array
    {
        return self::save($file, self::ENTREGAS, true, [
            'max_file_size' => (int)Config::get('uploads.evidence.max_file_size', 20 * 1024 * 1024),
            'mimes'         => Config::get('uploads.evidence.allowed_mimes', [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/x-ms-bmp',
            ]),
            'extensions'    => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'],
            'min_width'     => (int)Config::get('uploads.evidence.min_width', 160),
            'min_height'    => (int)Config::get('uploads.evidence.min_height', 120),
            'max_width'     => (int)Config::get('uploads.evidence.max_width', 0),
            'max_height'    => (int)Config::get('uploads.evidence.max_height', 0),
            'mime_error'    => 'La foto debe ser JPG, PNG, WEBP, GIF o BMP. Si tu teléfono guarda las fotos en HEIC, cambia el formato en la cámara o envíala como JPG.',
            'min_size_error' => 'Tu foto es demasiado pequeña para leer tu trabajo. Tómala de nuevo un poco más cerca, con buena luz y apuntando de frente a tu libreta.',
        ]);
    }

    private static function extensionOf(string $mime): string
    {
        $map = [
            'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif',
            'image/webp' => 'webp', 'image/bmp' => 'bmp', 'image/x-ms-bmp' => 'bmp',
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
