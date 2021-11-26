<?php

namespace Bitrix\Sale;

use Bitrix\Main;
use	Bitrix\Sale\Internals\StatusTable;
use Bitrix\Sale\Internals\StatusLangTable;
use Bitrix\Main\UserGroupTable;
use Bitrix\Main\SystemException;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class StatusBase
 * @package Bitrix\Sale
 */
abstract class StatusBase
{
	const TYPE = '';

	/**
	 * @param array $parameters
	 * @return Main\DB\Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getList(array $parameters = array())
	{
		if (static::TYPE !== '')
		{
			if (!isset($parameters['filter']))
			{
				$parameters['filter'] = ['=TYPE' => static::TYPE];
			}
			else
			{
				$parameters['filter'] = [
					'=TYPE' => static::TYPE,
					$parameters['filter']
				];
			}
		}

		return StatusTable::getList($parameters);
	}

	/**
	 * @param $userId
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected static function getUserGroups($userId)
	{
		global $USER;

		if ($userId == $USER->GetID())
		{
			$groups = $USER->GetUserGroupArray();
		}
		else
		{
			static $cacheGroups;

			if (isset($cacheGroups[$userId]))
			{
				$groups = $cacheGroups[$userId];
			}
			else
			{
				// TODO: DATE_ACTIVE_FROM >=< DATE_ACTIVE_TO
				$result = UserGroupTable::getList(array(
					'select' => array('GROUP_ID'),
					'filter' => array('USER_ID' => $userId)
				));

				$groups = array();
				while ($row = $result->fetch())
					$groups []= $row['GROUP_ID'];

				$cacheGroups[$userId] = $groups;
			}
		}

		return $groups;
	}

	/**
	 * @param $groupId
	 * @param $fromStatus
	 * @param array $operations
	 * @return bool
	 * @throws Main\NotImplementedException
	 * @throws SystemException
	 */
	public static function canGroupDoOperations($groupId, $fromStatus, array $operations)
	{
		if (!$operations)
		{
			throw new SystemException('provide at least one operation', 0, __FILE__, __LINE__);
		}

		if (!is_array($groupId))
		{
			$groupId = array($groupId);
		}

		if (in_array('1', $groupId, true) || \CMain::GetUserRight('sale', $groupId) >= 'W') // Admin
		{
			return true;
		}

		$operations = static::convertNamesToOperations($operations);

		$result = static::getList(array(
			'select' => array(
				'NAME' => 'Bitrix\Sale\Internals\StatusGroupTaskTable:STATUS.TASK.Bitrix\Main\TaskOperation:TASK.OPERATION.NAME',
			),
			'filter' => array(
				'=ID' => $fromStatus,
				'=Bitrix\Sale\Internals\StatusGroupTaskTable:STATUS.GROUP_ID' => $groupId,
				'=Bitrix\Sale\Internals\StatusGroupTaskTable:STATUS.TASK.Bitrix\Main\TaskOperation:TASK.OPERATION.NAME' => $operations,
			),
		));

		while ($row = $result->fetch())
		{
			if (($key = array_search($row['NAME'], $operations)) !== false)
			{
				unset($operations[$key]);
			}
		}

		return !$operations;
	}

