<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Section\ICloud;

use Bitrix\Calendar\Internals\Container;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\ICloudSectionSynchronizer;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Repository\Exception\PersistenceException;

class SendSectionCommandHandler
{
	private ICloudSectionSynchronizer $sectionSynchronizer;

	public function __construct()
	{
		$this->sectionSynchronizer = Container::getICloudSectionSynchronizer();
	}

	/**
	 * @throws ArgumentException
	 * @throws SynchronizerException
	 * @throws PersistenceException
	 */
	public function __invoke(SendSectionCommand $command): void
	{
		$this->sectionSynchronizer->sendSection($command->section);
	}
}
