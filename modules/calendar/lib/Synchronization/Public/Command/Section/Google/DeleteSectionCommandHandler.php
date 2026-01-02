<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Section\Google;

use Bitrix\Calendar\Internals\Container;
use Bitrix\Calendar\Synchronization\Internal\Exception\PushException;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\GoogleSectionSynchronizer;

class DeleteSectionCommandHandler
{
	private GoogleSectionSynchronizer $sectionSynchronizer;

	public function __construct()
	{
		$this->sectionSynchronizer = Container::getGoogleSectionSynchronizer();
	}

	/**
	 * @throws NotAuthorizedException
	 * @throws SynchronizerException
	 * @throws PushException
	 */
	public function __invoke(DeleteSectionCommand $command): void
	{
		$this->sectionSynchronizer->deleteSection($command->vendorId, $command->userId);
	}
}
