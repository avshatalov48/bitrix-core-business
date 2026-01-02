<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Section\Office365;

use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Office365SectionSynchronizer;
use Bitrix\Main\DI\ServiceLocator;

class DeleteSectionCommandHandler
{
	private Office365SectionSynchronizer $sectionSynchronizer;

	public function __construct()
	{
		$this->sectionSynchronizer = ServiceLocator::getInstance()->get(Office365SectionSynchronizer::class);
	}

	/**
	 * @throws SynchronizerException
	 */
	public function __invoke(DeleteSectionCommand $command): void
	{
		$this->sectionSynchronizer->deleteSection($command->vendorId, $command->userId);
	}
}
