<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Provider;

use Bitrix\Socialnetwork\Helper\InstanceTrait;
use Bitrix\Socialnetwork\Provider\File\File;
use CFile;

class FileProvider
{
	use InstanceTrait;

	public function get(int $fileId): ?File
	{
		$file = CFile::GetFileArray($fileId);

		if ($file === false)
		{
			return null;
		}

		return new File(
			id: (int)$file['ID'],
			src: $file['SRC'],
		);
	}
}