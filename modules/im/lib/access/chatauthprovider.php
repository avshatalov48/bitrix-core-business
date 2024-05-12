<?php

namespace Bitrix\Im\Access;

use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserAccessTable;

/**
 * Auth provider.
 *
 * @see \CAccess
 * todo: Implement full \IProviderInterface
 *
 * RegisterModuleDependences("main", "OnAuthProvidersBuildList", "im", "\\Bitrix\\Im\\Access\\ChatAuthProvider", "getProviders");
 *
 */
class ChatAuthProvider extends \CAuthProvider
{
	protected const PROVIDER_ID = 'imchat';
	protected const ACCESS_CODE_PREFIX = 'CHAT';

	public function __construct()
	{
		$this->id = self::PROVIDER_ID;
	}

	/**
	 * Event handler for main::OnAuthProvidersBuildList event.
	 * @see \CAccess::__construct
	 * @return array
	 */
	public static function getProviders(): array
	{
		return [
			[
				'ID' => self::PROVIDER_ID,
				'CLASS' => self::class,
				'PROVIDER_NAME' => Loc::getMessage('chat_auth_provider'),
				'NAME' => Loc::getMessage('chat_auth_provider_name'),
				'SORT' => 400,
			]
		];
	}

	/**
	 * Generates access code for chat. Ex: 'CHAT888'.
	 * @param int $chatId
	 * @return string
	 */
	public function generateAccessCode(int $chatId): string
	{
		return self::ACCESS_CODE_PREFIX. $chatId;
	}

	/**
	 * Returns restricted object names.
	 *
	 * @see \CAccess::GetNames
	 * @see \IProviderInterface::GetNames
	 *
	 * @param string[] $codes
	 * @return array{provider: string, name: string}
	 */
	public function getNames($codes): array
	{
		$chatIds = [];
		$accessCodePrefix = self::ACCESS_CODE_PREFIX;
		foreach ($codes as $code)
		{
			if (preg_match("/^{$accessCodePrefix}([0-9]+)$/i", $code, $match))
			{
				$chatIds[] = (int)$match[1];
			}
		}

		$result = [];
		if (count($chatIds) > 0)
		{
			$resChatData = \Bitrix\Im\Model\ChatTable::getList([
				'select' => ['ID', 'TITLE'],
				'filter' => ['=ID' => $chatIds],
			]);
			while ($chat = $resChatData->fetch())
			{
				$accessCode = $this->generateAccessCode($chat['ID']);
				$result[$accessCode] = [
					'provider' => Loc::getMessage('chat_auth_provider'),
				];
				if (!empty($chat['TITLE']))
				{
					$result[$accessCode]['name'] = $chat['TITLE'];
				}
				else
				{
					$result[$accessCode]['name'] = Loc::getMessage('chat_auth_title', ['#CHAT_ID#' => $chat['ID']]);
				}
			}
		}

		return $result;
	}

