<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Authentication\Policy;

use Bitrix\Main\IO;

class WeakPassword
{
	public const MIN_PASSWORD_LENGTH = 6;

	/**
	 * Creates a set of indexed files from one source file.
	 *
	 * @param string $dataFile The absolute path to the source file.
	 * @param string $path The absolute path to the folder which contains the indexed files. If the folder doesn't exist it'll be created.
	 * @return bool
	 */
	public static function createIndex(string $dataFile, string $path): bool
	{
		$file = new IO\File($dataFile);

		$passwords = $file->getContents();

		$passwords = str_replace(["\r\n", "\r"], "\n", $passwords);
		$passwords = explode("\n", $passwords);

		$hashedPasswords = [];
		foreach ($passwords as $password)
		{
			if (strlen($password) >= self::MIN_PASSWORD_LENGTH)
			{
				$hash = md5($password);
				$name = $hash[0] . $hash[1]; // 256 possible keys
				$hashedPasswords[$name][] = (string)$password;
			}
		}
		unset($passwords);

		foreach ($hashedPasswords as $name => $value)
		{
			// we need the first and the last \n as a search pattern separator
			$content = "\n" . implode("\n", $value) . "\n";

			$indexFile = new IO\File($path . '/' . $name . '.txt');

			if ($indexFile->putContents($content) === false)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks if a password exists in the database.
	 *
	 * @param string $password The password.
	 * @param string $path The absolute path to the folder which contains the indexed files.
	 * @return bool
	 */
	public static function exists(string $password, string $path): bool
	{
		$hash = md5($password);
		$name = $hash[0] . $hash[1];

		$indexFile = new IO\File($path . '/' . $name . '.txt');

		if (!$indexFile->isExists())
		{
			return false;
		}

		$passwords = $indexFile->getContents();

		return (str_contains($passwords, "\n" . $password . "\n"));
	}
}
