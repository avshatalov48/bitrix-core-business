<?php

namespace Bitrix\Calendar\Sync\Office365;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync\Exceptions\ApiException;
use Bitrix\Calendar\Sync\Exceptions\AuthException;
use Bitrix\Calendar\Sync\Exceptions\ConflictException;
use Bitrix\Calendar\Sync\Exceptions\GoneException;
use Bitrix\Calendar\Sync\Exceptions\NotFoundException;
use Bitrix\Calendar\Sync\Exceptions\RemoteAccountException;
use Bitrix\Calendar\Sync\Internals\ContextInterface;
use Bitrix\Calendar\Sync\Internals\HasContextTrait;
use Bitrix\Calendar\Sync\Office365\Dto\EventDto;
use Bitrix\Calendar\Sync\Office365\Dto\SectionDto;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\LoaderException;
use CCalendar;
use Generator;

class ApiService
{
	use HasContextTrait;

	/** @var Helper */
	private $helper;
	/** @var ApiClient */
	private $apiClient;

	/**
	 * @param ContextInterface $context
	 *
	 * @throws BaseException
	 * @throws LoaderException
	 * @throws RemoteAccountException
	 * @throws AuthException
	 */
	public function __construct(ContextInterface $context)
	{
		/** @var Office365Context $context */
		$this->context = $context;
		$this->apiClient = $this->context->getApiClient();
		$this->helper = $this->context->getHelper();
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
	public function getCalendarList(array $params): array
	{
		$response = $this->apiClient->get('me/calendars', $params);

		return (array) $response['value'];
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
	public function getEventList(array $params): array
	{
		$response = $this->apiClient->get(
			'me/calendars/' . $params['filter']['section_id'] . '/events',
			$params
		);

		return (array) $response['value'];
	}

	/**
	 * @param SectionDto $sectionDto
	 *
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
	public function createSection(SectionDto $sectionDto): array
	{
		return $this->apiClient->post('me/calendars?', array_filter($sectionDto->toArray()));
	}

	/**
	 * @param SectionDto $sectionDto
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
	public function updateSection(SectionDto $sectionDto): array
	{
		return $this->apiClient->patch('me/calendars/' . $sectionDto->id, array_filter($sectionDto->toArray()));
	}

	/**
	 * @param EventDto $eventDto
	 * @param string $sectionId
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
	public function createEvent(EventDto $eventDto, string $sectionId): array
	{
		return $this->apiClient->post(
			'me/calendars/' . $sectionId . '/events',
			array_filter($eventDto->toArray(true), static function ($val) {
				return $val !== [] && $val !== null;
			})
		);
	}

	/**
	 * @param SectionConnection $sectionConnection
	 *
	 * @return Generator
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws BaseException
	 * @throws ConflictException
	 * @throws NotFoundException
	 */
	public function getCalendarDelta(SectionConnection $sectionConnection): Generator
	{
		$baseUri = 'me/calendars/' . $sectionConnection->getVendorSectionId() . '/calendarView/delta?';
		$breakingFlag = false;
		do {
			$uri = $this->getDeltaUri($sectionConnection, $baseUri);

			try
			{
				$response = $this->apiClient->get($uri);
				if (!empty($response))
				{
					$breakingFlag = $this->processResponseAfterDelta($sectionConnection, $response);
				}
				if (!empty($response['value']))
				{
					yield $response['value'];
				}
				else
				{
					break;
				}
			}
			catch(GoneException $e)
			{
				if ($sectionConnection->getPageToken())
				{
					$sectionConnection->setPageToken(null);
				}
				elseif ($sectionConnection->getSyncToken())
				{
					$sectionConnection->setSyncToken(null);
				}
			}
		}
		while(!$breakingFlag);
	}

	/**
	 * @param string $uri
	 * @param string $name
	 *
	 * @return string|null
	 */
	private function getUriParam(string $uri, string $name): ?string
	{
		$result = null;
		if ($urlData = parse_url($uri))
		{
			parse_str($urlData['query'], $params);
			$result = $params[$name] ?? null;
		}

		return $result;
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
		return $this->apiClient->get(
			'me/events/' . $params['filter']['event_id'],
			$params
		);
	}

	/**
	 * @param EventDto $eventDto
	 * @param string $vendorEventId
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
	public function updateEvent(EventDto $eventDto, string $vendorEventId): array
	{
		if ($eventDto->isCancelled)
		{
			$response = $this->apiClient->post(
				'me/events/' . $vendorEventId . '/cancel',
				[
					'Comment' => 'Deleted from Bitrix',
				]
			);
		}
		else
		{
			try
			{
				$response = $this->apiClient->patch(
					'me/events/' . $vendorEventId,
					array_filter($eventDto->toArray(true), static function ($val) {
						return $val !== [] && $val !== null;
					})
				);
			}
			catch (NotFoundException $exception)
			{
				return [];
			}
		}

		return $response;
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
	public function getEventInstances(array $params): array
	{
		return $this->apiClient->get(
			'me/events/' . $params['filter']['event_id'] . '/instances',
			[
				'startDateTime' => $params['filter']['from'],
				'endDateTime' => $params['filter']['to'],
			]
		);
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
		try
		{
			$this->apiClient->delete(
				'me/events/' . $vendorEventId,
			);
		}
		catch (NotFoundException $exception)
		{
			return;
		}
	}

	/**
	 * @param string $vendorSectionId
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
	public function deleteSection(string $vendorSectionId)
	{
		$this->apiClient->delete(
			'me/calendars/' . $vendorSectionId,
		);
	}

	/**
	 * @param string $vendorSectionId
	 * @param string $state
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
	public function addSectionSubscription(string $vendorSectionId, string $state = ''): array
	{
		$data = [
			'changeType' => 'created,updated,deleted',
			'notificationUrl' => $this->getNotificationUrl(),
			'resource' => "me/calendars/$vendorSectionId/events",
			'expirationDateTime' => $this->getExpirationDateTime(),
			'clientState' => $state,
			'latestSupportedTlsVersion' => 'v1_2',
		];

		return $this->apiClient->post('subscriptions', $data);
	}

	/**
	 * @param string $subscriptionId
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
	public function renewSectionSubscription(string $subscriptionId): array
	{
		return $this->apiClient->patch('subscriptions/' . $subscriptionId, [
			'expirationDateTime' => $this->getExpirationDateTime(),
		]);
	}

	/**
	 * @param string $subscriptionId
	 *
	 * @return array
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws AuthException
	 * @throws ConflictException
	 * @throws GoneException
	 */
	public function deleteSectionSubscription(string $subscriptionId): array
	{
		try
		{
			return $this->apiClient->delete('subscriptions/' . $subscriptionId);
		}
		catch (NotFoundException|AuthException $exception)
		{
			return [];
		}
	}

	/**
	 * @param SectionConnection $sectionConnection
	 * @param string $baseUri
	 * @return string
	 */
	public function getDeltaUri(SectionConnection $sectionConnection, string $baseUri): string
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
			$interval = $this->helper->getDeltaInterval();
			$uri = $baseUri . 'startDateTime=' . $interval['from']->format($this->helper::TIME_FORMAT_LONG)
				. '&endDateTime=' . $interval['to']->format($this->helper::TIME_FORMAT_LONG);
		}
		return $uri;
	}

