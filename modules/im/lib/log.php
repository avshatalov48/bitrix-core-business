<?php
namespace Bitrix\Im;

class Log
{
	public static function write($data)
	{
		if (!is_array($data['USER_ID']))
		{
			$data['USER_ID'] = [(int)$data['USER_ID']];
		}

		$users = [];
		foreach ($data['USER_ID'] as $uid)
		{
			$uid = (int)$uid;
			if ($uid)
			{
				$users[] = $uid;
			}
		}

		if (empty($users))
		{
			return;
		}

		$action = $data['ACTION']?: 'UNKNOWN';
		$params = $data['PARAMS']?: [];
		$device = $data['DEVICE']?: 'UNKNOWN';

		global $USER;

		if (!in_array((int)$USER->GetID(), $users, true))
		{
			return;
		}

		$logUserId = $USER->GetID();

		$sessionId = \Bitrix\Main\Application::getInstance()->getKernelSession()->getId();
		$logName = md5($sessionId);
		$scriptName = \Bitrix\Main\Context::getCurrent()->getServer()->getScriptName();
		$userIp = \Bitrix\Main\Context::getCurrent()->getRequest()->getRemoteAddress();
		if ($device === 'UNKNOWN')
		{
			$device = mb_strpos($scriptName, 'desktop_app')? 'DESKTOP' : 'BROWSER';
		}

		$log = "\n------------------------\n";
		$log .= date("Y.m.d G:i:s")."\n";
		$log .= $action.' ['.$device.' - '.$userIp.' :: '.$sessionId."]\n";
		$log .= print_r($params, 1);
		$log .= "\n------------------------\n";

		\Bitrix\Main\IO\File::putFileContents($_SERVER["DOCUMENT_ROOT"]."/../logs/im/$logUserId/$logName.log", $log, \Bitrix\Main\IO\File::APPEND);
	}
}

