<?php
namespace Bitrix\Socialnetwork\Helper;

use Bitrix\Main\Web\Uri;

final class AvatarManager
{
	public const IMAGE_TYPE = 'image';
	public const ICON_TYPE = 'icon';
	public const COMMON_SPACE_AVATAR_ID = 'common-space';
	public const AVATAR_SIZE = 300;

	public function getImageAvatar(int $imageId): Avatar
	{
		$avatarId = $this->getAvatarImageUri($imageId);

		return new Avatar(self::IMAGE_TYPE, $avatarId);
	}

	public function getIconAvatar(string $avatarId): Avatar
	{
		if ($avatarId !== self::COMMON_SPACE_AVATAR_ID)
		{
			$avatarId = Workgroup::getAvatarTypeWebCssClass($avatarId);
		}

		return new Avatar(self::ICON_TYPE, $avatarId);
	}

	private function getAvatarImageUri(int $imageId): string
	{
		$result = '';

		$file = \CFile::getFileArray($imageId);
		if (!empty($file))
		{
			$fileResized = \CFile::resizeImageGet(
				$file,
				[
					'width' => 50,
					'height' => 50,
				]
			);

			$result = Uri::urnEncode(htmlspecialcharsbx($fileResized['src']));
		}

		return  $result;
	}

	/**
	 * @param array $newPhotoFile
	 * @return array
	 * @throws \RuntimeException
	 */
	public function loadAvatar(array $newPhotoFile): array
	{
		if (!(\CFile::checkImageFile($newPhotoFile, 0, 0, 0, ['IMAGE']) === null))
		{
			throw new \RuntimeException('The file is not an image.');
		}

		$newPhotoFile['MODULE_ID'] = 'socialnetwork';
		$fileId = \CFile::saveFile($newPhotoFile, 'socialnetwork');
		if (!$fileId)
		{
			throw new \RuntimeException('Unable to save file.');
		}

		$fileTmp = \CFile::resizeImageGet(
			$fileId,
			[
				'width' => self::AVATAR_SIZE,
				'height' => self::AVATAR_SIZE,
			],
			BX_RESIZE_IMAGE_PROPORTIONAL,
			false,
			false,
			true,
		);

		return [
			'fileId' => $fileId,
			'fileUri' => $fileTmp['src'],
		];
	}

	public function getAvatar(int $imageId): array
	{
		$fileId = \CFile::MakeFileArray($imageId);
		\CFile::ResizeImage($fileId, ['width' => self::AVATAR_SIZE, 'height' => self::AVATAR_SIZE]);

		return $fileId;
	}
}