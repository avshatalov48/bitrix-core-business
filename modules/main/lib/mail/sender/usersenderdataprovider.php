<?php

namespace Bitrix\Main\Mail\Sender;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main;
use Bitrix\Main\Mail\Internal\SenderTable;
use Bitrix\Main\Mail\Sender;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Config;
use Bitrix\Main\Loader;
use Bitrix\Mail\MailboxTable;

final class UserSenderDataProvider
{
	public const SENDER_TYPE = 'sender';
	public const ALIAS_TYPE = 'alias';
	public const MAILBOX_SENDER_TYPE = 'mailboxSender';
	public const MAILBOX_TYPE = 'mailbox';

	/**
	 * Returns a list of available senders for a specific or current user
	 */
	public static function getUserAvailableSenders(?int $userId = null): array
	{
		$isAdmin = false;
		if (!($userId > 0))
		{
			$currentUser = Main\Engine\CurrentUser::get();
			$userId = (int)$currentUser->getId();
			$isAdmin = self::isAdmin();
		}

		if (!$userId)
		{
			return [];
		}

		$senders = [];

		$userFormattedName = self::getUserFormattedName($userId);

		$sendersList = SenderTable::query()
			->setSelect(['ID', 'NAME', 'EMAIL', 'USER_ID', 'OPTIONS'])
			->where('IS_CONFIRMED')
			->where(Query::filter()
				->logic('or')
				->where('USER_ID', $userId)
				->where('IS_PUBLIC', true)
			)
			->where('PARENT_MODULE_ID', 'main')
			->fetchAll()
		;

		$emailsWithSmtpSenders = [];
		$availableSmtpSenders = [];
		$emailsWithoutSmtpSenders = [];
		foreach ($sendersList as $sender)
		{
			$sender['USER_ID'] = (int)$sender['USER_ID'];
			if (in_array($sender['EMAIL'], $emailsWithoutSmtpSenders, true))
			{
				continue;
			}

			if (
				!in_array($sender['EMAIL'], $availableSmtpSenders, true)
				&& !in_array($sender['EMAIL'], $emailsWithSmtpSenders, true)
			)
			{
				//excluded emails senders for box, the list of senders contains senders with smtp and mail addresses without smtp senders
				if (Sender::hasUserAvailableSmtpSenderByEmail($sender['EMAIL'], $userId))
				{
					$availableSmtpSenders[] = $sender['EMAIL'];
				}
				else
				{
					$emailsWithoutSmtpSenders[] = $sender['EMAIL'];

					continue;
				}
			}

			if (
				$userId !== $sender['USER_ID']
				&& self::getUserFormattedName($sender['USER_ID']) === $sender['NAME']
			)
			{
				$sender['NAME'] = $userFormattedName ?? $sender['NAME'];
			}
			else
			{
				$sender['NAME'] = trim($sender['NAME']) ?: $userFormattedName;
			}

			$sender['EMAIL'] = mb_strtolower($sender['EMAIL']);

			$senders[] = [
				'id' => (int)$sender['ID'],
				'name' => $sender['NAME'],
				'email' => $sender['EMAIL'],
				'can_delete' => $sender['USER_ID'] === $userId || $isAdmin,
				'type' => !empty($sender['OPTIONS']['smtp']) ? self::SENDER_TYPE : self::ALIAS_TYPE,
				'showEditHint' => false,
			];

			//excluded emails senders for box, the list of senders contains senders with smtp and mail addresses without smtp senders
			if (
				!in_array($sender['EMAIL'], $emailsWithSmtpSenders, true)
				&& !empty($sender['OPTIONS']['smtp'])
			)
			{
				$emailsWithSmtpSenders[] = $sender['EMAIL'];
			}
		}

		$senders = array_merge(
			self::getUserAvailableMailboxSenders(
				getMailboxesWithoutSmtp: true,
				userId: $userId,
			),
			$senders,
		);

		//leaves only senders with unique names
		$uniqueSenders = [];
		$uniqueSenderNames = [];
		foreach ($senders as $key => $sender)
		{
			$senders[$key]['formated'] = "{$sender['name']} <{$sender['email']}>";
			if (in_array($senders[$key]['formated'], $uniqueSenderNames, true))
			{
				continue;
			}

			$uniqueSenderNames[] = $senders[$key]['formated'];
			$uniqueSenders[] = $senders[$key];
		}

		return $uniqueSenders;
	}

