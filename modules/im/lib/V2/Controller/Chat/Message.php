<?php

namespace Bitrix\Im\V2\Controller\Chat;

use Bitrix\Im\Recent;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Controller\BaseController;
use Bitrix\Im\V2\Entity\View\ViewCollection;
use Bitrix\Im\V2\Message\Forward\ForwardService;
use Bitrix\Im\V2\Message\MessageError;
use Bitrix\Im\V2\Message\Reply\ReplyService;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Message\MessageService;
use Bitrix\Im\V2\Message\ReadService;
use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\CurrentUser;

class Message extends BaseController
{
	protected const MAX_MESSAGES_COUNT = 100;
	protected const MAX_MESSAGES_COUNT_FOR_FORWARD = 20;
	protected const MESSAGE_ON_PAGE_COUNT = 50;

	public function getPrimaryAutoWiredParameter()
	{
		return new ExactParameter(
			\Bitrix\Im\V2\Message::class,
			'message',
			function ($className, int $id) {
				return $this->getMessageById($id);
			}
		);
	}

	public function getAutoWiredParameters()
	{
		return array_merge([
			new ExactParameter(
				MessageCollection::class,
				'messages',
				function($className, array $ids) {
					if (count($ids) > self::MAX_MESSAGES_COUNT)
					{
						$this->addError(new MessageError(MessageError::TOO_MANY_MESSAGES));

						return null;
					}
					$ids = array_map('intval', $ids);

					return new MessageCollection($ids);
				}
			),
		], parent::getAutoWiredParameters());
	}

	/**
	 * @restMethod im.v2.Chat.Message.read
	 */
	public function readAction(MessageCollection $messages): ?array
	{
		$readResult = Chat::getInstance($messages->getCommonChatId())->readMessages($messages);

		if (!$readResult->isSuccess())
		{
			$this->addErrors($readResult->getErrors());

			return null;
		}

		return $this->convertKeysToCamelCase($readResult->getResult());
	}

	/**
	 * @restMethod im.v2.Chat.Message.tailViewers
	 */
	public function tailViewersAction(\Bitrix\Im\V2\Message $message, array $filter = [], array $order = [], int $limit = 50): ?array
	{
		$viewFilter = [
			'LAST_ID' => isset($filter['lastId']) ? (int)$filter['lastId'] : null,
			'MESSAGE_ID' => $message->getId(),
		];
		$viewOrder = ['ID' => $order['id'] ?? 'ASC'];
		$viewLimit = $this->getLimit($limit);

		$views = ViewCollection::find($viewFilter, $viewOrder, $viewLimit);

		return $this->toRestFormat($views);
	}

	/**
	 * @restMethod im.v2.Chat.Message.mark
	 */
	public function markAction(\Bitrix\Im\V2\Message $message): ?array
	{
		$markResult = $message->mark();

		if (!$markResult->isSuccess())
		{
			$this->addErrors($markResult->getErrors());

			return null;
		}

		return [];
	}

	/**
	 * @restMethod im.v2.Chat.Message.list
	 */
	public function listAction(Chat $chat, CurrentUser $user, int $limit = self::MESSAGE_ON_PAGE_COUNT): ?array
	{
		$startMessage = $this->getStartChatMessage($chat, $user);
		$messages = (new MessageService())->getMessageContext($startMessage, $limit, \Bitrix\Im\V2\Message::REST_FIELDS)->getResult();

		return $this->fillContextPaginationData($this->toRestFormat($messages), $messages, $startMessage, $limit);
	}

	/**
	 * @restMethod im.v2.Chat.Message.getContext
	 */
	public function getContextAction(\Bitrix\Im\V2\Message $message, int $range = self::MESSAGE_ON_PAGE_COUNT): ?array
	{
		$messages = (new MessageService())->getMessageContext($message, $range, \Bitrix\Im\V2\Message::REST_FIELDS)->getResult();

		return $this->fillContextPaginationData($this->toRestFormat($messages), $messages, $message, $range);
	}

