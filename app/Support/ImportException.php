<?php

namespace App\Support;

use RuntimeException;

// Ошибка чтения файла книги. Текст сообщения показывается администратору.
class ImportException extends RuntimeException
{
}
