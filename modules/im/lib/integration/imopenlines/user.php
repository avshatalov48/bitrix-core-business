<?php
namespace Bitrix\Im\Integration\Imopenlines;

class User
{
	public static function isOperator($userId = null)
	{
		if (!\Bitrix\Main\Loader::includeModule('imopenlines'))
		{
			return false;
		}

		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		return \Bitrix\ImOpenLines\Config::isOperator($userId);
	}
}