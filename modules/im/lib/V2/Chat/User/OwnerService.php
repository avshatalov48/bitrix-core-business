<?php
namespace Bitrix\Im\V2\Chat\User;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\RelationCollection;
use Bitrix\Main\UserAccessTable;
use Bitrix\Main\UserTable;

class OwnerService
{
	protected const DELAY_AFTER_USER_FIRED = 10;
	protected const CHAT_LIMIT = 1000;

	public static function onAfterUserUpdate(array $fields): void
	{
		if (!isset($fields['ACTIVE']) || $fields['ACTIVE'] !== 'N')
		{
			return;
		}

		\CAgent::AddAgent(
			self::getAgentName((int)$fields['ID'], 0),
			'im',
			'N',
			self::DELAY_AFTER_USER_FIRED,
			'',
			'Y',
			ConvertTimeStamp(time() + \CTimeZone::GetOffset() + self::DELAY_AFTER_USER_FIRED, "FULL"),
			existError: false
		);
	}

	public static function changeChatsOwnerAfterUserFiredAgent(int $ownerId, int $targetId = 0): string
	{
		$chatTypes = [Chat::IM_TYPE_OPEN, Chat::IM_TYPE_CHAT, Chat::IM_TYPE_OPEN_CHANNEL, Chat::IM_TYPE_CHANNEL];

		$query = ChatTable::query()
			->setSelect(['ID'])
			->where('AUTHOR_ID', $ownerId)
			->whereIn('TYPE', $chatTypes)
			->setLimit(self::CHAT_LIMIT)
			->setOrder(['ID'])
		;

		if ($targetId > 0)
		{
			$query->where('ID', '>', $targetId);
		}

		$chatCount = 0;
		foreach ($query->fetchAll() as $row)
		{
			$chatCount++;
			$chatId = (int)$row['ID'];

			$chat = Chat::getInstance($chatId);
			if (!isset($chat))
			{
				continue;
			}

			RelationTable::updateByFilter(
				['=CHAT_ID' => $chatId, '=USER_ID' => $chat->getAuthorId()],
				['MANAGER' => 'N']
			);

			$relationFilter = [
				'CHAT_ID' => $chatId,
				'!USER_ID' => $chat->getAuthorId(),
				'ACTIVE' => true,
				'ONLY_INTERNAL_TYPE' => true,
				'ONLY_INTRANET' => true,
			];

			$relations = RelationCollection::find($relationFilter, ['USER_ID' => 'ASC'], 1);
			foreach ($relations as $relation)
			{
				$user = $relation->getUser();
				if ($user->isExist())
				{
					$chat->setAuthorId($user->getId());
					$relation->setManager(true);

					break;
				}
			}

			$chat->save();
			$relations->save();
			$targetId = $chatId;
		}

		if ($chatCount === self::CHAT_LIMIT)
		{
			return self::getAgentName($ownerId, $targetId);
		}

		return '';
	}

	private static function getAgentName(int $userId, int $chatId): string
	{
		return "\Bitrix\Im\V2\Chat\User\OwnerService::changeChatsOwnerAfterUserFiredAgent({$userId}, {$chatId});";
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
				self::getAgentName((int)$user['ID'], 0),
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