	/**
	 * @param SectionConnection $sectionConnection
	 * @param array $response
	 *
	 * @return bool
	 */
	private function processResponseAfterDelta(SectionConnection $sectionConnection, array $response): bool
	{
		$sectionConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['success']);
		$breakingFlag = true;

		if ($token = $this->getPageToken($response))
		{
			$sectionConnection->setPageToken($token);
			$breakingFlag = false;
		}
		elseif ($token = $this->getSyncToken($response))
		{
			$sectionConnection->setPageToken(null);
			$sectionConnection->setSyncToken($token);
		}
		else
		{
			$sectionConnection->setPageToken(null);
			$sectionConnection->setSyncToken(null);
		}

		return $breakingFlag;
	}

	/**
	 * @param array $response
	 *
	 * @return string|null
	 */
	private function getPageToken(array $response): ?string
	{
		return !empty($response['@odata.nextLink'])
			? $this->getUriParam($response['@odata.nextLink'], '$skiptoken')
			: null
			;
	}
	/**
	 * @param array $response
	 *
	 * @return string|null
	 */
	private function getSyncToken(array $response): ?string
	{
		return !empty($response['@odata.deltaLink'])
			? $this->getUriParam($response['@odata.deltaLink'], '$deltatoken')
			: null
			;
	}

	/**
	 * @return string
	 */
	private function getNotificationUrl(): string
	{
		return str_replace('http:', 'https:', CCalendar::GetServerPath())
			. $this->helper::PUSH_PATH;
	}

	/**
	 * @return string
	 */
	private function getExpirationDateTime(): string
	{
		$time = time() + 70 * 60 * 60;
		return date("c", $time);
	}
}
