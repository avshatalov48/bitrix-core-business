<?php

namespace Bitrix\Calendar\Sync\Google;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Calendar\Sync\Connection\Server;
use Bitrix\Calendar\Sync\Entities\SyncEvent;
use Bitrix\Calendar\Sync\Entities\SyncEventMap;
use Bitrix\Calendar\Sync\Entities\SyncSection;
use Bitrix\Calendar\Sync\Entities\SyncSectionMap;
use Bitrix\Calendar\Sync\Google\Builders\BuilderSyncEventFromExternalData;
use Bitrix\Calendar\Sync\Google\Builders\BuilderSyncSectionFromExternalData;
use Bitrix\Calendar\Sync\Managers\IncomingEventManagerInterface;
use Bitrix\Calendar\Sync\Managers\IncomingSectionManagerInterface;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Main\Error;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

class ImportManager extends Manager implements IncomingSectionManagerInterface, IncomingEventManagerInterface
{
	private const CALENDAR_LIST_URL_CALENDAR_ID = '/users/me/calendarList/%CALENDAR_ID%';
	private const EVENT_LIST_URL_CALENDAR_ID = '/calendars/%CALENDAR_ID%/events';
	private const CALENDAR_LIST_URL = '/users/me/calendarList';
	private const CALENDAR_PRIMARY_ID = 'primary';
	public const SYNC_EVENTS_DATE_INTERVAL = '1 months';
	private const SYNC_EVENTS_LIMIT = 50;

	/**
	 * @var string
	 */
	protected ?string $syncToken = null;
	/**
	 * @var string
	 */
	protected ?string $pageToken = null;
	/**
	 * @var string|null
	 */
	protected ?string $etag = null;
	/**
	 * @var array|null
	 */
	protected ?array $defaultRemind = null;
	/**
	 * @var Date|null
	 */
	protected ?Date $modified = null;
	/**
	 * @var string|null
	 */
	protected ?string $lastSyncStatus = null;
	/**
	 * @var SectionConnection
	 */
	protected SectionConnection $syncSectionConnection;

	/**
	 * @return Result
	 */
	public function requestConnectionId(): Result
	{
		$result = new Result();
		try
		{
			// TODO: Remake it: move this logic to parent::request().
			// Or, better, in separate class.
			$this->httpClient->query(
				HttpClient::HTTP_GET,
				$this->prepareCalendarListUrlWithId(self::CALENDAR_PRIMARY_ID)
			);

			try
			{
				$externalResult = $this->parseResponse($this->httpClient->getResult());
			}
			catch (\Exception $e)
			{
				return $result->addError(new Error($e->getMessage()));
			}

			if ($this->isRequestSuccess())
			{
				$requestResult = $this->parseResponse($this->httpClient->getResult());

				return $result->setData(['id' => $requestResult['id']]);
			}
			$helper = new Helper();

			if ($helper->isNotValidSyncTokenError($this->prepareError($externalResult)))
			{
				$this->connection->setToken(null);
				$result->addError(new Error('Auth error on getting connection Id', 410));
				return $result;
			}

			if ($helper->isMissingRequiredAuthCredential($this->prepareError($externalResult)))
			{
				$this->handleUnauthorize($this->connection);
				$result->addError(new Error('Auth error on getting sections', 401));

				return $result;
			}

			return $result->addError(new Error('Do not sync sections'));
		}
		catch (\Exception $e)
		{
			return $result->addError(new Error('Failed to get connection name'));
		}
	}

	/**
	 * @return Result
	 */
	public function getSections(): Result
	{
		$result = new Result();
		//todo handle errors
		try
		{
			// TODO: Remake it: move this logic to parent::request().
			// Or, better, in separate class.
			$this->httpClient->query(
				HttpClient::HTTP_GET,
				$this->prepareCalendarListUrl()
			);

			try
			{
				$externalResult = $this->parseResponse($this->httpClient->getResult());
			}
			catch (\Exception $e)
			{
				return $result->addError(new Error($e->getMessage()));
			}

			if ($this->isRequestSuccess())
			{
				$this->connection->setToken($externalResult['nextSyncToken']);
				$this->etag = $externalResult['etag'];
				$this->connection->setStatus('[200] OK')
					->setLastSyncTime(new Date())
				;

				$map = new SyncSectionMap();
				if (!empty($externalResult['items']) && is_array($externalResult['items']))
				{
					foreach ($externalResult['items'] as $item)
					{
						$map->add(
							(new BuilderSyncSectionFromExternalData($item, $this->connection))->build(),
							$item['id']
						);
					}
				}

				return $result->setData(['externalSyncSectionMap' => $map]);
			}

			$helper = new Helper();

			if ($helper->isNotValidSyncTokenError($this->prepareError($externalResult)))
			{
				$this->connection->setToken(null);

				return $this->getSections();
			}

			if ($helper->isMissingRequiredAuthCredential($this->prepareError($externalResult)))
			{
				$this->handleUnauthorize($this->connection);
				$result->addError(new Error('Auth error on getting sections', 401));

				return $result;
			}

			$result->addError(new Error('Do not sync sections'));
		}
		catch (\Exception $e)
		{
			$result->addError(new Error('Failed to get sections'));
		}

		return $result;
	}

