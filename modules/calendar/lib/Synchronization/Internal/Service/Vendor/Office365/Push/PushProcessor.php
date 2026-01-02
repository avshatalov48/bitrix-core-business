<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Push;

use Bitrix\Calendar\Integration\Dav\ConnectionProvider;
use Bitrix\Calendar\Synchronization\Internal\Repository\SectionConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Service\ConnectionManager;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Office365EventSynchronizer;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Office365SectionSynchronizer;
use Bitrix\Calendar\Synchronization\Internal\Service\Push\AbstractPushProcessor;
use Bitrix\Calendar\Synchronization\Internal\Service\Push\PushStorageManager;

class PushProcessor extends AbstractPushProcessor
{
	public function __construct(
		SectionConnectionRepository $sectionConnectionRepository,
		PushStorageManager $pushStorageManager,
		ConnectionProvider $connectionProvider,
		ConnectionManager $connectionManager,
		Office365EventSynchronizer $eventSynchronizer,
		Office365SectionSynchronizer $sectionManager,
	)
	{
		parent::__construct(
			$sectionConnectionRepository,
			$pushStorageManager,
			$connectionProvider,
			$connectionManager,
			$eventSynchronizer,
			$sectionManager
		);
	}
}
