<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2021 Bitrix
 */
namespace Bitrix\Socialnetwork\Helper;

class Mention
{
	protected const PATTERN = '/\[user\s*=\s*([^\]]*)\](.+?)\[\/user\]/is' . BX_UTF_PCRE_MODIFIER;

	/**
	 * Processes text, parses all [user=N][/user] BB codes and returns user IDs
	 *
	 * @param string $text text to parse
	 * @return array of user IDs
	 */
	public static function getUserIds(string $text = ''): array
	{
		$usersData = self::getUsers($text);
		return array_map(function($item) { return $item['ID']; }, $usersData);
	}

	/**
	 * Processes text and parses all [user=N][/user] BB codes
	 *
	 * @param string $text text to parse
	 * @return array of parsed used data (ID and NAME fields)
	 */
	public static function getUsers(string $text = ''): array
	{
		$result = [];
		preg_match_all(self::PATTERN, $text, $matches);

		if (empty($matches))
		{
			return $result;
		}

		foreach($matches[1] as $key => $userId)
		{
			$result[] = [
				'ID' => (int)$userId,
				'NAME' => $matches[2][$key],
			];
		}

		return $result;
	}

	/**
	 * Clears all the [user=N][/user] BB codes but preserves user names
	 *
	 * @param string $text text to clear
	 * @return string processed text
	 */
	public static function clear(string $text = ''): string
	{
		return preg_replace(
			self::PATTERN,
			'\\2',
			$text
		);
	}
}
