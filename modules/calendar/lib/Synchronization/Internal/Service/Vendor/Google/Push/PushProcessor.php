<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Push;

use Bitrix\Calendar\Integration\Dav\ConnectionProvider;
use Bitrix\Calendar\Synchronization\Internal\Repository\SectionConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Service\ConnectionManager;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\GoogleEventSynchronizer;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\GoogleSectionSynchronizer;
use Bitrix\Calendar\Synchronization\Internal\Service\Push\AbstractPushProcessor;
use Bitrix\Calendar\Synchronization\Internal\Service\Push\PushStorageManager;

class PushProcessor extends AbstractPushProcessor
{
	public function __construct(
		SectionConnectionRepository $sectionConnectionRepository,
		GoogleEventSynchronizer $eventSynchronizer,
		GoogleSectionSynchronizer $sectionSynchronizer,
		PushStorageManager $pushStorageManager,
		ConnectionProvider $connectionProvider,
		ConnectionManager $connectionManager
	)
	{
		parent::__construct(
			$sectionConnectionRepository,
			$pushStorageManager,
			$connectionProvider,
			$connectionManager,
			$eventSynchronizer,
			$sectionSynchronizer
		);
	}
}
