<?php
namespace Bitrix\Im;

class Department
{
	public static function getColleagues($userId = null, $options = array())
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$pagination = isset($options['LIST'])? true: false;

		$limit = isset($options['LIST']['LIMIT'])? intval($options['LIST']['LIMIT']): 50;
		$offset = isset($options['LIST']['OFFSET'])? intval($options['LIST']['OFFSET']): 0;

		$list = Array();

		$departments = \Bitrix\Im\User::getInstance($userId)->getDepartments();
		$managers = self::getManagers($departments);
		foreach ($managers as $departmentId => $users)
		{
			foreach ($users as $uid)
			{
				if ($userId == $uid)
					continue;

				$list[$uid] = $uid;
			}
		}

		$employees = self::getEmployees($departments);
		foreach ($employees as $departmentId => $users)
		{
			foreach ($users as $uid)
			{
				if ($userId == $uid)
					continue;

				$list[$uid] = $uid;
			}
		}

		$result = self::getDepartmentYouManage($userId);
		if (!empty($result))
		{
			$managers = self::getManagers(null);
			foreach ($managers as $departmentId => $users)
			{
				foreach ($users as $uid)
				{
					if ($userId == $uid)
						continue;

					$list[$uid] = $uid;
				}
			}
		}

		$count = count($list);

		$list = array_slice($list, $offset, $limit);

		if ($options['USER_DATA'] == 'Y')
		{
			$result = Array();

			$getOptions = Array();
			if ($options['JSON'] == 'Y')
			{
				$getOptions['JSON'] = 'Y';
			}

			foreach ($list as $userId)
			{
				$result[] = \Bitrix\Im\User::getInstance($userId)->getArray($getOptions);
			}
		}
		else
		{
			$result = array_values($list);
		}


		if ($options['JSON'] == 'Y')
		{
			$result = $pagination? Array('total' => $count, 'result' => $result): $result;
		}
		else
		{
			$result = $pagination? Array('TOTAL' => $count, 'RESULT' => $result): $result;
		}

		return $result;
	}

	public static function getDepartmentYouManage($userId = null, $options = array())
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$list = \Bitrix\Im\Integration\Intranet\Department::getList();

		$result = Array();
		foreach ($list as $key => $department)
		{
			if ($department['MANAGER_USER_ID'] != $userId)
			{
				continue;
			}
			if ($options['USER_DATA'] == 'Y')
			{
				$userData = \Bitrix\Im\User::getInstance($department['MANAGER_USER_ID']);
				$department['MANAGER_USER_DATA'] = $options['JSON'] == 'Y'? $userData->getArray(Array('JSON' => 'Y')): $userData;
			}

			$result[$key] = $options['JSON'] == 'Y'? array_change_key_case($department, CASE_LOWER): $department;
		}

		if ($options['JSON'] == 'Y')
		{
			$result = array_values($result);
		}

		return $result;
	}

	public static function getStructure($options = array())
	{
		$list = \Bitrix\Im\Integration\Intranet\Department::getList();

		if (isset($options['FILTER']['ID']))
		{
			foreach ($list as $key => $department)
			{
				if (!in_array($department['ID'], $options['FILTER']['ID']))
				{
					unset($list[$key]);
				}
			}
		}

		$pagination = isset($options['LIST'])? true: false;

		$limit = isset($options['LIST']['LIMIT'])? intval($options['LIST']['LIMIT']): 50;
		$offset = isset($options['LIST']['OFFSET'])? intval($options['LIST']['OFFSET']): 0;

		if (isset($options['FILTER']['SEARCH']) && strlen($options['FILTER']['SEARCH']) > 1)
		{
			$count = 0;
			$breakAfterDigit = $offset === 0? $offset: false;

			$options['FILTER']['SEARCH'] = ToLower($options['FILTER']['SEARCH']);
			foreach ($list as $key => $department)
			{
				$checkField = ToLower($department['FULL_NAME']);
				if (
					strpos($checkField, $options['FILTER']['SEARCH']) !== 0
					&& strpos($checkField, ' '.$options['FILTER']['SEARCH']) === false
				)
				{
					unset($list[$key]);
				}
				if ($breakAfterDigit !== false)
				{
					$count++;
					if ($count === $breakAfterDigit)
					{
						break;
					}
				}
			}
		}

		$count = count($list);

		$list = array_slice($list, $offset, $limit);

		if ($options['JSON'] == 'Y' || $options['USER_DATA'] == 'Y')
		{
			if ($options['JSON'] == 'Y')
			{
				$list = array_values($list);
			}
			foreach ($list as $key => $department)
			{
				if ($options['USER_DATA'] == 'Y')
				{
					$userData = \Bitrix\Im\User::getInstance($department['MANAGER_USER_ID']);
					$department['MANAGER_USER_DATA'] = $options['JSON'] == 'Y'? $userData->getArray(Array('JSON' => 'Y')): $userData;
				}

				$list[$key] = $options['JSON'] == 'Y'? array_change_key_case($department, CASE_LOWER): $department;
			}
		}

		if ($options['JSON'] == 'Y')
		{
			$list = $pagination? Array('total' => $count, 'result' => $list): $list;
		}
		else
		{
			$list = $pagination? Array('TOTAL' => $count, 'RESULT' => $list): $list;
		}

		return $list;
	}

	public static function getManagers($ids = null, $options = array())
	{
		$list = \Bitrix\Im\Integration\Intranet\Department::getList();

		$userOptions = Array();
		if ($options['JSON'])
		{
			$userOptions['JSON'] = 'Y';
		}

		$managers = Array();
		foreach ($list as $department)
		{
			if ($department['MANAGER_USER_ID'] <= 0)
				continue;

			if (is_array($ids) && !in_array($department['ID'], $ids))
				continue;

			if ($options['USER_DATA'] == 'Y')
			{
				$managers[$department['ID']][] = \Bitrix\Im\User::getInstance($department['MANAGER_USER_ID'])->getArray($userOptions);
			}
			else
			{
				$managers[$department['ID']][] = $department['MANAGER_USER_ID'];
			}
		}

		return $managers;
	}

	public static function getEmployeesList($ids = null, $options = array())
	{
		if (!\Bitrix\Main\Loader::includeModule('intranet'))
		{
			return Array();
		}

		$structure = \CIntranetUtils::GetStructure();
		if (!$structure || !isset($structure['DATA']))
		{
			return Array();
		}

		$result = Array();
		foreach ($structure['DATA'] as $department)
		{
			if (is_array($ids) && !in_array($department['ID'], $ids))
				continue;

			if (!is_array($department['EMPLOYEES']))
			{
				$result[$department['ID']] = Array();
				continue;
			}

			foreach ($department['EMPLOYEES'] as $key => $value)
			{
				$department['EMPLOYEES'][$key] = (int)$value;
			}

			$result[$department['ID']] = $department['EMPLOYEES'];
		}

		return $result;
	}

	public static function getEmployees($ids = null, $options = array())
	{
		$list = self::getEmployeesList();

		$userOptions = Array();
		if ($options['JSON'])
		{
			$userOptions['JSON'] = 'Y';
		}

		$employees = Array();
		foreach ($list as $departmentId => $users)
		{
			if (is_array($ids) && !in_array($departmentId, $ids))
				continue;

			foreach ($users as $employeeId)
			{
				if ($options['USER_DATA'] == 'Y')
				{
					$employees[$departmentId][] = \Bitrix\Im\User::getInstance($employeeId)->getArray($userOptions);
				}
				else
				{
					$employees[$departmentId][] = $employeeId;
				}
			}
		}

		return $employees;
	}
}