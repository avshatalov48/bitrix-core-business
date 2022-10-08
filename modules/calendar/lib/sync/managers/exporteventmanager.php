<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Sync\Entities\SyncEventMap;
use Bitrix\Calendar\Sync\Entities\SyncSectionMap;
use Bitrix\Calendar\Sync\Factories\FactoryBase;
use Bitrix\Calendar\Sync\Util\Result;

class ExportEventManager
{
	protected FactoryBase $factory;
	protected SyncSectionMap $sectionMap;
	protected Result $exportResult;
	protected OutgoingEventManagerInterface $outgoingEventManager;

	/**
	 * @param FactoryBase $factory
	 * @param SyncSectionMap $sectionMap
	 */
	public function __construct(FactoryBase $factory, SyncSectionMap $sectionMap)
	{
		$this->factory = $factory;
		$this->sectionMap = $sectionMap;
		$this->outgoingEventManager = $factory->getOutgoingEventManager();
	}

	public function export(SyncEventMap $syncEventMap): self
	{
		if ($syncEventMap->count())
		{
			$this->exportResult = $this->outgoingEventManager->export($syncEventMap, $this->sectionMap);
		}

		return $this;
	}

	public function getEvents(): SyncEventMap
	{
		return $this->exportResult->isSuccess() && !empty($this->exportResult->getData()['externalSyncEventMap'])
			? $this->exportResult->getData()['externalSyncEventMap']
			: new SyncEventMap()
		;
	}
}
