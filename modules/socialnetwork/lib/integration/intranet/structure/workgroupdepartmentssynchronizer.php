<?php

namespace Bitrix\Socialnetwork\Integration\Intranet\Structure;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Config\Option;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\Update\WorkgroupDeptSync;

class WorkgroupDepartmentsSynchronizer
{
	private static ?self $instance = null;

	private function __construct()
	{
	}

	private function __clone()
	{
	}

	public static function getInstance(): self
	{
		if (!self::$instance)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @throws LoaderException
	 * @throws ArgumentOutOfRangeException
	 */
	public function synchronize(Workgroup $group, int $userId, bool $exclude = false): void
	{
		if (!Loader::includeModule('intranet'))
		{
			return;
		}

		$initiatorId = $userId ?: $group->getOwnerId();
		if (empty($group->getId()) || !$initiatorId)
		{
			return;
		}

		$workgroupsToSync = Option::get('socialnetwork', 'workgroupsToSync');

		$workgroupsToSync =
			$workgroupsToSync !== ''
				? @unserialize($workgroupsToSync, ['allowed_classes' => false])
				: []
		;

		if (!is_array($workgroupsToSync))
		{
			$workgroupsToSync = [];
		}
		$workgroupsToSync[] = [
			'groupId' => $group->getId(),
			'initiatorId' => $initiatorId,
			'exclude' => $exclude,
		];
		$workgroupsToSync = $this->reduceSyncList($workgroupsToSync);
		Option::set('socialnetwork', 'workgroupsToSync', serialize($workgroupsToSync));

		WorkgroupDeptSync::bind(1);
	}

	private function reduceSyncList(array $workgroupsToSync = []): array
	{
		$syncList = [];

		foreach ($workgroupsToSync as $workgroupData)
		{
			$workgroupId = (int)$workgroupData['groupId'];
			$syncList[$workgroupId] = $workgroupData;
		}

		return array_values($syncList);
	}
}
