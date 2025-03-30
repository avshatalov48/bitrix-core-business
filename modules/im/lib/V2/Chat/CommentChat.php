<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Disk\Folder;
use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Recent;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Message\Send\MentionService;
use Bitrix\Im\V2\Message\Send\SendingConfig;
use Bitrix\Im\V2\Message\Send\SendingService;
use Bitrix\Im\V2\Relation;
use Bitrix\Im\V2\RelationCollection;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Message;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Pull\Event;

/**
 * Chat for comments
 */
class CommentChat extends GroupChat
{
	protected const LOCK_TIMEOUT = 3;
	protected const EXTRANET_CAN_SEE_HISTORY = true;
	protected ?Chat $parentChat;
	protected ?Message $parentMessage;

	public static function get(Message $message, bool $createIfNotExists = true): Result
	{
		$result = new Result();
		$chat = null;
		$chatId = static::getIdByMessage($message);
		if ($chatId)
		{
			$chat = Chat::getInstance($chatId);
		}

		if ($chat instanceof self)
		{
			$chat->parentMessage = $message;

			return $result->setResult($chat);
		}

		if (!$createIfNotExists)
		{
			$result->addError(new ChatError(ChatError::NOT_FOUND));

			return $result;
		}

		return static::create($message);
	}

	protected function getMentionService(SendingConfig $config): MentionService
	{
		return new Message\Send\Mention\CommentMentionService($config);
	}

	public static function create(Message $message): Result
	{
		$result = new Result();
		$parentChat = $message->getChat();

		if (!$parentChat instanceof ChannelChat)
		{
			return $result->addError(new ChatError(ChatError::WRONG_PARENT_CHAT));
		}

		$isLocked = Application::getConnection()->lock(self::getLockName($message->getId()), self::LOCK_TIMEOUT);
		if (!$isLocked)
		{
			return $result->addError(new ChatError(ChatError::CREATION_ERROR));
		}

		$chat = Chat::getInstance(static::getIdByMessage($message));
		if ($chat instanceof self)
		{
			Application::getConnection()->unlock(self::getLockName($message->getId()));
			$chat->parentMessage = $message;

			return $result->setResult($chat);
		}

		$createResult = static::createInternal($message);
		Application::getConnection()->unlock(self::getLockName($message->getId()));

		return $createResult;
	}

	public function join(bool $withMessage = true): Chat
	{
		$this->getParentChat()->join();

		return parent::join(false);
	}

	public function getRole(): string
	{
		if (isset($this->role))
		{
			return $this->role;
		}

		$role = parent::getRole();

		if ($role === self::ROLE_MEMBER)
		{
			$role = $this->getParentChat()->getRole();
		}

		$this->role = $role;

		return $role;
	}

	protected function onAfterMessageSend(Message $message, SendingService $sendingService): void
	{
		$this->subscribe(true, $message->getAuthorId());
		$this->subscribeUsers(true, $message->getUserIdsFromMention(), $message->getPrevId());
		Message\LastMessages::insert($message);

		if (!$sendingService->getConfig()->skipCounterIncrements())
		{
			Recent::raiseChat($this->getParentChat(), $this->getParentRelationsForRaiseChat(), new DateTime());
		}

		parent::onAfterMessageSend($message, $sendingService);
	}

	protected function updateRecentAfterMessageSend(\Bitrix\Im\V2\Message $message, SendingConfig $config): Result
	{
		return new Result();
	}

	public function filterUsersToMention(array $userIds): array
	{
		return $this->getParentChat()->filterUsersToMention($userIds);
	}

	public function getRelations(): RelationCollection
	{
		$relations = parent::getRelations();
		$userIds = $relations->getUserIds();
		if (empty($userIds))
		{
			return $relations;
		}

		$parentRelations = $this->getParentChat()->getRelationsByUserIds($userIds);

		return $relations->filter(
			fn (Relation $relation) => $parentRelations->getByUserId($relation->getUserId(), $this->getParentChatId())
		);
	}

	public function getRelationsForSendMessage(): RelationCollection
	{
		return parent::getRelationsForSendMessage()->filterNotifySubscribed();
	}

