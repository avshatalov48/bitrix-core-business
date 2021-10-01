<?php
namespace Bitrix\Im\Integration\UI\EntitySelector\Helper;

use Bitrix\Main\EO_User;

class User
{
	public static function formatName(EO_User $user, array $options = []): string
	{
		return \CUser::formatName(
			!empty($options['nameTemplate'])
				? $options['nameTemplate']
				: \CSite::getNameFormat(false),
			[
				'NAME' => $user->getName(),
				'LAST_NAME' => $user->getLastName(),
				'SECOND_NAME' => $user->getSecondName(),
				'LOGIN' => $user['LOGIN'],
				'EMAIL' => $user['EMAIL'],
				'TITLE' => $user['TITLE'],
			],
			true,
			false
		);
	}

	public static function makeAvatar(EO_User $user): ?string
	{
		if (empty($user->getPersonalPhoto()))
		{
			return null;
		}

		$avatar = \CFile::resizeImageGet(
			$user->getPersonalPhoto(),
			['width' => 100, 'height' => 100],
			BX_RESIZE_IMAGE_EXACT,
			false
		);

		return !empty($avatar['src']) ? $avatar['src'] : null;
	}

	public static function getCurrentUserId(): int
	{
		return is_object($GLOBALS['USER']) ? (int)$GLOBALS['USER']->getId() : 0;
	}
}