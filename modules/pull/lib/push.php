<?php
namespace Bitrix\Pull;

class Push
{
	static $types = null;
	static $config = array();

	public static function add($users, $parameters)
	{
		unset($parameters['command']);
		unset($parameters['params']);
		return \Bitrix\Pull\Event::add($users, $parameters);
	}

	public static function send()
	{
		return \Bitrix\Pull\Event::send();
	}

	public static function getTypes()
	{
		if (is_array(self::$types))
		{
			return self::$types;
		}

		if (!\Bitrix\Main\Loader::includeModule('im'))
		{
			return Array();
		}

		$notifySchema = \CIMNotifySchema::GetNotifySchema();

		$result = Array();
		foreach ($notifySchema as $moduleId => $module)
		{
			if (strlen($module['NAME']) <= 0)
			{
				$info = \CModule::CreateModuleObject($moduleId);
				$name= $info->MODULE_NAME;
			}
			else
			{
				$name = $module['NAME'];
			}

			$types = Array();
			foreach ($module['NOTIFY'] as $notifyType => $notifyConfig)
			{
				if (!$notifyConfig['PUSH'] && $notifyConfig['DISABLED']['PUSH'])
				{
					continue;
				}
				$types[$notifyType] = Array(
					'NAME' => $notifyConfig['NAME'],
					'TYPE' => $notifyType,
					'DISABLED' => (bool)$notifyConfig['DISABLED']['PUSH'],
					'DEFAULT' => (bool)$notifyConfig['PUSH'],
				);
			}
			if (empty($types))
			{
				continue;
			}

			$result[$moduleId] = Array(
				'NAME' => $name,
				'MODULE_ID' => $moduleId,
				'TYPES' => $types
			);
		}

		self::$types = $result;

		return $result;
	}

	public static function getConfig($userId = null)
	{
		if (!\Bitrix\Main\Loader::includeModule('im'))
		{
			return Array();
		}

		if (is_null($userId) && is_object($GLOBALS['USER']))
		{
			$userId = $GLOBALS['USER']->getId();
		}

		$userId = intval($userId);
		if (!$userId)
		{
			return false;
		}

		if (isset(self::$config[$userId]))
		{
			return self::$config[$userId];
		}

		$pushDisabled = !\Bitrix\Pull\Push::getStatus($userId);

		$userOptions = \CUserOptions::GetOption('im', 'notify', Array(), $userId);

		$result = Array();
		foreach ($userOptions as $optionId => $optionValue)
		{
			list($clientId, $moduleId, $type) =  explode('|', $optionId);
			if ($clientId != \CIMSettings::CLIENT_PUSH)
			{
				continue;
			}

			$result[$moduleId][$type] = (bool)$optionValue;
		}

		$notifySchema = \CIMNotifySchema::GetNotifySchema();

		foreach ($notifySchema as $moduleId => $module)
		{
			foreach ($module['NOTIFY'] as $notifyType => $notifyConfig)
			{
				if ($pushDisabled)
				{
					$result[$moduleId][$notifyType] = false;
					continue;
				}

				if (!$notifyConfig['PUSH'] && $notifyConfig['DISABLED']['PUSH'])
				{
					continue;
				}

				if (!isset($result[$moduleId][$notifyType]) || $notifyConfig['DISABLED']['PUSH'])
				{
					$result[$moduleId][$notifyType] = (bool)$notifyConfig['PUSH'];
				}
			}
		}

		self::$config[$userId] = $result;

		return $result;
	}

	public static function setConfig($config, $userId = null)
	{
		if (!\Bitrix\Main\Loader::includeModule('im'))
		{
			return false;
		}

		if (!is_array($config))
		{
			return false;
		}

		if (is_null($userId) && is_object($GLOBALS['USER']))
		{
			$userId = $GLOBALS['USER']->getId();
		}
		$userId = intval($userId);
		if ($userId <= 0)
		{
			return false;
		}

		$types = self::getTypes();
		$userConfig = self::getConfig($userId);
		$userOptions = \CUserOptions::GetOption('im', 'notify', Array(), $userId);

		$needUpdate = false;
		foreach ($types as $moduleId => $module)
		{
			foreach ($module['TYPES'] as $typeId => $type)
			{
				if (isset($config[$moduleId][$typeId]))
				{
					$needUpdate = true;
					$userConfig[$moduleId][$typeId] = (bool)$config[$moduleId][$typeId];
				}
				if ($type['DEFAULT'] == $userConfig[$moduleId][$typeId])
				{
					unset($userOptions['push|'.$moduleId.'|'.$typeId]);
				}
				else
				{
					$userOptions['push|'.$moduleId.'|'.$typeId] = $userConfig[$moduleId][$typeId];
				}
			}
		}

		if ($needUpdate)
		{
			\CUserOptions::SetOption('im', 'notify', $userOptions, false, $userId);
			\CIMSettings::ClearCache($userId);
			unset(self::$config[$userId]);
		}

		return true;
	}

	public static function setConfigTypeStatus($moduleId, $typeId, $status, $userId = null)
	{
		return self::setConfig(Array($moduleId => Array($typeId => $status)), $userId);
	}

	public static function getConfigTypeStatus($moduleId, $typeId, $userId = null)
	{
		$config = self::getConfig($userId);
		return isset($config[$moduleId][$typeId])? $config[$moduleId][$typeId]: true;
	}

	public static function getStatus($userId = null)
	{
		if (!\CPullOptions::GetPushStatus())
		{
			return null;
		}

		if (is_null($userId) && is_object($GLOBALS['USER']))
		{
			$userId = $GLOBALS['USER']->getId();
		}
		$userId = intval($userId);
		if (!$userId)
		{
			return false;
		}

		return (bool)\CUserOptions::GetOption('pull', 'push_status', true, $userId);
	}

	public static function setStatus($status, $userId = null)
	{
		if (!\CPullOptions::GetPushStatus())
		{
			return null;
		}

		if (is_null($userId) && is_object($GLOBALS['USER']))
		{
			$userId = $GLOBALS['USER']->getId();
		}
		$userId = intval($userId);
		if (!$userId)
		{
			return false;
		}

		$status = $status === false? false: true;

		return (bool)\CUserOptions::SetOption('pull', 'push_status', $status, false, $userId);
	}
}