<?php
namespace Bitrix\MessageService\Integration;

use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

class Pull
{
	private static $canUse;

	public static function canUse()
	{
		if (static::$canUse === null)
		{
			static::$canUse = Loader::includeModule('pull');
		}
		return static::$canUse;
	}

	public static function onMessagesUpdate(array $messages)
	{
		if (!static::canUse())
		{
			return false;
		}

		return static::addToStack('message_update', [
			'messages' => static::convertData($messages)
		]);
	}

	/**
	 * @param string $command Pull command name.
	 * @param array $params Command parameters.
	 * @return bool
	 */
	private static function addToStack($command, array $params)
	{
		if (!static::canUse())
		{
			return false;
		}

		return \CPullWatch::addToStack(
			'MESSAGESERVICE',
			array(
				'module_id' => 'messageservice',
				'command' => $command,
				'params' => $params,
			)
		);
	}

	/**
	 * Converts message fields to the suitable for sending via p&p format.
	 *
	 * @param array $messages
	 * @return array
	 */
	private static function convertData(array $messages): array
	{
		foreach($messages as $k => $message)
		{
			foreach ($message as $field => $value)
			{
				if ($value instanceof DateTime)
				{
					$messages[$k][$field] = (string)$value;
				}
			}
		}

		return $messages;
	}
}