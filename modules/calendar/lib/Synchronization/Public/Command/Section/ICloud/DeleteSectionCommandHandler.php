<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Section\ICloud;

use Bitrix\Calendar\Internals\Container;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\ICloudSectionSynchronizer;

class DeleteSectionCommandHandler
{
	private ICloudSectionSynchronizer $sectionSynchronizer;

	public function __construct()
	{
		$this->sectionSynchronizer = Container::getICloudSectionSynchronizer();
	}

	/**
	 * @throws SynchronizerException
	 */
	public function __invoke(DeleteSectionCommand $command): void
	{
		$this->sectionSynchronizer->deleteSection($command->vendorId, $command->userId);
	}
}
