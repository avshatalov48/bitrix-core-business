<?php

namespace Bitrix\Im\V2\Message\Reaction;

use Bitrix\Im\Model\ReactionTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Result;
use Bitrix\Imopenlines\MessageParameter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

class ReactionService
{
	use ContextCustomer;

	private bool $withLegacy;
	private Message $message;

	public function __construct(Message $message, bool $withLegacy = true)
	{
		$this->message = $message;
		$message->setContext($this->context);
		$this->withLegacy = $withLegacy;
	}

	public function addReaction(string $reaction, bool $byEvent = false): Result
	{
		$result = new Result();
		$reactionItem = new ReactionItem();
		$reactionItem
			->setMessageId($this->message->getMessageId())
			->setChatId($this->message->getChatId())
			->setUserId($this->getContext()->getUserId())
			->setContext($this->getContext())
			->setReaction($reaction)
		;

		$this->deleteAllReactions();

		try
		{
			$saveResult = $reactionItem->save();
			if (!$saveResult->isSuccess())
			{
				return $result->addErrors($saveResult->getErrors());
			}
		}
		catch (SystemException $exception)
		{
			return $result->addError(new ReactionError(ReactionError::ALREADY_SET));
		}

		if (!$byEvent && $this->isMessageLiveChat())
		{
			$this->processAddForLiveChat($reaction);
		}

		if ($this->withLegacy)
		{
			$this->addLegacy();
		}

		$this->sendNotification($reactionItem);

		(new PushService())->add($reactionItem);

		return $result;
	}

	public function deleteReaction(string $reaction, bool $byEvent = false): Result
	{
		$result = new Result();
		$reactionItem = ReactionItem::getByMessage($this->message->getMessageId(), $reaction, $this->getContext()->getUserId());

		if ($reactionItem === null)
		{
			return $result->addError(new ReactionError(ReactionError::NOT_FOUND));
		}

		$deleteResult = $reactionItem->delete();

		if (!$deleteResult->isSuccess())
		{
			return $result->addErrors($deleteResult->getErrors());
		}

		if (!$byEvent && $this->isMessageLiveChat())
		{
			$this->processDeleteForLiveChat($reaction);
		}

		if ($this->withLegacy)
		{
			$this->deleteLegacy();
		}

		(new PushService())->delete($reactionItem);

		return $result;
	}

	private function sendNotification(ReactionItem $reaction): void
	{
		if (
			$this->message->getAuthorId() === 0
			|| $this->message->getAuthorId() === $this->getContext()->getUserId()
			|| Chat::getInstance($reaction->getChatId())->getEntityType() === 'LIVECHAT'
		)
		{
			return;
		}

		$arMessageFields = [
			'MESSAGE_TYPE' => IM_MESSAGE_SYSTEM,
			'TO_USER_ID' => $this->message->getAuthorId(),
			'FROM_USER_ID' => $this->getContext()->getUserId(),
			'NOTIFY_TYPE' => IM_NOTIFY_FROM,
			'NOTIFY_MODULE' => 'im',
			'NOTIFY_EVENT' => 'like',
			'NOTIFY_TAG' => $this->getNotifyTag($reaction),
			'NOTIFY_MESSAGE' => $this->getTextNotification($reaction),
		];
		\CIMNotify::Add($arMessageFields);
	}

	private function getNotifyTag(ReactionItem $reaction): string
	{
		$chat = Chat::getInstance($reaction->getChatId());
		if ($chat instanceof Chat\PrivateChat)
		{
			$type = 'P';
			$id = $this->getContext()->getUserId();
		}
		else
		{
			$type = 'G';
			$id = $chat->getChatId();
		}

		return "RATING|IM|{$type}|{$id}|{$reaction->getMessageId()}|{$reaction->getReaction()}";
	}

	private function getTextNotification(ReactionItem $reaction): string
	{
		$genderModifier = "_{$this->getContext()->getUser()->getGender()}";
		$chat = Chat::getInstance($reaction->getChatId())->withContext($this->context);
		$code = "IM_MESSAGE_REACTION{$genderModifier}";

		if ($chat instanceof Chat\PrivateChat)
		{
			return Loc::getMessage("{$code}_PRIVATE", [
				'#DIALOG_ID#' => $chat->getDialogContextId(),
				'#MESSAGE_ID#' => $this->message->getMessageId(),
				'#QOUTED_MESSAGE#' => $this->message->getForPush(),
				'#REACTION_NAME#' => $reaction->getLocName(),
			]);
		}

		return Loc::getMessage($code, [
			'#DIALOG_ID#' => $chat->getDialogContextId(),
			'#MESSAGE_ID#' => $this->message->getMessageId(),
			'#QOUTED_MESSAGE#' => $this->message->getForPush(),
			'#CHAT_ID#' => $this->message->getChatId(),
			'#CHAT_TITLE#' => $chat->getTitle(),
			'#REACTION_NAME#' => $reaction->getLocName(),
		]);
	}

	private function processAddForLiveChat(string $reaction): void
	{
		$connectorMid = $this->message->getParams()->get(MessageParameter::CONNECTOR_MID)->getValue();

		foreach ($connectorMid as $messageId)
		{
			$service = new static(new Message((int)$messageId), false);
			$service->setContext($this->getContext());
			$service->addReaction($reaction, true);
		}
	}

	private function processDeleteForLiveChat(string $reaction): void
	{
		$connectorMid = $this->message->getParams()->get(MessageParameter::CONNECTOR_MID)->getValue();

		foreach ($connectorMid as $messageId)
		{
			$service = new static(new Message((int)$messageId), false);
			$service->setContext($this->getContext());
			$service->deleteReaction($reaction, true);
		}
	}

	private function isMessageLiveChat(): bool
	{
		$chat = $this->message->getChat();
		$isLiveChat = $chat->getEntityType() === 'LIVECHAT';
		$isToLiveChat = false;
		if ($chat->getEntityType() === 'LINES')
		{
			[$connectorType] = explode('|', $chat->getEntityId());
			$isToLiveChat = $connectorType === 'livechat';
		}

		return $isLiveChat || $isToLiveChat;
	}

	private function hasAnyReaction(): bool
	{
		$result = ReactionTable::query()
			->setSelect(['MESSAGE_ID'])
			->where('MESSAGE_ID', $this->message->getMessageId())
			->where('USER_ID', $this->getContext()->getUserId())
			->setLimit(1)
			->fetch()
		;

		return $result !== false;
	}

	public function deleteAllReactions(): void
	{
		ReactionTable::deleteByFilter(['=MESSAGE_ID' => $this->message->getMessageId(), '=USER_ID' => $this->getContext()->getUserId()]);
	}

	private function addLegacy(): void
	{
		\CIMMessenger::Like($this->message->getMessageId(), 'plus', $this->getContext()->getUserId(), false, false);
	}

	private function deleteLegacy(): void
	{
		if (!$this->hasAnyReaction())
		{
			\CIMMessenger::Like($this->message->getMessageId(), 'minus', $this->getContext()->getUserId(), false, false);
		}
	}
}