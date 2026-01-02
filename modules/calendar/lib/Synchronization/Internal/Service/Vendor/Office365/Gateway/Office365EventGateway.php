<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Gateway;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Sync\Office365\Dto\EventDto;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\PreconditionFailedException;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\ApiException;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\ConflictException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\GoneException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotFoundException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\UnexpectedException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Dto\EventListResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Dto\EventResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Util\DeltaIntervalGenerator;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\HttpClient;

class Office365EventGateway extends AbstractOffice365Gateway
{
	public function __construct(
		HttpClient $client,
		protected readonly RequestParametersBuilder $requestParametersBuilder,
	)
	{
		parent::__construct($client);
	}

	/**
	 * @throws GoneException
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 */
	public function getDeltaEvents(SectionConnection $sectionConnection): EventListResponse
	{
		$baseUri = 'me/calendars/' . $sectionConnection->getVendorSectionId() . '/calendarView/delta?';

		$uri = $this->getDeltaUri($sectionConnection, $baseUri);

		$response = $this->get($uri);

		return EventListResponse::fromArray($response);
	}

	private function getDeltaUri(SectionConnection $sectionConnection, string $baseUri): string
	{
		if ($sectionConnection->getPageToken())
		{
			$uri = $baseUri . '$skiptoken=' . $sectionConnection->getPageToken();
		}
		elseif ($sectionConnection->getSyncToken())
		{
			$uri = $baseUri . '$deltatoken=' . $sectionConnection->getSyncToken();
		}
		else
		{
			$interval = ServiceLocator::getInstance()->get(DeltaIntervalGenerator::class)->getDeltaInterval();

			$uri = $baseUri . sprintf(
				'startDateTime=%s&endDateTime=%s',
				$interval['from']->format(DeltaIntervalGenerator::TIME_FORMAT_LONG),
				$interval['to']->format(DeltaIntervalGenerator::TIME_FORMAT_LONG)
			);
		}

		return $uri;
	}

	/**
	 * @throws DtoValidationException
	 * @throws GoneException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 */
	public function createEvent(Event $event, string $vendorSectionId): EventResponse
	{
		$params = $this->requestParametersBuilder->build($event);

		$response = $this->post("me/calendars/$vendorSectionId/events", $params);

		return EventResponse::fromArray($response);
	}

	/**
	 * @throws ConflictException
	 * @throws DtoValidationException
	 * @throws GoneException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws PreconditionFailedException
	 * @throws UnexpectedException
	 */
	public function updateEvent(Event $event, string $vendorEventId): EventResponse
	{
		if ($event->isDeleted())
		{
			$response = $this->post(
				"me/events/$vendorEventId/cancel",
				[
					'Comment' => 'Deleted from Bitrix',
				],
			);
		}
		else
		{
			$params = $this->requestParametersBuilder->build($event);

			$response = $this->patch("me/events/$vendorEventId", $params);
		}

		return EventResponse::fromArray($response);
	}

	/**
	 * @throws GoneException
	 * @throws NotAuthorizedException
	 * @throws UnexpectedException
	 */
	public function deleteEvent(string $vendorEventId): void
	{
		try
		{
			$this->delete("me/events/$vendorEventId");
		}
		catch (NotFoundException)
		{
			return;
		}
	}

	/**
	 * @throws GoneException
	 * @throws NotAuthorizedException
	 * @throws UnexpectedException
	 * @throws NotFoundException
	 */
	public function deleteInstance(string $masterVendorEventId, Date $excludedDate): void
	{
		$instance = $this->getInstanceForDay($masterVendorEventId, $excludedDate);

		if ($instance === null)
		{
			return;
		}

		try
		{
			$this->delete("me/events/$instance->id");
		}
		catch (NotFoundException)
		{
			return;
		}
	}

	/**
	 * @throws GoneException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 */
	public function getInstanceForDay(string $vendorEventId, Date $startDate): ?EventDto
	{
		$startDate = clone $startDate;

		if ($startDate instanceof DateTime)
		{
			$startDate->setTime(0,0);
		}

		$endDate = clone $startDate;

		$endDate->add('1 day');

		try
		{
			$instances = $this->getEventInstances(
				$vendorEventId,
				$startDate->format('c'),
				$endDate->format('c'),
			);
		}
		catch (ApiException $exception)
		{
			if (!in_array((int)$exception->getCode(), [400, 404], true))
			{
				throw $exception;
			}
		}

		return $instances[0] ?? null;
	}

	/**
	 * @return EventDto[]
	 *
	 * @throws GoneException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 */
	private function getEventInstances(
		string $vendorEventId,
		string $formattedStartDate,
		string $formattedEndDate,
	): array
	{
		$response = $this->get(
			"me/events/$vendorEventId/instances",
			[
				'startDateTime' => $formattedStartDate,
				'endDateTime' => $formattedEndDate,
			],
		);

		if (!empty($response['value']))
		{
			return array_map(function ($row) {
				return new EventDto($row);
			}, $response['value']) ?? [];
		}

		return [];
	}
}
