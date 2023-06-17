<?php

namespace Bitrix\Mail\Helper;

use Bitrix\Main;
use Bitrix\Bitrix24;
use Bitrix\Mail\MailboxTable;

/**
 * Class LicenseManager
 */
class LicenseManager
{
	public static function isEnabledNotificationOfMailMessageInCrm($userId): bool
	{
		if (Main\Loader::includeModule('crm') && Main\Loader::includeModule('im'))
		{
			foreach (
				[
					\CIMSettings::CLIENT_SITE,
					\CIMSettings::CLIENT_MAIL,
					\CIMSettings::CLIENT_PUSH,
				] as $clientId
			)
			{
				if (
					\CIMSettings::GetNotifyAccess(
						$userId,
						'crm',
						\CCrmNotifierSchemeType::IncomingEmailName,
						$clientId
					)
				)
				{
					return true;
				}
			}
		}

		return false;
	}

	public static function isMailClientReadyToUse(): bool
	{
		if (
			self::isSyncAvailable()
			&& count(MailboxTable::getUserMailboxes()) > 0
			&& self::checkUserHasNotExceededTheConnectedMailboxesLimit()
		)
		{
			return true;
		}

		return false;
	}

	public static function checkUserHasNotExceededTheConnectedMailboxesLimit($userId = null): bool
	{
		global $USER;

		if (!($userId > 0 || (is_object($USER) && $USER->isAuthorized())))
		{
			return false;
		}

		if (!($userId > 0))
		{
			$userId = $USER->getId();
		}

		$userMailboxesLimit = LicenseManager::getUserMailboxesLimit();

		if (
			$userMailboxesLimit >= 0
			&& count(MailboxTable::getTheOwnersMailboxes($userId)) > $userMailboxesLimit
		)
		{
			return false;
		}

		return true;
	}

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

	public static function getEmailsLimitToSendMessage(): int
	{
		if (Main\Loader::includeModule('bitrix24') && (!static::isSyncAvailable() || \CBitrix24::IsDemoLicense()))
		{
			return 1;
		}

		return -1;
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
