<?php

namespace Bitrix\Mail\Helper;

use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Mail\Internals\MailEntityOptionsTable;
use Bitrix\Main;
use Bitrix\Bitrix24;
use Bitrix\Mail\MailboxTable;
use Bitrix\Main\Mail\Internal\SenderTable;
use Bitrix\Main\Type\DateTime;
use Bitrix\Mail\Integration\Im\Notification;

/**
 * Class LicenseManager
 */
class LicenseManager
{
	private const MAILBOX_IS_LOCKED_PROPERTY = 1;
	private const MAILBOX_IS_AVAILABLE_PROPERTY = 0;

	private static function sendNotificationsAboutBlockedMailboxes($ids): void
	{
		foreach ($ids as $id)
		{
			Notification::add(
				null,
				'imposed_tariff_restrictions_on_the_mailbox',
				null,
				$id,
			);
		}
	}

	private static function getTariffRestrictionsMailboxListsByDBData($mailboxes, $filterByStatus = null): array
	{
		$lists = [
			'IDS_FOR_ADD' => [],
			'IDS_FOR_UPDATE' => [],
			'IDS_ALL' => [],
		];

		foreach ($mailboxes as $mailbox)
		{
			$mailboxId = (int) $mailbox['ID'];

			if (is_null($mailbox['TARIFF_RESTRICTIONS']))
			{
				$lists['IDS_FOR_ADD'][] = $mailboxId;
				$lists['IDS_ALL'][] = $mailboxId;
			}
			else if(is_null($filterByStatus) || (int)$mailbox['TARIFF_RESTRICTIONS'] === $filterByStatus)
			{
				$lists['IDS_FOR_UPDATE'][] = $mailboxId;
				$lists['IDS_ALL'][] = $mailboxId;
			}
		}

		return $lists;
	}

	private static function checkIdInTariffRestrictionsMailboxLists($checkedMailboxId, $lists): bool
	{
		foreach ($lists as $list)
		{
			foreach ($list as $id)
			{
				if ($id === $checkedMailboxId)
				{
					return true;
				}
			}
		}

		return false;
	}

	private static function getAvailabilitySyncStatusByMailboxList($checkedMailboxId, $mailboxList): bool
	{
		foreach ($mailboxList as $mailbox)
		{
			if ($checkedMailboxId === (int) $mailbox['ID'])
			{
				return !($mailbox['TARIFF_RESTRICTIONS']);
			}
		}

		return true;
	}

	public static function checkTheMailboxForSyncAvailability(int $checkedMailboxId): bool
	{
		$maxCountAvailableMailboxes = self::getUserMailboxesLimit();
		static $checkedMailboxes = [];

		if (!array_key_exists($checkedMailboxId, $checkedMailboxes))
		{
			$checkedMailboxes[$checkedMailboxId] = MailboxTable::getList(([
				'select' => [
					'ID',
					'USER_ID',
				],
				'filter' => [
					'=ID' => $checkedMailboxId,
				],
			]))->fetch();
		}

		if ($checkedMailboxes[$checkedMailboxId] && isset($checkedMailboxes[$checkedMailboxId]['USER_ID']))
		{
			$userId = (int) $checkedMailboxes[$checkedMailboxId]['USER_ID'];

			static $userMailboxes = [];

			if (!array_key_exists($userId, $userMailboxes))
			{
				$userMailboxes = MailboxTable::query()->addSelect('ID')
				->setSelect([
					'ID',
					'TARIFF_RESTRICTIONS' => 'OPTIONS.VALUE',
				])
				->where('USER_ID', $userId)
				->where('ACTIVE', 'Y')
				->registerRuntimeField(
					'',
					new Reference(
						'OPTIONS',
						MailEntityOptionsTable::class,
						Join::on('this.ID', 'ref.MAILBOX_ID')->where('ref.PROPERTY_NAME', 'TARIFF_RESTRICTIONS'),
						['join_type' => Join::TYPE_LEFT],
					),
				)->fetchAll();
			}

			if ($maxCountAvailableMailboxes < 0)
			{
				$activateMailboxes = self::getTariffRestrictionsMailboxListsByDBData(array_slice($userMailboxes, 0, count($userMailboxes)), static::MAILBOX_IS_LOCKED_PROPERTY);
				static::changeTariffRestrictionsOnTheMailboxes($activateMailboxes, static::MAILBOX_IS_AVAILABLE_PROPERTY);
				$mailboxAvailabilitySyncStatus = true;
			}
			else
			{
				$activateMailboxes = self::getTariffRestrictionsMailboxListsByDBData(array_slice($userMailboxes, 0, $maxCountAvailableMailboxes), static::MAILBOX_IS_LOCKED_PROPERTY);
				$blockMailboxes = self::getTariffRestrictionsMailboxListsByDBData(array_slice($userMailboxes, $maxCountAvailableMailboxes), static::MAILBOX_IS_AVAILABLE_PROPERTY);

				if (self::checkIdInTariffRestrictionsMailboxLists($checkedMailboxId, $activateMailboxes))
				{
					$mailboxAvailabilitySyncStatus = true;
				}
				else if(self::checkIdInTariffRestrictionsMailboxLists($checkedMailboxId, $blockMailboxes))
				{
					$mailboxAvailabilitySyncStatus = false;
				}
				else
				{
					$mailboxAvailabilitySyncStatus = self::getAvailabilitySyncStatusByMailboxList($checkedMailboxId, $userMailboxes);
				}

				self::sendNotificationsAboutBlockedMailboxes($blockMailboxes['IDS_ALL']);
				static::changeTariffRestrictionsOnTheMailboxes($activateMailboxes, static::MAILBOX_IS_AVAILABLE_PROPERTY);
				static::changeTariffRestrictionsOnTheMailboxes($blockMailboxes, static::MAILBOX_IS_LOCKED_PROPERTY);
			}

			return $mailboxAvailabilitySyncStatus;
		}

		return false;
	}

