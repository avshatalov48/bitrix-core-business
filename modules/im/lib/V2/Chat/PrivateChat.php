<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\Model\RecentTable;
use Bitrix\Im\User;
use Bitrix\Im\Recent;
use Bitrix\Im\Notify;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Entity\User\NullUser;
use Bitrix\Im\V2\Entity\User\UserBot;
use Bitrix\Im\V2\Entity\User\UserPopupItem;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\Send\PushService;
use Bitrix\Im\V2\Message\Send\SendingConfig;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Relation;
use Bitrix\Im\V2\Relation\AddUsersConfig;
use Bitrix\Im\V2\Relation\Reason;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Rest\PopupDataAggregatable;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Service\Locator;
use Bitrix\ImBot\Bot\Network;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Pull\Event;

class PrivateChat extends Chat implements PopupDataAggregatable
{
	protected const EXTRANET_CAN_SEE_HISTORY = true;

	protected function getDefaultType(): string
	{
		return self::IM_TYPE_PRIVATE;
	}

	protected function checkAccessInternal(int $userId): Result
	{
		return $this->getCompanion($userId)->checkAccess($userId);
	}

	/**
	 * Allows to send mention notification.
	 * @return bool
	 */
	public function allowMention(): bool
	{
		return false;
	}

	public function setManageSettings(string $manageSettings): Chat
	{
		return $this;
	}

	public function setManageUsersAdd(string $manageUsersAdd): Chat
	{
		return $this;
	}

	public function setManageUsersDelete(string $manageUsersDelete): Chat
	{
		return $this;
	}

	public function setManageUI(string $manageUI): Chat
	{
		return $this;
	}

	public function setManageMessages(string $manageMessages): Chat
	{
		return $this;
	}

	public function getDialogId(): ?string
	{
		if ($this->dialogId || !$this->getChatId())
		{
			return $this->dialogId;
		}

		$this->dialogId = $this->getCompanion()->getId();

		return $this->dialogId;
	}

	public function getDialogContextId(): ?string
	{
		return $this->getDialogId() . ':' .$this->getContext()->getUserId();
	}

	public function getStartId(?int $userId = null): int
	{
		return 0;
	}

	public function getMultidialogData(): array
	{
		if (!Loader::includeModule('imbot'))
		{
			return [];
		}

		$bot = null;

		foreach ($this->getRelations() as $relation)
		{
			$user = $relation->getUser();

			if (!$user instanceof UserBot)
			{
				continue;
			}

			$botType = $user->getBotData()->toRestFormat()['type'] ?? null;
			if ($botType === 'support24' || $botType === 'network')
			{
				$bot = $user;
			}
		}

		if (!$bot)
		{
			return [];
		}

		$otherUser = $this->getCompanion($bot->getId());

		return Network::getBotAsMultidialog($bot->getId(), $otherUser->getId());
	}

	protected function prepareMessage(Message $message): void
	{
		parent::prepareMessage($message);

		if (!$message->getAuthorId())
		{
			$message->setAuthorId($this->getContext()->getUserId());
		}

		$message
			->setNotifyModule('im')
			->setNotifyEvent($message->isSystem() ? Notify::EVENT_PRIVATE_SYSTEM : Notify::EVENT_PRIVATE)
		;
	}

	protected function onBeforeMessageSend(Message $message, SendingConfig $config): Result
	{
		$result = parent::onBeforeMessageSend($message, $config);

		if (!$message->getAuthorId())
		{
			return $result->addError(new Message\MessageError(Message\MessageError::WRONG_SENDER));
		}

		return $result;
	}

	/**
	 * @return int
	 */
	public function getOpponentId(): int
	{
		/** @var Relation $relation */
		$opponentUserId = 0;
		foreach ($this->getRelations() as $relation)
		{
			if (
				User::getInstance($relation->getUserId())->isActive()
				&& $this->getAuthorId() != $relation->getUserId()
			)
			{
				$opponentUserId = $relation->getUserId();
				break;
			}
		}

		return $opponentUserId;
	}

