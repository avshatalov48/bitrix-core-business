<?php
namespace Bitrix\Im\Integration\Imopenlines;

use Bitrix\Main\Loader;

use Bitrix\Imopenlines;

class Limit
{
	private static function isModuleIncluded()
	{
		return Loader::includeModule('imopenlines');
	}

	public static function getLicenseUsersLimit()
	{
		return false;
	}

	public static function canUseVoteHead()
	{
		if (!self::isModuleIncluded())
			return false;

		return Imopenlines\Limit::canUseVoteHead();
	}

	public static function canJoinChatUser()
	{
		if (!self::isModuleIncluded())
			return false;

		return Imopenlines\Limit::canJoinChatUser();
	}

	public static function canTransferToLine()
	{
		if (!self::isModuleIncluded())
			return false;

		return Imopenlines\Limit::canTransferToLine();
	}
}