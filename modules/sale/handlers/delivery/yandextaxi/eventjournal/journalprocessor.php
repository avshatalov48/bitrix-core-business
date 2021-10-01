<?php

namespace Sale\Handlers\Delivery\YandexTaxi\EventJournal;

use Bitrix\Sale\Delivery\Services\Manager;
use Sale\Handlers\Delivery\YandexTaxi\Internals\ClaimsTable;
use Sale\Handlers\Delivery\YandextaxiHandler;

/**
 * Class JournalProcessor
 * @package Sale\Handlers\Delivery\YandexTaxi\EventJournal
 * @internal
 */
final class JournalProcessor
{
	/** @var EventReader */
	protected $eventReader;

	/** @var EventProcessor */
	protected $eventProcessor;

	/**
	 * Process constructor.
	 * @param EventReader $eventReader
	 * @param EventProcessor $eventProcessor
	 */
	public function __construct(EventReader $eventReader, EventProcessor $eventProcessor)
	{
		$this->eventReader = $eventReader;
		$this->eventProcessor = $eventProcessor;
	}

	/**
	 * @param int $serviceId
	 * @return string|null
	 */
	public static function processJournal(int $serviceId)
	{
		/** @var YandextaxiHandler $service */
		$service = Manager::getObjectById($serviceId);
		if (!$service)
		{
			return null;
		}

		$instance = $service->getYandexTaxiJournalProcessor();
		$agent = $instance->getAgentName($serviceId);

		$configValues = $service->getConfigValues();
		$prevCursor = isset($configValues['MAIN']['CURSOR']) && !empty($configValues['MAIN']['CURSOR'])
			? $configValues['MAIN']['CURSOR']
			: null;


		$readResult = $instance->eventReader->read($serviceId, $prevCursor);

		if ($readResult->isSuccess())
		{
			$instance->eventProcessor->process($serviceId, $readResult->getEvents());

			return $instance->hasMore() === false ? null : $agent;
		}

		return $agent;
	}

	/**
	 * @param int $serviceId
	 * @return string
	 */
	public function getAgentName(int $serviceId): string
	{
		return '\\' . static::class . sprintf('::processJournal(%s);', $serviceId);
	}

	/**
	 * @return bool
	 */
	private function hasMore()
	{
		$notFinalizedClaim = ClaimsTable::getList(
			[
				'filter' => ['=FURTHER_CHANGES_EXPECTED' => 'Y'],
				'limit' => 1,
			]
		)->fetch();

		return $notFinalizedClaim ? true : false;
	}
}