	/**
	 * Removes user's access codes.
	 *
	 * @param int $userId
	 * @return void
	 */
	public function deleteByUser($userId): void
	{
		$userId = (int)$userId;
		if ($userId > 0)
		{
			$connection = \Bitrix\Main\Application::getConnection();
			$helper = $connection->getSqlHelper();
			$providerId = $helper->forSql($this->id);
			$connection->queryExecute("
				DELETE FROM b_user_access
				WHERE PROVIDER_ID = '{$providerId}' AND USER_ID = {$userId} 
			");
		}

		parent::deleteByUser($userId);
	}

	/**
	 * Add chat's access code for specific users.
	 *
	 * @param int $chatId
	 * @param int[] $userIds
	 * @return void
	 */
	public function addChatCodes(int $chatId, array $userIds): void
	{
		$userIds = array_filter(array_map('intVal', $userIds));
		if ($chatId > 0 && !empty($userIds))
		{
			$connection = \Bitrix\Main\Application::getConnection();
			$helper = $connection->getSqlHelper();
			$providerId = $helper->forSql($this->id);
			$accessCode = $helper->forSql($this->generateAccessCode($chatId));

			$users = implode(',', $userIds);

			$sql = $helper->getInsertIgnore(
				'b_user_access',
				'(USER_ID, PROVIDER_ID, ACCESS_CODE)',
				"SELECT ID, '{$providerId}', '{$accessCode}'
					FROM b_user
					WHERE ID IN({$users})"
			);

			$connection->queryExecute($sql);

			foreach ($userIds as $uid)
			{
				\CAccess::ClearCache($uid);
			}
		}
	}

	/**
	 * Removes chat's access code.
	 *
	 * @param int $chatId
	 * @param int[] $userIds For specific users.
	 * @return void
	 */
	public function deleteChatCodes(int $chatId, ?array $userIds = null): void
	{
		if ($chatId > 0)
		{
			$connection = \Bitrix\Main\Application::getConnection();
			$helper = $connection->getSqlHelper();
			$providerId = $helper->forSql($this->id);
			$accessCode = $helper->forSql($this->generateAccessCode($chatId));

			if ($userIds === null)
			{
				$res = \Bitrix\Main\UserAccessTable::getList([
					'filter' => ['=ACCESS_CODE' => $accessCode],
					'select' => ['USER_ID']
				]);
				$userIds = [];
				while ($row = $res->fetch())
				{
					$userIds[] = (int)$row['USER_ID'];
				}

				$connection->queryExecute("
					DELETE FROM b_user_access
					WHERE PROVIDER_ID = '{$providerId}' AND ACCESS_CODE = '{$accessCode}' 
				");
			}
			else
			{
				$userIds = array_filter(array_map('intVal', $userIds));
				if (count($userIds) > 0)
				{
					$users = implode(',', $userIds);
					$connection->queryExecute("
						DELETE FROM b_user_access
						WHERE PROVIDER_ID = '{$providerId}'
							AND ACCESS_CODE = '{$accessCode}'
							AND USER_ID IN({$users})
					");
				}
			}

			foreach ($userIds as $uid)
			{
				\CAccess::ClearCache($uid);
			}
		}
	}

	public function isCodeAlreadyExists(int $chatId, int $userId): bool
	{
		$result = UserAccessTable::query()
			->setSelect(['USER_ID'])
			->where('USER_ID', $userId)
			->where('ACCESS_CODE', $this->generateAccessCode($chatId))
			->where('PROVIDER_ID', $this->id)
			->setLimit(1)
			->fetch()
		;

		return $result !== false;
	}

	/**
	 * Updates chat's access codes.
	 *
	 * @param int $chatId
	 * @return void
	 */
	public function updateChatCodesByRelations(int $chatId): void
	{
		if ($chatId > 0)
		{
			$connection = \Bitrix\Main\Application::getConnection();
			$helper = $connection->getSqlHelper();
			$providerId = $helper->forSql($this->id);
			$accessCode = $helper->forSql($this->generateAccessCode($chatId));

			$sql = $helper->getInsertIgnore(
				'b_user_access',
				'(USER_ID, PROVIDER_ID, ACCESS_CODE)',
				"SELECT R.USER_ID, '{$providerId}', '{$accessCode}'
					FROM b_im_relation R 
						INNER JOIN b_user U ON R.USER_ID = U.ID
						LEFT JOIN b_user_access A 
							ON U.ID = A.USER_ID
							AND A.PROVIDER_ID = '{$providerId}'
							AND A.ACCESS_CODE = '{$accessCode}'
					WHERE 
						R.CHAT_ID = {$chatId}
						AND A.ID IS NULL
						AND (CASE 
							WHEN U.EXTERNAL_AUTH_ID = 'imconnector' AND POSITION('livechat|' in U.XML_Id) = 1 THEN 1
							WHEN U.EXTERNAL_AUTH_ID = 'imconnector' THEN 0
							ELSE 1
						END) = 1"
			);

			$connection->queryExecute($sql);

			$connection->queryExecute("
				DELETE FROM b_user_access
				WHERE PROVIDER_ID = '{$providerId}'
					AND ACCESS_CODE = '{$accessCode}'
					AND USER_ID NOT IN(
						SELECT R.USER_ID
						FROM b_im_relation R
						WHERE R.CHAT_ID = {$chatId}
					)
			");

			$res = \Bitrix\Main\UserAccessTable::getList([
				'filter' => ['=ACCESS_CODE' => $accessCode],
				'select' => ['USER_ID']
			]);
			while ($row = $res->fetch())
			{
				\CAccess::ClearCache($row['USER_ID']);
			}
		}
	}

	/**
	 * Adds user's access code to chat.
	 *
	 * @param int $chatId
	 * @param int $userId
	 * @return void
	 */
	public function addUserCode(int $chatId, int $userId): void
	{
		\CAccess::AddCode($userId, $this->id, $this->generateAccessCode($chatId));
	}

	/**
	 * Adds user's access code to chat.
	 *
	 * @param int $chatId
	 * @param int $userId
	 * @return void
	 */
	public function removeUserCode(int $chatId, int $userId): void
	{
		\CAccess::RemoveCode($userId, $this->id, $this->generateAccessCode($chatId));
	}
}