	public function getCompanion(?int $userId = null): \Bitrix\Im\V2\Entity\User\User
	{
		$userId = $userId ?? $this->getContext()->getUserId();

		$relations = $this->getRelations();

		if (!$relations->hasUser($userId, $this->getChatId()))
		{
			return new NullUser();
		}

		foreach ($relations as $relation)
		{
			if ($relation->getUserId() !== $userId)
			{
				return $relation->getUser();
			}
		}

		return new NullUser();
	}

	protected function getFieldsForRecent(int $userId, Message $message): array
	{
		$fields = parent::getFieldsForRecent($userId, $message);
		if (empty($fields))
		{
			return [];
		}
		$fields['ITEM_ID'] = $this->getCompanion($userId)->getId();

		return $fields;
	}

	protected function insertRecent(array $fields): void
	{
		foreach ($fields as $item)
		{
			RecentTable::merge($item, $item, ['USER_ID', 'ITEM_TYPE', 'ITEM_ID']);
		}
	}

	public function getDisplayedTitle(): ?string
	{
		return Loc::getMessage(
			'IM_PRIVATE_CHAT_TITLE',
			[
				//todo: replace to $this->getContext()->getUser() when ->getUser will return V2 User
				'#CHAT_MEMBER_NAME_1#' => \Bitrix\Im\V2\Entity\User\User::getInstance($this->getContext()->getUserId())->getName(),
				'#CHAT_MEMBER_NAME_2#' => $this->getCompanion()->getName(),
			]
		);
	}

	public function addUsers(array $userIds, AddUsersConfig $config = new AddUsersConfig()): Chat
	{
		return $this;
	}

	public function sendPushUpdateMessage(Message $message): void
	{
		$pushFormat = new Message\PushFormat($message);
		$push = $pushFormat->formatMessageUpdate();
		$authorId = $message->getAuthorId();
		$opponentId = $this->getCompanion($authorId)->getId();

		$push['params']['dialogId'] = $authorId;
		$push['params']['fromUserId'] = $authorId;
		$push['params']['toUserId'] = $opponentId;
		Event::add($opponentId, $push);

		$push['params']['dialogId'] = $opponentId;
		$push['params']['fromUserId'] = $opponentId;
		$push['params']['toUserId'] = $authorId;
		Event::add($authorId, $push);
	}

	protected function getPushService(\Bitrix\Im\V2\Message $message, SendingConfig $config): PushService
	{
		return new Message\Send\Push\PrivatePushService($message, $config);
	}

	protected function sendPushReadSelf(MessageCollection $messages, int $lastId, int $counter): void
	{
		$companionId = $this->getDialogId();
		\Bitrix\Pull\Event::add($this->getContext()->getUserId(), [
			'module_id' => 'im',
			'command' => 'readMessage',
			'params' => [
				'dialogId' => $companionId,
				'chatId' => $this->getChatId(),
				'senderId' => $this->getContext()->getUserId(),
				'id' => (int)$companionId,
				'userId' => (int)$companionId,
				'lastId' => $lastId,
				'counter' => $counter,
				'muted' => false,
				'unread' => Recent::isUnread($this->getContext()->getUserId(), $this->getType(), $this->getDialogId() ?? ''),
				'viewedMessages' => $messages->getIds(),
			],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		]);
	}

	protected function sendPushReadOpponent(MessageCollection $messages, int $lastId): array
	{
		$companionId = $this->getDialogId();
		$pushMessage = [
			'module_id' => 'im',
			'command' => 'readMessageOpponent',
			'expiry' => 3600,
			'params' => [
				'dialogId' => $this->getContext()->getUserId(),
				'chatId' => $this->getChatId(),
				'userId' =>  $this->getContext()->getUserId(),
				'userName' => User::getInstance($this->getContext()->getUserId())->getFullName(false),
				'lastId' => $lastId,
				'date' => (new DateTime())->format('c'),
				'chatMessageStatus' => $this->getReadService()->getChatMessageStatus($this->getChatId()),
				'viewedMessages' => $messages->getIds(),
			],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		];
		\Bitrix\Pull\Event::add($companionId, $pushMessage);

		return $pushMessage;
	}