	/**
	 * Returns a list of available senders for user
	 */
	public static function getUserAvailableSendersByEmail(string $email, int $userId = null): ?array
	{
		$isAdmin = false;
		if (!$userId)
		{
			$userId = (int)Main\Engine\CurrentUser::get()->getId();
			$isAdmin = self::isAdmin();
		}

		$senders = [];
		$mailboxSenders = self::getUserAvailableMailboxSenders(
			email: $email,
			userId: $userId,
		);

		$senderListQuery = SenderTable::query()
			->setSelect(['*'])
			->where('IS_CONFIRMED')
			->where('EMAIL', $email)
			->where(Query::filter()
				->logic('or')
				->where('USER_ID', $userId)
				->where('IS_PUBLIC', true)
			)
		;

		if (Loader::includeModule('mail'))
		{
			$senderListQuery->where('PARENT_MODULE_ID', 'main');
		}

		$userFormattedName = self::getUserFormattedName($userId);
		$senderList = $senderListQuery->fetchAll();
		$userData = [];
		foreach ($senderList as $sender)
		{
			$ownerId = (int)$sender['USER_ID'];
			if (!empty($sender['OPTIONS']['smtp']['server']))
			{
				$type = self::SENDER_TYPE;
			}
			else
			{
				$type = self::ALIAS_TYPE;
			}

			if (empty($sender['NAME']))
			{
				$sender['NAME'] =  self::getUserFormattedName($ownerId);
			}

			if (!isset($userData[$ownerId]))
			{
				$userData[$ownerId] = self::getUserInfo($ownerId);
			}

			$canEdit = $ownerId === $userId || $isAdmin;
			$isOwner = $ownerId === $userId;

			if (
				$userId !== $ownerId
				&& self::getUserFormattedName((int)$sender['USER_ID']) === $sender['NAME']
			)
			{
				$sender['NAME'] = $userFormattedName ?? $sender['NAME'];
			}

			$senders[] = [
				'id' => (int)$sender['ID'],
				'name' => $sender['NAME'],
				'type' => $type,
				'email' => $sender['EMAIL'],
				'userId' => $ownerId,
				'canEdit' => $canEdit,
				'isOwner' => $isOwner,
				'avatar' => $userData[$ownerId]['userAvatar'] ?? null,
				'userUrl' => $userData[$ownerId]['userUrl'] ?? null,
			];
		}

		return array_merge($senders, $mailboxSenders);
	}

	/**
	 * Returns senders connected with mailboxes that current user has access to
	 */
	public static function getUserAvailableMailboxSenders(
		?string $email = null,
		?bool $getMailboxesWithoutSmtp = false,
		?array $excludedEmails = [],
		?int $userId = null,
	): array
	{
		if (!$userId)
		{
			$userId = (int)Main\Engine\CurrentUser::get()->getId();
		}

		$isAdmin = self::isAdmin();

		$senders = [];
		if (!Loader::includeModule('mail') || !$userId)
		{
			return $senders;
		}

		$currentUserFormattedName = self::getUserFormattedName($userId);
		foreach (MailboxTable::getUserMailboxes($userId) as $mailbox)
		{
			if (
				empty($mailbox['EMAIL'])
				|| ($email && $email !== $mailbox['EMAIL'])
				|| in_array($mailbox['EMAIL'], $excludedEmails, true)
			)
			{
				continue;
			}

			$mailbox['USER_ID'] = (int)$mailbox['USER_ID'];
			$canEdit = $mailbox['USER_ID'] === $userId || $isAdmin;
			$isOwner = $mailbox['USER_ID'] === $userId;

			$sender = null;
			if (self::isSmtpAvailable())
			{
				$sender = SenderTable::query()
					->setSelect(['*'])
					->where('IS_CONFIRMED', true)
					->where('PARENT_MODULE_ID', 'mail')
					->where('PARENT_ID', $mailbox['ID'])
					->setLimit(1)
					->fetchObject()
				;
			}

			if (!$sender && !$getMailboxesWithoutSmtp)
			{
				continue;
			}

			$userFormattedName = self::getUserFormattedName((int)($sender['USER_ID'] ?? $mailbox['USER_ID']));
			$senderName = trim($mailbox['USERNAME'] ?? '');
			if ($sender)
			{
				$sender['USER_ID'] = (int)$sender['USER_ID'];
				if (empty($sender['NAME']) && empty($mailbox['USERNAME']))
				{
					$senderName = self::getUserFormattedName($sender['USER_ID']);
				}

				if (!empty($mailbox['USERNAME']))
				{
					if ($sender['NAME'] !== $mailbox['USERNAME'])
					{
						SenderTable::update($sender['ID'], ['NAME' => $senderName]);
					}
				}

				if (empty($senderName) && $sender['NAME'])
				{
					$senderName = $sender['NAME'];
					MailboxTable::update($mailbox['ID'], ['USERNAME' => $senderName]);
				}

				if (!isset($userData[$sender['USER_ID']]))
				{
					$userData[$sender['USER_ID']] = self::getUserInfo($sender['USER_ID']);
				}

				$canEdit = $sender['USER_ID'] === $userId || $isAdmin;
				$isOwner = $sender['USER_ID'] === $userId;
			}

			if (
				empty($senderName)
				|| (
					$userId !== (int)$mailbox['USER_ID']
					&& self::getUserFormattedName((int)$mailbox['USER_ID']) === $senderName
				)
			)
			{
				$senderName = $currentUserFormattedName ?? $senderName;
			}

			if (!empty($sender['USER_ID']))
			{
				$avatar = $userData[$sender['USER_ID']]['userAvatar'] ?? null;
				$userUrl = $userData[$sender['USER_ID']]['userUrl'] ?? null;
			}

			$senders[] = [
				'id' => $sender['ID'] ?? $mailbox['ID'],
				'mailboxId' => $sender['PARENT_ID'] ?? $mailbox['ID'],
				'name' => !empty($senderName) ? $senderName : $userFormattedName,
				'type' => $sender ? self::MAILBOX_SENDER_TYPE : self::MAILBOX_TYPE,
				'email' => $mailbox['EMAIL'],
				'userId' => $sender['USER_ID'] ?? $mailbox['USER_ID'],
				'canEdit' => $canEdit,
				'isOwner' => $isOwner,
				'editHref' => $canEdit ? self::getMailboxConfigPath($sender['PARENT_ID'] ?? $mailbox['ID']) : null,
				'avatar' => $avatar ?? null,
				'userUrl' => $userUrl ?? null,
				'showEditHint' => empty($sender),
			];
		}

		return $senders;
	}