	protected function getParentRelationsForRaiseChat(): RelationCollection
	{
		$userIds = $this->getRelationsForSendMessage()->getUserIds();

		return $this->getParentChat()->getRelationsByUserIds($userIds);
	}

	public function subscribe(bool $subscribe = true, ?int $userId = null): Result
	{
		$userId ??= $this->getContext()->getUserId();
		$result = new Result();

		$relation = $this->getRelationByUserId($userId);

		if ($relation === null)
		{
			return $result->addError(new ChatError(ChatError::ACCESS_DENIED));
		}

		$relation->setNotifyBlock(!$subscribe)->save();
		$this->sendSubscribePush($subscribe, [$userId]);

		if (!$subscribe)
		{
			$this->read();
		}

		return $result;
	}

	protected function getValidUsersToAdd(array $userIds): array
	{
		$userIds = parent::getValidUsersToAdd($userIds);

		return $this->getParentChat()->getRelationsByUserIds($userIds)->getUserIds();
	}

	public function subscribeUsers(bool $subscribe = true, array $userIds = [], ?int $lastId = null): Result
	{
		$result = new Result();

		if (empty($userIds))
		{
			return $result;
		}

		$this->addUsers($userIds, new Relation\AddUsersConfig(hideHistory: false));
		$relations = $this->getRelations();
		$subscribedUsers = [];
		foreach ($userIds as $userId)
		{
			$relation = $relations->getByUserId($userId, $this->getId());
			if ($relation === null || !$relation->getNotifyBlock())
			{
				continue;
			}
			$relation->setNotifyBlock(false);
			if ($lastId)
			{
				$relation->setLastId($lastId);
			}
			$subscribedUsers[] = $userId;
		}

		$relations->save(true);
		$this->sendSubscribePush($subscribe, $subscribedUsers);

		return $result;
	}

	protected function sendSubscribePush(bool $subscribe, array $userIds): void
	{
		if (!Loader::includeModule('pull') || empty($userIds))
		{
			return;
		}
		Event::add(
			$userIds,
			[
				'module_id' => 'im',
				'command' => 'commentSubscribe',
				'params' => [
					'dialogId' => $this->getDialogId(),
					'subscribe' => $subscribe,
					'messageId' => $this->getParentMessageId(),
				],
				'extra' => \Bitrix\Im\Common::getPullExtra(),
			]
		);
	}

	protected function createDiskFolder(): ?Folder
	{
		$parentFolder = $this->getParentChat()->getOrCreateDiskFolder();
		if (!$parentFolder)
		{
			return null;
		}

		$folder = $parentFolder->addSubFolder(
			[
				'NAME' => "chat{$this->getId()}",
				'CREATED_BY' => $this->getContext()->getUserId(),
			],
			[],
			true
		);

		if ($folder)
		{
			$this->setDiskFolderId($folder->getId())->save();
		}

		return $folder;
	}

	protected function createRelation(int $userId, Relation\AddUsersConfig $config): Relation
	{
		$notifyBlock = $userId !== $this->getParentMessage()?->getAuthorId();

		return parent::createRelation($userId, $config)->setLastId(0)->setNotifyBlock($notifyBlock);
	}

	protected function getDefaultType(): string
	{
		return self::IM_TYPE_COMMENT;
	}

	public function setParentChat(?Chat $chat): self
	{
		$this->parentChat = $chat;

		return $this;
	}

	public function getParentChat(): Chat
	{
		$this->parentChat ??= Chat::getInstance($this->getParentChatId());

		return $this->parentChat;
	}

	public function setParentMessage(?Message $message): self
	{
		$this->parentMessage = $message;

		return $this;
	}

	public function getParentMessage(): ?Message
	{
		$this->parentMessage ??= new Message($this->getParentMessageId());

		return $this->parentMessage;
	}

	protected function sendMessageUsersAdd(array $usersToAdd, Relation\AddUsersConfig $config): void
	{
		return;
	}

	protected function sendDescriptionMessage(?int $authorId = null): void
	{
		return;
	}

	protected function sendMessageUserDelete(int $userId, Relation\DeleteUserConfig $config): void
	{
		return;
	}

