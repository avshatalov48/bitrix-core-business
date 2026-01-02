<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Gateway;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Sync\Google\EventConverter;
use Bitrix\Calendar\Sync\Google\Helper;
use Bitrix\Calendar\Synchronization\Internal\Entity\EventConnection;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\AccessDeniedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\BadRequestException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\ConflictException;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\Google\RateLimitExceededException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\Google\SyncTokenNotValidException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotFoundException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\UnexpectedException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Dto\EventListResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Dto\EventResponse;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\HttpClient;

class GoogleEventGateway extends AbstractGoogleGateway
{
	/**
	 * @throws AccessDeniedException
	 * @throws BadRequestException
	 * @throws ConflictException
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws RateLimitExceededException
	 * @throws UnexpectedException
	 */
	public function createEvent(Event $event, string $googleCalendarId): EventResponse
	{
		try
		{
			$data = (new EventConverter($event))->convertForCreate();
		}
		catch (SystemException $e)
		{
			throw new UnexpectedException(
				sprintf('Unable to convert event "%d": "%s"', $event->getId(), $e->getMessage()),
				$e->getCode(),
				$e
			);
		}

		$data['extendedProperties'] = [
			'private' => [
				'bxId' => $event->getId(),
			],
		];

		$this->request(
			HttpClient::HTTP_POST,
			self::BASE_PATH . sprintf('/calendars/%s/events/', urlencode($googleCalendarId)),
			$this->encode($data),
		);

		if ($this->isRequestSuccess())
		{
			return EventResponse::fromJson($this->client->getResult());
		}

		$this->processErrors('Event was not created');
	}

