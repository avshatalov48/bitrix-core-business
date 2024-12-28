<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\File;

use Bitrix\Main\Security\Random;
use Bitrix\Main\Web\MimeType;
use CFile;
use CTempFile;

class File
{
	public static function createImageFromBase64(string $value): array
	{
		$value = base64_decode($value);

		$mime = MimeType::getByContent($value);
		if (!MimeType::isImage($mime))
		{
			return [];
		}

		[, $type] = explode('/', $mime);

		$fileName = Random::getString(32);
		$fileName = CTempFile::GetFileName($fileName . '.' . $type);

		if (!CheckDirPath($fileName))
		{
			return [];
		}

		file_put_contents($fileName, $value);

		return CFile::MakeFileArray($fileName);
	}
}