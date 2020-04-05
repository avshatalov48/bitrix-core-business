<?php
namespace Bitrix\MessageService;

class MessageType
{
	const SMS = 'SMS';

	public static function isSupported($type)
	{
		return $type === static::SMS;
	}
}