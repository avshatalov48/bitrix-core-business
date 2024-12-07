<?php

namespace Bitrix\Calendar\Integration\Im;

use Bitrix\Calendar\Core\Common;
use Bitrix\Calendar\EventCategory\Dto\EventCategoryAttendeesUpdateDto;
use Bitrix\Calendar\EventCategory\Enum\AttendeesUpdateTypeEnum;
use Bitrix\Calendar\Integration\Im\EventCategoryAttendees\JobUserStorage;
use Bitrix\Calendar\Internals\Log\Logger;
use Bitrix\Calendar\OpenEvents\Item\Category;
use Bitrix\Calendar\OpenEvents\Provider;
use Bitrix\Calendar\OpenEvents\Service\CategoryAttendeeService;
use Bitrix\Calendar\OpenEvents\Service\CategoryBanService;
use Bitrix\Main;

final class EventCategoryAttendees
{
	private const LOG_MARKER = 'DEBUG_CALENDAR_IM_CHAT_ATTENDEE_PROCESSOR';
	private static ?Logger $logger = null;
	private static ?JobUserStorage $usersStorage = null;

	public static function onChannelUsersAdd(array $eventData): void
	{
		try
		{
			$updateDto = new EventCategoryAttendeesUpdateDto(
				$eventData['CHAT_ID'],
				AttendeesUpdateTypeEnum::TYPE_ADD
			);

			self::onChannelUsersUpdate($updateDto, $eventData['NEW_USERS']);
		}
		catch (\Throwable $e)
		{
			self::getLogger()->log($e);
		}
	}

	public static function onChannelUserDelete(array $eventData): void
	{
		try
		{
			$updateDto = new EventCategoryAttendeesUpdateDto(
				$eventData['CHAT_ID'],
				AttendeesUpdateTypeEnum::TYPE_DELETE
			);
			self::onChannelUsersUpdate($updateDto, [$eventData['USER_ID']]);
		}
		catch (\Throwable $e)
		{
			self::getLogger()->log($e);
		}
	}

	public static function applyChannelUsersUpdateToCategory(
		EventCategoryAttendeesUpdateDto $updateDto,
	): void
	{
		$categoryProvider = new Provider\CategoryProvider(Common::SYSTEM_USER_ID);

		$eventCategory = $categoryProvider->getByChannelId($updateDto->chatId) ?? null;

		if (!$eventCategory)
		{
			self::getLogger()->log([
				'message' => 'event category not exist for connected channel',
				...$updateDto->toArray()
			]);

			return;
		}

		try
		{
			if ($eventCategory->closed)
			{
				self::processClosedEventCategory($eventCategory, $updateDto);
			}
			else
			{
				self::processOpenEventCategory($eventCategory, $updateDto);
			}
		} catch (\Throwable $e)
		{
			self::getLogger()->log($e);
		}
	}

	private static function onChannelUsersUpdate(EventCategoryAttendeesUpdateDto $updateDto, array $userIds): void
	{
		$isBackgroundJobAlreadyScheduled = self::getJobStorage()->has($updateDto);
		self::getJobStorage()->add($updateDto, $userIds);
		if ($isBackgroundJobAlreadyScheduled)
		{
			return;
		}

		self::addBackgroundJob($updateDto);
	}

	private static function addBackgroundJob(EventCategoryAttendeesUpdateDto $updateDto)
	{
		Main\Application::getInstance()->addBackgroundJob(
			job: [self::class, 'applyChannelUsersUpdateToCategory'],
			args: [$updateDto],
		);
	}

	private static function processClosedEventCategory(
		Category $eventCategory,
		EventCategoryAttendeesUpdateDto $updateDto,
	): void
	{
		$categoryAttendeeService = CategoryAttendeeService::getInstance();
		switch ($updateDto->type)
		{
			case AttendeesUpdateTypeEnum::TYPE_ADD:
				$categoryAttendeeService->addAttendeesToCategoryByChunk(
					$eventCategory->id,
					self::getJobStorage()->get($updateDto),
				);
				break;
			case AttendeesUpdateTypeEnum::TYPE_DELETE:
				$categoryAttendeeService->deleteAttendeesFromCategory(
					$eventCategory->id,
					self::getJobStorage()->get($updateDto),
				);
				break;
			default:
				return;
		}

		self::getJobStorage()->clear($updateDto);
	}

	private static function processOpenEventCategory(
		Category $eventCategory,
		EventCategoryAttendeesUpdateDto $updateDto,
	): void
	{
		switch ($updateDto->type)
		{
			case AttendeesUpdateTypeEnum::TYPE_DELETE:
				$categoryBanService = CategoryBanService::getInstance();
				$categoryBanService->banCategoryMulti($eventCategory->id, self::getJobStorage()->get($updateDto));
				break;
		}
	}

	private static function getLogger(): Logger
	{
		self::$logger ??= new Logger(self::LOG_MARKER);

		return self::$logger;
	}

	private static function getJobStorage(): JobUserStorage
	{
		self::$usersStorage ??= new JobUserStorage();

		return self::$usersStorage;
	}
}
