<?php

namespace Bitrix\Calendar\Sync\Google;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Connection\EventConnection;
use Bitrix\Calendar\Sync\Connection\Server;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync\Entities\InstanceMap;
use Bitrix\Calendar\Sync\Entities\SyncEvent;
use Bitrix\Calendar\Sync\Entities\SyncEventMap;
use Bitrix\Calendar\Sync\Entities\SyncSection;
use Bitrix\Calendar\Sync\Entities\SyncSectionMap;
use Bitrix\Calendar\Sync\Google\Builders\BuilderEventConnectionFromExternalEvent;
use Bitrix\Calendar\Sync\Managers\OutgoingEventManagerInterface;
use Bitrix\Calendar\Sync\Util\EventContext;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Calendar\Util;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use DateTimeInterface;
use LogicException;

class OutgoingEventManager extends Manager implements OutgoingEventManagerInterface
{
	public const LINE_SEPARATOR = "\r\n";
	public const BATCH_PATH = 'https://www.googleapis.com/batch/calendar/v3/';
	public const CHUNK_LENGTH = 50;

	/**
	 * @param SyncEventMap $syncEventMap
	 * @param SyncSectionMap $syncSectionMap
	 *
	 * @return Result
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws LoaderException
	 * @throws ObjectException
	 */
	public function export(SyncEventMap $syncEventMap, SyncSectionMap $syncSectionMap): Result
	{
		$result = new Result();
		$result->setData([
			'syncEventMap' => $syncEventMap,
		]);

		$syncEventListForExport = [];
		$delayExportSyncEventList = [];

		/** @var SyncEvent $syncEvent */
		foreach ($syncEventMap as $syncEvent)
		{
			if (
				$syncEvent->getEventConnection()
				&& ($syncEvent->getEvent()->getVersion() === $syncEvent->getEventConnection()->getVersion())
			)
			{
				continue;
			}

			if (
				$syncEvent->isRecurrence()
				&& $instanceMap =  $syncEvent->getInstanceMap()
			)
			{
				if (empty($delayExportSyncEventList[$syncEvent->getEvent()->getSection()->getId()]))
				{
					$delayExportSyncEventList[$syncEvent->getEvent()->getSection()->getId()] = [];
				}

				array_push($delayExportSyncEventList[$syncEvent->getEvent()->getSection()->getId()], ...$instanceMap);
			}

			$syncEventListForExport[$syncEvent->getEvent()->getSection()->getId()][$syncEvent->getEvent()->getUid()] = $syncEvent;
		}

		/** @var SyncSection $syncSection */
		foreach ($syncSectionMap as $syncSection)
		{
			if ($syncEventList = ($syncEventListForExport[$syncSection->getSection()->getId()] ?? null))
			{
				$this->exportBatch(
					$syncEventList,
					$syncSection,
					$delayExportSyncEventList[$syncSection->getSection()->getId()] ?? null
				);
			}
		}

		return new Result();
	}

	/**
	 * @throws ObjectException
	 * @throws LoaderException
	 * @throws ArgumentException
	 * @throws BaseException
	 */
	private function exportBatch(array $syncEventList, SyncSection $syncSection, ?array $syncEventInstanceList = null): void
	{

		// single or recurrence
		foreach (array_chunk($syncEventList, self::CHUNK_LENGTH) as $batch)
		{
			$body = $this->prepareMultipartMixed($batch, $syncSection);
			// TODO: Remake it: move this logic to parent::request().
			// Or, better, in separate class.
			$this->httpClient->post(self::BATCH_PATH, $body);

			$this->multipartDecode($this->httpClient->getResult(), $syncEventList);
		}

		// instances
		if ($syncEventInstanceList !== null)
		{
			foreach (array_chunk($syncEventInstanceList, self::CHUNK_LENGTH) as $batch)
			{
				$body = $this->prepareMultipartMixed($batch, $syncSection, $syncEventList);
				// TODO: Remake it: move this logic to parent::request().
				// Or, better, in separate class.
				$this->httpClient->post(self::BATCH_PATH, $body);

				$this->multipartDecode($this->httpClient->getResult(), $syncEventList);
			}
		}
	}