	public static function changeTariffRestrictionsOnTheMailboxes($tariffRestrictionsMailboxLists, $tariffRestrictionsType): void
	{
		if (isset($tariffRestrictionsMailboxLists['IDS_FOR_ADD']) && count($tariffRestrictionsMailboxLists['IDS_FOR_ADD']))
		{
			$rowsForAdd = [];
			foreach ($tariffRestrictionsMailboxLists['IDS_FOR_ADD'] as $id)
			{
				$rowsForAdd[] = [
					'MAILBOX_ID' => $id,
					'ENTITY_TYPE' => 'MAILBOX',
					'ENTITY_ID' => $id,
					'PROPERTY_NAME' => 'TARIFF_RESTRICTIONS',
					'VALUE' => $tariffRestrictionsType,
					'DATE_INSERT' => new DateTime(),
				];
			}

			foreach ($rowsForAdd as $row)
			{
				$filter = [
					'=MAILBOX_ID' => $row['MAILBOX_ID'],
					'=ENTITY_TYPE' => 'MAILBOX',
					'=ENTITY_ID' => $row['ENTITY_ID'],
					'=PROPERTY_NAME' => 'TARIFF_RESTRICTIONS',
				];

				$keyRow = [
					'MAILBOX_ID' => $row['MAILBOX_ID'],
					'ENTITY_TYPE' => 'MAILBOX',
					'ENTITY_ID' => $row['ENTITY_ID'],
					'PROPERTY_NAME' => 'TARIFF_RESTRICTIONS',
				];

				if (MailEntityOptionsTable::getCount($filter))
				{
					MailEntityOptionsTable::update(
						$keyRow,
						[
							'VALUE' => $row['VALUE'],
							'DATE_INSERT' => new DateTime(),
						],
					);
				}
				else
				{
					MailEntityOptionsTable::add($row);
				}
			}
		}

		if (isset($tariffRestrictionsMailboxLists['IDS_FOR_UPDATE']) && count($tariffRestrictionsMailboxLists['IDS_FOR_UPDATE']))
		{
			$rowsForUpdate = [];
			foreach ($tariffRestrictionsMailboxLists['IDS_FOR_UPDATE'] as $id)
			{
				$rowsForUpdate[] = [
					'MAILBOX_ID' => $id,
					'ENTITY_TYPE' => 'MAILBOX',
					'ENTITY_ID' => $id,
					'PROPERTY_NAME' => 'TARIFF_RESTRICTIONS',
				];
			}

			if (count($rowsForUpdate))
			{
				MailEntityOptionsTable::updateMulti($rowsForUpdate, [
					'VALUE' => $tariffRestrictionsType,
					'DATE_INSERT' => new DateTime(),
				]);
			}
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
	public static function isCleanupOldEnabled(): bool
	{
		if (Main\Application::getConnection()->getType() === 'pgsql')
		{
			return false; // not implemented yet
		}

		return static::getSyncOldLimit() > 0;
	}

}
