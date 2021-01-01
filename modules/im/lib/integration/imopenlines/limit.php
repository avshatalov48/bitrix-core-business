<?php
namespace Bitrix\Im\Integration\Imopenlines;

class Limit
{
	private static function isModuleIncluded()
	{
		return \Bitrix\Main\Loader::includeModule('imopenlines');
	}

	public static function getLicenseUsersLimit()
	{
		return false;
	}

	public static function canUseVoteHead()
	{
		if (!self::isModuleIncluded())
			return false;

		return \Bitrix\Imopenlines\Limit::canUseVoteHead();
	}

	public static function canJoinChatUser()
	{
		if (!self::isModuleIncluded())
			return false;

		if (!method_exists('Bitrix\Imopenlines\Limit', 'canJoinChatUser')) // TODO remove this after release imopnline 20.5.0
			return true;

		return \Bitrix\Imopenlines\Limit::canJoinChatUser();
	}

	public static function canTransferToLine()
	{
		if (!self::isModuleIncluded())
			return false;

		if (!method_exists('Bitrix\Imopenlines\Limit', 'canTransferToLine')) // TODO remove this after release imopnline 20.5.0
			return true;

		return \Bitrix\Imopenlines\Limit::canTransferToLine();
	}
}