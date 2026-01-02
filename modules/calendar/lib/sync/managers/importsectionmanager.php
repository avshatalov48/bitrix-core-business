<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sync;
use Bitrix\Calendar\Sync\Factories\FactoryBase;

class ImportSectionManager
{
	private ?Sync\Entities\SyncSectionMap $externalSyncSectionMap = null;
	private IncomingSectionManagerInterface $importManager;

	/**
	 * @param FactoryBase $factory
	 */
	public function __construct(FactoryBase $factory)
	{
		$this->importManager = $factory->getIncomingSectionManager();
	}

	/**
	 * @return $this
	 *
	 * @throws Core\Base\BaseException
	 * @throws Sync\Exceptions\AuthException
	 * @throws Sync\Exceptions\RemoteAccountException
	 */
	public function import(): ImportSectionManager
	{
		$result = $this->importManager->getSections();
		if ($result->isSuccess())
		{
			$this->externalSyncSectionMap = $result->getData()['externalSyncSectionMap'];
		}

		return $this;
	}

	/**
	 * @return Core\Base\Map|null
	 */
	public function getSyncSectionMap(): ?Sync\Entities\SyncSectionMap
	{
		return $this->externalSyncSectionMap ?? new Sync\Entities\SyncSectionMap();
	}

	public function getSyncToken(): ?string
	{
		// @todo No usages?
		return $this->importManager->getSyncToken();
	}

	public function getEtag(): ?string
	{
		// @todo No usages?
		return $this->importManager->getEtag();
	}

	public function getStatus(): ?string
	{
		// @todo No usages?
		return $this->importManager->getStatus();
	}
}
