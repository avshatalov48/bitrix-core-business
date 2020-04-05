<?php
namespace Bitrix\Socialservices\EncryptedToken;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Socialservices\EncryptedToken\UserTable as EncryptedTokenUserTable;
use Bitrix\Socialservices\UserTable;

class Agent
{
	public static function init()
	{
		Option::set("socialservices", "allow_encrypted_tokens", true);
		$interval = IsModuleInstalled('bitrix24') ? 600 : 0;
		\CAgent::AddAgent(
			__CLASS__.'::runAgent();',
			"socialservices",
			"N",
			60,
			"",
			"Y",
			ConvertTimeStamp((time() + \CTimeZone::GetOffset() + $interval), 'FULL')
		);
	}

	public static function runAgent()
	{
		if (self::run())
		{
			return __CLASS__.'::runAgent();';
		}

		UserTable::enableCrypto('OATOKEN');
		UserTable::enableCrypto('OASECRET');
		UserTable::enableCrypto('REFRESH_TOKEN');
		return '';
	}

	public static function run()
	{
		$limit = Option::get("socialservices", "encrypt_tokens_step_limit", 500);
		$lastEncryptedUserId = Option::get("socialservices", "last_encrypted_user_id", 0);
		$users = UserTable::getList([
			'order' => ['ID' => 'ASC'],
			'select' => [
				'ID', 'OATOKEN', 'OASECRET', 'REFRESH_TOKEN'
			],
			'filter' => ['>ID' => $lastEncryptedUserId],
			'limit' => $limit
		]);
		$found = 0;
		while ($user = $users->fetch())
		{
			$found++;

			UserTable::update($user['ID'], [
				'OATOKEN' => $user['OATOKEN'],
				'OASECRET' => $user['OASECRET'],
				'REFRESH_TOKEN' => $user['REFRESH_TOKEN'],
			]);

			$lastEncryptedUserId = $user['ID'];
		}
		Option::set("socialservices", "last_encrypted_user_id", $lastEncryptedUserId);
		return ($found >= $limit);
	}
}