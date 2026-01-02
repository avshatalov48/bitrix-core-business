<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Gateway;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Synchronization\Internal\Entity\EventConnection;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\AccessDeniedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\BadRequestException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotFoundException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\UnexpectedException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Dto\SyncTokenResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Dto\EventListResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Dto\EventResponse;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UuidGenerator;
use Bitrix\Main\Web\HttpClient;

class ICloudEventGateway extends AbstractICloudGateway
{
	/**
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 * @throws AccessDeniedException
	 * @throws BadRequestException
	 */
	public function createEvent(Event $event, string $iCloudCalendarId): EventResponse
	{
		$this->client->setHeader('Content-type', 'text/calendar');

		$iCloudEventId = UuidGenerator::generateV4();

		$uri = $this->getUri($iCloudCalendarId, $iCloudEventId);

		try
		{
			$data = $this->requestDataBuilder->buildSendEventData($event, $iCloudEventId);
		}
		catch (LoaderException|SystemException $e)
		{
			$this->processErrors(
				sprintf('Event was not created. Failed to build request data: "%s"', $e->getMessage()),
			);
		}

		$this->request(
			HttpClient::HTTP_PUT,
			$uri,
			$data,
		);

		if (!$this->isRequestSuccess())
		{
			$this->processErrors('Event was not created');
		}

		return $this->getEvent($uri);
	}

	/**
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 * @throws AccessDeniedException
	 * @throws BadRequestException
	 */
	public function updateEvent(Event $event, EventConnection $eventConnection, string $iCloudCalendarId): EventResponse
	{
		$this->client->setHeader('Content-type', 'text/calendar');

		$vendorEventId = $eventConnection->getVendorEventId();

		$uri = $this->getUri($iCloudCalendarId, $vendorEventId);

		try
		{
			$data = $this->requestDataBuilder->buildSendEventData($event, $vendorEventId);
		}
		catch (LoaderException|SystemException $e)
		{
			$this->processErrors(
				sprintf('Event was not updated. Failed to build request data: "%s"', $e->getMessage()),
			);
		}

		$this->request(
			HttpClient::HTTP_PUT,
			$uri,
			$data,
		);

		if (!$this->isRequestSuccess())
		{
			$this->processErrors('Event was not updated');
		}

		return $this->getEvent($uri);
	}

	/**
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 * @throws AccessDeniedException
	 */
	public function deleteEvent(string $iCloudCalendarId, string $iCloudEventId): void
	{
		$uri = $this->getUri($iCloudCalendarId, $iCloudEventId);

		$this->request(HttpClient::HTTP_DELETE, $uri);

		if (!$this->isDeleteRequestSuccess())
		{
			$this->processErrors('Event was not deleted');
		}
	}

	/**
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 * @throws AccessDeniedException
	 * @throws BadRequestException
	 */
	public function getEvents(SectionConnection $sectionConnection): EventListResponse
	{
		$this->client->setHeader('Depth', 1);
		$this->client->setHeader('Content-type', 'text/xml');

		$syncToken = $sectionConnection->getSyncToken();
		$initialSync = empty($syncToken);
		$uri = $this->serverPath . $sectionConnection->getVendorSectionId();

		$initialSyncTokenResponse = null;
		if ($initialSync)
		{
			$this->client->setHeader('Depth', 0);
			$getSectionSyncData = $this->requestDataBuilder->buildGetSectionSyncTokenData();

			$this->request(
				$this->davMethodProvider->getPropfindMethod(),
				$uri,
				$getSectionSyncData,
			);

			if (!$this->isDavRequestSuccess())
			{
				$this->processErrors('Calendar sync-token was not received');
			}

			$initialSyncTokenResponse = SyncTokenResponse::fromXml($this->client->getResult());

			$this->client->setHeader('Depth', 1);
		}

		$syncEventsData = $this->requestDataBuilder->buildSyncEventsData($initialSync ? null : $syncToken);

		try
		{
			$this->request(
				$this->davMethodProvider->getReportMethod(),
				$uri,
				$syncEventsData,
			);

			if (!$this->isDavRequestSuccess())
			{
				$this->processErrors('Event was not received');
			}
		}
		catch (AccessDeniedException $e)
		{
			if (!empty($syncToken) && str_contains($this->client->getResult(), 'valid-sync-token'))
			{
				// Sync token is no longer valid, need to re-sync from scratch
				$sectionConnection->setSyncToken(null);

				return $this->getEvents($sectionConnection);
			}

			throw $e;
		}

		$syncEventListResponse = EventListResponse::fromXml($this->client->getResult());

		$eventsResponses = $syncEventListResponse->getItems();

		if (empty($eventsResponses))
		{
			if ($initialSync && $initialSyncTokenResponse)
			{
				$syncEventListResponse->etag = $initialSyncTokenResponse->etag;
				$syncEventListResponse->nextSyncToken = $initialSyncTokenResponse->nextSyncToken;
			}

			return $syncEventListResponse;
		}

		$eventsResponsesHrefs = array_map(
			static fn(EventResponse $item): string => $item->href,
			$eventsResponses,
		);

		$getEventsData = $this->requestDataBuilder->buildGetEventsData($eventsResponsesHrefs);

		$this->request(
			$this->davMethodProvider->getReportMethod(),
			$uri,
			$getEventsData,
		);

		if (!$this->isDavRequestSuccess())
		{
			$this->processErrors('Event was not received');
		}

		$filledEventListResponse = EventListResponse::fromXml($this->client->getResult());

		if ($initialSync && $initialSyncTokenResponse)
		{
			$filledEventListResponse->etag = $initialSyncTokenResponse->etag;
			$filledEventListResponse->nextSyncToken = $initialSyncTokenResponse->nextSyncToken;
		}
		else
		{
			$filledEventListResponse->etag = $syncEventListResponse->etag;
			$filledEventListResponse->nextSyncToken = $syncEventListResponse->nextSyncToken;
		}

		return $filledEventListResponse;
	}

	/**
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 * @throws AccessDeniedException
	 * @throws BadRequestException
	 */
	public function getEvent(string $uri): EventResponse
	{
		$this->client->setHeader('Depth', 2);
		$this->client->setHeader('Content-type', 'text/xml');

		$data = $this->requestDataBuilder->buildGetEventData();

		$this->request(
			$this->davMethodProvider->getPropfindMethod(),
			$uri,
			$data,
		);

		if (!$this->isDavRequestSuccess())
		{
			$this->processErrors('Event was not received');
		}

		return EventResponse::fromXml($this->client->getResult());
	}

	private function getUri(string $calendarId, string $eventId): string
	{
		return $this->serverPath . $calendarId . $eventId . '.ics';
	}
}
