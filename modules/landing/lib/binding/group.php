<?php
namespace Bitrix\Landing\Binding;

use Bitrix\Landing\Connector\SocialNetwork;
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
	 * By group id returns binding site id.
	 *
	 * @param int $groupId Group id.
	 * @return int|null
	 */
	public static function getSiteIdByGroupId(int $groupId): ?int
	{
		$res = BindingTable::getList([
			'select' => [
				'ENTITY_ID',
			],
			'filter' => [
				'=BINDING_TYPE' => self::$bindingType,
				'=BINDING_ID' => $groupId,
				'=ENTITY_TYPE' => self::ENTITY_TYPE_SITE,
			],
		]);

		return $res->fetch()['ENTITY_ID'] ?? null;
	}

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
					'GROUP_TITLE' => 'GROUP.NAME',
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
							'=this.BINDING_ID' => new \Bitrix\Main\DB\SqlExpression('?s', 'ref.ID'),
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
	 *
	 * @param bool $fullData Returns full data not ids only.
	 * @return array
	 */
	protected static function getAccessTasks(bool $fullData = false): array
	{
		static $tasks = [];

		if (empty($tasks))
		{
			$res = \CTask::getList(
				['LETTER' => 'ASC'],
				['MODULE_ID' => 'landing'],
			);
			while ($row = $res->fetch())
			{
				if ($row['LETTER'] > 'D')
				{
					$row['NAME'] = str_replace('landing_right_', '', $row['NAME']);
					$tasks[] = $fullData ? $row : $row['ID'];
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
	 * Invokes when bind group was occurred.
	 *
	 * @param int $siteId Site id.
	 * @param array $groupRoles Reference map between access names and group roles (not landing roles!).
	 * @return void
	 */
	protected function addSiteRights(int $siteId, array $groupRoles = []): void
	{
		$tasks = self::getAccessTasks(true);
		$roleId = self::getRoleId();

		// for new binding
		if (!$groupRoles && \Bitrix\Main\Loader::includeModule('socialnetwork'))
		{
			$groupRoles = [
				'read' => SONET_ROLES_USER,
				'edit' => SONET_ROLES_USER,
				'sett' => SONET_ROLES_USER,
				'delete' => SONET_ROLES_USER,
			];

			// try to retrieve roles from group features
			$res = \CSocNetFeaturesPerms::getList(
				[],
				[
					'FEATURE_ENTITY_ID' => $this->bindingId,
					'FEATURE_ENTITY_TYPE' => SONET_ENTITY_GROUP,
					'FEATURE_FEATURE' => SocialNetwork::SETTINGS_CODE,
				],
			);
			while ($row = $res->fetch())
			{
				$groupRoles[$row['OPERATION_ID']] = $row['ROLE'];
			}
		}

		foreach ($tasks as $task)
		{
			if (!isset($groupRoles[$task['NAME']]))
			{
				continue;
			}
			$check = RightsTable::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'ENTITY_ID' => $siteId,
					'=ENTITY_TYPE' => Rights::ENTITY_TYPE_SITE,
					'=ACCESS_CODE' => 'SG' . $this->bindingId . '_' . $groupRoles[$task['NAME']],
					'TASK_ID' => $task['ID'],
					'ROLE_ID' => $roleId
				],
			])->fetch();
			if (!$check)
			{
				RightsTable::add([
					'ENTITY_ID' => $siteId,
					'ENTITY_TYPE' => Rights::ENTITY_TYPE_SITE,
					'TASK_ID' => $task['ID'],
					'ACCESS_CODE' => 'SG' . $this->bindingId . '_' . $groupRoles[$task['NAME']],
					'ROLE_ID' => $roleId
				])->isSuccess();
			}
		}
	}

	/**
	 * Invokes when rebind group was occurred.
	 *
	 * @param int $siteId Site id.
	 * @return void
	 */
	protected function updateSiteRights(int $siteId): void
	{
		$opsToRoles = $this->getOperationsToRolesMap($this->bindingId);
		$this->removeSiteRights($siteId);
		$this->addSiteRights($siteId, $opsToRoles);
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
				'ACCESS_CODE' => 'SG' . $this->bindingId . '_%',
				'ROLE_ID' => $roleId
			]
		]);
		while ($row = $res->fetch())
		{
			RightsTable::delete($row['ID'])->isSuccess();
		}
	}

	/**
	 * Returns references between operations and roles in specific group.
	 *
	 * @param int $groupId Group id.
	 * @return array
	 */
	private function getOperationsToRolesMap(int $groupId): array
	{
		$opsToRoles = [];

		if (!\Bitrix\Main\Loader::includeModule('socialnetwork'))
		{
			return $opsToRoles;
		}

		$res = \CSocNetFeaturesPerms::getList(
			[],
			[
				'FEATURE_ENTITY_ID' => $groupId,
				'FEATURE_ENTITY_TYPE' => SONET_ENTITY_GROUP,
				'FEATURE_FEATURE' => SocialNetwork::SETTINGS_CODE,
			],
		);
		while ($row = $res->fetch())
		{
			$opsToRoles[$row['OPERATION_ID']] = $row['ROLE'];
		}

		return $opsToRoles;
	}
}