	/**
	 * Returns sender email data available for user and list of available senders associated with that email
	 */
	public static function getSenderTransitionalData(int $senderId): ?array
	{
		$sender = SenderTable::getById($senderId)->fetch();
		$transitionalData = [];

		if (!$sender)
		{
			return null;
		}

		$userId = (int)Main\Engine\CurrentUser::get()->getId();

		if (!$userId)
		{
			return null;
		}

		$isAdmin = self::isAdmin();
		$transitionalData['email'] = $sender['EMAIL'];
		$transitionalData['senders'] = self::getUserAvailableSendersByEmail($sender['EMAIL']);
		$isModuleMailAvailable = Loader::includeModule('mail');

		if ($isModuleMailAvailable)
		{
			foreach (MailboxTable::getUserMailboxes() as $mailbox)
			{
				if (
					$mailbox['EMAIL'] === $sender['EMAIL']
					&& ((int)$mailbox['USER_ID'] === $userId || $isAdmin)
				)
				{
					foreach ($transitionalData['senders'] as $emailSender)
					{
						if ($emailSender['type'] !== self::MAILBOX_SENDER_TYPE)
						{
							continue;
						}
						$transitionalData['id'] = $mailbox['ID'];
						$transitionalData['type'] = self::MAILBOX_TYPE;
						$transitionalData['href'] =  self::getMailboxConfigPath($mailbox['ID']);
						$transitionalData['canEdit'] = true;
						return $transitionalData;
					}

				}
			}
		}

		if (
			($sender['PARENT_MODULE_ID'] === 'main' || !$isModuleMailAvailable)
			&& !empty($sender['OPTIONS']['smtp']['server'])
			&& ((int)$sender['USER_ID'] === $userId || $isAdmin)
		)
		{
			$transitionalData['id'] = $senderId;
			$transitionalData['type'] = self::SENDER_TYPE;
			$transitionalData['canEdit'] = true;
			return $transitionalData;
		}

		foreach ($transitionalData['senders'] as $emailSender)
		{
			if (
				$emailSender['type'] === self::SENDER_TYPE
				&& ($emailSender['USER_ID'] === $userId || $isAdmin)
			)
			{
				$transitionalData['id'] = $emailSender['id'];
				$transitionalData['type'] = self::SENDER_TYPE;
				$transitionalData['canEdit'] = true;
				return $transitionalData;
			}
		}

		return $transitionalData;
	}

