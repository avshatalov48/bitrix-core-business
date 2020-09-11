<?php
namespace Bitrix\Landing\Binding;

use \Bitrix\Landing\Role;
use \Bitrix\Landing\Rights;
use \Bitrix\Landing\Internals\RightsTable;
use \Bitrix\Landing\Internals\BindingTable;

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
	 * Accepts array with site data and replaces site title to group title.
	 * @param array $input Site data ([ID] at least).
	 * @return array
	 */
	public static function recognizeSiteTitle(array $input): array
	{
		$sitesTitle = [];

		if (!\Bitrix\Main\Loader::includeModule('socialnetwork'))
		{
			return $input;
		}

		foreach ($input as $key => $item)
		{
			if (isset($item['ID']))
			{
				$sitesTitle[$item['ID']] = '';
			}
		}

		if ($sitesTitle)
		{
			// get real title for sonet group
			$res = BindingTable::getList([
				'select' => [
					'ENTITY_ID',
					'GROUP_TITLE' => 'GROUP.NAME'
				],
				'filter' => [
					'=BINDING_TYPE' => self::$bindingType,
					'=ENTITY_TYPE' => self::ENTITY_TYPE_SITE,
					'=ENTITY_ID' => array_keys($sitesTitle)
				],
				'runtime' => [
					new \Bitrix\Main\Entity\ReferenceField(
						'GROUP',
						'Bitrix\Socialnetwork\WorkgroupTable',
						[
							'=this.BINDING_ID' => 'ref.ID'
						]
					)
				]
			]);
			while ($row = $res->fetch())
			{
				$sitesTitle[$row['ENTITY_ID']] = $row['GROUP_TITLE'];
			}

			// replace sites titles to thr groups titles
			foreach ($input as $key => &$item)
			{
				if (isset($item['ID']) && $sitesTitle[$item['ID']])
				{
					$item['TITLE'] = $sitesTitle[$item['ID']];
				}
			}
			unset($item);
		}

		return $input;
	}

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