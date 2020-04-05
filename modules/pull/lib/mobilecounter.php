<?php
namespace Bitrix\Pull;

class MobileCounter
{
	const MOBILE_APP = 'Bitrix24';

	public static function getTypes()
	{
		$types = Array();

		$event = new \Bitrix\Main\Event("pull", "onGetMobileCounterTypes");
		$event->send();

		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() != \Bitrix\Main\EventResult::SUCCESS)
			{
				continue;
			}

			$result = $eventResult->getParameters();
			if (!is_array($types))
			{
				continue;
			}

			foreach ($result as $type => $config)
			{
				$config['TYPE'] = $eventResult->getModuleId().'_'.$type;
				$types[$eventResult->getModuleId().'_'.$type] = $config;
			}
		}

		return $types;
	}

	public static function get($userId = null)
	{
		if (is_null($userId) && is_object($GLOBALS['USER']))
		{
			$userId = $GLOBALS['USER']->getId();
		}

		$userId = intval($userId);
		if (!$userId)
		{
			return false;
		}

		$counter = 0;

		if (IsModuleInstalled('intranet'))
		{
			if (\Bitrix\Main\Loader::includeModule('im')) // TODO remove IM include!
			{
				$siteId = \Bitrix\Im\User::getInstance($userId)->isExtranet()? 'ex': 's1';
			}
			else
			{
				$siteId = 's1';
			}
		}
		else
		{
			$siteId = \Bitrix\Main\Context::getCurrent()->getSite();
			if (!$siteId)
			{
				$siteId = 's1';
			}
		}

		$event = new \Bitrix\Main\Event("pull", "onGetMobileCounter", array(
			'USER_ID' => $userId,
			'SITE_ID' => $siteId
		));
		$event->send();

		$typeStatus = self::getConfig($userId);

		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() != \Bitrix\Main\EventResult::SUCCESS)
			{
				continue;
			}

			$result = $eventResult->getParameters();

			$type = $eventResult->getModuleId().'_'.$result['TYPE'];
			if ($typeStatus[$type] === false)
			{
				continue;
			}

			if (intval($result['COUNTER']) > 0)
			{
				$counter += $result['COUNTER'];
			}
		}

		return $counter;
	}

	public static function getConfig($userId = null)
	{
		if (is_null($userId) && is_object($GLOBALS['USER']))
		{
			$userId = $GLOBALS['USER']->getId();
		}

		$userId = intval($userId);
		if ($userId <= 0)
		{
			return false;
		}

		$types = Array();

		foreach (self::getTypes() as $type => $config)
		{
			$types[$type] = $config['DEFAULT'];
		}

		$options = \CUserOptions::GetOption('pull', 'mobileCounterType', Array(), $userId);
		foreach ($options as $type => $default)
		{
			$types[$type] = $default;
		}

		return $types;
	}

	public static function setConfigType($type, $status, $userId = null)
	{
		return self::setConfig(Array($type => $status), $userId);
	}

	public static function setConfig($config, $userId = null)
	{
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

		$needUpdate = false;
		$types = self::getConfig($userId);

		foreach ($config as $type => $status)
		{
			if (!isset($types[$type]))
			{
				continue;
			}
			$types[$type] = (bool)$status;
			$needUpdate = true;
		}

		if ($needUpdate)
		{
			\CUserOptions::SetOption('pull', 'mobileCounterType', $types, $userId);
		}

		return true;
	}

	public static function send($userId = null, $appId = self::MOBILE_APP)
	{
		if (is_null($userId) && is_object($GLOBALS['USER']))
		{
			$userId = $GLOBALS['USER']->getId();
		}

		$userId = intval($userId);
		if ($userId <= 0)
		{
			return false;
		}

		\Bitrix\Pull\Push::add($userId, Array(
			'module_id' => 'pull',
			'push' => Array('badge' => 'Y')
		));

		return true;
	}

	public static function onSonetLogCounterClear($counterType = '', $timestamp = 0)
	{
		$userId = is_object($GLOBALS['USER'])? intval($GLOBALS['USER']->getId()): 0;

		if (
			$userId <= 0
			|| $counterType != '**'
		)
		{
			return false;
		}

		self::send($userId);

		return true;
	}
}