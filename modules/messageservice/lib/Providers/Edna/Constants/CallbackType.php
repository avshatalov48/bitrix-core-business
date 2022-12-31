<?php

namespace Bitrix\MessageService\Providers\Edna\Constants;

class CallbackType
{
	public const MESSAGE_STATUS = 'statusCallbackUrl';
	public const INCOMING_MESSAGE = 'inMessageCallbackUrl';
	public const TEMPLATE_REGISTER_STATUS = 'messageMatcherCallbackUrl';

	public static function getAllTypeList(): array
	{
		return [
			self::MESSAGE_STATUS,
			self::INCOMING_MESSAGE,
			self::TEMPLATE_REGISTER_STATUS
		];
	}
}