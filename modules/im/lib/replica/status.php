<?php
namespace Bitrix\Im\Replica;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class Status
{
	public static function isDeprecated(): bool
	{
		return (
			time() > mktime(0, 0, 0, 9, 1, 2021)
			|| \Bitrix\Main\Config\Option::get('im', 'test_replica_deprecated') === 'Y'
		);
	}

	public static function getMessageDialog(): string
	{
		$link = '[URL='.\Bitrix\UI\Util::getArticleUrlByCode("14137758").']'.
			Loc::getMessage('IM_REPLICA_STATUS_LINK').
		'[/URL]';

		return Loc::getMessage('IM_REPLICA_STATUS_DIALOG', [
			'#LINK#' => $link,
		]);
	}

	public static function getMessageChat(array $users): string
	{
		$link = '[URL='.\Bitrix\UI\Util::getArticleUrlByCode("14137758").']'.
			Loc::getMessage('IM_REPLICA_STATUS_LINK').
		'[/URL]';

		if (count($users) > 1)
		{
			$message = Loc::getMessage('IM_REPLICA_STATUS_CHAT', [
				'#LINK#' => $link,
				'#USERS#' => implode(', ', $users)
			]);
		}
		else
		{
			$message = Loc::getMessage('IM_REPLICA_STATUS_CHAT_SINGLE', [
				'#LINK#' => $link,
				'#USER#' => $users[0]
			]);
		}

		return $message;
	}

	private static function getReplicaCode(): string
	{
		if (function_exists('bx_domain_to_name'))
		{
			return bx_domain_to_name(BX24_HOST_NAME);
		}

		if (defined("BX24_REPLICA_NAME"))
		{
			return BX24_REPLICA_NAME;
		}

		return '';
	}

	public static function checkAgent(): string
	{
		if (!self::isDeprecated())
		{
			return '\Bitrix\Im\Chat::checkReplicaDeprecatedAgent();';
		}

		$db = \Bitrix\Main\Application::getInstance()->getConnection();
		if (!$db->isTableExists('b_replica_map'))
		{
			return '';
		}

		$replicaCode = $db->getSqlHelper()->forSql(self::getReplicaCode());

		$result = $db->query("
			SELECT distinct RM.ID_VALUE as CHAT_ID
			from b_replica_map RM
			INNER JOIN b_im_chat C ON C.ID = RM.ID_VALUE AND C.TYPE = 'C'
			where RM.TABLE_NAME = 'b_im_chat.ID' AND RM.NODE_TO <> '".$replicaCode."'  
		");
		while ($row = $result->fetch())
		{
			$chatId = (int)$row['CHAT_ID'];
			$userList = [];
			$relationList = [];

			$users = $db->query("
				SELECT R.ID as RID, R.NOTIFY_BLOCK, U.ID, U.LOGIN, U.NAME, U.LAST_NAME, U.PERSONAL_GENDER, U.EXTERNAL_AUTH_ID
				FROM b_im_relation R
				INNER JOIN b_user U ON U.EXTERNAL_AUTH_ID = 'replica' AND U.ID = R.USER_ID
				WHERE R.CHAT_ID = ".$chatId."
			");
			while ($user = $users->fetch())
			{
				if ($user['NOTIFY_BLOCK'] === 'Y')
				{
					continue;
				}

				$relationList[] = $user['RID'];
				$userList[$user['ID']] = '[b]'.\Bitrix\Im\User::formatFullNameFromDatabase($user).'[/b]';
			}

			if (empty($userList))
			{
				continue;
			}

			$message = \Bitrix\Im\Replica\Status::getMessageChat($userList);

			\Bitrix\Im\Model\MessageTable::add([
				'CHAT_ID' => $chatId,
				'AUTHOR_ID' => 0,
				'MESSAGE' => $message,
				'NOTIFY_MODULE' => 'im',
				'NOTIFY_EVENT' => 'group',
			]);

			foreach ($relationList as $relationId)
			{
				\Bitrix\Im\Model\RelationTable::update($relationId, [
					'NOTIFY_BLOCK' => 'Y'
				]);
			}
		}

		return '';
	}
}
