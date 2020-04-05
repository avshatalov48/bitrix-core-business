<?php
namespace Bitrix\MessageService\Integration;

use Bitrix\Main\Loader;

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
		return static::addToStack('message_update', array('messages' => $messages));
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
}