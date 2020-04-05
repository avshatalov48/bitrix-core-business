<?php

namespace Bitrix\Mail\Helper;

use Bitrix\Main;
use Bitrix\Bitrix24;

/**
 * Class LicenseManager
 */
class LicenseManager
{

	/**
	 * Checks if mailboxes synchronization is available
	 *
	 * @return bool
	 */
	public static function isSyncAvailable()
	{
		if (!Main\Loader::includeModule('bitrix24'))
		{
			return true;
		}

		return (bool) Bitrix24\Feature::isFeatureEnabled('mail_mailbox_sync');
	}

	public static function getSharedMailboxesLimit()
	{
		if (!Main\Loader::includeModule('bitrix24'))
		{
			return -1;
		}

		return (int) Bitrix24\Feature::getVariable('mail_shared_mailboxes_limit');
	}

	/**
	 * How many mailboxes a user can connect
	 *
	 * @return int
	 */
	public static function getUserMailboxesLimit()
	{
		if (!Main\Loader::includeModule('bitrix24'))
		{
			return -1;
		}

		if (!static::isSyncAvailable())
		{
			return 0;
		}

		return (int) Bitrix24\Feature::getVariable('mail_user_mailboxes_limit');
	}

	/**
	 * Returns the number of days to store messages
	 *
	 * @return int
	 */
	public static function getSyncOldLimit()
	{
		if (!Main\Loader::includeModule('bitrix24'))
		{
			return (int) Main\Config\Option::get('mail', 'sync_old_limit2', -1);
		}

		return (int) Bitrix24\Feature::getVariable('mail_sync_old_limit');
	}

	/**
	 * Checks if old messages should be deleted
	 *
	 * @return bool
	 */
	public static function isCleanupOldEnabled()
	{
		return static::getSyncOldLimit() > 0;
	}

}
