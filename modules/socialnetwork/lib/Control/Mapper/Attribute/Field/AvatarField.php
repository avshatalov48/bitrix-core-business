<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Mapper\Attribute\Field;

use CFile;

class AvatarField implements FieldInterface
{
	public function __construct(
		private readonly string $toField
	)
	{

	}
	public function getValue(mixed $value): array
	{
		$imageId = (int)$value;
		if ($imageId <= 0)
		{
			return [];
		}

		$image = CFile::MakeFileArray($imageId);
		if (!$image)
		{
			return [];
		}

		CFile::ResizeImage($image, ["width" => 300, "height" => 300]);

		return $image;
	}

	public function getFieldName(): string
	{
		return $this->toField;
	}
}