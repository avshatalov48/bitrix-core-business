<?php

namespace Bitrix\Calendar\Sync\Handlers;

use Bitrix\Calendar\Core\Role\Role;
use Bitrix\Calendar\Core;
use Bitrix\Calendar\Util;

class MasterPushHandler extends Core\Handlers\HandlerBase
{
	public const MASTER_STAGE = [
		0 => 'connection_created',
		1 => 'sections_sync_finished',
		2 => 'import_finished',
		3 => 'export_finished',
	];

	protected Role $owner;
	protected string $vendorName;
	protected string $accountName;

	public function __construct(Role $owner, string $vendorName, string $accountName)
	{
		$this->owner = $owner;
		$this->vendorName = $vendorName;
		$this->accountName = $accountName;
	}

	/**
	 * @var string $stage
	 * //self::MASTER_STAGE
	 */
	public function __invoke(string $stage)
	{
		Util::addPullEvent(
			'process_sync_connection',
			$this->owner->getId(),
			[
				'vendorName' => $this->vendorName,
				'stage' => $stage,
				'accountName' => $this->accountName,
			]
		);
	}

}
