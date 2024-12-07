<?php

namespace Bitrix\Im\V2\Import;

use Bitrix\Im\Chat;
use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\EO_Relation;
use Bitrix\Im\Model\EO_Relation_Collection;
use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\Model\UserTable;
use Bitrix\Im\Recent;
use Bitrix\Im\User;
use Bitrix\Im\V2\Chat\ChatError;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\DateTime;
use Bitrix\Rest\AppTable;

class ImportService
{
	public const IMPORT_GROUP_CHAT_ENTITY_TYPE = 'IMPORT_GROUP';
	public const IMPORT_PRIVATE_CHAT_ENTITY_TYPE = 'IMPORT_PRIVATE';
	public const IMPORT_GROUP_FINISH_ENTITY_TYPE = 'IMPORT_GROUP_FINISH';
	public const IMPORT_PRIVATE_FINISH_ENTITY_TYPE = 'IMPORT_PRIVATE_FINISH';
	public const IMPORT_ARCHIVE_ENTITY_TYPE = 'IMPORT_ARCHIVE';

	private int $userId;
	private array $chat;
	private ImportSendingService $sendingService;

	public function __construct(array $chat, ?int $userId = null, ?ImportSendingService $sendingService = null)
	{
		$this->chat = $chat;
		if (isset($userId))
		{
			$this->userId = $userId;
		}
		if (isset($sendingService))
		{
			$this->sendingService = $sendingService;
		}
		else
		{
			$this->sendingService = new ImportSendingService($chat);
		}
	}

	public static function create(array $chatData): Result
	{
		$result = new Result();

		$chatData['ENTITY_TYPE'] =
			$chatData['TYPE'] === \IM_MESSAGE_PRIVATE
				? self::IMPORT_PRIVATE_CHAT_ENTITY_TYPE
				: self::IMPORT_GROUP_CHAT_ENTITY_TYPE
		;
		$chatData['SKIP_ADD_MESSAGE'] = 'Y';

		$chatService = new \CIMChat(0);
		$chatId = $chatService->Add($chatData);

		if ($chatId === 0)
		{
			return $result->addError(new ChatError(ChatError::CREATION_ERROR));
		}

		return $result->setResult([
			'CHAT_ID' => $chatId,
			'TYPE' => $chatData['TYPE']
		]);
	}

	public function addMessages(array $messages): Result
	{
		return $this->sendingService->addMessages($messages);
	}

	public function updateMessages(array $messages): Result
	{
		return $this->sendingService->updateMessages($messages);
	}

	public function abort(): Result
	{
		$result = new Result();
		\CIMChat::deleteChat($this->chat);

		return $result;
	}

	public function commitGroup(array $users, string $clientId): Result
	{
		return $this->commitCommon(
			$users,
			$clientId,
			[
				'ENTITY_TYPE' => self::IMPORT_GROUP_FINISH_ENTITY_TYPE,
				'ENTITY_ID' => null,
				'ENTITY_DATA_1' => $clientId
			]
		);
	}

	public function commitPrivate(bool $newIsMain, bool $hideOriginal, string $clientId): Result
	{
		$result = new Result();

		$initUsers = $this->getInitUsers();
		$originalChat = $this->getOriginalChat($initUsers);
		$originalChatId = null;
		if ($originalChat !== null)
		{
			$originalChatId = (int)$originalChat['ID'];
			if ($this->hasRealMessages($originalChat))
			{
				if ($newIsMain)
				{
					if ($hideOriginal)
					{
						$this->hideChat($originalChat, $initUsers);
					}
					else
					{
						$this->convertOriginalToGroup($originalChat, $initUsers);
					}
				}
				else
				{
					$this->convertToGroup($initUsers);
					$this->chat['MESSAGE_TYPE'] = \IM_MESSAGE_CHAT;
				}
			}
			else
			{
				$this->hideChat($originalChat, $initUsers);
			}
		}

		$this->commitCommon(
			$initUsers,
			$clientId,
			[
				'ENTITY_TYPE' => self::IMPORT_PRIVATE_FINISH_ENTITY_TYPE,
				'ENTITY_ID' => $originalChatId,
				'ENTITY_DATA_1' => $clientId
			]
		);

		if ($this->chat['MESSAGE_TYPE'] === \IM_MESSAGE_CHAT)
		{
			$chatService = new \CIMChat(0);
			$managers = [];
			foreach ($initUsers as $user)
			{
				$managers[$user] = true;
			}
			$chatService->SetManagers((int)$this->chat['ID'], $managers, false);
		}

		return $result;
	}

	private function hideChat(array $chat, array $users): void
	{
		$id = (int)$chat['ID'];

		RelationTable::deleteByFilter(['=CHAT_ID' => $id]);
		Recent::hide($users[0], $users[1]);
		Recent::hide($users[1], $users[0]);
		sort($users);
		ChatTable::update($id, ['ENTITY_TYPE' => self::IMPORT_ARCHIVE_ENTITY_TYPE, 'ENTITY_ID' => "{$users[0]}|{$users[1]}"]);
	}

