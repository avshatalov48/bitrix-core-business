<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\EventJournal;

use Bitrix\Main\Error;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Delivery\Services\Table;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\Api;

/**
 * Class Reader
 * @package Sale\Handlers\Delivery\Taxi\Yandex\EventJournal
 */
class Reader
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
	 * @return ReadResult
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function read(int $deliveryServiceId, $prevCursor): ReadResult
	{
		$result = new ReadResult();

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
				$result->addEvent($event);
			}
		} while ($prevCursor != $cursor);

		if (!is_null($cursor))
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

		return $result;
	}
}