	protected function sendGreetingMessage(?int $authorId = null)
	{
		$messageText = Loc::getMessage('IM_COMMENT_CREATE_V2');

		\CIMMessage::Add([
			'MESSAGE_TYPE' => $this->getType(),
			'TO_CHAT_ID' => $this->getChatId(),
			'FROM_USER_ID' => 0,
			'MESSAGE' => $messageText,
			'SYSTEM' => 'Y',
			'PUSH' => 'N',
			'SKIP_PULL' => 'Y', // todo: remove
			'SKIP_COUNTER_INCREMENTS' => 'Y',
			'PARAMS' => [
				'NOTIFY' => 'N',
			],
		]);
	}

	protected function sendBanner(?int $authorId = null): void
	{
		return;
	}

	protected static function mirrorDataEntityFields(): array
	{
		$result = parent::mirrorDataEntityFields();
		$result['PARENT_MESSAGE'] = [
			'set' => 'setParentMessage',
			'skipSave' => true,
		];
		$result['PARENT_CHAT'] = [
			'set' => 'setParentChat',
			'skipSave' => true,
		];

		return $result;
	}

	protected function prepareParams(array $params = []): Result
	{
		$result = new Result();

		if (!isset($params['PARENT_CHAT']) || !$params['PARENT_CHAT'] instanceof Chat)
		{
			return $result->addError(new ChatError(ChatError::WRONG_PARENT_CHAT));
		}

		if (!isset($params['PARENT_MESSAGE']) || !$params['PARENT_MESSAGE'] instanceof Message)
		{
			return $result->addError(new ChatError(ChatError::WRONG_PARENT_MESSAGE));
		}

		$params['PARENT_ID'] = $params['PARENT_CHAT']->getId();
		$params['PARENT_MID'] = $params['PARENT_MESSAGE']->getId();
		$params['USERS'][] = $params['PARENT_MESSAGE']->getAuthorId();

		return parent::prepareParams($params);
	}

	protected static function createInternal(Message $message): Result
	{
		$result = new Result();

		$parentChat = $message->getChat();

		$addResult = ChatFactory::getInstance()->addChat([
			'TYPE' => self::IM_TYPE_COMMENT,
			'PARENT_CHAT' => $parentChat,
			'PARENT_MESSAGE' => $message,
			'OWNER_ID' => $parentChat->getAuthorId(),
			'AUTHOR_ID' => $parentChat->getAuthorId(),
		]);

		if (!$addResult->isSuccess())
		{
			return $addResult;
		}

		/** @var static $chat */
		$chat = $addResult->getResult()['CHAT'];
		$chat->parentMessage = $message;
		$chat->sendPushChatCreate();

		return $result->setResult($chat);
	}

	protected static function getIdByMessage(Message $message): int
	{
		$row = ChatTable::query()
			->setSelect(['ID'])
			->where('PARENT_ID', $message->getChatId())
			->where('PARENT_MID', $message->getId())
			->fetch() ?: []
		;

		return (int)($row['ID'] ?? 0);
	}

	protected function sendPushChatCreate(): void
	{
		Event::add(
			$this->getParentChat()->getRelations()->getUserIds(),
			[
				'module_id' => 'im',
				'command' => 'chatCreate',
				'params' => [
					'id' => $this->getId(),
					'parentChatId' => $this->getParentChatId(),
					'parentMessageId' => $this->getParentMessageId(),
				],
				'extra' => \Bitrix\Im\Common::getPullExtra(),
			]
		);
	}

	protected function checkAccessInternal(int $userId): Result
	{
		return $this->getParentMessage()?->checkAccess($userId)
			?? (new Result())->addError(new ChatError(ChatError::ACCESS_DENIED))
		;
	}

	protected function addIndex(): Chat
	{
		return $this;
	}

	protected function updateIndex(): Chat
	{
		return $this;
	}

	protected static function getLockName(int $messageId): string
	{
		return 'com_create_' . $messageId;
	}

	protected function sendPushOnChangeUsers(RelationCollection $relations, array $pushMessage): void
	{
		if (!\Bitrix\Main\Loader::includeModule('pull'))
		{
			return;
		}

		\CPullWatch::AddToStack('IM_PUBLIC_COMMENT_' . $this->getParentChatId(), $pushMessage);
	}
}
