<?php

namespace Bitrix\Mail\Helper;

use Bitrix\Main\Localization\Loc;

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
