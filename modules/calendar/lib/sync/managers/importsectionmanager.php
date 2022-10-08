<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sync;
use Bitrix\Calendar\Sync\Factories\FactoryBase;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectNotFoundException;
use Exception;

class ImportSectionManager
{
	private ?Sync\Entities\SyncSectionMap $externalSyncSectionMap = null;
	private IncomingSectionManagerInterface $importManager;

	/**
	 * @param FactoryBase $factory
	 * @param Core\Base\Map $syncSectionCollection
	 *
	 * @throws ObjectNotFoundException
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
		return $this->importManager->getSyncToken();
	}

	public function getEtag(): ?string
	{
		return $this->importManager->getEtag();
	}

	public function getStatus(): ?string
	{
		return $this->importManager->getStatus();
	}
}
