<?php
namespace Bitrix\Pull;

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

		return (bool)\CUserOptions::SetOption('pull', 'push_smartfilter_status', $status, $userId);
	}
}