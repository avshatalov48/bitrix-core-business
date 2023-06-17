<?php

namespace Bitrix\Im\V2\Service;

use Bitrix\Main\DI\ServiceLocator;

class Locator
{
	private static ?Context $context = null;

	private function __construct()
	{}

	public static function getMessenger(): Messenger
	{
		return ServiceLocator::getInstance()->get('Im.Messenger');
	}

	public static function getContext(): Context
	{
		if (!self::$context instanceof Context)
		{
			self::setContext(new Context());
		}

		return self::$context;
	}

	public static function setContext(?Context $context): void
	{
		self::$context = $context;
	}
}