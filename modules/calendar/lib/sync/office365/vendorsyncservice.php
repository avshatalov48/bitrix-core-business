<?php

namespace Bitrix\Calendar\Sync\Office365;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Sync\Exceptions\ApiException;
use Bitrix\Calendar\Sync\Exceptions\AuthException;
use Bitrix\Calendar\Sync\Exceptions\ConflictException;
use Bitrix\Calendar\Sync\Exceptions\GoneException;
use Bitrix\Calendar\Sync\Exceptions\NotFoundException;
use Bitrix\Calendar\Sync\Exceptions\RemoteAccountException;
use Bitrix\Calendar\Sync\Internals\HasContextTrait;
use Bitrix\Calendar\Sync\Office365\Dto\DateTimeDto;
use Bitrix\Calendar\Sync\Office365\Dto\EventDto;
use Bitrix\Calendar\Sync\Office365\Dto\SectionDto;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\LoaderException;
use Generator;

class VendorSyncService
{
	use HasContextTrait;

	/**
	 * @var ApiService
	 */
	private ApiService $apiService;

	/**
	 * @param Office365Context $context
	 *
	 * @throws BaseException
	 * @throws RemoteAccountException
	 * @throws AuthException
	 * @throws LoaderException
	 */
	public function __construct(Office365Context $context)
	{
		$this->context = $context;
		$this->apiService = $context->getApiService();
	}