	/**
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ArgumentException|\Bitrix\Main\LoaderException
	 */
	public function getEvents(SyncSection $syncSection): Result
	{
		$map = new SyncEventMap();
		$result = (new Result())->setData([
			'externalSyncEventMap' => $map,
		]);

		try
		{
			// TODO: Remake it: move this logic to parent::request().
			// Or, better, in separate class.
			$this->syncSectionConnection = $syncSection->getSectionConnection();
			$this->httpClient->query(
				HttpClient::HTTP_GET,
				$this->prepareEventListUrl(
					$this->syncSectionConnection->getVendorSectionId(),
					$this->prepareRequestParams($this->syncSectionConnection)
				)
			);

			try
			{
				$externalResult = $this->parseResponse($this->httpClient->getResult());
			}
			catch (\Exception $e)
			{
				return $result->addError(new Error($e->getMessage()));
			}


			if ($this->isRequestSuccess())
			{
				$impatientInstanceListByUid = [];

				$this->etag = $externalResult['etag'];
				$this->syncToken = $externalResult['nextSyncToken'] ?? null;
				$this->pageToken = $externalResult['nextPageToken'] ?? null;
				$this->lastSyncStatus = \Bitrix\Calendar\Sync\Dictionary::SYNC_SECTION_ACTION['success'];

				$this->handleSuccessBehavior($externalResult);

				if (!empty($externalResult['items']) && is_array($externalResult['items']))
				{
					foreach ($externalResult['items'] as $item)
					{
						$syncEvent = (new BuilderSyncEventFromExternalData($item, $this->connection, $syncSection))
							->build();

						if ($syncEvent->isInstance() || $syncEvent->getVendorRecurrenceId())
						{
							/** @var SyncEvent $masterEvent */
							$masterEvent = $map->has($syncEvent->getVendorRecurrenceId())
								? $map->getItem($syncEvent->getVendorRecurrenceId())
								: null
							;

							if (!$masterEvent)
							{
								$impatientInstanceListByUid[$syncEvent->getVendorRecurrenceId()][] = $syncEvent;
								continue;
							}

							$masterEvent->addInstance($syncEvent);
						}
						else
						{
							if ($syncEvent->isRecurrence()
								&& ($instanceList = ($impatientInstanceListByUid[$syncEvent->getUid()] ?? null))
							)
							{
								$syncEvent->addInstanceList($instanceList);
								unset($impatientInstanceListByUid[$syncEvent->getUid()]);
							}

							$map->add($syncEvent, $syncEvent->getVendorEventId());
						}
					}
				}

				foreach ($impatientInstanceListByUid as $syncEventList)
				{
					foreach($syncEventList as $syncEvent)
					{
						$map->add($syncEvent, $syncEvent->getVendorEventId());
					}
				}

				return $result;
			}

			$helper = new Helper();

			if ($helper->isNotValidSyncTokenError($this->prepareError($externalResult)))
			{
				$syncSection->getSectionConnection()->setSyncToken(null);

				return $this->getEvents($syncSection);
			}

			if ($helper->isMissingRequiredAuthCredential($this->prepareError($externalResult)))
			{
				$this->handleUnauthorize($this->connection);
				$result->addError(new Error('Auth error on getting events', 401));

				return $result;
			}

			$this->handleErroneousBehavior($syncSection);
		}
		catch (BaseException $e)
		{
			$result->addError(new Error($e->getMessage()));
		}

		return $result;
	}

	/**
	 * @return Result
	 */
	public function getSectionConnection(): Result
	{
		return (new Result())->setData(['sectionConnection' => new SectionConnection()]);
	}

	/**
	 * @return string|null
	 */
	public function getStatus(): ?string
	{
		return $this->lastSyncStatus;
	}

	/**
	 * @return Result
	 */
	public function getConnection(): Result
	{
		return (new Result())->setData([
			'connection' => $this->connection,
		]);
	}

	/**
	 * @return string
	 */
	public function getSyncToken(): ?string
	{
		return $this->syncToken;
	}

	/**
	 * @return string
	 */
	public function getPageToken(): ?string
	{
		return $this->pageToken;
	}

	/**
	 * @return string
	 */
	public function getEtag(): ?string
	{
		return $this->etag;
	}

	/**
	 * @return array|null
	 */
	public function getDefaultRemind(): ?array
	{
		return $this->defaultRemind;
	}

	/**
	 * @return Date
	 */
	public function getModified(): ?Date
	{
		return $this->modified;
	}

