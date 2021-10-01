<?php

namespace Sale\Handlers\Delivery\YandexTaxi\EventJournal;

use Bitrix\Main\Error;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Delivery\Services\Table;
use Bitrix\Sale\Delivery\Requests\RequestTable;
use Sale\Handlers\Delivery\YandexTaxi\Api\Api;
use Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\Journal\Event;

/**
 * Class EventReader
 * @package Sale\Handlers\Delivery\YandexTaxi\EventJournal
 * @internal
 */
final class EventReader
{
	/** @var Api */
	protected $api;

	/**
	 * Reader constructor.
	 * @param Api $api
	 */
	public function __construct(Api $api)
	{
		$this->api = $api;
	}

	/**
	 * @param int $deliveryServiceId
	 * @param string|null $prevCursor
	 * @return EventCollectionResult
	 */
	public function read(int $deliveryServiceId, $prevCursor): EventCollectionResult
	{
		$result = new EventCollectionResult();
		$resultEvents = [];

		$cursor = null;

		do
		{
			if (!is_null($cursor))
			{
				$prevCursor = $cursor;
			}

			$getJournalRecordsResult = $this->api->getJournalRecords($prevCursor);
			if (!$getJournalRecordsResult->isSuccess())
			{
				return $result->addError(new Error('get_journal_records'));
			}

			$cursor = $getJournalRecordsResult->getCursor();
			$events = $getJournalRecordsResult->getEvents();

			foreach ($events as $event)
			{
				$resultEvents[] = $event;
			}
		} while ($prevCursor != $cursor);

		if (!is_null($cursor))
		{
			$this->updateCursor($deliveryServiceId, $cursor);
		}

		$filteredEvents = $this->filterEvents($deliveryServiceId, $resultEvents);
		foreach ($filteredEvents as $event)
		{
			$result->addEvent($event);
		}

		return $result;
	}

	/**
	 * @param int $deliveryServiceId
	 * @param Event[] $events
	 * @return Event[]
	 */
	private function filterEvents(int $deliveryServiceId, array $events): array
	{
		$deliveryServiceIds = array_column(
			Manager::getList(
				[
					'select' => ['ID'],
					'filter' => ['PARENT_ID' => $deliveryServiceId]
				]
			)->fetchAll(),
			'ID'
		);
		$deliveryServiceIds[] = $deliveryServiceId;

		$claimIds =	array_column(
			RequestTable::getList(
				[
					'filter' => [
						'=DELIVERY_ID' => $deliveryServiceIds,
						'=EXTERNAL_ID' => array_map(
							function ($event)
							{
								return $event->getClaimId();
							},
							$events
						),
					],
				]
			)->fetchAll(),
			'EXTERNAL_ID'
		);

		return array_filter(
			$events,
			function ($event) use ($claimIds)
			{
				return in_array($event->getClaimId(), $claimIds, true);
			}
		);
	}

	/**
	 * @param int $deliveryServiceId
	 * @param string $cursor
	 */
	private function updateCursor(int $deliveryServiceId, string $cursor): void
	{
		$service = Table::getList(
			[
				'filter' => [
					'=ID' => $deliveryServiceId
				]
			]
		)->fetch();

		$config = $service['CONFIG'];
		$config['MAIN']['CURSOR'] = $cursor;

		Manager::update($service['ID'], ['CONFIG' => $config]);
	}
}
