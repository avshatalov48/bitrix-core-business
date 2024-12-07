<?php

namespace Bitrix\Calendar\OpenEvents\Service;

use Bitrix\Calendar\Core\EventCategory\EventCategory;
use Bitrix\Calendar\Core\Mappers\EventCategory as EventCategoryMapper;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\EventCategory\Event\AfterEventCategoryUpdate;
use Bitrix\Calendar\EventOption\Helper\EventOptionHelper;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Calendar\Internals\Exception\PermissionDenied;
use Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryTable;
use Bitrix\Calendar\OpenEvents\Internals\OpenEventOptionTable;
use Bitrix\Main\Application;
use Bitrix\Main\DB\Connection;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Text\Emoji;
use Bitrix\Calendar\OpenEvents\Controller\Request\EventCategory as RequestDto;
use Bitrix\Calendar\Integration;

final class CategoryService
{
	private static ?self $instance;

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function createEventCategory(
		int $userId,
		RequestDto\CreateEventCategoryDto $createEventCategoryDto
	): EventCategory
	{
		$eventCategory = new EventCategory();
		$eventCategory->setName(Emoji::encode($createEventCategoryDto->name));
		$eventCategory->setDescription(Emoji::encode($createEventCategoryDto->description));
		$eventCategory->setCreatorId($userId);

		// if channelId selected, than ignore next request fields: attendees, departmentIds, closed
		if ($createEventCategoryDto->channelId)
		{
			$attendeeUserIds = $this->processCategoryCreationByChannel(
				$userId,
				$eventCategory,
				$createEventCategoryDto->channelId
			);
		}
		else
		{
			$attendeeUserIds = $this->processCategoryCreationByRequest(
				$userId,
				$eventCategory,
				$createEventCategoryDto
			);
		}

		$this->getEventCategoryMapper()->create($eventCategory);

		if (!empty($attendeeUserIds))
		{
			CategoryAttendeeService::getInstance()->addAttendeesToCategory(
				$eventCategory->getId(),
				$attendeeUserIds,
			);
		}

		if(!$eventCategory->getClosed())
		{
			CategoryMuteService::getInstance()->setMutedByDefault($eventCategory->getId());
		}

		return $eventCategory;
	}

	public function updateEventCategory(
		int $userId,
		EventCategory $eventCategory,
		RequestDto\UpdateEventCategoryDto $updateEventCategoryDto
	): void
	{
		$name = $updateEventCategoryDto->name;
		if (!empty($name) && $eventCategory->getName() !== $name)
		{
			$eventCategory->setName(Emoji::encode($name));
		}

		$description = $updateEventCategoryDto->description;
		if ($eventCategory->getDescription() !== $description)
		{
			$eventCategory->setDescription(Emoji::encode($description));
		}

		$closed = $updateEventCategoryDto->closed;
		if ($closed !== null && $eventCategory->getClosed() !== $closed)
		{
			$eventCategory->setClosed($closed);
		}

		$attendeeEntities = $updateEventCategoryDto->attendees;
		if ($attendeeEntities)
		{
			CategoryAttendeeService::getInstance()->processAttendeesForExistCategory(
				$userId,
				$eventCategory,
				$attendeeEntities
			);
		}

		$this->getEventCategoryMapper()->update($eventCategory);
	}

	public function deleteEventCategory(EventCategory $eventCategory): void
	{
		// TODO: need transaction?
		/** @var Connection $connection */
		$connection = Application::getInstance()->getConnection();
		$connection->startTransaction();
		$this->getEventCategoryMapper()->delete($eventCategory);
		if (!EventOptionHelper::changeCategoryForEvents($eventCategory->getId()))
		{
			$connection->rollbackTransaction();

			throw new \RuntimeException();
		}

		$connection->commitTransaction();
	}

