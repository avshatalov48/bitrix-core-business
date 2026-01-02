<?php

namespace Bitrix\Im\V2\Message\Reaction;

use Bitrix\Im\Model\ReactionTable;
use Bitrix\Im\V2\Analytics\MessageAnalytics;
use Bitrix\Im\V2\Anchor\AnchorFeature;
use Bitrix\Im\V2\Anchor\DI\AnchorContainer;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Result;
use Bitrix\Imopenlines\MessageParameter;
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

		(new PushService())->add($reactionItem);
		(new MessageAnalytics($this->message))->addAddReaction($reaction);
		(new ReactionEvent($this->message, $reactionItem, ReactionEvent::ADD_REACTION))->sendBotEvent();

		$this->addAnchors($reaction);
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
		(new ReactionEvent($this->message, $reactionItem, ReactionEvent::DELETE_REACTION))->sendBotEvent();

		$this->deleteAnchor();

		return $result;
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

	private function addAnchors(string $reaction): void
	{
		if (!AnchorFeature::isOn())
		{
			return;
		}

		$anchorService = AnchorContainer::getInstance()
			->getAnchorService($this->message)
			->setContext($this->getContext());

		$anchorService->addReactionAnchor($reaction);
	}

	private function deleteAnchor(): void
	{
		$anchorService = AnchorContainer::getInstance()
			->getAnchorService($this->message)
			->setContext($this->getContext());

		$anchorService->deleteReactionAnchors();
	}
}
