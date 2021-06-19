<?php
namespace Bitrix\Landing\Chat;

use \Bitrix\Landing\Manager;
use \Bitrix\Landing\File;

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Chat extends \Bitrix\Landing\Internals\BaseTable
{
	/**
	 * Internal class.
	 * @var string
	 */
	public static $internalClass = 'ChatTable';

	/**
	 * Check file id and returns id.
	 * @param int $avatarId Avatar (file) id.
	 * @return int
	 */
	protected static function getAvatarId($avatarId)
	{
		$avatar = File::getFileArray($avatarId);
		if ($avatar)
		{
			$avatarId = (int)$avatar['ID'];
			File::releaseFile($avatarId);
		}
		else
		{
			$avatarId = 0;
		}

		return $avatarId;
	}

	/**
	 * Creates new record and return it.
	 * @param array $fields Fields array.
	 * @return \Bitrix\Main\ORM\Data\AddResult
	 */
	public static function add($fields)
	{
		if (array_key_exists('CHAT_ID', $fields))
		{
			unset($fields['CHAT_ID']);
		}
		if (array_key_exists('AVATAR', $fields))
		{
			$avatarId = self::getAvatarId($fields['AVATAR']);
		}
		else
		{
			$avatarId = 0;
		}

		// first create chat in th IM module
		if (
			isset($fields['TITLE']) &&
			\Bitrix\Main\Loader::includeModule('im')
		)
		{
			$userId = Manager::getUserId();
			$chat = new \CIMChat(0);
			$chatId = $chat->add([
				'TITLE' => $fields['TITLE'],
				'USERS' => [$userId],
				'AVATAR_ID' => $avatarId,
				'OWNER_ID' => $userId,
				'ENTITY_TYPE' => 'LANDING'
			]);
			if ($chatId)
			{
				// welcome message
				\CIMChat::addMessage([
					'FROM_USER_ID' => $userId,
					'SYSTEM' => 'Y',
					'TO_CHAT_ID' => $chatId,
					'MESSAGE' => Loc::getMessage('LANDING_CHAT_WELCOME_CREATE_MESSAGE'),
				]);
				$fields['CHAT_ID'] = $chatId;
				// and create internal chat
				$result = parent::add($fields);
				if ($result->isSuccess())
				{
					\CIMChat::SetChatParams($chatId, [
						'ENTITY_ID' => $result->getId()
					]);
				}
				return $result;
			}
		}

		return parent::add($fields);
	}

	/**
	 * Returns records of table.
	 * @param array $params Params array like ORM style.
	 * @return \Bitrix\Main\DB\Result
	 */
	public static function getList($params = [])
	{
		//@todo: make access filter
		return parent::getList($params);
	}

	/**
	 * Return chat row by id.
	 * @param int $id Chat id.
	 * @return array
	 */
	public static function getRow($id): array
	{
		static $chats = [];

		if (!array_key_exists($id, $chats))
		{
			$res = self::getList([
				'filter' => [
					'ID' => intval($id)
				]
			]);
			$chats[$id] = $res->fetch();
		}

		return $chats[$id];
	}

	/**
	 * Updates record.
	 * @param int $id Record key.
	 * @param array $fields Fields array.
	 * @return \Bitrix\Main\Result
	 */
	public static function update($id, $fields = [])
	{
		$chatRow = self::getRow($id);

		if (!$chatRow)
		{
			$result = new \Bitrix\Main\Result;
			$result->addErrors([
				new \Bitrix\Main\Error(
					Loc::getMessage('LANDING_CHAT_ERROR_CHAT_UPDATE'),
					'ERROR_CHAT_UPDATE'
				)
			]);
			return $result;
		}

		$result = parent::update($id, $fields);

		// update IM chat
		if ($result->isSuccess())
		{
			$chat = new \CIMChat(0);
			// title
			if (
				isset($fields['TITLE']) &&
				$chatRow['TITLE'] != $fields['TITLE']
			)
			{
				$chat->rename(
					$chatRow['CHAT_ID'],
					$fields['TITLE'],
					false
				);
			}
			// avatar
			if (
				isset($fields['AVATAR']) &&
				$chatRow['AVATAR'] != $fields['AVATAR']
			)
			{
				$fields['AVATAR'] = self::getAvatarId($fields['AVATAR']);
				if ($fields['AVATAR'])
				{
					File::deletePhysical(
						$chatRow['AVATAR']
					);
					$chat->setAvatarId(
						$chatRow['CHAT_ID'],
						$fields['AVATAR']
					);
				}
			}
		}

		return $result;
	}

	/**
	 * Returns chat members ids.
	 * @param int $chatId Chat id.
	 * @return array
	 */
	public static function getMembersId(int $chatId): array
	{
		$ids = [];

		if ($chatId && \Bitrix\Main\Loader::includeModule('im'))
		{
			$chatRow = self::getRow($chatId);
			$users = \Bitrix\Im\Chat::getUsers($chatRow['CHAT_ID']);
			foreach ($users as $user)
			{
				$ids[] = $user['id'];
			}
		}

		return $ids;
	}

	/**
	 * Invite current user to the chat and returns IM chat id.
	 * @param int $internalId Internal chat id.
	 * @return int
	 */
	public static function joinChat(int $internalId): int
	{
		$chatId = 0;

		$chatRow = self::getRow($internalId);
		if ($chatRow && \Bitrix\Main\Loader::includeModule('im'))
		{
			$chatId = $chatRow['CHAT_ID'];
			$userId = Manager::getUserId();
			$users = self::getMembersId($internalId);
			if (!in_array($userId, $users))
			{
				$chat = new \CIMChat(0);
				$chat->addUser($chatId, [$userId], false);
				Binding::clearCache($internalId);
			}
		}

		return $chatId;
	}
}