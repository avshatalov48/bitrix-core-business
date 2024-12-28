<?php
namespace Bitrix\Landing;

use \Bitrix\Landing\Internals\RightsTable;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\UserAccessTable;

Loc::loadMessages(__FILE__);

class Rights
{
	/**
	 * Site entity type.
	 */
	const ENTITY_TYPE_SITE = 'S';

	/**
	 * Access types for different levels.
	 */
	const ACCESS_TYPES = [
		'denied' => 'denied',
		'read' => 'read',
		'edit' => 'edit',
		'sett' => 'sett',
		'public' => 'public',
		'delete' => 'delete'
	];

	/**
	 * Additional rights for some functionality.
	 */
	const ADDITIONAL_RIGHTS = [
		'menu24' => 'menu24',//show in main menu of Bitrix24
		'admin' => 'admin',//admin rights
		'create' => 'create',//can create new sites
		'unexportable' => 'unexportable',
		'knowledge_menu24' => 'knowledge_menu24',// show Knowledge in main menu of Bitrix24
		'knowledge_admin' => 'knowledge_admin',//admin rights
		'knowledge_create' => 'knowledge_create',//can create new Knowledge base
		'knowledge_unexportable' => 'knowledge_unexportable',
		'knowledge_extension' => 'knowledge_extension',
		'group_create' => 'group_create',//can create new social network group base
		'group_admin' => 'group_admin',//admin rights
		'group_menu24' => 'group_menu24',// show group in main menu of Bitrix24
		'group_unexportable' => 'group_unexportable',
		'mainpage_create' => 'mainpage_create',
	];

	const SET_PREFIX = [
		'knowledge',
		'group',
	];

	const REVERSE_RIGHTS = [
		'unexportable',
		'knowledge_unexportable',
		'group_unexportable',
	];

	/**
	 * Allowed site ids with full access.
	 * @var int[]
	 */
	protected static $allowedSites = [];

	/**
	 * If true, rights is not checking.
	 * @var bool
	 */
	protected static $available = true;

	/**
	 * If true, rights is not checking (global mode).
	 * @var bool
	 */
	protected static $globalAvailable = true;

	/**
	 * Context user id.
	 * @var int
	 */
	protected static $userId = null;

	/**
	 * Set rights checking to 'no'.
	 * @return void
	 */
	public static function setOff()
	{
		self::$available = false;
	}

	/**
	 * Set rights checking to 'yes'.
	 * @return void
	 */
	public static function setOn()
	{
		self::$available = true;
	}

	/**
	 * Set rights checking to 'no' (global mode).
	 * @return void
	 */
	public static function setGlobalOff()
	{
		self::$globalAvailable = false;
	}

	/**
	 * Set rights checking to 'yes' (global mode).
	 * @return void
	 */
	public static function setGlobalOn()
	{
		self::$globalAvailable = true;
	}

	/**
	 * Check current status for checking rights.
	 * @return bool
	 */
	public static function isOn()
	{
		if (
			defined('LANDING_DISABLE_RIGHTS') &&
			LANDING_DISABLE_RIGHTS === true
		)
		{
			return false;
		}
		if (!self::$globalAvailable)
		{
			return false;
		}
		return self::$available;
	}

	/**
	 * Current user is admin or not.
	 * @return bool
	 */
	public static function isAdmin()
	{
		if (self::hasAdditionalRight(self::ADDITIONAL_RIGHTS['admin'], null, false, true))
		{
			return true;
		}
		return Manager::isAdmin();
	}

	/**
	 * Returns allowed sites with full access.
	 * @return int[]
	 */
	public static function getAllowedSites(): array
	{
		return self::$allowedSites;
	}

	/**
	 * Sets context user id.
	 * @param int $uid
	 * @return void
	 */
	public static function setContextUserId(int $uid): void
	{
		self::$userId = $uid;
	}

	/**
	 * Clears context user id.
	 * @return void
	 */
	public static function clearContextUserId(): void
	{
		self::$userId = null;
	}

	/**
	 * Returns context user id (current by default).
	 * @return int
	 */
	public static function getContextUserId(): int
	{
		if (!self::$userId)
		{
			self::$userId = Manager::getUserId();
		}
		return self::$userId;
	}

	/**
	 * Available or not permission feature by current plan.
	 * @return bool
	 */
	protected static function isFeatureOn()
	{
		return Manager::checkFeature(
			Manager::FEATURE_PERMISSIONS_AVAILABLE
		);
	}