	/**
	 * @throws AccessDeniedException
	 * @throws BadRequestException
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws RateLimitExceededException
	 * @throws UnexpectedException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function updateEvent(Event $event, EventConnection $eventConnection, string $googleCalendarId): EventResponse
	{
		$data = $this->buildEventDataForUpdate($event, $eventConnection);

		$this->request(
			HttpClient::HTTP_PUT,
			sprintf(
				'%s/calendars/%s/events/%s',
				self::BASE_PATH,
				urlencode($googleCalendarId),
				urlencode($eventConnection->getVendorEventId()),
			),
			$this->encode($data),
		);

		if ($this->isRequestSuccess())
		{
			return EventResponse::fromJson($this->client->getResult());
		}

		/** @noinspection PhpUnhandledExceptionInspection */
		$this->processErrors('Event was not updated');
	}

	/**
	 * @throws AccessDeniedException
	 * @throws BadRequestException
	 * @throws NotAuthorizedException
	 * @throws RateLimitExceededException
	 * @throws UnexpectedException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function deleteEvent(string $googleCalendarId, string $googleEventId): void
	{
		$this->request(
			HttpClient::HTTP_DELETE,
			sprintf(
				'%s/calendars/%s/events/%s',
				self::BASE_PATH,
				urlencode($googleCalendarId),
				urlencode($googleEventId),
			),
			$this->encode(
				[
					'sendUpdates' => 'all',
				],
			),
		);

		if ($this->isDeleteRequestSuccess())
		{
			return;
		}

		/** @noinspection PhpUnhandledExceptionInspection */
		$this->processErrors('Event was not deleted');
	}

	/**
	 * @throws AccessDeniedException
	 * @throws BadRequestException
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 * @throws RateLimitExceededException
	 */
	public function createInstance(Event $event, string $masterVendorEventId, string $googleCalendarId): EventResponse
	{
		$eventConnection = new EventConnection();

		$eventConnection
			->setVendorEventId($event->buildInstanceId($masterVendorEventId))
			->setRecurrenceId($masterVendorEventId)
		;

		return $this->updateEvent($event, $eventConnection, $googleCalendarId);
	}

	/**
	 * @throws AccessDeniedException
	 * @throws BadRequestException
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws RateLimitExceededException
	 * @throws UnexpectedException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function deleteInstance(
		Event $masterEvent,
		string $masterVendorEventId,
		string $googleCalendarId,
		Date $excludeDate,
		?Date $originalDate = null,
	): EventResponse
	{
		$instance = $this->getInstanceForDay($masterEvent, $excludeDate, $originalDate);

		$eventConnection = new EventConnection();

		$eventConnection
			->setVendorEventId($instance->buildInstanceId($masterVendorEventId))
			->setRecurrenceId($masterVendorEventId)
		;

		$data = $this->buildEventDataForDelete($instance, $eventConnection);

		$this->request(
			HttpClient::HTTP_PUT,
			sprintf(
				'%s/calendars/%s/events/%s',
				self::BASE_PATH,
				urlencode($googleCalendarId),
				urlencode($eventConnection->getVendorEventId()),
			),
			$this->encode($data),
		);

		if ($this->isRequestSuccess())
		{
			return EventResponse::fromJson($this->client->getResult());
		}

		/** @noinspection PhpUnhandledExceptionInspection */
		$this->processErrors('Event instance was not deleted');
	}

	/**
	 * @throws UnexpectedException
	 */
	private function buildEventDataForUpdate(Event $event, EventConnection $eventConnection): array
	{
		try
		{
			$data = (new EventConverter($event))->convertForCreate();
		}
		catch (SystemException $e)
		{
			throw new UnexpectedException(
				sprintf('Unable to convert event "%d": "%s"', $event->getId(), $e->getMessage()),
				$e->getCode(),
				$e
			);
		}

		$data['id'] = $eventConnection->getVendorEventId();

		if ($event->isInstance())
		{
			$data['recurringEventId'] = $eventConnection->getRecurrenceId();
		}

		$eventData = $eventConnection->getData();

		if (!empty($eventData['attendees']))
		{
			$data['attendees'] = $eventData['attendees'];
		}

		return $data;
	}

	/**
	 * @throws UnexpectedException
	 */
	private function buildEventDataForDelete(Event $event, EventConnection $eventConnection): array
	{
		$data = $this->buildEventDataForUpdate($event, $eventConnection);

		$data['status'] = 'cancelled';

		return $data;
	}

	private function getInstanceForDay(Event $event, Date $excludedDate, ?Date $originalDate = null): Event
	{
		$instanceEvent = clone $event;

		$instanceStart = $this->cloneWithReplacedDate($event->getStart(), $excludedDate);
		$instanceEvent->setStart($instanceStart);

		$instanceEnd = $this->cloneWithReplacedDate($event->getEnd(), $excludedDate);
		$instanceEvent->setEnd($this->cloneWithReplacedDate($instanceEnd, $excludedDate));

		$originalDateFrom = $originalDate ?? $instanceEvent->getStart();
		$instanceEvent
			->setOriginalDateFrom(clone $originalDateFrom)
			->setRecurringRule(null)
		;

		return $instanceEvent;
	}

	private function cloneWithReplacedDate(Date $date, Date $replacedDate): Date
	{
		$date = clone $date;

		$date->getDate()->setDate(
			$replacedDate->getYear(),
			$replacedDate->getMonth(),
			$replacedDate->getDay(),
		);

		return $date;
	}

	/**
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws RateLimitExceededException
	 * @throws SyncTokenNotValidException
	 * @throws UnexpectedException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function getEvents(SectionConnection $sectionConnection): EventListResponse
	{
		$this->request(
			HttpClient::HTTP_GET,
			$this->prepareEventsListUrl($sectionConnection),
		);

		if ($this->isRequestSuccess())
		{
			return EventListResponse::fromJson($this->client->getResult());
		}

		/** @noinspection PhpUnhandledExceptionInspection */
		$this->processErrors('Events list was not retrieved from Google');
	}

	private function prepareEventsListUrl(SectionConnection $sectionConnection): string
	{
		$url = sprintf('%s/calendars/%s/events', self::BASE_PATH, urlencode($sectionConnection->getVendorSectionId()));
		$url .= '?' . preg_replace('/(%3D)/', '=', http_build_query($this->buildListRequestParams($sectionConnection)));

		return $url;
	}

	private function buildListRequestParams(SectionConnection $sectionConnection): array
	{
		$params = [
			'pageToken' => $sectionConnection->getPageToken(),
			'showDeleted' => 'true',
		];

		if ($syncToken = $sectionConnection->getSyncToken())
		{
			$params['syncToken'] = $syncToken;
		}
		else
		{
			$params['maxResults'] = 50;
			$params['timeMin'] = (new Date())->sub('1 months')->format(Helper::DATE_TIME_FORMAT_WITH_MICROSECONDS);
		}

		return $params;
	}
}
