<?php

namespace Bitrix\MessageService\Providers\Edna\Constants;

class ChannelType
{
	public const WHATSAPP = 'WHATSAPP';
	public const SMS = 'SMS';
	public const VIBER = 'VIBER';

	public static function getAllTypeList(): array
	{
		return [
			self::WHATSAPP,
			self::SMS,
			self::VIBER,
		];
	}
}