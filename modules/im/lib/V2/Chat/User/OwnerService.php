<?php
namespace Bitrix\Im\V2\Chat\User;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\V2\Chat;
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
			"\Bitrix\Im\V2\Chat\User\OwnerService::changeChatsOwnerAfterUserFiredAgent({$fields['ID']});",
			'im',
			'N',
			self::DELAY_AFTER_USER_FIRED,
			'',
			'Y',
			ConvertTimeStamp(time() + \CTimeZone::GetOffset() + self::DELAY_AFTER_USER_FIRED, "FULL")
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

			$ownerRelation = $chat->getRelations([
				'FILTER' => [
					'USER_ID' => $ownerId
				],
				'LIMIT' => 1
			]);
			if ($ownerRelation->current())
			{
				$ownerRelation->current()->setManager(false);
				$ownerRelation->current()->save();
			}

			$relations = $chat->getRelations([
				'FILTER' => [
					'!USER_ID' => $ownerId
				]
			]);
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
				"\Bitrix\Im\V2\Chat\User\OwnerService::changeChatsOwnerAfterUserFiredAgent(" . (int)$user['ID'] . ");",
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
}
