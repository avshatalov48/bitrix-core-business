<?php

namespace Bitrix\Mail\Helper;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Mail\Internals\MailboxDirectoryTable;
use Bitrix\Mail\Internals\MailCounterTable;

/**
 * Class MessageFolder
 */
class MessageFolder
{
	const TRASH = 'trash';
	const SPAM = 'spam';
	const INCOME = 'income';
	const OUTCOME = 'outcome';
	const DRAFTS = 'drafts';

	public static function increaseDirCounter($mailboxId, $dirForMoveMessages = false, $dirForMoveMessagesId, $idsUnseenCount)
	{
		if(!is_null($dirForMoveMessages) && $dirForMoveMessages === false || !$dirForMoveMessages->isInvisibleToCounters()){
			if (MailCounterTable::getCount([
				'=MAILBOX_ID' => $mailboxId,
				'=ENTITY_TYPE' => 'DIR',
				'=ENTITY_ID' => $dirForMoveMessagesId
			])
			)
			{
				MailCounterTable::update(
					[
						'MAILBOX_ID' => $mailboxId,
						'ENTITY_TYPE' => 'DIR',
						'ENTITY_ID' => $dirForMoveMessagesId
					],
					[
						"VALUE" => new \Bitrix\Main\DB\SqlExpression("?# + $idsUnseenCount", "VALUE")
					]
				);
			}
			else
			{
				MailCounterTable::add([
					'MAILBOX_ID' => $mailboxId,
					'ENTITY_TYPE' => 'DIR',
					'ENTITY_ID' => $dirForMoveMessagesId
				],
					[
						"VALUE" => $idsUnseenCount,
					]);
			}
		}
	}

	public static function decreaseDirCounter($mailboxId, $dirWithMessagesId, $idsUnseenCount)
	{
		if($dirWithMessagesId)
		{
			if(MailCounterTable::getCount([
				'=MAILBOX_ID' => $mailboxId,
				'=ENTITY_TYPE' => 'DIR',
				'=ENTITY_ID' => $dirWithMessagesId,
				'>=VALUE' => $idsUnseenCount
			]))
			{
				MailCounterTable::update(
					[
						'MAILBOX_ID' => $mailboxId,
						'ENTITY_TYPE' => 'DIR',
						'ENTITY_ID' => $dirWithMessagesId
					],
					[
						"VALUE" => new \Bitrix\Main\DB\SqlExpression("?# - $idsUnseenCount", "VALUE")
					]
				);
			}
		}
	}

	public static function getDirIdForMessages($mailboxId, $messagesIds)
	{
		$dirWithMessagesId = MailboxDirectoryTable::getList([
			'runtime' => array(
				new Main\ORM\Fields\Relations\Reference(
					'UID',
					'Bitrix\Mail\MailMessageUidTable',
					[
						'=this.DIR_MD5' => 'ref.DIR_MD5',
						'=this.MAILBOX_ID' => 'ref.MAILBOX_ID',
					],
					[
						'join_type' => 'INNER',
					]
				),
			),
			'select' => [
				'ID',
			],
			'filter' => [
				'@UID.ID' => $messagesIds,
				'=MAILBOX_ID' => $mailboxId,
			],
			'limit' => 1,
		])->fetchAll();

		if(isset($dirWithMessagesId[0]['ID']))
		{
			return $dirWithMessagesId[0]['ID'];
		}
		return false;
	}

	/**
	 * @param array $message
	 * @param array $mailboxOptions
	 * @return string
	 */
	public static function getFolderNameByHash($messageFolderHash, $mailboxOptions)
	{
		$folderName = '';
		if (!empty($mailboxOptions['imap']['dirsMd5']))
		{
			$names = array_filter(
				$mailboxOptions['imap']['dirsMd5'],
				function ($hash) use ($messageFolderHash)
				{
					return $hash == $messageFolderHash;
				}
			);
			if (count($names) == 1)
			{
				$folderName = array_keys($names)[0];
			}
		}
		return $folderName;
	}

	public static function getFolderHashByType($folderType, $mailboxOptions)
	{
		$folderHash = '';
		if (!empty($mailboxOptions['imap']['dirsMd5']))
		{
			$name = static::getFolderNameByType($folderType, $mailboxOptions);
			$hashes = array_filter(
				$mailboxOptions['imap']['dirsMd5'],
				function ($_name) use ($name)
				{
					return $_name == $name;
				},
				ARRAY_FILTER_USE_KEY
			);
			if (count($hashes) == 1)
			{
				$folderHash = array_values($hashes)[0];
			}
		}

		return $folderHash;
	}

	public static function getFolderNameByType($folderType, $mailboxOptions)
	{
		if (!empty($mailboxOptions['imap']) && is_array($mailboxOptions['imap']))
		{
			$imapOptions = $mailboxOptions['imap'];
			if (!empty($imapOptions[$folderType]) && isset($imapOptions[$folderType][0]))
			{
				return $imapOptions[$folderType][0];
			}
		}
		return null;
	}

	public static function getDisabledFolders($mailboxOptions)
	{
		$disabled = empty($mailboxOptions['imap']['disabled']) ? [] : $mailboxOptions['imap']['disabled'];
		$ignore = empty($mailboxOptions['imap']['ignore']) ? [] : $mailboxOptions['imap']['ignore'];
		return array_merge($disabled, $ignore);
	}

	public static function isDisabledFolder($folder, $mailboxOptions)
	{
		return in_array($folder, static::getDisabledFolders($mailboxOptions), true);
	}

	public static function getFormattedPath(array $path, $mailboxOptions)
	{
		$root = array_shift($path);

		if (mb_strtolower($root) == 'inbox' && !static::isDisabledFolder($root, $mailboxOptions))
		{
			$root = Loc::getMessage('MAIL_CLIENT_INBOX_ALIAS');
		}

		array_unshift($path, $root);

		return $path;
	}

	public static function getFormattedName(array $path, $mailboxOptions, $full = true)
	{
		$path = static::getFormattedPath($path, $mailboxOptions);

		return $full ? join(' / ', $path) : end($path);
	}

}