	/**
	 * @throws ObjectException
	 * @throws ArgumentException
	 * @throws BaseException
	 */
	private function prepareMultipartMixed(
		array $eventCollection,
		SyncSection $syncSection,
		array $syncEventList = []
	): string
	{
		$boundary = $this->generateBoundary();
		$this->setContentTypeHeader($boundary);
		$data = implode('', $this->getBatchItemList(
			$eventCollection,
			$syncSection,
			$syncEventList,
			$boundary,
		));
		$data .= "--{$boundary}--" . self::LINE_SEPARATOR;

		return $data;
	}

	/**
	 * @param SyncEvent $syncEvent
	 * @return string
	 */
	private function calculateHttpMethod(SyncEvent $syncEvent): string
	{
		if (
			$syncEvent->isInstance()
			|| ($syncEvent->getEventConnection() && $syncEvent->getEventConnection()->getVendorEventId())
		)
		{
			return HttpClient::HTTP_PUT;
		}

		if ($syncEvent->getAction() === Dictionary::SYNC_EVENT_ACTION['delete'])
		{
			return HttpClient::HTTP_DELETE;
		}

		return HttpClient::HTTP_POST;
	}

	/**
	 * @param $response
	 * @param SyncEventMap $syncEventMap
	 * @return void
	 */
	private function multipartDecode($response, array $syncEventList): void
	{
		$boundary = $this->httpClient->getClient()->getHeaders()->getBoundary();

		$response = str_replace("--$boundary--", "--$boundary", $response);
		$parts = explode("--$boundary" . self::LINE_SEPARATOR, $response);

		foreach ($parts as $part)
		{
			$part = trim($part);
			if (!empty($part))
			{
				$partEvent = explode(self::LINE_SEPARATOR . self::LINE_SEPARATOR, $part);
				$data = $this->getMetaInfo($partEvent[1]);


				$eventId = $this->getId($partEvent[0]);
				if ($eventId === null)
				{
					continue;
				}

				try
				{
					$parsedData = Json::decode($partEvent[2]);
				}
				catch (ArgumentException $e)
				{
					continue;
				}

				if ($data['status'] === 200)
				{

					/** @var SyncEvent $masterEvent */
					$syncEvent = $syncEventList[$parsedData['iCalUID']];

					if ($syncEvent === null)
					{
						continue;
					}

					if ($syncEvent->hasInstances() && isset($parsedData['originalStartTime']))
					{
						$syncEvent = $this->getInstanceByOriginalDate($syncEvent, $parsedData);

						// TODO: it's workaround to skip errors
						if ($syncEvent === null)
						{
							continue;
						}
					}

					$eventConnection = (new BuilderEventConnectionFromExternalEvent(
						$parsedData,
						$syncEvent,
						$this->connection
					))->build();

					$syncEvent
						->setEventConnection($eventConnection)
						->setAction(Dictionary::SYNC_EVENT_ACTION['success'])
					;
				}
				elseif (isset($parsedData['error']['code'], $parsedData['error']['message']))
				{
					return;
				}
			}
		}
	}

	/**
	 * @param $headers
	 * @return array
	 */
	private function getMetaInfo($headers): array
	{

		$data = [];
		foreach (explode("\n", $headers) as $k => $header)
		{
			if($k === 0 && preg_match('#HTTP\S+ (\d+)#', $header, $find))
			{
				$data['status'] = (int)$find[1];
				continue;
			}

			if(mb_strpos($header, ':') !== false)
			{
				[$headerName, $headerValue] = explode(':', $header, 2);
				if(mb_strtolower($headerName) === 'etag')
				{
					$data['etag'] = trim($headerValue);
				}
			}
		}

		return $data;
	}

	/**
	 * @param $headers
	 * @return int|null
	 */
	private function getId($headers): ?int
	{
		foreach (explode("\n", $headers) as $header)
		{
			if(mb_strpos($header, ':') !== false)
			{
				[$headerName, $headerValue] = explode(':', $header, 2);
				if(mb_strtolower($headerName) === 'content-id')
				{
					$part = explode(':', $headerValue);
					return (int)rtrim($part[1], '>');
				}
			}
		}

		return null;
	}

