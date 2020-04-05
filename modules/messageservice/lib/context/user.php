<?php
namespace Bitrix\MessageService\Context;

use Bitrix\Main\Loader;

class User
{
	public static function getId()
	{
		$user = static::getUser();
		return $user? (int)$user->getId() : 0;
	}

	/** @return \CUser|null */
	public static function getUser()
	{
		return isset($GLOBALS['USER']) && $GLOBALS['USER'] instanceof \CUser ? $GLOBALS['USER'] : null;
	}

	public static function isAdmin()
	{
		$user = static::getUser();

		if ($user && $user->isAuthorized())
		{
			return (
				$user->IsAdmin()
				||
				Loader::includeModule('bitrix24') && \CBitrix24::isPortalAdmin($user->getId())
			);
		}

		return false;
	}
}