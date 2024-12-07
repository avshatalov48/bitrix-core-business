<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Socialnetwork\Internals\Counter\Push;

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Socialnetwork\Internals\Counter;

class WorkgroupRequestsSender
{

	public function __construct()
	{

	}

	/**
	 * @param array $pushList
	 */
	public function send(array $pushList): void
	{
		if (
			!ModuleManager::isModuleInstalled('pull')
			|| !Loader::includeModule('pull')
		)
		{
			return;
		}

		$this->sendPersonalPush($pushList);
	}

	/**
	 * @param array $pushList
	 */
	protected function sendPersonalPush(array $pushList = []): void
	{
		$workgroupIdList = [];

		foreach ($pushList as $push)
		{
			if (!in_array($push['EVENT'], [
				Counter\Event\EventDictionary::EVENT_WORKGROUP_USER_ADD,
				Counter\Event\EventDictionary::EVENT_WORKGROUP_USER_UPDATE,
				Counter\Event\EventDictionary::EVENT_WORKGROUP_USER_DELETE,
			], true)
			)
			{
				continue;
			}

			$workgroupIdList[] = $push['GROUP_ID'];
		}

		$workgroupIdList = array_unique($workgroupIdList);
		if (empty($workgroupIdList))
		{
			return;
		}

		$userIdList = $this->getRecipients($workgroupIdList);

		foreach ($workgroupIdList as $workgroupId)
		{
			if (empty($userIdList[$workgroupId]))
			{
				continue;
			}

			(new PushSender())->sendUserCounters($userIdList[$workgroupId], [
				'workgroupId' => $workgroupId,
				'values' => $this->getWorkgroupCounters($workgroupId),
			]);
		}
	}

	protected function getWorkgroupCounters($workgroupId): array
	{
		return [];
	}

	/**
	 * @param int $groupId
	 * @return array
	 */
	protected function getRecipients(array $groupIdList = []): array
	{
		$result = [];

		if (empty($groupIdList))
		{
			return $result;
		}

		$userIdList = [];

		$res = WorkgroupTable::query()
			->setSelect([
				'ID',
				'INITIATE_PERMS'
			])
			->setFilter([
				'@ID' => $groupIdList,
			])
			->exec();

		while ($workgroupFields = $res->fetch())
		{
			$roleFilterValue = $this->getRoleFilterValue($workgroupFields['INITIATE_PERMS']);

			$resRelation = UserToGroupTable::query()
				->setSelect([
					'GROUP_ID',
					'USER_ID',
				])
				->setFilter([
					'@ROLE' => $roleFilterValue,
					'GROUP_ID' => (int)$workgroupFields['ID'],
					'=USER.ACTIVE' => 'Y'
				])
				->exec();

			$userIdList[(int)$workgroupFields['ID']] = [];
			while ($relationFields = $resRelation->fetch())
			{
				$userIdList[(int)$workgroupFields['ID']][] = (int)$relationFields['USER_ID'];
			}
		}

		return $userIdList;
	}

	protected function getRoleFilterValue($initiatePermsValue): array
	{
		return [ UserToGroupTable::ROLE_OWNER ];
	}
}
