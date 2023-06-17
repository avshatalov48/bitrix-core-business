<?php

namespace Bitrix\Im\V2\Marketplace\Types;

final class Context
{
	public const ALL = 'ALL';
	public const USER = 'USER';
	public const CHAT = 'CHAT';
	public const LINES = 'LINES';
	public const CRM = 'CRM';

	public static function getTypes(): array
	{
		return [
			self::ALL,
			self::USER,
			self::CHAT,
			self::LINES,
			self::CRM,
		];
	}
}