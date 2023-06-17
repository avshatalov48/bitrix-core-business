<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\User;
use Bitrix\Im\Recent;
use Bitrix\Im\Notify;
use Bitrix\Im\V2\Bot\BotService;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Entity\User\NullUser;
use Bitrix\Im\V2\Link\Url\UrlService;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\Params;
use Bitrix\Im\V2\Message\MessageError;
use Bitrix\Im\V2\Message\ReadService;
use Bitrix\Im\V2\Message\Send\MentionService;
use Bitrix\Im\V2\Message\Send\PushService;
use Bitrix\Im\V2\Message\Send\SendingConfig;
use Bitrix\Im\V2\Message\Send\SendingService;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Relation;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Service\Locator;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

class PrivateChat extends Chat
{
	protected function getDefaultType(): string
	{
		return self::IM_TYPE_PRIVATE;
	}

	protected function checkAccessWithoutCaching(int $userId): bool
	{
		return $this->getCompanion($userId)->hasAccess($userId);
	}

	/**
	 * Allows to send mention notification.
	 * @return bool
	 */
	public function allowMention(): bool
	{
		return false;
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

	/**
	 * @param Message $message
	 * @return void
	 */
	public function riseInRecent(Message $message): void
	{
		/** @var Relation $relation */
		$opponentUserId = 0;
		foreach ($this->getRelations() as $relation)
		{
			if (
				User::getInstance($relation->getUserId())->isActive()
				&& $message->getAuthorId() != $relation->getUserId()
			)
			{
				$opponentUserId = $relation->getUserId();
				break;
			}
		}

		foreach ($this->getRelations() as $relation)
		{
			if (!User::getInstance($relation->getUserId())->isActive())
			{
				continue;
			}

			\CIMContactList::SetRecent([
				'ENTITY_ID' => $relation->getUserId() == $opponentUserId ? $message->getAuthorId() : $opponentUserId,
				'MESSAGE_ID' => $message->getMessageId(),
				'CHAT_TYPE' => self::IM_TYPE_PRIVATE,
				'CHAT_ID' => $relation->getChatId(),
				'RELATION_ID' => $relation->getId(),
				'USER_ID' => $relation->getUserId(),
			]);
		}
	}

	public function getCompanion(?int $userId = null): \Bitrix\Im\V2\Entity\User\User
	{
		$userId = $userId ?? $this->getContext()->getUserId();

		$relations = $this->getRelations(['LIMIT' => 2]);

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

	public function getTitle(): ?string
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

	protected function sendPushUnreadSelf(int $unreadToId, int $lastId, int $counter, ?array $lastMessageStatuses = null): void
	{
		\Bitrix\Pull\Event::add($this->getContext()->getUserId(), [
			'module_id' => 'im',
			'command' => 'unreadMessage',
			'expiry' => 3600,
			'params' => [
				'dialogId' => $this->getDialogId(),
				'chatId' => $this->chatId,
				'userId' => (int)$this->getDialogId(),
				'date' => new \Bitrix\Main\Type\DateTime(),
				'counter' => $counter,
				'muted' => false,
				'unread' => Recent::isUnread($this->getContext()->getUserId(), $this->getType(), $this->getDialogId()),
				'unreadToId' => $unreadToId,
			],
			'push' => ['badge' => 'Y'],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		]);
	}

	protected function sendPushUnreadOpponent(string $chatMessageStatus, int $unreadTo, ?array $lastMessageStatuses = null): void
	{
		$pushMessage = [
			'module_id' => 'im',
			'command' => 'unreadMessageOpponent',
			'expiry' => 3600,
			'params' => [
				'dialogId' => $this->getContext()->getUserId(),
				'chatId' => $this->chatId,
				'userId' =>$this->getContext()->getUserId(),
				'chatMessageStatus' => $chatMessageStatus,
				'unreadTo' => $unreadTo,
			],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		];
		\Bitrix\Pull\Event::add($this->getDialogId(), $pushMessage);
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
	 * Provides message sending process.
	 *
	 * @param Message|string|array $message
	 * @param SendingConfig|array|null $sendingConfig
	 * @return Result
	 */
	public function sendMessage($message, $sendingConfig = null): Result
	{
		$result = new Result;

		if (!$this->getChatId())
		{
			return $result->addError(new ChatError(ChatError::WRONG_TARGET_CHAT));
		}

		if (!$message instanceof Message)
		{
			$message = new Message($message);
		}

		$message
			->setRegistry($this->messageRegistry)
			->setContext($this->context)
			->setChatId($this->getChatId())
		;

		if (!$message->getNotifyModule())
		{
			$message->setNotifyModule('im');
		}
		if ($message->isSystem())
		{
			$message->setNotifyEvent(Notify::EVENT_PRIVATE_SYSTEM);
		}
		else
		{
			$message->setNotifyEvent(Notify::EVENT_PRIVATE);
		}

		// config for sending process
		if ($sendingConfig instanceof SendingConfig)
		{
			$sendingServiceConfig = $sendingConfig;
		}
		else
		{
			$sendingServiceConfig = new SendingConfig();
			if (is_array($sendingConfig))
			{
				$sendingServiceConfig->fill($sendingConfig);
			}
		}
		// sending process
		$sendService = new SendingService($sendingServiceConfig);
		$sendService->setContext($this->context);

		// check duplication by UUID
		if (
			!$message->isSystem()
			&& $message->getUuid()
		)
		{
			$checkUuidResult = $sendService->checkDuplicateByUuid($message);
			if (!$checkUuidResult->isSuccess())
			{
				return $result->addErrors($checkUuidResult->getErrors());
			}
			$data = $checkUuidResult->getResult();
			if (!empty($data['messageId']))
			{
				return $result->setResult($checkUuidResult->getResult());
			}
		}

		// author from current context
		if (
			!$message->getAuthorId()
			&& !$message->isSystem()
		)
		{
			$message->setAuthorId($this->getContext()->getUserId());
			if (!$message->getAuthorId())
			{
				return $result->addError(new ChatError(ChatError::WRONG_SENDER));
			}
		}

		// Extranet cannot send system
		if (
			$message->isSystem()
			&& $message->getAuthorId()
			&& User::getInstance($message->getAuthorId())->isExtranet()
		)
		{
			$message->markAsSystem(false);
		}

		// permissions
		if (
			!$sendingServiceConfig->skipUserCheck()
			&& !$sendingServiceConfig->convertMode()
			&& !$message->isSystem()
		)
		{
			if (!$this->hasAccess($message->getAuthorId()))
			{
				return $result->addError(new ChatError(ChatError::ACCESS_DENIED));
			}
		}

		// fire event `im:OnBeforeChatMessageAdd` before message send
		$eventResult = $sendService->fireEventBeforeMessageSend($this, $message);
		if (!$eventResult->isSuccess())
		{
			// cancel sending by event
			return $result->addErrors($eventResult->getErrors());
		}

		// check for empty message
		if (
			!$message->getMessage()
			&& !$message->hasFiles()
			&& !$message->getParams()->isSet(Params::ATTACH)
		)
		{
			return $result->addError(new MessageError(MessageError::EMPTY_MESSAGE));
		}

		// Replacements / DateLink
		if ($sendingServiceConfig->generateUrlPreview())
		{
			$message->parseDates();
		}

		// Emoji
		$message->checkEmoji();


		// BB codes with disk files
		$message->uploadFileFromText();

		// Format attached files
		$message->formatFilesMessageOut();

		// Save + Save Params
		$saveResult = $message->save();
		if (!$saveResult->isSuccess())
		{
			return $result->addErrors($saveResult->getErrors());
		}

		// Unread
		$readService = new ReadService($message->getAuthorId());
		$readService->markMessageUnread($message, $this->getRelations());

		// Chat message counter
		$this
			->setLastMessageId($message->getMessageId())
			->incrementMessageCount()
			->save()
		;

		// Recent
		if ($sendingServiceConfig->addRecent())
		{
			$readService->markRecentUnread($message);
		}

		// Counters
		$counters = [];
		if ($sendingServiceConfig->sendPush())
		{
			$counters = $readService->getCountersForUsers($message, $this->getRelations());
		}

		// fire event `im:OnAfterMessagesAdd`
		$sendService->fireEventAfterMessageSend($this, $message);

		// Recent
		if (
			$sendingServiceConfig->addRecent()
			&& !$sendingServiceConfig->skipAuthorAddRecent()// Do not add author into recent list in case of self message chat.
		)
		{
			$this->riseInRecent($message);
		}

		// Rich
		if ($sendingServiceConfig->generateUrlPreview())
		{
			// generate preview or set bg job
			$message->generateUrlPreview();
		}

		if ($this->getParentMessageId())
		{
			$this->updateParentMessageCount();
		}

		// send Push
		if ($sendingServiceConfig->sendPush())
		{
			$pushService = new PushService($sendingServiceConfig);
			$pushService->sendPushPrivateChat($this, $message, $counters);
		}

		// Mentions
		if (!$message->isSystem())
		{
			$mentionService = new MentionService($sendingServiceConfig);
			$mentionService->setContext($this->context);
			$mentionService->sendMentions($this, $message);
		}

		// Run message command
		$botService = new BotService($sendingServiceConfig);
		$botService->setContext($this->context);
		$botService->runMessageCommand($this, $message);

		// Links
		(new UrlService())->saveUrlsFromMessage($message);

		// search
		$message->updateSearchIndex();

		$result->setResult(['messageId' => $message->getMessageId()]);

		return $result;
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
			$result->setResult($row);
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

		$chat = new PrivateChat($params);
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

		$chat->updateIndex();

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
}
