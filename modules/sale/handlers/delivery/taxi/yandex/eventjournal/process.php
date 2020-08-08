<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\EventJournal;

use Sale\Handlers\Delivery\Taxi\Yandex\ClaimsTable;

/**
 * Class Process
 * @package Sale\Handlers\Delivery\Taxi\Yandex\EventJournal
 */
class Process
{
	/** @var Reader */
	protected $reader;

	/** @var Processor */
	protected $processor;

	/**
	 * Process constructor.
	 * @param Reader $reader
	 * @param Processor $processor
	 */
	public function __construct(Reader $reader, Processor $processor)
	{
		$this->reader = $reader;
		$this->processor = $processor;
	}

	/**
	 * @param int $serviceId
	 * @param string|null $prevCursor
	 * @return bool|null Indicates if all claims have been finalized and we currently are not expecting any new events appear in the journal
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function run(int $serviceId, $prevCursor)
	{
		$readResult = $this->reader->read($serviceId, $prevCursor);

		if ($readResult->isSuccess())
		{
			$this->processor->process($readResult->getEvents());

			return $this->hasMore();
		}

		return null;
	}

	/**
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function hasMore()
	{
		$notFinalizedClaim = ClaimsTable::getList(
			[
				'filter' => ['FURTHER_CHANGES_EXPECTED' => 'Y'],
				'limit' => 1,
			]
		)->fetch();

		return $notFinalizedClaim ? true : false;
	}
}
