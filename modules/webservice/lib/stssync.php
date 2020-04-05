<?php
namespace Bitrix\WebService;

use Bitrix\Intranet\OutlookApplication;
use Bitrix\Main\Authentication\ApplicationManager;
use Bitrix\Main\Authentication\ApplicationPasswordTable;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Type\DateTime;

class StsSync
{
	const SERVICE_PATH = '/stssync/';

	public static function getUrl($type, $servicePath, $linkUrl, $prefix, $name, $guid)
	{
		\CJSCore::Init(array('stssync'));

		$port = Context::getCurrent()->getRequest()->isHttps() ? 443 : 80;

		if(Loader::includeModule('ldap'))
		{
			$port = \CLdapUtil::getTargetPort();
		}

		return 'BX.StsSync.sync(\''.$type.'\', \''.static::SERVICE_PATH.$servicePath.'\', \''.\CUtil::jsEscape($linkUrl).'\', \''.\CUtil::jsEscape($prefix).'\', \''.\CUtil::jsEscape($name).'\', \''.$guid.'\', '.intval($port).')';
	}

	public static function checkAuth($userId, $ap)
	{
		global $USER;

		if(Loader::includeModule('intranet'))
		{
			$appPassword = ApplicationPasswordTable::findPassword($userId, $ap);
			if($appPassword !== false)
			{
				if($appPassword["APPLICATION_ID"] === OutlookApplication::ID)
				{
					$appManager = ApplicationManager::getInstance();
					if($appManager->checkScope($appPassword["APPLICATION_ID"]) === true)
					{
						ApplicationPasswordTable::update($appPassword["ID"], array(
							'DATE_LOGIN' => new DateTime(),
							'LAST_IP' => Context::getCurrent()->getRequest()->getRemoteAddress(),
						));

						setSessionExpired(true);
						return $USER->Authorize($userId);
					}
				}
			}
		}

		return false;
	}

	public static function getAuth($type = '')
	{
		if(Loader::includeModule('intranet'))
		{
			return \Bitrix\Intranet\OutlookApplication::generateAppPassword($type);
		}
		else
		{
			return false;
		}
	}
}