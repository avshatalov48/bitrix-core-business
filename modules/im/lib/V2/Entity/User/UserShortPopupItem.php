<?php

namespace Bitrix\Im\V2\Entity\User;

class UserShortPopupItem extends UserPopupItem
{
	public function toRestFormat(array $option = []): array
	{
		$option['USER_SHORT_FORMAT'] = true;

		return parent::toRestFormat($option);
	}

	public static function getRestEntityName(): string
	{
		return 'usersShort';
	}
}