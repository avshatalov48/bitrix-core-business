<?php

namespace Bitrix\Mail\Helper;

use Bitrix\Main;

/**
 * Class LicenseManager
 */
class LicenseManager
{

	public static function getSharedMailboxesLimit()
	{
		if (!Main\Loader::includeModule('bitrix24'))
		{
			return -1;
		}

		return (int) Main\Config\Option::get('mail', 'shared_mailboxes_limit', -1);
	}

	/** How many mailboxes per one user can be connected
	 * @return int|null
	 */
	public static function getUserMailboxesLimit()
	{
		if (!Main\Loader::includeModule('bitrix24'))
		{
			return -1;
		}

		return (int) Main\Config\Option::get('mail', 'user_mailboxes_limit', -1);
	}

	/**
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getSyncOldLimit()
	{
		if (!Main\ModuleManager::isModuleInstalled('bitrix24'))
		{
			return -1;
		}

		return (int) Main\Config\Option::get('mail', 'sync_old_limit', -1);
	}

}