	/**
	 * @restMethod im.v2.Chat.Message.tail
	 */
	public function tailAction(Chat $chat, array $filter = [], array $order = [], int $limit = 50): ?array
	{
		$messageOrder = [];
		$messageFilter = [];

		if (isset($order['id']))
		{
			$messageOrder['ID'] = strtoupper($order['id']);
		}

		if (isset($filter['lastId']))
		{
			$messageFilter['LAST_ID'] = (int)$filter['lastId'];
		}

		$messageFilter['START_ID'] = $chat->getStartId();
		$messageFilter['CHAT_ID'] = $chat->getChatId();

		$messages = MessageCollection::find($messageFilter, $messageOrder, $this->getLimit($limit), null, \Bitrix\Im\V2\Message::REST_FIELDS);
		$rest = $this->toRestFormat($messages);
		//todo: refactor. Change to popup data.
		$rest['hasNextPage'] = $messages->count() >= $limit;

		return $rest;
	}

	/**
	 * @restMethod im.v2.Chat.Message.pin
	 */
	public function pinAction(\Bitrix\Im\V2\Message $message): ?array
	{
		$pinResult = $message->pin();

		if (!$pinResult->isSuccess())
		{
			$this->addErrors($pinResult->getErrors());

			return null;
		}

		return [];
	}

	/**
	 * @restMethod im.v2.Chat.Message.unpin
	 */
	public function unpinAction(\Bitrix\Im\V2\Message $message): ?array
	{
		$unpinResult = $message->unpin();

		if (!$unpinResult->isSuccess())
		{
			$this->addErrors($unpinResult->getErrors());

			return null;
		}

		return [];
	}

	/**
	 * @restMethod im.v2.Chat.Message.forward
	 */
	public function forwardAction(Chat $chat, MessageCollection $messages, ?string $comment = null): ?array
	{
		if ($messages->count() > self::MAX_MESSAGES_COUNT_FOR_FORWARD)
		{
			$this->addError(new MessageError(MessageError::TOO_MANY_MESSAGES));

			return null;
		}

		$service = new ForwardService($chat);
		$result = $service->createMessages($messages, $comment);

		if (!$result->hasResult())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return $this->toRestFormat($result->getResult());
	}

	/**
	 * @restMethod im.v2.Chat.Message.reply
	 */
	public function replyAction(\Bitrix\Im\V2\Message $message, string $comment): ?array
	{
		$service = new ReplyService();
		$result = $service->createMessage($message, $comment);

		if (!$result->hasResult())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return $this->toRestFormat($result->getResult());
	}

	private function getStartChatMessage(Chat $chat, CurrentUser $user): \Bitrix\Im\V2\Message
	{
		$readService = new ReadService();
		$startMessageId =
			Recent::getMarkedId((int)$user->getId(), $chat->getType(), $chat->getDialogId())
				?: $readService->getLastIdByChatId($chat->getChatId())
		;

		return (new \Bitrix\Im\V2\Message($startMessageId))->setChatId($chat->getChatId());
	}

	private function getCountHigherMessages(MessageCollection $messages, int $id): int
	{
		$count = 0;

		foreach ($messages as $message)
		{
			if ($message->getId() < $id)
			{
				++$count;
			}
		}

		return $count;
	}

	private function getLastSelectedId(MessageCollection $messages): int
	{
		return max($messages->getIds() ?: [0]);
	}

	//todo: refactor. Change to popup data.
	private function fillContextPaginationData(
		array $rest,
		MessageCollection $messages,
		\Bitrix\Im\V2\Message $targetMessage,
		int $range
	): array
	{
		$rest['hasPrevPage'] = $this->getCountHigherMessages($messages, $targetMessage->getId() ?? 0) >= $range;
		$lastSelectedId = $this->getLastSelectedId($messages);
		$lastMessageIdInChat = (new ReadService())->getLastMessageIdInChat($targetMessage->getChatId());
		$rest['hasNextPage'] = $lastSelectedId > 0 && $lastMessageIdInChat > 0 && $lastSelectedId < $lastMessageIdInChat;

		return $rest;
	}
}