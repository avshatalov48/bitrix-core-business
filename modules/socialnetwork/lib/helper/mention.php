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
	protected const PATTERN_USER = '/\[user\s*=\s*([^\]]*)\](.+?)\[\/user\]/is' . BX_UTF_PCRE_MODIFIER;
	protected const PATTERN_PROJECT = '/\[project\s*=\s*([^\]]*)\](.+?)\[\/project\]/is' . BX_UTF_PCRE_MODIFIER;
	protected const PATTERN_DEPARTMENT = '/\[department\s*=\s*([^\]]*)\](.+?)\[\/department\]/is' . BX_UTF_PCRE_MODIFIER;

	protected static function getPatternsList(): array
	{
		return [
			self::PATTERN_USER,
			self::PATTERN_PROJECT,
			self::PATTERN_DEPARTMENT,
		];
	}

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
		return self::getEntitiesList($text, self::PATTERN_USER);
	}

	public static function getProjectIds(string $text = ''): array
	{
		$projectsData = self::getProjects($text);
		return array_map(function($item) { return $item['ID']; }, $projectsData);
	}

	public static function getProjects(string $text = ''): array
	{
		return self::getEntitiesList($text, self::PATTERN_PROJECT);
	}

	public static function getDepartmentIds(string $text = ''): array
	{
		$departmentsData = self::getDepartments($text);
		return array_map(function($item) { return $item['ID']; }, $departmentsData);
	}

	public static function getDepartments(string $text = ''): array
	{
		return self::getEntitiesList($text, self::PATTERN_DEPARTMENT);
	}

	protected static function getEntitiesList(string $text = '', string $pattern = ''): array
	{
		$result = [];
		preg_match_all($pattern, $text, $matches);

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
		$result = $text;

		foreach (self::getPatternsList() as $pattern)
		{
			$result = preg_replace(
				$pattern,
				'\\2',
				$result
			);
		}

		return $result;
	}
}