	/**
	 * Gets tasks for access.
	 * @return array
	 */
	public static function getAccessTasks()
	{
		static $tasks = [];

		if (empty($tasks))
		{
			$res = \CTask::getList(
				['LETTER' => 'ASC'],
				['MODULE_ID' => 'landing']
			);
			while ($row = $res->fetch())
			{
				$row['NAME'] = mb_substr($row['NAME'], 14);
				$tasks[$row['ID']] = $row;
			}
		}

		return $tasks;
	}

	/**
	 * Gets tasks for access.
	 * @return array
	 */
	public static function getAccessTasksReferences()
	{
		static $tasks = [];

		if (empty($tasks))
		{
			foreach (self::getAccessTasks() as $accessTask)
			{
				$tasks[$accessTask['NAME']] = $accessTask['ID'];
			}
		}

		return $tasks;
	}

	/**
	 * Remove all rows for entity.
	 * @param int|array $entityId Entity id (id or array of id).
	 * @param string $entityType Entity type.
	 * @return void
	 */
	protected static function removeData($entityId, $entityType)
	{
		if (self::isFeatureOn())
		{
			$res = RightsTable::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'ENTITY_ID' => $entityId,
					'=ENTITY_TYPE' => $entityType
				]
			]);
			while ($row = $res->fetch())
			{
				RightsTable::delete($row['ID']);
			}
		}
	}

	/**
	 * Remove all rows for site.
	 * @param int|array $siteId Site id (id or array of id).
	 * @return void
	 */
	public static function removeDataForSite($siteId)
	{
		self::removeData(
			$siteId,
			self::ENTITY_TYPE_SITE
		);
	}

	/**
	 * Get all rows for entity.
	 * @param int|array $entityId Entity id (id or array of id).
	 * @param string $entityType Entity type.
	 * @param array $preDefined Predefined array of rights.
	 * @return array
	 */
	protected static function getData($entityId, $entityType, array $preDefined = [])
	{
		static $access = null;
		$items = [];
		$codes = [];

		if ($access === null)
		{
			$access = new \CAccess;
		}

		// filter (with predefined_
		$filter = [
			'ENTITY_ID' => $entityId,
			'=ENTITY_TYPE' => $entityType
		];
		if ($preDefined)
		{
			$filter['=ACCESS_CODE'] = array_keys($preDefined);
		}

		// main query
		$res = RightsTable::getList([
			'select' => [
				'TASK_ID',
				'ACCESS_CODE'
			],
			'filter' => $filter
		]);
		while ($row = $res->fetch())
		{
			$codes[] = $row['ACCESS_CODE'];
			if (!isset($items[$row['ACCESS_CODE']]))
			{
				$row['TASK_ID'] = [$row['TASK_ID']];
				$items[$row['ACCESS_CODE']] = $row;
			}
			else
			{
				$items[$row['ACCESS_CODE']]['TASK_ID'][] = $row['TASK_ID'];
			}
			if (isset($preDefined[$row['ACCESS_CODE']]))
			{
				unset($preDefined[$row['ACCESS_CODE']]);
			}
		}

		$items = array_values($items);

		// fill with predefined
		foreach ($preDefined as $accessCode => $rightCode)
		{
			$items[] = [
				'TASK_ID' => $rightCode,
				'ACCESS_CODE' => $accessCode
			];
			$codes[] = $accessCode;
		}

		// get titles
		if ($items)
		{
			$codesNames  = $access->getNames($codes);
			foreach ($items as &$item)
			{
				if (isset($codesNames[$item['ACCESS_CODE']]))
				{
					$item['ACCESS_PROVIDER'] = (
								isset($codesNames[$item['ACCESS_CODE']]['provider']) &&
								$codesNames[$item['ACCESS_CODE']]['provider']
							)
						? $codesNames[$item['ACCESS_CODE']]['provider']
						: '';
					$item['ACCESS_NAME'] = isset($codesNames[$item['ACCESS_CODE']]['name'])
						? $codesNames[$item['ACCESS_CODE']]['name']
						: $item['ACCESS_CODE'];
				}
			}
			unset($item);
		}

		return $items;
	}

	/**
	 * Get all rows for site.
	 * @param int|array $siteId Site id (id or array of id).
	 * @param array $preDefined Predefined array of rights.
	 * @return array
	 */
	public static function getDataForSite($siteId, array $preDefined = [])
	{
		return self::getData(
			$siteId,
			self::ENTITY_TYPE_SITE,
			$preDefined
		);
	}

	/**
	 * Get all available operations for entity (for current user).
	 * @param int|array $entityId Entity id (id or array of id).
	 * @param string $entityType Entity type.
	 * @return array
	 */
	protected static function getOperations($entityId, $entityType)
	{
		// full access for allowed sites
		if (
			$entityType == self::ENTITY_TYPE_SITE &&
			in_array($entityId, self::$allowedSites)
		)
		{
			$types = self::ACCESS_TYPES;
			unset($types[self::ACCESS_TYPES['delete']]);
			return array_values($types);
		}

		// check scoped method
		if (
			$entityType == self::ENTITY_TYPE_SITE
			&& !is_array($entityId) && $entityId > 0
		)
		{
			$scopeOperationsSite = Site\Type::getOperationsForSite($entityId);
			if ($scopeOperationsSite !== null)
			{
				return array_values($scopeOperationsSite);
			}
		}

		$operations = [];
		$operationsDefault = [];
		$wasChecked = false;
		$uid = self::getContextUserId();
		$extendedMode = self::isExtendedMode();

		// full access for admin
		if (
			$uid &&
			self::isOn() &&
			!self::isAdmin() &&
			self::isFeatureOn() &&
			self::exist()
		)
		{
			$wasChecked = true;
			$entityIdFilter = $entityId;
			if (is_array($entityIdFilter))
			{
				$entityIdFilter[] = 0;
			}
			else
			{
				$entityIdFilter = [
					$entityIdFilter, 0
				];
			}
			$filter = [
				'ENTITY_ID' => $entityIdFilter,
				'=ENTITY_TYPE' => $entityType,
				'USER_ACCESS.USER_ID' => $uid,
				'!TASK_OPERATION.OPERATION.NAME' => false
			];
			if ($extendedMode)
			{
				$filter['ROLE_ID'] = 0;
			}
			else
			{
				$filter['ROLE_ID'] = Role::getExpectedRoleIds();
			}
			$res = RightsTable::getList(
				[
					'select' => [
						'ENTITY_ID',
						'OPERATION_NAME' => 'TASK_OPERATION.OPERATION.NAME'
					],
					'filter' => $filter
				]
			);
			while ($row = $res->fetch())
			{
				if ($row['ENTITY_ID'] == 0)
				{
					$operationsDefault[] = mb_substr($row['OPERATION_NAME'], 8);
					continue;
				}
				if (!isset($operations[$row['ENTITY_ID']]))
				{
					$operations[$row['ENTITY_ID']] = array();
				}
				$operations[$row['ENTITY_ID']][] = mb_substr($row['OPERATION_NAME'], 8);
				$operations[$row['ENTITY_ID']] = array_unique($operations[$row['ENTITY_ID']]);
			}
		}

		// set full rights, if rights are empty
		foreach ((array) $entityId as $id)
		{
			if (!isset($operations[$id]))
			{
				if ($wasChecked && !$extendedMode)
				{
					$operations[$id] = !empty($operationsDefault)
						? $operationsDefault
						: [self::ACCESS_TYPES['denied']];
				}
				else
				{
					$operations[$id] = array_values(self::ACCESS_TYPES);
				}
			}
		}

		return is_array($entityId)
				? $operations
				: $operations[$entityId];
	}

	/**
	 * Returns  all available operations for site (for current user).
	 * @param int|array $siteId Site id (id or array of id).
	 * @return array
	 */
	public static function getOperationsForSite($siteId): array
	{
		if (
			is_array($siteId) ||
			$siteId == 0 ||
			Site::ping($siteId, true)
		)
		{
			return self::getOperations(
				$siteId,
				self::ENTITY_TYPE_SITE
			);
		}
		else
		{
			return [];
		}
	}

	/**
	 * Can current user do something.
	 * @param int $siteId Site id.
	 * @param string $accessType Access type code.
	 * @param bool $deleted And from recycle bin.
	 * @return boolean
	 */
	public static function hasAccessForSite($siteId, $accessType, $deleted = false)
	{
		static $operations = [];
		$siteId = intval($siteId);

		if (!is_string($accessType))
		{
			return false;
		}

		if (!isset($operations[$siteId]))
		{
			if ($siteId === 0 || !self::isOn() || Site::ping($siteId, $deleted))
			{
				$operations[$siteId] = self::getOperations(
					$siteId,
					self::ENTITY_TYPE_SITE
				);
			}
			else
			{
				$operations[$siteId] = [];
			}
		}

		return in_array($accessType, $operations[$siteId]);
	}

	/**
	 * Can current user do something.
	 * @param int $landingId Landing id.
	 * @param string $accessType Access type code.
	 * @return boolean
	 */
	public static function hasAccessForLanding($landingId, $accessType)
	{
		static $operations = [];
		$landingId = intval($landingId);

		if (!is_string($accessType))
		{
			return false;
		}

		if (!isset($operations[$landingId]))
		{
			$site = Landing::getList([
 				'select' => [
					'SITE_ID'
				],
				'filter' => [
					'ID' => $landingId,
					'=SITE.DELETED' => ['Y', 'N'],
					'=DELETED' => ['Y', 'N']
				]
			])->fetch();

			if ($site)
			{
				$operations[$landingId] = self::getOperations(
					$site['SITE_ID'],
					self::ENTITY_TYPE_SITE
				);
			}
			else
			{
				$operations[$landingId] = [];
			}
		}

		return in_array($accessType, $operations[$landingId]);
	}

	/**
	 * Set operations for entity.
	 * @param int $entityId Entity id.
	 * @param string $entityType Entity type.
	 * @param array $rights Rights array (set empty for clear rights).
	 * @return boolean
	 */
	protected static function setOperations($entityId, $entityType, array $rights = [])
	{
		if (!self::isFeatureOn())
		{
			return false;
		}

		$tasks = self::getAccessTasksReferences();
		$entityId = intval($entityId);

		self::removeData(
			$entityId,
			$entityType
		);

		// add new rights
		foreach ($rights as $accessCode => $rightCodes)
		{
			$rightCodes = (array) $rightCodes;
			if (in_array(self::ACCESS_TYPES['denied'], $rightCodes))
			{
				$rightCodes = [self::ACCESS_TYPES['denied']];
			}
			else if (!in_array(self::ACCESS_TYPES['read'], $rightCodes))
			{
				$rightCodes[] = self::ACCESS_TYPES['read'];
			}

			foreach ($rightCodes as $rightCode)
			{
				if (isset($tasks[$rightCode]))
				{
					RightsTable::add([
						'ENTITY_ID' => $entityId,
						'ENTITY_TYPE' => $entityType,
						'TASK_ID' => $tasks[$rightCode],
						'ACCESS_CODE' => $accessCode
					]);
				}
			}
		}

		return true;
	}

	/**
	 * Set operations for site.
	 * @param int $siteId Site id.
	 * @param array $rights Rights array (set empty for clear rights).
	 * @return bool
	 */
	public static function setOperationsForSite($siteId, array $rights = [])
	{
		$siteId = intval($siteId);

		if ($siteId == 0 || Site::ping($siteId))
		{
			return self::setOperations(
				$siteId,
				self::ENTITY_TYPE_SITE,
				$rights
			);
		}
		else
		{
			return false;
		}
	}

	/**
	 * If any records of rights exists.
	 * @return bool
	 */
	protected static function exist()
	{
		static $exist = null;

		if ($exist === null)
		{
			$type = Site\Type::getCurrentScopeId();
			$res = RightsTable::getList([
				'select' => [
					'ID'
				],
				'filter' => $type
						? ['=ROLE.TYPE' => $type]
						: [],
				'limit' => 1
			]);
			$exist = (bool) $res->fetch();
		}

		return $exist;
	}

	/**
	 * Gets access filter for current user.
	 * @param array $additionalFilterOr Additional filter for OR section.
	 * @return array
	 */
	public static function getAccessFilter(array $additionalFilterOr = [])
	{
		$filter = [];

		if (
			self::isOn() &&
			!self::isAdmin() &&
			self::isFeatureOn() &&
			self::exist()
		)
		{
			$tasks = self::getAccessTasksReferences();
			$extendedRights = self::isExtendedMode();
			$uid = self::getContextUserId();

			if ($extendedRights)
			{
				$filter[] = [
					'LOGIC' => 'OR',
					[
						'!RIGHTS.TASK_ID' => $tasks[Rights::ACCESS_TYPES['denied']],
						'RIGHTS.USER_ACCESS.USER_ID' => $uid
					],
					[
						'=RIGHTS.TASK_ID' => null
					],
					$additionalFilterOr
				];
			}
			else
			{
				if ($additionalFilterOr)
				{
					$filter[] = [
						'LOGIC' => 'OR',
						[
							'!RIGHTS.TASK_ID' => $tasks[Rights::ACCESS_TYPES['denied']],
							'RIGHTS.USER_ACCESS.USER_ID' => $uid
						],
						$additionalFilterOr
					];
				}
				else
				{
					$filter['RIGHTS.USER_ACCESS.USER_ID'] = $uid;
					$filter['!RIGHTS.TASK_ID'] = $tasks[Rights::ACCESS_TYPES['denied']];
				}
			}
		}

		return $filter;
	}

	/**
	 * Extended mode available.
	 * @return bool
	 */
	public static function isExtendedMode()
	{
		if (Manager::isB24())
		{
			return Manager::getOption('rights_extended_mode', 'N') == 'Y';
		}
		else
		{
			return true;
		}
	}

	/**
	 * Switch extended mode.
	 * @return void
	 */
	public static function switchMode()
	{
		if (self::isFeatureOn())
		{
			$current = Manager::getOption('rights_extended_mode', 'N');
			$current = ($current == 'Y') ? 'N' : 'Y';
			Manager::setOption('rights_extended_mode', $current);
		}
	}

	/**
	 * Refresh additional rights for all roles.
	 * @param array $additionalRights Array for set additional.
	 * @return void
	 */
	public static function refreshAdditionalRights(array $additionalRights = [])
	{
		if (!self::isFeatureOn())
		{
			return;
		}

		$rights = [];
		foreach (self::ADDITIONAL_RIGHTS as $right)
		{
			$rights[$right] = [];
		}

		// get additional from all roles
		$res = Role::getList([
			'select' => [
				'ID', 'ACCESS_CODES', 'ADDITIONAL_RIGHTS'
			]
		]);
		while ($row = $res->fetch())
		{
			$row['ACCESS_CODES'] = (array) $row['ACCESS_CODES'];
			$row['ADDITIONAL_RIGHTS'] = (array) $row['ADDITIONAL_RIGHTS'];
			foreach ($row['ADDITIONAL_RIGHTS'] as $right)
			{
				if (isset($rights[$right]))
				{
					$rights[$right][$row['ID']] = $row['ACCESS_CODES'];
				}
			}
		}

		// refresh options
		foreach ($rights as $code => $right)
		{
			// gets current from option
			$option = Manager::getOption('access_codes_' . $code, '');
			$option = unserialize($option, ['allowed_classes' => false]);
			if (isset($option[0]))
			{
				$right[0] = $option[0];
			}

			// rewrite some rights, if need
			if (
				isset($additionalRights[$code]) &&
				is_array($additionalRights[$code])
			)
			{
				foreach ($additionalRights[$code] as $i => $accCodes)
				{
					$right[$i] = (array) $accCodes;
				}
			}

			// set new rights in option
			Manager::setOption('access_codes_' . $code, $right ? serialize($right) : '');

			// clear menu cache
			if (Manager::isB24())
			{
				Manager::getCacheManager()->clearByTag(
					'bitrix24_left_menu'
				);
				Manager::getCacheManager()->cleanDir(
					'menu'
				);
				\CBitrixComponent::clearComponentCache(
					'bitrix:menu'
				);
			}
		}
	}

	/**
	 * Set additional right.
	 * @param string $code Code from ADDITIONAL_RIGHTS.
	 * @param array $accessCodes Additional rights array.
	 * @return void
	 */
	public static function setAdditionalRightExtended($code, array $accessCodes = [])
	{
		if (!is_string($code))
		{
			return;
		}
		self::refreshAdditionalRights([
		  	$code => [
				0 => $accessCodes
			]
		]);
	}

	/**
	 * Gets additional right.
	 * @param string $code Code from ADDITIONAL_RIGHTS.
	 * @return array
	 */
	public static function getAdditionalRightExtended($code)
	{
		static $access = null;
		$return = [];

		if (!is_string($code))
		{
			return $return;
		}
		if ($access === null)
		{
			$access = new \CAccess;
		}

		$option = Manager::getOption('access_codes_' . $code, '');
		$option = unserialize($option, ['allowed_classes' => false]);
		$accessCodes = isset($option[0]) ? (array)$option[0] : [];
		$codesNames  = $access->getNames($accessCodes);

		foreach ($accessCodes as $code)
		{
			if (isset($codesNames[$code]))
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
				$return[$code] = [
					'CODE' => $code,
					'PROVIDER' => $provider,
					'NAME' => $name
				];
			}
		}

		return $return;
	}

	/**
	 * Gets additional rights with labels.
	 * @return array
	 */
	public static function getAdditionalRightsLabels()
	{
		$rights = [];

		$type = Site\Type::getCurrentScopeId();

		foreach (self::ADDITIONAL_RIGHTS as $right)
		{
			if (mb_strpos($right, '_') > 0)
			{
				[$prefix, ] = explode('_', $right);
				$prefix = mb_strtoupper($prefix);
				if ($prefix != $type)
				{
					continue;
				}
			}
			else if ($type !== null)
			{
				continue;
			}
			$rights[$right] = Loc::getMessage('LANDING_RIGHTS_R_'.mb_strtoupper($right));
		}

		return $rights;
	}

	/**
	 * Has user some extra access?
	 * @return bool
	 */
	protected static function hasExtraRights(): bool
	{
		// has context user access to crm forms
		if (\Bitrix\Main\Loader::includeModule('crm'))
		{
			$access = new \CCrmPerms(self::getContextUserId());
			if (!$access->havePerm('WEBFORM', BX_CRM_PERM_NONE, 'WRITE'))
			{
				// grant access to crm forms sites
				$res = Site::getList([
					'select' => [
						'ID'
					],
					'filter' => [
						'%=CODE' => '/' . Site\Type::PSEUDO_SCOPE_CODE_FORMS . '%',
						'=SPECIAL' => 'Y',
						'CHECK_PERMISSIONS' => 'N'
					]
				]);
				while ($row = $res->fetch())
				{
					self::$allowedSites[] = $row['ID'];
				}

				return true;
			}
		}
		return false;
	}

	/**
	 * Has current user additional right or not.
	 * @param string $code Code from ADDITIONAL_RIGHTS.
	 * @param string $type Scope type.
	 * @param bool $checkExtraRights Check extra rights.
	 * @return bool
	 */
	public static function hasAdditionalRight($code, $type = null, bool $checkExtraRights = false, bool $strict = false)
	{
		static $options = [];

		if ($checkExtraRights && self::hasExtraRights())
		{
			return true;
		}

		if (!is_string($code))
		{
			return false;
		}
		if ($type === null)
		{
			$type = Site\Type::getCurrentScopeId();
		}

		if ($type !== null)
		{
			$type = mb_strtolower($type);
			//todo: need fix it: remove if() and add 'mainpage_admin', 'mainpage_menu24' in ADDITIONAL_RIGHTS and check preview.bitrix24.site
			if ($type == mb_strtolower(Site\Type::SCOPE_CODE_MAINPAGE))
			{
				return true;
			}
			$code = $type . '_' . $code;
		}

		if (array_key_exists($code, self::ADDITIONAL_RIGHTS))
		{
			if (!self::isFeatureOn())
			{
				return true;
			}

			if (!self::getContextUserId())
			{
				return false;
			}

			if (Manager::isAdmin())
			{
				if (in_array($code, self::REVERSE_RIGHTS))
				{
					return false;
				}
				return true;
			}

			$accessCodes = [];
			if (!isset($options[$code]))
			{
				$options[$code] = Manager::getOption('access_codes_' . $code, '');
				$options[$code] = unserialize($options[$code], ['allowed_classes' => false]);
			}
			$option = $options[$code];

			if (!is_array($option) && !$strict)
			{
				return true;
			}

			if (empty($option))
			{
				return false;
			}

			if (self::isExtendedMode())
			{
				if (isset($option[0]) && is_array($option[0]))
				{
					$accessCodes = $option[0];
				}
			}
			else
			{
				if (isset($option[0]))
				{
					unset($option[0]);
				}
				foreach ($option as $roleAccess)
				{
					$accessCodes = array_merge($accessCodes, (array)$roleAccess);
				}
				$accessCodes = array_unique($accessCodes);
			}

			if ($accessCodes)
			{
				static $accessCodesStatic = [];

				sort($accessCodes);
				$accessCodesStr = implode('|', $accessCodes);

				if (array_key_exists($accessCodesStr, $accessCodesStatic))
				{
					return $accessCodesStatic[$accessCodesStr];
				}

				$res = UserAccessTable::getList([
					'select' => [
						'USER_ID'
					],
					'filter' => [
						'=ACCESS_CODE' => $accessCodes,
						'USER_ID' => self::getContextUserId()
					]
				]);
				$accessCodesStatic[$accessCodesStr] = (boolean)$res->fetch();
				return $accessCodesStatic[$accessCodesStr];
			}

			return false;
		}

		return false;
	}
}