	/**
	 * Get statuses that user can switch to.
	 *
	 * @param $userId
	 * @param $fromStatus
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	public static function getAllowedUserStatuses($userId, $fromStatus)
	{
		return static::getAllowedGroupStatuses(static::getUserGroups($userId), $fromStatus);
	}

	/**
	 * @param $groupId
	 * @param $fromStatus
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	protected static function getAllowedGroupStatuses($groupId, $fromStatus)
	{
		static $cacheAllowStatuses = array();

		if (! is_array($groupId))
			$groupId = array($groupId);

		$cacheKey = md5($groupId."_".(is_array($fromStatus) ? join('|', $fromStatus) : $fromStatus));

		if (in_array('1', $groupId, true) || \CMain::GetUserRight('sale', $groupId) >= 'W') // Admin
		{
			if (!array_key_exists($cacheKey, $cacheAllowStatuses))
			{
				$result = static::getList(array(
					'select' => array(
						'ID',
						'NAME' => 'Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME'
					),
					'filter' => array(
						'=TYPE' => static::TYPE,
						'=Bitrix\Sale\Internals\StatusLangTable:STATUS.LID' => LANGUAGE_ID),
					'order'  => array(
						'SORT'
					),
				));

				while ($row = $result->fetch())
				{
					$cacheAllowStatuses[$cacheKey][$row['ID']] = $row['NAME'];
				}
			}
		}
		else
		{
			if (!array_key_exists($cacheKey, $cacheAllowStatuses))
			{
				$cacheAllowStatuses[$cacheKey] = array();

				$dbRes = static::getList(array( // check if group can change from status
					'select' => array('ID'),
					'filter' => array(
						'=ID' => $fromStatus,
						'=TYPE' => static::TYPE,
						'=Bitrix\Sale\Internals\StatusGroupTaskTable:STATUS.GROUP_ID' => $groupId,
						'=Bitrix\Sale\Internals\StatusGroupTaskTable:STATUS.TASK.Bitrix\Main\TaskOperation:TASK.OPERATION.NAME' => 'sale_status_from',
					),
					'limit' => 1,
				));

				if ($dbRes->fetch())
				{
					$result = static::getList(array(
						'select' => array('ID', 'NAME' => 'Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME'),
						'filter' => array(
							'=TYPE' => static::TYPE,
							'=Bitrix\Sale\Internals\StatusLangTable:STATUS.LID' => LANGUAGE_ID,
							'=Bitrix\Sale\Internals\StatusGroupTaskTable:STATUS.GROUP_ID' => $groupId,
							'=Bitrix\Sale\Internals\StatusGroupTaskTable:STATUS.TASK.Bitrix\Main\TaskOperation:TASK.OPERATION.NAME' => 'sale_status_to',
						),
						'order' => array('SORT'),
					));

					while ($row = $result->fetch())
					{
						$cacheAllowStatuses[$cacheKey][$row['ID']] = $row['NAME'];
					}
				}
			}
		}

		return $cacheAllowStatuses[$cacheKey] ?? [];
	}

	/**
	 * @param $names
	 * @return array
	 */
	private static function convertNamesToOperations($names)
	{
		$operations = array();

		foreach ($names as $name)
		{
			$operations[] = 'sale_status_'.mb_strtolower($name);
		}

		return $operations;
	}

	/**
	 * Get all statuses for current class type.
	 *
	 * @return mixed
	 * @throws Main\NotImplementedException
	 */
	public static function getAllStatuses()
	{
		static $statusList = array();

		if (!$statusList)
		{
			$result = static::getList(array(
				'select' => array('ID'),
				'filter' => array('=TYPE' => static::TYPE),
				'order'  => array('SORT' => 'ASC')
			));

			while ($row = $result->fetch())
			{
				$statusList[$row['ID']] = $row['ID'];
			}
		}

		return $statusList;
	}

	/**
	 * Get all statuses names for current class type.
	 *
	 * @param null $lang
	 * @return mixed
	 * @throws Main\NotImplementedException
	 */
	public static function getAllStatusesNames($lang = null)
	{
		$parameters = array(
			'select' => array("ID", "NAME" => 'Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME'),
			'filter' => array(
				'=TYPE' => static::TYPE,
			),
			'order'  => array('SORT' => 'ASC')
		);

		if ($lang !== null)
		{
			$parameters['filter']['=Bitrix\Sale\Internals\StatusLangTable:STATUS.LID'] = $lang;
		}
		elseif (defined("LANGUAGE_ID"))
		{
			$parameters['filter']['=Bitrix\Sale\Internals\StatusLangTable:STATUS.LID'] = LANGUAGE_ID;
		}

		static $allStatusesNames = array();

		if (!$allStatusesNames)
		{
			$result = static::getList($parameters);
			while ($row = $result->fetch())
			{
				$allStatusesNames[$row['ID']] = $row['NAME'];
			}
		}

		return $allStatusesNames;
	}

