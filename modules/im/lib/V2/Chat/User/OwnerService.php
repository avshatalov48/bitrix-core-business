<?php
namespace Bitrix\Im\V2\Chat\User;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\RelationCollection;
use Bitrix\Main\UserAccessTable;
use Bitrix\Main\UserTable;

class OwnerService
{
	protected const DELAY_AFTER_USER_FIRED = 10;

	public static function onAfterUserUpdate(array $fields): void
	{
		if (!isset($fields['ACTIVE']) || $fields['ACTIVE'] !== 'N')
		{
			return;
		}

		\CAgent::AddAgent(
			__CLASS__. "::changeChatsOwnerAfterUserFiredAgent({$fields['ID']});",
			'im',
			'N',
			self::DELAY_AFTER_USER_FIRED,
			'',
			'Y',
			ConvertTimeStamp(time() + \CTimeZone::GetOffset() + self::DELAY_AFTER_USER_FIRED, "FULL"),
			existError: false
		);
	}

	public static function changeChatsOwnerAfterUserFiredAgent(int $ownerId): string
	{
		$ownerChats = ChatTable::getList([
			'filter' => [
				'AUTHOR_ID' => $ownerId,
				'TYPE' => [Chat::IM_TYPE_OPEN, Chat::IM_TYPE_CHAT]
			]
		]);

		foreach ($ownerChats as $ownerChat)
		{
			$chat = Chat\ChatFactory::getInstance()->getChat($ownerChat['ID']);

			$ownerRelation = $chat->getRelationByUserId($ownerId);
			if ($ownerRelation)
			{
				$ownerRelation->setManager(false);
				$ownerRelation->save();
			}

			$relations = RelationCollection::find(['CHAT_ID' => $chat->getId(), '!USER_ID' => $ownerId]);
			if ($relations->count())
			{
				foreach ($relations as $relation)
				{
					$user = $relation->getUser();
					if (
						$user->isExist()
						&& $user->isActive()
						&& !$user->isBot()
						&& !$user->isExtranet()
						&& !$user->isConnector()
					)
					{
						$chat->setAuthorId($relation->getUserId());
						$chat->save();

						$relation->setManager(true);
						$relation->save();

						break;
					}
				}
			}
		}

		return '';
	}

	public static function changeChatsOwnerForAllFiredUsersAgent(): string
	{
		$firedUsers = UserTable::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'ACTIVE' => 'N'
			]
		])->fetchAll();

		foreach ($firedUsers as $key => $user)
		{
			\CAgent::AddAgent(
				__CLASS__. '::changeChatsOwnerAfterUserFiredAgent(' . (int)$user['ID'] . ');',
				'im',
				'N',
				self::DELAY_AFTER_USER_FIRED,
				'',
				'Y',
				ConvertTimeStamp(time() + \CTimeZone::GetOffset() + self::DELAY_AFTER_USER_FIRED * $key, "FULL")
			);
		}

		return '';
	}

	public static function migrateOwnershipOfGeneralChatAgent(): string
	{
		$generalChatId = \COption::GetOptionInt('im', 'general_chat_id');
		if (!$generalChatId)
		{
			return '';
		}

		$oldChat = Chat\ChatFactory::getInstance()->getChatById($generalChatId);
		if ($oldChat instanceof Chat\NullChat)
		{
			return '';
		}

		$oldChat
			->setType(Chat::IM_TYPE_OPEN)
			->setEntityType(Chat::ENTITY_TYPE_GENERAL)
			->save();

		$generalChat = Chat\ChatFactory::getInstance()->getGeneralChat();
		if (!$generalChat || $generalChat instanceof Chat\NullChat)
		{
			return '';
		}

		$canPostAll = (\COption::GetOptionString('im', 'allow_send_to_general_chat_all', 'Y') === 'Y');
		if ($canPostAll)
		{
			$generalChat
				->setManageMessages(Chat::MANAGE_RIGHTS_MEMBER)
				->save();

			return '';
		}

		$chatRights = \COption::GetOptionString('im', 'allow_send_to_general_chat_rights');
		if (!$chatRights)
		{
			return '';
		}

		$users = UserAccessTable::getList([
			'select' => [
				'USER_ID'
			],
			'filter' => [
				'=ACCESS_CODE' => explode(',', $chatRights)
			],
			'group' => [
				'USER_ID'
			]
		])->fetchAll();

		if (!$users)
		{
			return '';
		}

		$userIds = array_column($users, 'USER_ID');

		$relations = $generalChat->getRelations();
		foreach ($relations as $relation)
		{
			if (in_array($relation->getUserId(), $userIds))
			{
				$relation->setManager(true);
				$relation->save();
			}
		}

		$generalChat
			->setManageMessages(Chat::MANAGE_RIGHTS_MANAGERS)
			->save();

		return '';
	}
}
