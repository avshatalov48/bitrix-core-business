<?php

namespace Bitrix\Mail\Helper\User;

use Bitrix\Main\Config\Option;

final class Tool
{
	public function resizePhoto(int $photo, int $width, int $height): array
	{
		$preview = \CFile::resizeImageGet(
			$photo,
			['width' => $width, 'height' => $height],
			BX_RESIZE_IMAGE_EXACT,
			false,
			false,
			true,
		);

		return $preview ?: [];
	}

	public function formatName(array $userData): string
	{
		$nameFormat = \CSite::GetNameFormat(false);

		return \CUser::FormatName($nameFormat, $userData, true, false);
	}

	public function getPathToProfile(int $userId): string
	{
		return str_replace(
			['#user_id#'],
			$userId,
			Option::get('main', 'TOOLTIP_PATH_TO_USER', false, SITE_ID),
		);
	}
}