	/**
	 * Get statuses user can do operations within
	 *
	 * @param $userId
	 * @param array $operations
	 * @return array|mixed
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	public static function getStatusesUserCanDoOperations($userId, array $operations)
	{
		return static::getStatusesGroupCanDoOperations(static::getUserGroups($userId), $operations);
	}

	/**
	 * @param $groupId
	 * @param array $operations
	 * @return array|mixed
	 * @throws Main\NotImplementedException
	 */
	public static function getStatusesGroupCanDoOperations($groupId, array $operations)
	{
		static $cacheStatuses = array();

		if (!is_array($groupId))
			$groupId = array($groupId);

		$cacheHash = md5(static::TYPE."|".join('_', $groupId)."|".join('_', $operations));

		if (!empty($cacheStatuses[$cacheHash]))
		{
			return $cacheStatuses[$cacheHash];
		}

		if (in_array('1', $groupId, true) || \CMain::GetUserRight('sale', $groupId) >= 'W') // Admin
		{
			$statuses = static::getAllStatuses();
		}
		else
		{
			$statuses = static::getStatusesByGroupId($groupId, $operations);
		}

		$cacheStatuses[$cacheHash] = $statuses;

		return $statuses;
	}

	/**
	 * @param $groupId
	 * @param array $operations
	 * @return array
	 * @throws Main\NotImplementedException
	 */
	private static function getStatusesByGroupId(array $groupId, array $operations = array())
	{
		$operations = static::convertNamesToOperations($operations);

		$parameters = array(
			'select' => array(
				'ID',
				'OPERATION' => 'Bitrix\Sale\Internals\StatusGroupTaskTable:STATUS.TASK.Bitrix\Main\TaskOperation:TASK.OPERATION.NAME',
			),
			'filter' => array(
				'=TYPE' => static::TYPE,
				'=Bitrix\Sale\Internals\StatusGroupTaskTable:STATUS.GROUP_ID' => $groupId,
			),
			'order'  => array('SORT'),
		);

		if (!empty($operations))
		{
			$parameters['filter']['=Bitrix\Sale\Internals\StatusGroupTaskTable:STATUS.TASK.Bitrix\Main\TaskOperation:TASK.OPERATION.NAME'] = $operations;
		};

		$statuses = array();
		$dbRes = static::getList($parameters);
		while ($row = $dbRes->fetch())
		{
			if ((string)$row['OPERATION'] === '')
			{
				continue;
			}

			$statuses[$row['ID']] = $row['ID'];
		}

		return $statuses;
	}

	/**
	 * @throws Main\NotImplementedException
	 */
	public static function getInitialStatus()
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @throws Main\NotImplementedException
	 */
	public static function getFinalStatus()
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @param array $data
	 * @throws Main\ArgumentException
	 * @throws SystemException
	 * @throws \Exception
	 */
	public static function install(array $data)
	{
		if (! ($statusId = $data['ID']) || ! is_string($statusId))
		{
			throw new SystemException('invalid status ID', 0, __FILE__, __LINE__);
		}

		if ($languages = $data['LANG'])
		{
			unset($data['LANG']);

			if (! is_array($languages))
				throw new SystemException('invalid status LANG', 0, __FILE__, __LINE__);
		}

		$data['TYPE'] = static::TYPE;

		// install status if it is not installed

		if (! StatusTable::getById($statusId)->fetch())
		{
			StatusTable::add($data);
		}

		// install status languages if they are not installed

		if ($languages)
		{
			$installedLanguages = array();

			$result = StatusLangTable::getList(array(
				'select' => array('LID'),
				'filter' => array('=STATUS_ID' => $statusId),
			));

			while ($row = $result->fetch())
			{
				$installedLanguages[$row['LID']] = true;
			}

			foreach ($languages as $language)
			{
				if (! is_array($language))
					throw new SystemException('invalid status language', 0, __FILE__, __LINE__);

				if (! $installedLanguages[$language['LID']])
				{
					$language['STATUS_ID'] = $statusId;

					StatusLangTable::add($language);
				}
			}
		}
	}
}
