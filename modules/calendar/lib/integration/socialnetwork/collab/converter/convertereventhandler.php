<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Integration\SocialNetwork\Collab\Converter;

use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Internals\Log\Logger;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Collab\Converter\Event\ConverterEvent;
use Bitrix\Socialnetwork\Item\Workgroup\Type;

class ConverterEventHandler
{
	private const LOG_MARKER = 'DEBUG_CALENDAR_SOCNET_CONVERTER';

	private static ?Logger $logger = null;

	/**
	 * @param ConverterEvent $event
	 */
	public static function onConvert(mixed $event): void
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			return;
		}

		try
		{
			$entityBefore = $event->getEntityBefore();
			$entityAfter = $event->getEntityAfter();

			if ($entityAfter->getType() === $entityBefore->getType() || is_null($entityAfter->getType()))
			{
				return;
			}

			$eventType = self::getEventTypeByGroupType($entityAfter->getType());

			self::bindAgent($entityAfter->getId(), $eventType);
		}
		catch (\Throwable $e)
		{
			self::getLogger()->log($e);
		}
	}

	private static function getEventTypeByGroupType(Type $type): string
	{
		return match($type) {
			Type::Collab => Dictionary::EVENT_TYPE['collab'],
			default => '',
		};
	}

	private static function bindAgent(int $groupId, string $eventType): void
	{
		\CAgent::AddAgent(
			UpdateEventTypeAgent::makeAgentName($groupId, $eventType),
			'calendar',
			'N',
			5,
		);
	}

	private static function getLogger(): Logger
	{
		self::$logger ??= new Logger(self::LOG_MARKER);

		return self::$logger;
	}
}
