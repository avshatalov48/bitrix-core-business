<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2021 Bitrix
 */
namespace Bitrix\Socialnetwork\Helper;

class User
{
	public static function getCurrentUserId(): int
	{
		global $USER;

		return (int)$USER->getId();
	}
}