	/**
	 * @param SyncEvent|null $masterEvent
	 * @param SyncEvent $syncEvent
	 * @param EventContext $eventContext
	 *
	 * @return void
	 */
	private function prepareEventContextForInstance(
		?SyncEvent $masterEvent,
		SyncEvent $syncEvent,
		EventContext $eventContext
	): void
	{
		/** @var SyncEvent $masterEvent */
		if ($masterEvent && $masterEvent->isSuccessAction())
		{
			$masterVendorEventId = $masterEvent->getVendorEventId();
		}
		else
		{
			//todo handle instance. possible write to log
			return;
		}

		$prefix = $syncEvent->getEvent()->isFullDayEvent()
			? $syncEvent->getEvent()->getOriginalDateFrom()->format('Ymd')
			: $syncEvent->getEvent()->getOriginalDateFrom()->setTimeZone(Util::prepareTimezone())->format('Ymd\THis\Z')
		;
		$eventContext->setEventConnection(
			(new EventConnection())
				->setVendorEventId(
					$masterVendorEventId
					. '_'
					. $prefix
				)
				->setRecurrenceId($masterVendorEventId)
		);
	}

	/**
	 * @param SyncEvent $syncEvent
	 * @return EventConverter
	 */
	private function getEventConverter(SyncEvent $syncEvent): EventConverter
	{
		return new EventConverter(
			$syncEvent->getEvent(),
			$syncEvent->getEventConnection(),
			$syncEvent->getInstanceMap()
		);
	}

	/**
	 * @param SyncEvent $masterEvent
	 * @param $event
	 * @return SyncEvent|null
	 */
	private function getInstanceByOriginalDate(SyncEvent $masterEvent, $event): ?SyncEvent
	{
		if (isset($event['originalStartTime']['dateTime']))
		{
			$eventOriginalStart = Date::createDateTimeFromFormat(
				$event['originalStartTime']['dateTime'],
				DateTimeInterface::ATOM
			);
		}
		elseif (isset($event['originalStartTime']['date']))
		{
			$eventOriginalStart = Date::createDateFromFormat(
				$event['originalStartTime']['date'],
				Helper::DATE_FORMAT
			);
		}

		return $masterEvent
			->getInstanceMap()
			->getItem(InstanceMap::getKeyByDate($eventOriginalStart));
	}

	/**
	 * @param SyncEvent $masterEvent
	 * @param SyncEvent $syncEvent
	 * @return void
	 */
	private function prepareEventForInstance(SyncEvent $masterEvent, SyncEvent $syncEvent): void
	{
		if ($syncEvent->getEvent()->getVersion() < $masterEvent->getEvent()->getVersion())
		{
			$syncEvent->getEvent()->setVersion($masterEvent->getEvent()->getVersion());
		}
	}

	/**
	 * @param SyncEvent $syncEvent
	 * @param SyncSection $syncSection
	 * @param array $syncEventList
	 * @param EventManager $eventManager
	 * @return array
	 * @throws BaseException
	 * @throws ObjectException
	 */
	private function prepareContextForHttpQuery(
		SyncEvent $syncEvent,
		SyncSection $syncSection,
		array $syncEventList,
		EventManager $eventManager
	): array
	{
		$method = $this->calculateHttpMethod($syncEvent);

		$eventContext = (new EventContext())->setSectionConnection($syncSection->getSectionConnection());
		if ($syncEvent->isInstance())
		{
			if ($eventConnection = $syncEvent->getEventConnection())
			{
				$eventContext->setEventConnection($eventConnection);
			}
			else
			{
				$this->prepareEventContextForInstance($syncEventList[$syncEvent->getUid()], $syncEvent, $eventContext);
				$this->prepareEventForInstance($syncEventList[$syncEvent->getUid()], $syncEvent);
				$syncEvent->setEventConnection($eventContext->getEventConnection());
			}

			if (
				($eventContext->getSectionConnection() === null)
				|| ($eventContext->getEventConnection() === null)
			)
			{
				throw new LogicException('you should set event or section info');
			}

			$methodHeader = $method . ' ' . $eventManager->prepareUpdateUrl($eventContext) . self::LINE_SEPARATOR;
			$converter = $this->getEventConverter($syncEvent);
			$vendorEvent = $converter->convertForUpdate();
		}
		elseif ($syncEvent->getEventConnection() !== null)
		{
			$eventContext->setEventConnection($syncEvent->getEventConnection());
			$methodHeader = $method . ' ' . $eventManager->prepareUpdateUrl($eventContext) . self::LINE_SEPARATOR;
			$converter = $this->getEventConverter($syncEvent);
			$vendorEvent = $converter->convertForUpdate();
		}
		elseif ($method !== Dictionary::SYNC_EVENT_ACTION['delete'])
		{
			$methodHeader = $method . ' ' . $eventManager->prepareCreateUrl($eventContext) . self::LINE_SEPARATOR;
			$converter = $this->getEventConverter($syncEvent);
			$vendorEvent = $converter->convertForCreate();
		}
		else
		{
			throw new LogicException('do not detect action');
		}

		return [$methodHeader, $vendorEvent];
	}