	/**
	 * @param string $result
	 * @return mixed
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function prepareSectionResult(string $result)
	{
		return Json::decode($result);
	}

	/**
	 * @return string
	 */
	private function prepareCalendarListUrl(): string
	{
		$requestParams = '';

		if ($token = $this->connection->getToken())
		{
			$requestParams = '?' . http_build_query([
					'showDeleted' => 'true',
					'showHidden' => 'true',
					'syncToken' => $token,
				]);
			$requestParams = preg_replace('/(%3D)/', '=', $requestParams);
		}

		return $this->connection->getVendor()->getServer()->getFullPath()
			. self::CALENDAR_LIST_URL
			. $requestParams
		;
	}

	/**
	 * @param string $calendarId
	 * @return string
	 */
	private function prepareCalendarListUrlWithId(string $calendarId): string
	{
		return Server::mapUri(
			$this->connection->getVendor()->getServer()->getFullPath() . self::CALENDAR_LIST_URL_CALENDAR_ID,
			[
				'%CALENDAR_ID%' => $calendarId,
			]
		);
	}

	/**
	 * @param string $calendarId
	 * @param array $requestParams
	 * @return string
	 */
	private function prepareEventListUrl(string $calendarId, array $requestParams = []): string
	{
		$url = Server::mapUri(
			$this->connection->getVendor()->getServer()->getFullPath() . self::EVENT_LIST_URL_CALENDAR_ID,
			[
				'%CALENDAR_ID%' => urlencode($calendarId),
			]
		);

		if (!empty($requestParams))
		{
			$url .= '?' . preg_replace('/(%3D)/', '=', http_build_query($requestParams));
		}

		return $url;
	}

	/**
	 * @param string $result
	 * @return mixed
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function parseResponse(string $result)
	{
		return Json::decode($result);
	}

	/**
	 * @param SyncSection $syncSection
	 * @return void
	 */
	private function handleErroneousBehavior(SyncSection $syncSection): void
	{
		$sectionConnection = $syncSection->getSectionConnection();
		if ($sectionConnection)
		{
			$this->syncToken = $sectionConnection->getSyncToken();
			$this->pageToken = $sectionConnection->getPageToken();
			$this->etag = $sectionConnection->getVersionId();
			$this->modified = $sectionConnection->getLastSyncDate();
		}
	}

	/**
	 * @param $result
	 * @return void
	 * @throws \Bitrix\Main\ObjectException
	 */
	private function handleSuccessBehavior($result): void
	{
		$this->syncSectionConnection->setSyncToken($result['nextSyncToken'] ?? null);
		$this->syncSectionConnection->setPageToken($result['nextPageToken'] ?? null);
		// $this->pageToken = $result['nextPageToken'];
		// $this->etag = $result['etag'];
		$this->syncSectionConnection->setVersionId($result['etag'] ?? null);
		$this->defaultRemind = $result['defaultReminders'][0] ?? null;
		$this->modified = Date::createDateTimeFromFormat($result['updated'],
			Helper::DATE_TIME_FORMAT_WITH_MICROSECONDS);
		// TODO: Remake it: Overdependence from $this->httpClient
		$status = $this->httpClient->getStatus();
		$this->lastSyncStatus = \Bitrix\Calendar\Sync\Dictionary::SYNC_SECTION_ACTION['success'];
	}

	/**
	 * @param SectionConnection $sectionConnection
	 * @return array
	 */
	private function getRequestParamsWithSyncToken(SectionConnection $sectionConnection): array
	{
		return [
			'pageToken' => $sectionConnection->getPageToken(),
			'syncToken' => $sectionConnection->getSyncToken(),
			'showDeleted' => 'true',
		];
	}

	/**
	 * @param SectionConnection $sectionConnection
	 * @return array
	 */
	private function getRequestParamsForFirstSync(SectionConnection $sectionConnection): array
	{
		return [
			'pageToken' => $sectionConnection->getPageToken(),
			'showDeleted' => 'true',
			'maxResults' => self::SYNC_EVENTS_LIMIT,
			'timeMin' => (new Date())->sub(self::SYNC_EVENTS_DATE_INTERVAL)->format(Helper::DATE_TIME_FORMAT_WITH_MICROSECONDS),
		];
	}

	/**
	 * @param array|null $error
	 *
	 * @return string
	 */
	public function prepareError(array $error = null): string
	{
		if (
			isset($error['error']['code'], $error['error']['message'])
			&& $error !== null
		)
		{
			return '['
				. $error['error']['code']
				. ']'
				. ' '
				. $error['error']['message']
			;
		}

		return '';
	}
	/**
	 * @param SectionConnection $sectionConnection
	 * @return array
	 */
	private function prepareRequestParams(SectionConnection $sectionConnection): array
	{
		return array_filter($sectionConnection->getSyncToken()
			? $this->getRequestParamsWithSyncToken($sectionConnection)
			: $this->getRequestParamsForFirstSync($sectionConnection)
		);
	}
}