	protected function sendEventRead(int $startId, int $endId, int $counter, bool $byEvent): void
	{
		foreach(GetModuleEvents("im", "OnAfterUserRead", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array(Array(
				'DIALOG_ID' => $this->getDialogId(),
				'CHAT_ID' => $this->getChatId(),
				'CHAT_ENTITY_TYPE' => 'USER',
				'CHAT_ENTITY_ID' => '',
				'START_ID' => $startId,
				'END_ID' => $endId,
				'COUNT' => $counter,
				'USER_ID' => $this->getContext()->getUserId(),
				'BY_EVENT' => $byEvent
			)));
		}
	}

	/**
	 * Looks for private chat one-to-one by its participants.
	 *
	 * @param array $params
	 * <pre>
	 * [
	 *    (int) CHAT_ID
	 *    (int) FROM_USER_ID
	 *    (int) TO_USER_ID
	 * ]
	 * </pre>
	 * @param Context|null $context
	 * @return Result
	 */
	public static function find(array $params, ?Context $context = null): Result
	{
		$result = new Result;

		if (isset($params['CHAT_ID']))
		{
			$chatId = (int)$params['CHAT_ID'];
			$relations = \CIMChat::getRelationById($chatId, false, true, false);

			$params['TO_USER_ID'] = (int)$params['FROM_USER_ID'];//check for self-personal chat
			foreach ($relations as $rel)
			{
				if (
					$params['TO_USER_ID']
					&& $rel['USER_ID'] == $params['FROM_USER_ID']
				)
				{
					continue;
				}

				$params['TO_USER_ID'] = (int)$rel['USER_ID'];
			}
		}

		if (empty($params['FROM_USER_ID']))
		{
			$context = $context ?? Locator::getContext();
			$params['FROM_USER_ID'] = $context->getUserId();
		}

		$params['FROM_USER_ID'] = (int)$params['FROM_USER_ID'];
		$params['TO_USER_ID'] = (int)$params['TO_USER_ID'];

		if ($params['FROM_USER_ID'] <= 0)
		{
			return $result->addError(new ChatError(ChatError::WRONG_SENDER));
		}
		if ($params['TO_USER_ID'] <= 0)
		{
			return $result->addError(new ChatError(ChatError::WRONG_RECIPIENT));
		}

		$connection = \Bitrix\Main\Application::getConnection();

		$res = $connection->query("
			SELECT 
				C.*
			FROM
				b_im_chat C,
				b_im_relation RF,
				b_im_relation RT
			WHERE
				C.ID = RT.CHAT_ID
				AND RF.CHAT_ID = RT.CHAT_ID
				AND C.TYPE = '" . self::IM_TYPE_PRIVATE . "'
				AND RF.USER_ID = " . $params['FROM_USER_ID'] . "
				AND RT.USER_ID = " . $params['TO_USER_ID'] . "
				AND RF.MESSAGE_TYPE = '" . self::IM_TYPE_PRIVATE . "'
				AND RT.MESSAGE_TYPE = '" . self::IM_TYPE_PRIVATE . "'
		");
		if ($row = $res->fetch())
		{
			$result->setResult([
				'ID' => (int)$row['ID'],
				'TYPE' => $row['TYPE'],
				'ENTITY_TYPE' => $row['ENTITY_TYPE'],
				'ENTITY_ID' => $row['ENTITY_ID'],
			]);
		}

		return $result;
	}

	public function add(array $params, ?Context $context = null): Result
	{
		$result = new Result;

		$paramsResult = $this->prepareParams($params);
		if (!$paramsResult->isSuccess())
		{
			return $result->addErrors($paramsResult->getErrors());
		}

		$params = $paramsResult->getResult();

		if (!\Bitrix\Im\Dialog::hasAccess($params['FROM_USER_ID'], $params['TO_USER_ID']))
		{
			return $result->addError(new ChatError(ChatError::ACCESS_DENIED));
		}

		if ($params['FROM_USER_ID'] == $params['TO_USER_ID'])
		{
			return (new FavoriteChat($params))->add($params);
		}

		$chatResult = self::find($params);
		if ($chatResult->isSuccess() && $chatResult->hasResult())
		{
			$chatParams = $chatResult->getResult();

			return $result->setResult([
				'CHAT_ID' => (int)$chatParams['ID'],
				'CHAT' => self::load($chatParams),
			]);
		}

		$chat = new static($params);
		$chat->save();

		if ($chat->getChatId() <= 0)
		{
			return $result->addError(new ChatError(ChatError::CREATION_ERROR));
		}

		\Bitrix\Im\Model\RelationTable::add([
			'CHAT_ID' => $chat->getChatId(),
			'MESSAGE_TYPE' => \IM_MESSAGE_PRIVATE,
			'USER_ID' => $params['FROM_USER_ID'],
			'STATUS' => \IM_STATUS_READ,
		]);
		\Bitrix\Im\Model\RelationTable::add([
			'CHAT_ID' => $chat->getChatId(),
			'MESSAGE_TYPE' => \IM_MESSAGE_PRIVATE,
			'USER_ID' => $params['TO_USER_ID'],
			'STATUS' => \IM_STATUS_READ,
		]);

		$botJoinFields = [
			'CHAT_TYPE' => \IM_MESSAGE_PRIVATE,
			'MESSAGE_TYPE' => \IM_MESSAGE_PRIVATE
		];
		if (
			User::getInstance($params['FROM_USER_ID'])->isExists()
			&& !User::getInstance($params['FROM_USER_ID'])->isBot()
		)
		{
			$botJoinFields['BOT_ID'] = $params['TO_USER_ID'];
			$botJoinFields['USER_ID'] = $params['FROM_USER_ID'];
			$botJoinFields['TO_USER_ID'] = $params['TO_USER_ID'];
			$botJoinFields['FROM_USER_ID'] = $params['FROM_USER_ID'];
			\Bitrix\Im\Bot::onJoinChat($params['FROM_USER_ID'], $botJoinFields);
		}
		elseif (
			User::getInstance($params['TO_USER_ID'])->isExists()
			&& !User::getInstance($params['TO_USER_ID'])->isBot()
		)
		{
			$botJoinFields['BOT_ID'] = $params['FROM_USER_ID'];
			$botJoinFields['USER_ID'] = $params['TO_USER_ID'];
			$botJoinFields['TO_USER_ID'] = $params['TO_USER_ID'];
			$botJoinFields['FROM_USER_ID'] = $params['FROM_USER_ID'];
			\Bitrix\Im\Bot::onJoinChat($params['TO_USER_ID'], $botJoinFields);
		}

		$chat->isFilledNonCachedData = false;

		return $result->setResult([
			'CHAT_ID' => $chat->getChatId(),
			'CHAT' => $chat,
		]);
	}

	protected function prepareParams(array $params = []): Result
	{
		$result = new Result();

		if (isset($params['FROM_USER_ID']))
		{
			$params['AUTHOR_ID'] = $params['FROM_USER_ID'] = (int)$params['FROM_USER_ID'];
		}
		if ($params['FROM_USER_ID'] <= 0)
		{
			return $result->addError(new ChatError(ChatError::WRONG_SENDER));
		}

		if (isset($params['TO_USER_ID']))
		{
			$params['TO_USER_ID'] = (int)$params['TO_USER_ID'];
		}
		else
		{
			$params['TO_USER_ID'] = 0;
		}

		if ($params['TO_USER_ID'] <= 0)
		{
			return $result->addError(new ChatError(ChatError::WRONG_RECIPIENT));
		}

		$result->setResult($params);

		return $result;
	}

	protected function addIndex(): Chat
	{
		return $this;
	}

	protected function updateIndex(): Chat
	{
		return $this;
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		$userIds = [$this->getContext()->getUserId(), $this->getCompanion()->getId()];

		return new PopupData([new UserPopupItem($userIds)], $excludedList);
	}
}