<?php

namespace Bitrix\Calendar\Integration\Im;

use Bitrix\Calendar\Core\Builders\EventCategory\EventCategoryBuilderFromProviderObject;
use Bitrix\Calendar\Core\Common;
use Bitrix\Calendar\EventCategory\Dto\EventCategoryRenameDto;
use Bitrix\Calendar\Integration\Im\EventCategoryService as EventCategoryImIntegration;
use Bitrix\Calendar\Internals\Log\Logger;
use Bitrix\Calendar\OpenEvents\Controller\Request\EventCategory\UpdateEventCategoryDto;
use Bitrix\Calendar\OpenEvents\Provider;
use Bitrix\Calendar\OpenEvents\Service\CategoryService;
use Bitrix\Main;

final class EventCategoryRename
{
	private const LOG_MARKER = 'DEBUG_CALENDAR_IM_CHAT_RENAME';
	private static ?Logger $logger = null;

	public static function onChannelRename(/* args */): void
	{
		try
		{
			// this helps better understand why method fails, if wrong arguments presented by event
			[$chatId, $name, $chatEntityType] = func_get_args();

			if ($chatEntityType !== EventCategoryImIntegration::OPEN_EVENT_CATEGORY_IM_ENTITY_TYPE)
			{
				return;
			}

			if (!is_int($chatId) || !is_string($name))
			{
				self::getLogger()->log([
					'message' => 'can not process event arguments',
					[
						'chatId' => $chatId,
						'name' => $name,
					]
				]);

				return;
			}

			$renameDto = new EventCategoryRenameDto(
				$chatId,
				$name,
			);
			self::addBackgroundJob($renameDto);
		}
		catch (\Throwable $e)
		{
			self::getLogger()->log($e);
		}
	}

	public static function applyChannelRenameToCategory(
		EventCategoryRenameDto $renameDto
	): void
	{
		$userId = Common::SYSTEM_USER_ID;
		$categoryProvider = new Provider\CategoryProvider($userId);
		$eventCategory = $categoryProvider->getByChannelId($renameDto->chatId) ?? null;

		if (!$eventCategory)
		{
			self::getLogger()->log([
				'message' => 'event category not exist for connected channel',
				...$renameDto->toArray()
			]);

			return;
		}

		if ($eventCategory->name === $renameDto->name)
		{
			return;
		}

		CategoryService::getInstance()->updateEventCategory(
			$userId,
			(new EventCategoryBuilderFromProviderObject($eventCategory))->build(),
			UpdateEventCategoryDto::fromRequest(['name' => $renameDto->name])
		);
	}

	private static function addBackgroundJob(EventCategoryRenameDto $renameDto)
	{
		Main\Application::getInstance()->addBackgroundJob(
			job: [self::class, 'applyChannelRenameToCategory'],
			args: [$renameDto],
		);
	}

	private static function getLogger(): Logger
	{
		self::$logger ??= new Logger(self::LOG_MARKER);

		return self::$logger;
	}
}
