<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use ZipArchive;

// разбор загруженной книги (txt, fb2 или zip с fb2)
// возвращает данные о книге и список глав: название и текст
class BookImport
{
    public static function fromUpload(UploadedFile $file, string $mode = 'word', string $marker = 'Глава'): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        if ($ext === 'zip') {
            $raw = self::readFb2FromZip($path);
            $ext = 'fb2';
        } else {
            $raw = file_get_contents($path);
        }

        if ($ext === 'fb2') {
            return Fb2Parser::parse($raw);
        }

        // txt: делим на главы по слову, которое указал администратор
        return [
            'meta' => [],
            'chapters' => BookText::chapters(BookText::decode($raw), $marker, $mode),
            'cover' => null,
        ];
    }

    private static function readFb2FromZip(string $path): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new ImportException('На сервере не включено расширение zip. Загрузите файл fb2 без архива.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new ImportException('Не удалось открыть zip-архив.');
        }

        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (str_ends_with(strtolower($stat['name']), '.fb2')) {
                    if ($stat['size'] > 40 * 1024 * 1024) {
                        throw new ImportException('Файл внутри архива слишком большой.');
                    }
                    $content = $zip->getFromIndex($i);
                    if ($content === false) {
                        throw new ImportException('Не удалось распаковать файл из архива.');
                    }
                    return $content;
                }
            }
        } finally {
            $zip->close();
        }

        throw new ImportException('В архиве нет файла .fb2.');
    }
}
