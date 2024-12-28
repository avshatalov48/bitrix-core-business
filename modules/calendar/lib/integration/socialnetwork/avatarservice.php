<?php

namespace Bitrix\Calendar\Integration\SocialNetwork;

use Bitrix\Main\Loader;
use Bitrix\Main\Web\Uri;
use Bitrix\Socialnetwork\Helper\Avatar;

final class AvatarService
{
	private const IMAGE_TYPE = 'image';
	private const DEFAULT_WIDTH = 150;
	private const DEFAULT_HEIGHT = 150;

	public function getAvatar(int $imageId): ?Avatar
	{
		if (!$this->isAvailable())
		{
			return null;
		}

		return new Avatar(self::IMAGE_TYPE, $this->getImageAvatarUri($imageId));
	}

	private function getImageAvatarUri(int $imageId): string
	{
		$result = '';

		$file = \CFile::getFileArray($imageId);
		if (!empty($file))
		{
			$fileResized = \CFile::resizeImageGet(
				$file,
				[
					'width' => self::DEFAULT_WIDTH,
					'height' => self::DEFAULT_HEIGHT,
				]
			);

			$result = Uri::urnEncode(htmlspecialcharsbx($fileResized['src']));
		}

		return $result;
	}

	private function isAvailable(): bool
	{
		return Loader::includeModule('socialnetwork');
	}
}