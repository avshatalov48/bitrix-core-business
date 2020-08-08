<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\EventJournal;

use Bitrix\Main\Type\DateTime;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\Api;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\ApiResult\Journal\Event;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\ApiResult\Journal\PriceChanged;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\ApiResult\Journal\StatusChanged;
use Sale\Handlers\Delivery\Taxi\Yandex\ClaimsTable;

/**
 * Class Processor
 * @package Sale\Handlers\Delivery\Taxi\Yandex\EventJournal
 */
class Processor
{
	const CLAIM_UPDATED_EVENT_CODE = 'OnClaimUpdated';

	/** @var Api */
	protected $api;

	/** @var array */
	private $claims = [];

	/** @var Event[] */
	private $events = [];

	/**
	 * Processor constructor.
	 * @param Api $api
	 */
	public function __construct(Api $api)
	{
		$this->api = $api;
	}

	/**
	 * @param array $events
	 */
	public function process(array $events)
	{
		$this->prepareClaims($events);
		$this->prepareEvents($events);
		$this->findChanges();
		$this->applyChanges();
	}

	/**
	 * @param Event[] $events
	 */
	private function prepareClaims(array $events)
	{
		$claimsIds = [];

		foreach ($events as $event)
		{
			$claimsIds[$event->getClaimId()] = true;
		}

		$claims = ClaimsTable::getList(
			[
				'filter' => ['=EXTERNAL_ID' => array_keys($claimsIds)],
			]
		)->fetchAll();

		$eventTypes = $this->getKnownEventTypes();
		foreach ($claims as $claim)
		{
			$this->claims[$claim['EXTERNAL_ID']] = [
				'CURRENT_ITEM' => $claim,
				'CHANGES' => array_combine($eventTypes, array_fill(0, count($eventTypes), null)),
			];
		}
	}

	/**
	 * @param Event[] $events
	 */
	private function prepareEvents(array $events)
	{
		foreach ($events as $event)
		{
			$claimId = $event->getClaimId();
			$eventCode = $event->getCode();

			if (!isset($this->events[$claimId]))
			{
				$this->events[$claimId] = [];
			}


			if (!isset($this->events[$claimId][$eventCode]))
			{
				$this->events[$claimId][$eventCode] = [];
			}
			$this->events[$claimId][$eventCode][] = $event;
		}
	}

	/**
	 * @return array
	 */
	private function getKnownEventTypes()
	{
		return [
			PriceChanged::EVENT_CODE,
			StatusChanged::EVENT_CODE,
		];
	}

	/**
	 * @return void
	 */
	private function findChanges()
	{
		$eventTypes = $this->getKnownEventTypes();
		foreach ($this->claims as $claimId => $claimItem)
		{
			$claim = $claimItem['CURRENT_ITEM'];

			if (!isset($this->events[$claimId]))
			{
				continue;
			}

			foreach ($eventTypes as $eventType)
			{
				if (!isset($this->events[$claimId][$eventType]))
				{
					continue;
				}

				/** @var Event[] $events */
				$events = $this->events[$claimId][$eventType];

				foreach ($events as $event)
				{
					$eventUpdatedAt = $this->readDate($event->getUpdatedTs());
					$currentUpdatedAt = $this->readDate($claim['EXTERNAL_UPDATED_TS']);

					if ($currentUpdatedAt >= $eventUpdatedAt)
					{
						continue;
					}

					/** @var Event|null $latestEvent */
					$latestEvent = $claimItem['CHANGES'][$eventType];
					if (is_null($latestEvent) ||
						$this->readDate($latestEvent->getUpdatedTs()) < $eventUpdatedAt
					)
					{
						$this->claims[$claimId]['CHANGES'][$eventType] = $event;
					}
				}
			}
		}
	}

	/**
	 * @return void
	 */
	private function applyChanges()
	{
		foreach ($this->claims as $claimId => $claimItem)
		{
			$newUpdatedTs = null;
			$fields = [];
			/*** @var Event $event */
			foreach ($claimItem['CHANGES'] as $eventType => $event)
			{
				if (is_null($event))
				{
					continue;
				}

				$fields += $event->provideUpdateFields();

				if (is_null($newUpdatedTs)
					|| $this->readDate($event->getUpdatedTs()) > $this->readDate($newUpdatedTs))
				{
					$newUpdatedTs = $event->getUpdatedTs();
				}
			}

			if (!$fields)
			{
				continue;
			}

			$this->updateClaim(
				$claimItem['CURRENT_ITEM']['ID'],
				$newUpdatedTs,
				$fields
			);
		}
	}

	/**
	 * @param int $id
	 * @param string $newUpdatedTs
	 * @param array $fields
	 */
	private function updateClaim(int $id, string $newUpdatedTs, array $fields)
	{
		$fields = array_merge(
			$fields,
			[
				'UPDATED_AT' => new DateTime(),
				'EXTERNAL_UPDATED_TS' => $newUpdatedTs,
			]
		);

		$updateResult = ClaimsTable::update($id, $fields);
		if ($updateResult->isSuccess())
		{
			(new \Bitrix\Main\Event(
				'sale',
				static::CLAIM_UPDATED_EVENT_CODE,
				[
					'ID' => $id,
					'FIELDS' => $fields,
				]
			))->send();
		}
	}

	/**
	 * @param string $dateTime
	 * @return bool|\DateTime
	 */
	private function readDate(string $dateTime)
	{
		return \DateTime::createFromFormat('Y-m-d\TH:i:s.uP', $dateTime);
	}
}