	private function getOriginalChat(array $users): ?array
	{
		$originalChatId = \CIMMessage::GetChatId($users[0], $users[1], false);
		if ($originalChatId === 0)
		{
			return null;
		}

		return Chat::getById($originalChatId);
	}

	private function hasRealMessages(array $chat): bool
	{
		if ((int)$chat['MESSAGE_COUNT'] === 0)
		{
			return false;
		}

		if ((int)$chat['MESSAGE_COUNT'] === 1)
		{
			return \CIMMessageParam::Get((int)$chat['LAST_MESSAGE_ID'], 'USER_JOIN') !== null;
		}

		return true;
	}

	private function getInitUsers(): array
	{
		$initUsers = [];
		[$initUsers[0], $initUsers[1]] = explode('|', $this->chat['ENTITY_DATA_1']);

		return array_map('intval', $initUsers);
	}

	private function commitCommon(array $users, string $clientId, array $finishEntityData = []): Result
	{
		$result = new Result();
		$chatId = (int)$this->chat['ID'];

		$users = array_map('intval', $users);
		$folderMembers = $users;
		if (!in_array($this->userId, $users, true))
		{
			\CIMDisk::ChangeFolderMembers($chatId, $this->userId, false);
		}
		else
		{
			foreach ($folderMembers as $index => $folderMember)
			{
				if ($folderMember === $this->userId)
				{
					unset($folderMembers[$index]);
				}
			}
		}

		$this->fillChatActualData();
		$this->addUsersInChat($this->chat, $users);
		\CIMDisk::ChangeFolderMembers($chatId, $users);
		ChatTable::update(
			$chatId,
			[
				'ENTITY_TYPE' => $finishEntityData['ENTITY_TYPE'] ?? null,
				'ENTITY_ID' => $finishEntityData['ENTITY_ID'] ?? null,
				'ENTITY_DATA_1' => $finishEntityData['ENTITY_DATA_1'] ?? null,
				'USER_COUNT' => count($users),
				'MESSAGE_COUNT' => $this->chat['MESSAGE_COUNT'],
				'LAST_MESSAGE_ID' => $this->chat['LAST_MESSAGE_ID'],
				'PREV_MESSAGE_ID' => $this->chat['PREV_MESSAGE_ID'],
			]
		);
		$this->showInRecent($this->chat);
		$this->sendFinishMessage($users, $clientId);

		return $result;
	}

	private function sendFinishMessage(array $users, string	$clientId): void
	{
		$appName = $this->getRestAppName($clientId) ?? '';
		\CIMMessenger::Add([
			'MESSAGE' => Loc::getMessage('IM_IMPORT_FINISH_MESSAGE', ['#APP_NAME#' => $appName]),
			'FROM_USER_ID' => $this->chat['MESSAGE_TYPE'] === \IM_MESSAGE_PRIVATE ? $users[0] : 0,
			'TO_CHAT_ID' => (int)$this->chat['ID'],
			'MESSAGE_TYPE' => $this->chat['MESSAGE_TYPE'],
			'SYSTEM' => 'Y',
		]);
	}

	private function fillChatActualData(): void
	{
		$lastMessageIds = $this->getLastMessageIds((int)$this->chat['ID']);
		$this->chat['MESSAGE_COUNT'] = $this->getMessageCount((int)$this->chat['ID']);
		$this->chat['LAST_MESSAGE_ID'] = $lastMessageIds[0] ?? 0;
		$this->chat['PREV_MESSAGE_ID'] = $lastMessageIds[1] ?? 0;
	}

	private function getMessageCount(int $chatId): int
	{
		return MessageTable::getCount(Query::filter()->where('CHAT_ID', $chatId));
	}

	private function getLastMessageIds(int $chatId): array
	{
		$result = [];

		$messages = MessageTable::query()
			->setSelect(['ID'])
			->where('CHAT_ID', $chatId)
			->setOrder(['DATE_CREATE' => 'DESC'])
			->setLimit(2)
			->fetchCollection()
		;

		foreach ($messages as $message)
		{
			$result[] = $message->getId();
		}

		return $result;
	}

	private function showInRecent(array $chatData): Result
	{
		$relations = Chat::getRelation((int)$chatData['ID'], ['WITHOUT_COUNTERS' => 'Y']);

		foreach ($relations as $userId => $relation)
		{
			$entityId =
				$chatData['MESSAGE_TYPE'] === \IM_MESSAGE_PRIVATE
					? $this->getEntityIdForPrivateChat($relations, (int)$relation['USER_ID'])
					: (int)$chatData['ID']
			;
			\CIMContactList::SetRecent(Array(
				'ENTITY_ID' => $entityId,
				'MESSAGE_ID' => (int)$chatData['LAST_MESSAGE_ID'],
				'CHAT_TYPE' => $chatData['MESSAGE_TYPE'],
				'USER_ID' => $relation['USER_ID'],
				'CHAT_ID' => $relation['CHAT_ID'],
				'RELATION_ID' => $relation['ID'],
			));
		}

		return new Result();
	}

