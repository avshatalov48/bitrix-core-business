<?php

namespace Bitrix\Im\V2\Marketplace\Types;

final class Role
{
	public const USER = 'USER';
	public const ADMIN = 'ADMIN';

	public static function getTypes(): array
	{
		return [
			self::USER,
			self::ADMIN
		];
	}
}