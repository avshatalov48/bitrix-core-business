<?php

namespace Bitrix\Bizproc\UI;

use Bitrix\Main\EO_User;

class UserView implements \JsonSerializable
{
	private EO_User $user;

	public function __construct(EO_User $user)
	{
		$this->user = $user;
	}

	public function getUserId(): int
	{
		return $this->user->getId();
	}

	public function getFullName(): string
	{
		return \CUser::FormatName(
			\CSite::GetNameFormat(),
			[
				'LOGIN' => $this->user->getLogin(),
				'NAME' => $this->user->getName(),
				'LAST_NAME' => $this->user->getLastName(),
				'SECOND_NAME' => $this->user->getSecondName(),
			],
			true,
			false
		);
	}

	public function getUserAvatar(int $size = 100): ?string
	{
		static $cache = [];

		$photo = $this->user->getPersonalPhoto();
		if (empty($photo) || $size <= 0)
		{
			return null;
		}

		$avatarId = "{$photo}{$size}";
		if (!isset($cache[$avatarId]))
		{
			$cache[$avatarId] = '';
			if ($photo > 0)
			{
				$originalFile = \CFile::getFileArray($photo);

				if ($originalFile !== false)
				{
					$resizedFile = \CFile::resizeImageGet(
						$originalFile,
						[
							'width' => $size,
							'height' => $size
						],
						BX_RESIZE_IMAGE_EXACT,
						false,
						false,
						true,
					);

					$cache[$avatarId] = $resizedFile['src'];
				}
			}
		}

		return $cache[$avatarId];
	}

	public function jsonSerialize(): array
	{
		$userId = $this->getUserId();

		return [
			'id' => $userId,
			'login' => $this->user->getLogin(),
			'name' => $this->user->getName(),
			'lastName' => $this->user->getLastName(),
			'secondName' => $this->user->getSecondName(),
			'fullName' => $this->getFullName(),
			'workPosition' => $this->user->getWorkPosition(),
			'link' => "/company/personal/user/{$userId}/",
			'avatarSize100' => $this->getUserAvatar(),
		];
	}
}