	private function addUsersInChat(array $chatData, array $users): void
	{
		$relationCollection = new EO_Relation_Collection();
		$lastRead = new DateTime();

		foreach ($users as $user)
		{
			$relation = new EO_Relation();
			$relation
				->setChatId((int)$chatData['ID'])
				->setMessageType($chatData['MESSAGE_TYPE'])
				->setUserId($user)
				->setStartId(0)
				->setLastId((int)$chatData['LAST_MESSAGE_ID'])
				->setLastSendId((int)$chatData['LAST_MESSAGE_ID'])
				->setLastFileId(0)
				->setStartCounter(0)
				->setLastRead($lastRead)
			;
			$relationCollection->add($relation);
		}

		$relationCollection->save(true);
	}

	private function getEntityIdForPrivateChat(array $relations, int $userId): int
	{
		foreach ($relations as $relation)
		{
			if ((int)$relation['USER_ID'] !== $userId)
			{
				return (int)$relation['USER_ID'];
			}
		}

		return 0;
	}

	private function convertToGroup(array $users): void
	{
		$title = Loc::getMessage(
			'IM_IMPORT_GROUP_FROM_PRIVATE_CHAT_TITLE',
			[
				'#USER_NAME_1#' => User::getInstance($users[0])->getFullName(false),
				'#USER_NAME_2#' => User::getInstance($users[1])->getFullName(false),
			]
		);
		ChatTable::update((int)$this->chat['ID'], ['TYPE' => \IM_MESSAGE_CHAT, 'TITLE' => $title]);
	}

	private function convertOriginalToGroup(array $originalChat, array $users): void
	{
		$chatId = (int)$originalChat['ID'];
		$title = Loc::getMessage(
			'IM_IMPORT_GROUP_FROM_ORIGINAL_PRIVATE_CHAT_TITLE',
			[
				'#USER_NAME_1#' => User::getInstance($users[0])->getFullName(false),
				'#USER_NAME_2#' => User::getInstance($users[1])->getFullName(false),
			]
		);
		Recent::hide($users[0], $users[1]);
		Recent::hide($users[1], $users[0]);
		ChatTable::update((int)$originalChat['ID'], ['TYPE' => \IM_MESSAGE_CHAT, 'TITLE' => $title]);
		$originalChat['MESSAGE_TYPE'] = \IM_MESSAGE_CHAT;
		$sqlUpdateRelation = "UPDATE b_im_relation SET MESSAGE_TYPE= '" . \IM_MESSAGE_CHAT . "' WHERE CHAT_ID={$chatId}";
		Application::getConnection()->query($sqlUpdateRelation);
		$this->showInRecent($originalChat);
	}

	private function getRestAppName(string $clientId): ?string
	{
		$app = AppTable::getByClientId($clientId);
		if (!is_array($app))
		{
			return null;
		}

		$appNamesByPriority = [$app['MENU_NAME'], $app['MENU_NAME_DEFAULT'], $app['MENU_NAME_LICENSE'], $app['APP_NAME']];

		foreach ($appNamesByPriority as $appName)
		{
			if ($appName !== '')
			{
				return $appName;
			}
		}

		return null;
	}

	public function hasAccess(): bool
	{
		return $this->isImportInProgress() && self::isAdmin($this->userId);
	}

	public static function isAdmin(int $userId): bool
	{
		global $USER;
		if (Loader::includeModule('bitrix24'))
		{
			if (
				$USER instanceof \CUser
				&& $USER->isAuthorized()
				&& $USER->isAdmin()
				&& (int)$USER->getId() === $userId
			)
			{
				return true;
			}

			return \CBitrix24::isPortalAdmin($userId);
		}

		if (
			$USER instanceof \CUser
			&& $USER->isAuthorized()
			&& (int)$USER->getId() === $userId
		)
		{
			return $USER->isAdmin();
		}

		$result = false;
		$groups = UserTable::getUserGroupIds($userId);
		foreach ($groups as $groupId)
		{
			if ((int)$groupId === 1)
			{
				$result = true;
				break;
			}
		}

		return $result;
	}

	private function isImportInProgress(): bool
	{
		$entityType = $this->chat['ENTITY_TYPE'] ?? '';

		return (
			$entityType === self::IMPORT_PRIVATE_CHAT_ENTITY_TYPE
			|| $entityType === self::IMPORT_GROUP_CHAT_ENTITY_TYPE
		);
	}
}