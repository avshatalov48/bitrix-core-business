<?php
namespace Bitrix\Pull;

use Bitrix\Main\Config\Option;

class PushSmartfilter
{
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

		$isSmartFilterDisabledByDefault =  Option::get('pull', 'is_smartfilter_disabled_by_default', 'N') === 'Y';
		if ($isSmartFilterDisabledByDefault)
		{
			return (bool)\CUserOptions::GetOption('pull', 'push_smartfilter_status_v2', false, $userId);
		}

		return (bool)\CUserOptions::GetOption('pull', 'push_smartfilter_status', true, $userId);
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

		$isSmartFilterDisabledByDefault =  Option::get('pull', 'is_smartfilter_disabled_by_default', 'N') === 'Y';
		if ($isSmartFilterDisabledByDefault)
		{
			return (bool)\CUserOptions::SetOption('pull', 'push_smartfilter_status_v2', $status, false, $userId);
		}

		return (bool)\CUserOptions::SetOption('pull', 'push_smartfilter_status', $status, false, $userId);
	}
}