	/**
	 * @param string $boundary
	 * @param SyncEvent $syncEvent
	 * @param $vendorEvent
	 * @param $methodHeader
	 * @return string
	 * @throws ArgumentException
	 */
	private function prepareBatchItem(
		string $boundary,
		SyncEvent $syncEvent,
		array $vendorEvent,
		string $methodHeader
	): string
	{
		$data = '--' . $boundary . self::LINE_SEPARATOR;

		$data .= 'Content-Type: application/http' . self::LINE_SEPARATOR;

		$id = $syncEvent->getEvent()->getId();
		$data .= "Content-ID: item{$id}:{$id}" . self::LINE_SEPARATOR . self::LINE_SEPARATOR;

		$content = Json::encode($vendorEvent, JSON_UNESCAPED_SLASHES);

		$data .= $methodHeader;
		$data .= 'Content-type: application/json' . self::LINE_SEPARATOR;
		$data .= 'Content-Length: ' . mb_strlen($content) . self::LINE_SEPARATOR . self::LINE_SEPARATOR;

		$data .= $content;
		$data .= self::LINE_SEPARATOR . self::LINE_SEPARATOR;

		return $data;
	}

	/**
	 * @param array $eventCollection
	 * @param SyncSection $syncSection
	 * @param array $syncEventList
	 * @param string $boundary
	 * @param $data
	 * @param array $batchItems
	 * @return array
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws ObjectException
	 */
	private function getBatchItemList(
		array $eventCollection,
		SyncSection $syncSection,
		array $syncEventList,
		string $boundary
	): array
	{
		$batchItems = [];
		/*** @var SyncEvent $syncEvent */
		foreach ($eventCollection as $syncEvent)
		{
			try
			{
				$eventManager = new EventManager($this->connection, $this->userId);
				[$methodHeader, $vendorEvent] = $this->prepareContextForHttpQuery(
					$syncEvent,
					$syncSection,
					$syncEventList,
					$eventManager
				);

				$batchItems[] = $this->prepareBatchItem($boundary, $syncEvent, $vendorEvent, $methodHeader);
			}
			catch (LogicException $e)
			{
				// $syncEvent->setAction($this->calculateAction($syncEvent));
				continue;
			}
		}

		return $batchItems;
	}

	/**
	 * @return string
	 */
	private function generateBoundary(): string
	{
		return 'BXC' . md5(mt_rand() . time());
	}

	/**
	 * @param string $boundary
	 * @return void
	 */
	private function setContentTypeHeader(string $boundary): void
	{
		$this->httpClient->getClient()->setHeader('Content-type', 'multipart/mixed; boundary=' . $boundary);
	}

	/**
	 * @param SyncEventMap $syncEventMap
	 * @param int $eventId
	 * @return SyncEvent
	 */
	private function findSyncEvent(array $syncEventList, int $eventId): array
	{
		return array_filter($syncEventList, function (SyncEvent $syncEvent) use ($eventId) {
			if ($syncEvent->getEventId() === $eventId)
			{
				return true;
			}

			if ($syncEvent->hasInstances())
			{
				/** @var SyncEvent $instance */
				foreach ($syncEvent->getInstanceMap() as $instance)
				{
					if ($syncEvent->getEventId() === $eventId)
					{
						return true;
					}
				}
			}

			return false;
		});
	}

	private function calculateLastSyncStatusForFailedSyncEvent(SyncEvent $syncEvent, array $error)
	{
		if ($error['code'] === 404)
		{
			if (
				($error['message'] === 'Not Found')
				&& $syncEvent->getAction() === Dictionary::SYNC_EVENT_ACTION['update']
			)
			{
				$syncEvent->getEventConnection()->setLastSyncStatus(Dictionary::SYNC_STATUS['create']);
			}
		}
	}

	// /**
	//  * @param SyncEvent $syncEvent
	//  * @return void
	//  */
	// private function calculateAction(SyncEvent $syncEvent)
	// {
	// 	$syncEvent->setAction(Dictionary::SYNC_EVENT_ACTION['success']);
	// }
}