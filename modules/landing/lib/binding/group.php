<?php
namespace Bitrix\Landing\Binding;

use \Bitrix\Landing\Role;
use \Bitrix\Landing\Rights;
use \Bitrix\Landing\Internals\RightsTable;

class Group extends Entity
{
	/**
	 * Type of role for group's type.
	 */
	const ROLE_TYPE = 'GROUP';

	/**
	 * Binding type.
	 * @var string
	 */
	protected static $bindingType = 'G';

	/**
	 * Returns tasks for access.
	 * @return array
	 */
	protected static function getAccessTasks()
	{
		static $tasks = [];

		if (empty($tasks))
		{
			$res = \CTask::getList(
				[
					'LETTER' => 'ASC'
				],
				[
					'MODULE_ID' => 'landing'
				]
			);
			while ($row = $res->fetch())
			{
				if ($row['LETTER'] > 'D')
				{
					$tasks[] = $row['ID'];
				}
			}
		}

		return $tasks;
	}

	/**
	 * Returns role id for access to new site.
	 * @return int
	 */
	protected static function getRoleId()
	{
		static $roleId = null;

		if ($roleId !== null)
		{
			return $roleId;
		}

		$res = Role::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'=TYPE' => self::ROLE_TYPE
			]
		]);
		if ($row = $res->fetch())
		{
			$roleId = $row['ID'];
		}
		else
		{
			$res = Role::add([
				'XML_ID' => 'MANAGER',
				'TYPE' => self::ROLE_TYPE
			]);
			if ($res->isSuccess())
			{
				$roleId = $res->getId();
			}
		}

		if (!$roleId)
		{
			$roleId = 0;
		}

		return (int) $roleId;
	}

	/**
	 * Call when binding new group.
	 * @param int $siteId Site id.
	 * @return void
	 */
	protected function addSiteRights($siteId)
	{
		$tasks = self::getAccessTasks();
		$roleId = self::getRoleId();

		foreach ($tasks as $taskId)
		{
			$check = RightsTable::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'ENTITY_ID' => $siteId,
					'=ENTITY_TYPE' => Rights::ENTITY_TYPE_SITE,
					'=ACCESS_CODE' => 'SG' . $this->bindingId . '_K',
					'TASK_ID' => $taskId,
					'ROLE_ID' => $roleId
				]
			])->fetch();
			if (!$check)
			{
				RightsTable::add([
					'ENTITY_ID' => $siteId,
					'ENTITY_TYPE' => Rights::ENTITY_TYPE_SITE,
					'TASK_ID' => $taskId,
					'ACCESS_CODE' => 'SG' . $this->bindingId . '_K',
					'ROLE_ID' => $roleId
				])->isSuccess();
			}
		}
	}

	/**
	 * Call when unbinding group.
	 * @param int $siteId Site id.
	 * @return void
	 */
	protected function removeSiteRights($siteId)
	{
		$roleId = self::getRoleId();

		$res = RightsTable::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'ENTITY_ID' => $siteId,
				'=ENTITY_TYPE' => Rights::ENTITY_TYPE_SITE,
				'=ACCESS_CODE' => 'SG' . $this->bindingId . '_K',
				'ROLE_ID' => $roleId
			]
		]);
		while ($row = $res->fetch())
		{
			RightsTable::delete($row['ID'])->isSuccess();
		}
	}
}