<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Mapper\Field;

use Bitrix\Socialnetwork\Collab\Control\File\File;
use CFile;

class AvatarMapper implements ValueMapperInterface
{
	public function getValue(mixed $value): array
	{
		if ($value === '')
		{
			return [
				'del' => 'Y',
			];
		}

		$image = File::createImageFromBase64($value);

		CFile::ResizeImage($image, ["width" => 300, "height" => 300]);

		if (!is_array($image))
		{
			return [];
		}

		return $image;
	}
}