	public static function getUserFormattedName(?int $userId = null): ?string
	{
		if (!$userId)
		{
			$userId = Main\Engine\CurrentUser::get()->getId();
		}

		if (!$userId)
		{
			return null;
		}

		$userData = self::getUserData($userId);
		if (!$userData)
		{
			return null;
		}

		return \CUser::formatName(\CSite::getNameFormat(), $userData, true, false);
	}

	public static function getAddressInEmailAngleFormat(
		string $email,
		?string $senderName = null,
		?int $userId = null,
	): ?string
	{
		if (strlen($senderName ?? '') === 0)
		{
			$senderName = self::getUserFormattedName($userId);
		}

		if (!$senderName)
		{
			return null;
		}

		return "{$senderName} <{$email}>";
	}

	public static function getSenderNameByMailboxId(int $mailboxId, bool $getSenderWithoutSmtp = false): ?array
	{
		$sender = SenderTable::query()
			->setSelect(['*'])
			->where('IS_CONFIRMED', true)
			->where('PARENT_MODULE_ID', 'mail')
			->where('PARENT_ID', $mailboxId)
			->setLimit(1)
			->fetchObject()
		;

		if ($sender)
		{
			return [
				'id' => $sender['ID'],
				'name' => (strlen($sender['NAME'] ?? '') > 0) ? $sender['NAME'] : self::getUserFormattedName($sender['USER_ID']),
				'email' => $sender['EMAIL'],
				'type' => self::MAILBOX_SENDER_TYPE,
			];
		}

		if (
			!$getSenderWithoutSmtp
			|| !self::canUseMailboxTable()
		)
		{
			return null;
		}

		$mailbox = MailboxTable::getById($mailboxId)->fetch();

		if (!$mailbox)
		{
			return null;
		}

		return [
			'id' => $mailbox['ID'],
			'name' => (strlen($mailbox['USERNAME'] ?? '') > 0) ? $mailbox['USERNAME'] : self::getUserFormattedName($mailbox['USER_ID']),
			'email' => $mailbox['EMAIL'],
			'type' => self::MAILBOX_TYPE,
		];
	}

	private static function getMailboxConfigPath(int $mailboxId): string
	{
		return str_replace('#id#', $mailboxId, Config\Option::get('intranet', 'path_mail_config', SITE_DIR . 'mail/'));
	}

	public static function getUserInfo(int $userId): ?array
	{
		static $userInfo = [];

		if (isset($userInfo[$userId]))
		{
			return $userInfo[$userId];
		}

		$userData = self::getUserData($userId);
		if (!$userData)
		{
			return null;
		}

		$userUrl = str_replace('#USER_ID#', $userId, \COption::GetOptionString('intranet', 'path_user', '/company/personal/user/#USER_ID#/'));

		if ($userData['PERSONAL_PHOTO'])
		{
			$resizedImage = \CFile::ResizeImageGet(
				$userData["PERSONAL_PHOTO"],
				['width' => 50, 'height' => 50],
				BX_RESIZE_IMAGE_EXACT,
				false,
				false,
				true
			);
			if (!empty($resizedImage['src']))
			{
				$userAvatar = $resizedImage['src'];
			}
		}

		$userInfo[$userId] = [
			'userUrl' => $userUrl,
			'userAvatar' => $userAvatar ?? null,
		];

		return $userInfo[$userId];
	}

	private static function canUseMailboxTable(): bool
	{
		return Loader::includeModule('mail') && class_exists('Bitrix\Mail\MailboxTable');
	}

	private static function isSmtpAvailable(): bool
	{
		$defaultMailConfiguration = Configuration::getValue('smtp');
		return Loader::includeModule('bitrix24')
			|| $defaultMailConfiguration['enabled']
		;
	}

	public static function isAdmin(): bool
	{
		$currentUser = Main\Engine\CurrentUser::get();

		return $currentUser->isAdmin() || $currentUser->canDoOperation('bitrix24_config');
	}

	private static function getUserData(int $userId): ?array
	{
		static $userData = [];
		if (!empty($userData[$userId]))
		{
			return $userData[$userId];
		}

		$select = [
			'ID',
			'NAME',
			'LAST_NAME',
			'SECOND_NAME',
			'PERSONAL_PHOTO',
			'LOGIN',
			'EMAIL',
		];

		$userData[$userId] = \Bitrix\Main\UserTable::getList([
			'select' => $select,
			'filter' => ['=ID' => $userId],
		])->fetch();

		return $userData[$userId] ?: null;
	}
}