	/**
	 * @param array $params
	 *
	 * @return SectionDto[]
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws AuthException
	 * @throws ConflictException
	 * @throws NotFoundException
	 * @throws GoneException
	 */
	public function getSections(array $params = []): array
	{
		$result = $this->apiService->getCalendarList($params);
		return array_map(function ($row){
			return new SectionDto($row);
		}, $result);
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws AuthException
	 * @throws ConflictException
	 * @throws GoneException
	 * @throws NotFoundException
	 */
	public function getEvents(array $params): array
	{
		$result = $this->apiService->getEventList($params);

		return array_map(function ($row){
			return new EventDto($row);
		}, $result);
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws AuthException
	 * @throws ConflictException
	 * @throws GoneException
	 * @throws NotFoundException
	 */
	public function getEvent(array $params): array
	{
		$result = $this->apiService->getEvent($params);

		return array_map(function ($row){
			return new EventDto($row);
		}, $result);
	}

	/**
	 * @param SectionDto $sectionDto
	 *
	 * @return SectionDto
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws AuthException
	 * @throws ConflictException
	 * @throws GoneException
	 * @throws NotFoundException
	 */
	public function createSection(SectionDto $sectionDto): SectionDto
	{
		$newSection = $this->apiService->createSection($sectionDto);

		return new SectionDto($newSection);
	}

	/**
	 * @param SectionDto $sectionDto
	 *
	 * @return SectionDto
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws AuthException
	 * @throws ConflictException
	 * @throws GoneException
	 * @throws NotFoundException
	 */
	public function updateSection(SectionDto $sectionDto): SectionDto
	{
		$newSection = $this->apiService->updateSection($sectionDto);

		return new SectionDto($newSection);
	}

	/**
	 * @param EventDto $dto
	 * @param string $sectionId
	 *
	 * @return EventDto
	 *
	 * @throws ApiException
	 * @throws AuthException
	 * @throws ConflictException
	 * @throws GoneException
	 * @throws NotFoundException
	 * @throws ArgumentException
	 */
	public function createEvent(EventDto $dto, string $sectionId): ?EventDto
	{
		if ($newEvent = $this->apiService->createEvent($dto, $sectionId))
		{
			return new EventDto($newEvent);
		}

		return null;
	}

	/**
	 * @param SectionConnection $sectionLink
	 *
	 * @return Generator|array{exceptions: ?array, seriesMaster: ?EventDto, singleInstance: ?EventDto}
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws BaseException
	 * @throws ConflictException
	 * @throws NotFoundException
	 */
	public function getCalendarDelta(SectionConnection $sectionLink): Generator
	{
		foreach ($this->apiService->getCalendarDelta($sectionLink) as $batch)
		{
			$events = [];
			foreach ($batch as $item) {
				if (!empty($item['@removed']))
				{
					$events[$item['id']][Helper::EVENT_TYPES['deleted']] = new EventDto($item);
				}
				elseif ($item['type'] === Helper::EVENT_TYPES['single'])
				{
					$events[$item['id']][$item['type']] = new EventDto($item);
				}
				elseif ($item['type'] === Helper::EVENT_TYPES['series'])
				{
					$events[$item['id']][$item['type']] = new EventDto($item);
				}
				elseif ($item['type'] === Helper::EVENT_TYPES['exception'])
				{
					$events[$item['seriesMasterId']][Helper::EVENT_TYPES['exception']][$item['id']] = new EventDto($item);
					$events[$item['seriesMasterId']][Helper::EVENT_TYPES['occurrence']][] = new DateTimeDto($item['start']);
				}
				elseif ($item['type'] === Helper::EVENT_TYPES['occurrence'])
				{
					$events[$item['seriesMasterId']][Helper::EVENT_TYPES['occurrence']][] = new DateTimeDto($item['start']);
				}
			}
			foreach ($events as $id => $eventDelta)
			{
				yield $id => $eventDelta;
			}
		}
	}

	/**
	 * @param string $vendorEventId
	 * @param EventDto $eventDto
	 *
	 * @return EventDto
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws AuthException
	 * @throws ConflictException
	 * @throws GoneException
	 * @throws NotFoundException
	 */
	public function updateEvent(string $vendorEventId, EventDto $eventDto): ?EventDto
	{
		if ($event = $this->apiService->updateEvent($eventDto, $vendorEventId))
		{
			return new EventDto($event);
		}

		return null;
	}

	/**
	 * @param array $params
	 *
	 * @return EventDto[]
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws AuthException
	 * @throws ConflictException
	 * @throws GoneException
	 * @throws NotFoundException
	 */
	public function getEventInstances(array $params): array
	{
		$result = $this->apiService->getEventInstances($params);

		if (!empty($result['value']))
		{
			return array_map(function ($row){
				return new EventDto($row);
			}, $result['value']) ?? [];
		}

		return [];
	}

	/**
	 * @param string $vendorEventId
	 *
	 * @return void
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws AuthException
	 * @throws ConflictException
	 * @throws GoneException
	 * @throws NotFoundException
	 */
	public function deleteEvent(string $vendorEventId)
	{
		$this->apiService->deleteEvent($vendorEventId);
	}

	/**
	 * @param SectionDto $dto
	 *
	 * @return void
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws AuthException
	 * @throws ConflictException
	 * @throws GoneException
	 * @throws NotFoundException
	 */
	public function deleteSection(SectionDto $dto)
	{
		$this->apiService->deleteSection($dto->id);
	}

	/**
	 * @param SectionConnection $link
	 *
	 * @return array|null
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws AuthException
	 * @throws ConflictException
	 * @throws GoneException
	 * @throws NotFoundException
	 */
	public function subscribeSection(SectionConnection $link): ?array
	{
		$channelId = $this->getChannelId($link);
		$result = $this->apiService->addSectionSubscription(
			$link->getVendorSectionId(),
			$channelId
		);

		if ($result)
		{
			$result['channelId'] = $channelId;
		}

		return $result;
	}

	/**
	 * @param string $subscribeId
	 *
	 * @return array
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws AuthException
	 * @throws ConflictException
	 * @throws GoneException
	 * @throws NotFoundException
	 */
	public function resubscribe(string $subscribeId): array
	{
		return $this->apiService->renewSectionSubscription($subscribeId);
	}

	/**
	 * @param string $subscribeId
	 *
	 * @return array
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws AuthException
	 * @throws ConflictException
	 * @throws GoneException
	 */
	public function unsubscribe(string $subscribeId): array
	{
		return $this->apiService->deleteSectionSubscription($subscribeId);
	}

	/**
	 * @param SectionConnection $link
	 * @return string
	 */
	private function getChannelId(SectionConnection $link): string
	{
		return 'BX_OFFICE_SC_' . $link->getConnection()->getOwner()->getId() . '_' . md5($link->getId() . time());
	}
}
