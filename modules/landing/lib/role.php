<?php
namespace Bitrix\Landing;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Internals\RightsTable;

Loc::loadMessages(__FILE__);

/**
 * Class Role.
 * In now time Role entity exist only for sites.
 * @package Bitrix\Landing
 */

class Role extends \Bitrix\Landing\Internals\BaseTable
{
	/**
	 * Expected type for role.
	 * @var string
	 */
	protected static $expectedType = null;

	/**
	 * Internal class.
	 * @var string
	 */
	public static $internalClass = 'RoleTable';

	/**
	 * Set forbidden rights in default role manager.
	 * @var array
	 */
	public static $forbiddenManagerRights = [
		'admin',
		'knowledge_admin',
		'unexportable',
		'knowledge_unexportable',
		'knowledge_extension',
	];

	/**
	 * Set forbidden rights in default role admin.
	 * @var array
	 */
	public static $forbiddenAdminRights = [
		'unexportable',
		'knowledge_unexportable'
	];

	/**
	 * For correct work we need at least one role.
	 * @return void
	 */
	public static function checkRequiredRoles(): void
	{
		$type = Site\Type::getCurrentScopeId();
		$res = self::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'=TYPE' => $type
			],
			'order' => [
				'ID' => 'asc'
			]
		]);
		while ($role = $res->fetch())
		{
			$taskRefs = Rights::getAccessTasksReferences();
			$taskReadId = $taskRefs[Rights::ACCESS_TYPES['read']];
			$taskDenyId = $taskRefs[Rights::ACCESS_TYPES['denied']];
			$resRight = RightsTable::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'ENTITY_ID' => 0,
					'TASK_ID' => [$taskReadId, $taskDenyId],
					'ROLE_ID' => $role['ID'],
					'=ENTITY_TYPE' => Rights::ENTITY_TYPE_SITE
				]
			]);
			if (!$resRight->fetch())
			{
				RightsTable::add([
					'ENTITY_ID' => 0,
					'ENTITY_TYPE' => Rights::ENTITY_TYPE_SITE,
					'TASK_ID' => $taskReadId,
					'ROLE_ID' => $role['ID'],
					'ACCESS_CODE' => 'G1'
				]);
			}
		}

		if (isset($taskRefs))
		{
			return;
		}

		$keyDemoInstalled = 'role_demo_installed';
		if ($type)
		{
			$keyDemoInstalled .= '_' . mb_strtolower($type);
		}
		Manager::setOption($keyDemoInstalled, 'N');
		self::fetchAll();
	}

	/**
	 * Gets all roles. Install demo data if need.
	 * @return array
	 */
	public static function fetchAll()
	{
		static $roles = null;

		$type = Site\Type::getCurrentScopeId();

		if ($roles !== null)
		{
			return $roles;
		}

		$roles = [];
		$codes = [];
		$access = new \CAccess;

		// gets from db
		$res = self::getList([
			'filter' => [
				'=TYPE' => $type
			],
			'order' => [
				'ID' => 'asc'
			]
		]);
		while ($row = $res->fetch())
		{
			if (!trim($row['TITLE']))
			{
				$row['TITLE'] = Loc::getMessage('LANDING_ROLE_DEF_' . $row['XML_ID']);
			}
			$row['ACCESS_CODES'] = !$row['ACCESS_CODES'] ? [] : (array)$row['ACCESS_CODES'];
			$roles[$row['ID']] = $row;
			$codes = array_merge($codes, $row['ACCESS_CODES']);
		}

		// get titles for access codes
		if ($roles)
		{
			$codesNames  = $access->getNames($codes);
			foreach ($roles as &$role)
			{
				foreach ($role['ACCESS_CODES'] as &$code)
				{
					$provider = (
						isset($codesNames[$code]['provider']) &&
						$codesNames[$code]['provider']
					)
						? $codesNames[$code]['provider']
						: '';
					$name = isset($codesNames[$code]['name'])
						? $codesNames[$code]['name']
						: $code;
					$code = [
						'CODE' => $code,
						'PROVIDER' => $provider,
						'NAME' => $name
					];
				}
				unset($code);
			}
			unset($role);
		}

		// install demo data if need
		$keyDemoInstalled = 'role_demo_installed';
		if ($type)
		{
			$keyDemoInstalled .= '_'.mb_strtolower($type);
		}
		if (
			empty($roles) &&
			Manager::getOption($keyDemoInstalled, 'N') == 'N'
		)
		{
			$roles = null;
			self::installDemo($type);
			Manager::setOption($keyDemoInstalled, 'Y');
			return self::fetchAll();
		}

		return $roles;
	}

	/**
	 * Install demo data.
	 * @param string $type Type of roles.
	 * @return void
	 */
	public static function installDemo($type = null)
	{
		Manager::enableFeatureTmp(
			Manager::FEATURE_PERMISSIONS_AVAILABLE
		);

		$defGroup = 'G1';
		// for B24 gets employees group
		if (Manager::isB24())
		{
			$res = \Bitrix\Main\GroupTable::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'=STRING_ID' => 'EMPLOYEES_' . SITE_ID
				]
			]);
			if ($row = $res->fetch())
			{
				$defGroup = 'G' . $row['ID'];
			}
			unset($row, $res);
		}

		$addRights = [];
		foreach (Rights::ADDITIONAL_RIGHTS as $accessCode)
		{
			if (mb_strpos($accessCode, '_') > 0)
			{
				[$prefix, ] = explode('_', $accessCode);
				$prefix = mb_strtoupper($prefix);
				if ($prefix == $type)
				{
					$addRights[] = $accessCode;
				}
			}
			else if ($type === null)
			{
				$addRights[] = $accessCode;
			}
		}

		$addRightsManager = $addRights;
		foreach (self::$forbiddenManagerRights as $rightCode)
		{
			$key = array_search($rightCode, $addRightsManager, true);
			if ($key)
			{
				array_splice($addRightsManager, $key, 1);
			}
		}
		$addRightsAdmin = $addRights;
		foreach (self::$forbiddenAdminRights as $rightCode)
		{
			$key = array_search($rightCode, $addRightsAdmin, true);
			if ($key)
			{
				array_splice($addRightsAdmin, $key, 1);
			}
		}

		$demoData = [
			'admin' => [
				'rights' => [
					'read',
					'edit',
					'sett',
					'public',
					'delete'
				],
				'additional_rights' => $addRightsAdmin,
				'access' => [
					$defGroup
				]
			],
			'manager' => [
				'rights' => [
					'read',
					'edit',
					'public'
				],
				'additional_rights' => $addRightsManager,
				'access' => []
			]
		];
		$type = (string)$type;
		foreach ($demoData as $code => $rights)
		{
			$code = mb_strtoupper($code);
			$check = false;
			/*$check = self::getList([
				'filter' => [
					'=XML_ID' => $code
				]
   			])->fetch();*/
			if (!$check)
			{
				$res = self::add([
					'TYPE' => $type,
					'XML_ID' => $code,
					'ADDITIONAL_RIGHTS' => $rights['additional_rights']
				]);
				if ($res->isSuccess())
				{
					self::setRights(
						$res->getId(),
						[0 => $rights['rights']]
					);
					if ($rights['access'])
					{
						self::setAccessCodes(
							$res->getId(),
							$rights['access']
						);
					}
				}
				unset($res);
			}
			unset($check);
		}
		unset($demoData, $defGroup, $code, $rights);

		Manager::disableFeatureTmp(
			Manager::FEATURE_PERMISSIONS_AVAILABLE
		);
	}

	/**
	 * Set new access codes for role and refresh all rights.
	 * @param int $roleId Role id.
	 * @param array $codes Codes array.
	 * @return void
	 */
	public static function setAccessCodes($roleId, array $codes = array())
	{
		if (!Manager::checkFeature(Manager::FEATURE_PERMISSIONS_AVAILABLE))
		{
			return;
		}

		$roleId = intval($roleId);

		self::update($roleId, [
			'ACCESS_CODES' => $codes
		]);

		self::setRights(
			$roleId,
			self::getRights($roleId)
		);

		Rights::refreshAdditionalRights();
	}

	/**
	 * Gets rights for each site in this role.
	 * @param int $roleId
	 * @return array
	 */
	public static function getRights($roleId)
	{
		$tasks = Rights::getAccessTasksReferences();
		$tasks = array_flip($tasks);
		$roleId = intval($roleId);
		$return = [];

		$res = RightsTable::getlist([
			'select' => [
				'ENTITY_ID',
				'TASK_ID'
			],
			'filter' => [
				'ROLE_ID' => $roleId,
				'=ENTITY_TYPE' => Rights::ENTITY_TYPE_SITE
			]
		]);
		while ($row = $res->fetch())
		{
			if (!isset($tasks[$row['TASK_ID']]))
			{
				continue;
			}
			if (!isset($return[$row['ENTITY_ID']]))
			{
				$return[$row['ENTITY_ID']] = [];
			}
			$right = $tasks[$row['TASK_ID']];
			if (!in_array($right, $return[$row['ENTITY_ID']]))
			{
				$return[$row['ENTITY_ID']][] = $right;
			}
		}

		return $return;
	}

	/**
	 * Set rights for role.
	 * @param int $roleId Role id.
	 * @param array $rights Rights array ([[site_id] => [right1, right2]]
	 * @param array $additionalRights Additional rights array ([Rights::ADDITIONAL_RIGHTS]).
	 * @return void
	 */
	public static function setRights($roleId, $rights = [], $additionalRights = null)
	{
		if (!Manager::checkFeature(Manager::FEATURE_PERMISSIONS_AVAILABLE))
		{
			return;
		}

		if (!empty($rights))
		{
			$rights = (array) $rights;
		}
		$roleId = intval($roleId);
		$tasks = Rights::getAccessTasksReferences();

		// func for setting additional rights
		$setAdditionalRights = function() use($roleId, $additionalRights)
		{
			// set additional rights
			if ($additionalRights !== null)
			{
				if (!is_array($additionalRights))
				{
					$additionalRights = [];
				}
				self::update($roleId, [
					'ADDITIONAL_RIGHTS' => $additionalRights
				]);
				Rights::refreshAdditionalRights();
			}
		};

		// gets access codes from role
		$res = self::getList([
			'select' => [
				'ACCESS_CODES'
			],
			'filter' => [
				'ID' => $roleId
			]
		]);
		if ($row = $res->fetch())
		{
			$accessCodes = $row['ACCESS_CODES'];
			if (!$accessCodes)
			{
				$accessCodes = ['G1'];
			}
		}
		else
		{
			$setAdditionalRights();
			return;
		}

		// first remove all rights for role
		$res = RightsTable::getlist([
			'select' => [
				'ID'
			],
			'filter' => [
				'ROLE_ID' => $roleId,
				'=ENTITY_TYPE' => Rights::ENTITY_TYPE_SITE
			]
		]);
		while ($row = $res->fetch())
		{
			RightsTable::delete($row['ID']);
		}

		if (empty($rights))
		{
			$setAdditionalRights();
			return;
		}

		// check for site exists
		$siteExists = [];
		$res = Site::getList([
			'select' => [
				'ID'
			],
			'filter' => array_keys($rights)
		]);
		while ($row = $res->fetch())
		{
			$siteExists[] = $row['ID'];
		}

		// and set new rights for each site
		$deniedCode = Rights::ACCESS_TYPES['denied'];
		$readCode = Rights::ACCESS_TYPES['read'];
		foreach ($rights as $siteId => $rightCodes)
		{
			if (!is_array($rightCodes))
			{
				continue;
			}
			if ($siteId > 0 && !in_array($siteId, $siteExists))
			{
				continue;
			}
			if (in_array($deniedCode, $rightCodes))
			{
				$rightCodes = [$deniedCode];
			}
			else if (!in_array($readCode, $rightCodes))
			{
				$rightCodes[] = $readCode;
			}
			foreach ($rightCodes as $rightCode)
			{
				if (isset($tasks[$rightCode]))
				{
					foreach ($accessCodes as $accessCode)
					{
						RightsTable::add([
							'ROLE_ID' => $roleId,
							'ENTITY_ID' => $siteId,
							'ENTITY_TYPE' => Rights::ENTITY_TYPE_SITE,
							'TASK_ID' => $tasks[$rightCode],
							'ACCESS_CODE' => $accessCode
						]);
					}
				}
			}
		}

		$setAdditionalRights();

		Manager::getCacheManager()->clearByTag(
			"intranet_menu_binding"
		);
	}

	/**
	 * Sets new expected type for role.
	 * @param string|null $type New expected type;
	 * @return void
	 */
	public static function setExpectedType($type)
	{
		if (is_string($type) || $type === null)
		{
			self::$expectedType = $type;
		}
	}

	/**
	 * Gets expected role type.
	 * @return string
	 */
	public static function getExpectedType()
	{
		return self::$expectedType;
	}

	/**
	 * Gets expected roles id.
	 * @return array
	 */
	public static function getExpectedRoleIds()
	{
		static $ids = [];

		if (!$ids)
		{
			$ids[] = -1;
			$res = self::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'=TYPE' => self::$expectedType
				]
			]);
			while ($row = $res->fetch())
			{
				$ids[] = $row['ID'];
			}
		}

		return $ids;
	}
}
