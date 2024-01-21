<?php

namespace Bitrix\Mail\Helper;

use Bitrix\Mail\Internals\MailEntityOptionsTable;
use Bitrix\Main;
use Bitrix\Bitrix24;
use Bitrix\Mail\MailboxTable;
use Bitrix\Mail\Integration\Im\Notification;
use Bitrix\Main\Mail\Internal\SenderTable;

/**
 * Class LicenseManager
 */
class LicenseManager
{
	private const MAILBOX_IS_LOCKED_PROPERTY = 1;
	private const MAILBOX_IS_AVAILABLE_PROPERTY = 0;

	public static function checkTheMailboxForSyncAvailability(int $checkedMailboxId): bool
	{
		$maxCountAvailableMailboxes = self::getUserMailboxesLimit();

		if ($maxCountAvailableMailboxes < 0)
		{
			return true;
		}

		static $checkedMailboxes = [];

		if (!array_key_exists($checkedMailboxId, $checkedMailboxes))
		{
			$checkedMailboxes[$checkedMailboxId] = MailboxTable::getById($checkedMailboxId)->fetch();
		}

		$mailboxAvailabilitySyncStatus = false;

		if ($checkedMailboxes[$checkedMailboxId] && isset($checkedMailboxes[$checkedMailboxId]['USER_ID']))
		{
			$userId = (int) $checkedMailboxes[$checkedMailboxId]['USER_ID'];

			static $userMailboxes = [];

			if (!array_key_exists($userId, $userMailboxes))
			{
				$userMailboxes[$userId] = MailboxTable::getList([
					'select' => [
						'ID',
					],
					'filter' => [
						'=USER_ID' => $userId,
						'=ACTIVE' => 'Y',
					],
					'order' => [
						'ID' => 'ASC',
					],
				])->fetchAll();
			}

			$mailboxNumber = 1;

			foreach ($userMailboxes[$userId] as $mailbox)
			{
				if (isset($mailbox['ID']))
				{
					$mailboxId = (int) $mailbox['ID'];
					if ($mailboxNumber <= $maxCountAvailableMailboxes)
					{
						if ($mailboxId === $checkedMailboxId)
						{
							$mailboxAvailabilitySyncStatus = true;
						}

						static::removeTariffRestrictionsOnTheMailbox($mailboxId);
					}
					else
					{
						static::imposeTariffRestrictionsOnTheMailbox($mailboxId);
					}
				}

				$mailboxNumber ++;
			}
		}

		return $mailboxAvailabilitySyncStatus;
	}

	private static function getMailboxTariffRestrictions($mailboxId, $overwriteCache = false): int
	{
		static $mailboxesRestriction = [];

		if ($overwriteCache || !array_key_exists($mailboxId, $mailboxesRestriction))
		{
			$filter = [
				'=MAILBOX_ID' => $mailboxId,
				'=ENTITY_TYPE' => 'MAILBOX',
				'=ENTITY_ID' => $mailboxId,
				'=PROPERTY_NAME' => 'TARIFF_RESTRICTIONS',
			];

			$restriction = MailEntityOptionsTable::getList([
				'select' => [
					'VALUE',
				],
				'filter' => $filter,
				'limit' => 1,
			])->fetch();

			if (isset($restriction['VALUE']))
			{
				$mailboxesRestriction[$mailboxId] = (int) $restriction['VALUE'];
			}
			else
			{
				$mailboxesRestriction[$mailboxId] = 0;
			}
		}

		return $mailboxesRestriction[$mailboxId];
	}

	private static function removeTariffRestrictionsOnTheMailbox($mailboxId): void
	{
		if (static::getMailboxTariffRestrictions($mailboxId) !== static::MAILBOX_IS_AVAILABLE_PROPERTY)
		{
			static::setTheOptionOfTariffRestrictions($mailboxId, static::MAILBOX_IS_AVAILABLE_PROPERTY);
			static::getMailboxTariffRestrictions($mailboxId, true);
		}
	}

	private static function imposeTariffRestrictionsOnTheMailbox($mailboxId): void
	{
		if (static::getMailboxTariffRestrictions($mailboxId) !== static::MAILBOX_IS_LOCKED_PROPERTY)
		{
			Notification::add(
				null,
				'imposed_tariff_restrictions_on_the_mailbox',
				null,
				$mailboxId
			);

			static::setTheOptionOfTariffRestrictions($mailboxId, static::MAILBOX_IS_LOCKED_PROPERTY);
			static::getMailboxTariffRestrictions($mailboxId, true);
		}
	}

	private static function setTheOptionOfTariffRestrictions($mailboxId, $optionValue): void
	{
		$filter = [
			'=MAILBOX_ID' => $mailboxId,
			'=ENTITY_TYPE' => 'MAILBOX',
			'=ENTITY_ID' => $mailboxId,
			'=PROPERTY_NAME' => 'TARIFF_RESTRICTIONS',
		];

		$keyRow = [
			'MAILBOX_ID' => $mailboxId,
			'ENTITY_TYPE' => 'MAILBOX',
			'ENTITY_ID' => $mailboxId,
			'PROPERTY_NAME' => 'TARIFF_RESTRICTIONS',
		];

		$fields = $keyRow;

		$fields['DATE_INSERT'] = new Main\Type\DateTime();
		$fields['VALUE'] = $optionValue;

		if (MailEntityOptionsTable::getCount($filter))
		{
			MailEntityOptionsTable::update(
				$keyRow,
				[
					'VALUE' => $optionValue,
					'DATE_INSERT' => new Main\Type\DateTime(),
				],
			);
		}
		else
		{
			MailEntityOptionsTable::add(
				$fields
			);
		}
	}

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

	private static function getCountSenders(int $userId = 0)
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


		return SenderTable::getCount([
			'IS_CONFIRMED' => true,
			[
				'LOGIC' => 'OR',
				'=USER_ID' => $userId,
				'IS_PUBLIC' => true,
			],
		]);
	}

	public static function isMailClientReadyToUse($userId = null): bool
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

		if (
			self::isSyncAvailable()
			&& (self::getCountSenders($userId) || count(MailboxTable::getUserMailboxes($userId)) > 0 )
			&& self::checkUserHasNotExceededTheConnectedMailboxesLimit($userId)
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