	public function updateEventsCounter(int $eventCategoryId, bool $increment = true, int $value = 1): void
	{
		$incdecParts[] = $increment ? '+' : '-';
		$incdecParts[] = $value;
		OpenEventCategoryTable::update($eventCategoryId, [
			'EVENTS_COUNT' => new SqlExpression('?# ' . implode('', $incdecParts), 'EVENTS_COUNT'),
		]);

		$this->getEventCategoryMapper()->resetCacheById($eventCategoryId);

		(new AfterEventCategoryUpdate($eventCategoryId))->emit();
	}

	public function updateLastActivity(int $eventCategoryId): void
	{
		$lastEventQuery = OpenEventOptionTable::query()
			->setSelect([
				'EVENT_ID',
			])
			->registerRuntimeField(
				new ReferenceField(
					'EVENT',
					EventTable::getEntity(),
					Join::on('this.EVENT_ID', 'ref.ID'),
				),
			)
			->where('CATEGORY_ID', $eventCategoryId)
			->where('EVENT.DELETED', 'N')
			->setOrder(['EVENT_ID' => 'DESC'])
			->setLimit(1)
		;

		$lastEventId = (int)($lastEventQuery->fetch()['EVENT_ID'] ?? 0);

		if ($lastEventId > 0)
		{
			$dateCreateQuery = EventTable::query()
				->setSelect(['DATE_CREATE'])
				->where('ID', $lastEventId)
			;
		}
		else
		{
			$dateCreateQuery = OpenEventCategoryTable::query()
				->setSelect(['DATE_CREATE'])
				->where('ID', $eventCategoryId)
			;
		}

		$dateCreate = $dateCreateQuery->fetch()['DATE_CREATE'] ?? null;

		if ($dateCreate === null)
		{
			return;
		}

		OpenEventCategoryTable::update($eventCategoryId, [
			'LAST_ACTIVITY' => $dateCreate,
		]);

		$this->getEventCategoryMapper()->resetCacheById($eventCategoryId);

		(new AfterEventCategoryUpdate($eventCategoryId))->emit();
	}

	private function processCategoryCreationByChannel(int $userId, EventCategory $eventCategory, int $channelId): array
	{
		/** @var Integration\Im\EventCategoryServiceInterface $imIntegrationService */
		$imIntegrationService = ServiceLocator::getInstance()->get(Integration\Im\EventCategoryServiceInterface::class);

		$hasAccess = $imIntegrationService->isManagerOfChannel($userId, $channelId);
		if (!$hasAccess)
		{
			throw new PermissionDenied();
		}

		$imIntegrationService->connectChannelToCategory($channelId);
		$eventCategory->setChannelId($channelId);
		$eventCategory->setClosed($imIntegrationService->isChannelPrivate($channelId));

		$imIntegrationService->updateChannel($eventCategory);

		return $eventCategory->getClosed() ? $imIntegrationService->getChannelUsers($channelId) : [];
	}

	private function processCategoryCreationByRequest(
		int $userId,
		EventCategory $eventCategory,
		RequestDto\CreateEventCategoryDto $createEventCategoryDto
	): array
	{
		$eventCategory->setClosed($createEventCategoryDto->closed);
		$attendeesEntities = $createEventCategoryDto->attendees;
		$attendeeUserIds = [];
		if ($attendeesEntities && $createEventCategoryDto->closed)
		{
			$attendeeUserIds = CategoryAttendeeService::getInstance()->processAttendeesForNewCategory(
				$userId,
				$eventCategory,
				$attendeesEntities,
			);
		}

		$departmentIds = [];
		if ($createEventCategoryDto->closed || $createEventCategoryDto->isPrimary)
		{
			$departmentIds = $createEventCategoryDto->departmentIds;
		}

		/** @var Integration\Im\EventCategoryServiceInterface $imIntegrationService */
		$imIntegrationService = ServiceLocator::getInstance()->get(Integration\Im\EventCategoryServiceInterface::class);
		$channelId = $imIntegrationService->createChannel($eventCategory, $attendeeUserIds, $departmentIds);
		$eventCategory->setChannelId($channelId);

		return $attendeeUserIds;
	}

	private function getEventCategoryMapper(): EventCategoryMapper
	{
		/** @var Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');

		return $mapperFactory->getEventCategory();
	}

	private function __construct()
	{
	}
}
