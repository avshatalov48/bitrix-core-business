<?php

namespace Bitrix\Im\V2\Link\Favorite;

use Bitrix\Main\ORM\Query\Query;
use Bitrix\Im\Model\LinkFavoriteTable;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Error;
use Bitrix\Im\V2\Link\Push;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Message\Params;
use Bitrix\Im\V2\Message\MessageError;

class FavoriteService
{
	use ContextCustomer;

	public const ADD_FAVORITE_MESSAGE_EVENT = 'messageFavoriteAdd';
	public const DELETE_FAVORITE_MESSAGE_EVENT = 'messageFavoriteDelete';

	public function getCount(int $chatId): int
	{
		$filter = Query::filter()
			->where('CHAT_ID', $chatId)
			->where('AUTHOR_ID', $this->getContext()->getUserId())
		;

		return LinkFavoriteTable::getCount($filter);
	}

	public function markMessageAsFavorite(Message $message): Result
	{
		$result = new  Result();

		$favoriteMessage = FavoriteItem::createFromMessage($message, $this->getContext());
		$saveResult = $this->saveFavoriteMessage($favoriteMessage);

		if (!$saveResult->isSuccess())
		{
			return $result->addErrors($saveResult->getErrors());
		}

		$this->saveInParam($message);

		$pushRecipient = ['RECIPIENT' => [$this->getContext()->getUserId()]];

		Push::getInstance()
			->setContext($this->context)
			->sendFull($favoriteMessage, static::ADD_FAVORITE_MESSAGE_EVENT, $pushRecipient)
		;

		return $result;
	}

	public function unmarkMessageAsFavorite(Message $message): Result
	{
		$result = new Result();

		$favoriteMessage = FavoriteItem::getByMessageAndUserId($message, $this->getContext()->getUserId());

		if ($favoriteMessage === null)
		{
			return $result;
		}

		$deleteResult = $favoriteMessage->delete();

		if (!$deleteResult->isSuccess())
		{
			return $result->addErrors($deleteResult->getErrors());
		}

		$deleteParamResult = $this->deleteFromParam($message);

		if (!$deleteParamResult->isSuccess())
		{
			return $result->addErrors($deleteParamResult->getErrors());
		}

		$pushRecipient = ['RECIPIENT' => [$this->getContext()->getUserId()]];

		Push::getInstance()
			->setContext($this->context)
			->sendIdOnly($favoriteMessage, static::DELETE_FAVORITE_MESSAGE_EVENT, $pushRecipient)
		;

		return $result;
	}

	public function unmarkMessageAsFavoriteForAll(Message $message): Result
	{
		$result = new Result();

		$favoriteMessages = FavoriteCollection::getByMessage($message);

		if ($favoriteMessages === null || $favoriteMessages->count() === 0)
		{
			return $result;
		}

		$deleteResult = $favoriteMessages->delete();

		if (!$deleteResult->isSuccess())
		{
			return $result->addErrors($deleteResult->getErrors());
		}

		/** @var FavoriteItem $favoriteMessage */
		foreach ($favoriteMessages as $favoriteMessage)
		{
			$pushRecipient = ['RECIPIENT' => [$favoriteMessage->getAuthorId()]];
			Push::getInstance()
				->setContext($this->context)
				->sendIdOnly($favoriteMessage,static::DELETE_FAVORITE_MESSAGE_EVENT, $pushRecipient)
			;
		}

		return $result;
	}

	protected function saveFavoriteMessage(FavoriteItem $favoriteMessage): Result
	{
		try
		{
			return $favoriteMessage->save();
		}
		catch (\Bitrix\Main\SystemException $exception)
		{
			return (new Result())->addError(new MessageError(MessageError::MESSAGE_IS_ALREADY_FAVORITE));
		}
	}

	protected function saveInParam(Message $message): Result
	{
		$favoriteListParam = $message->getParams()->get(Params::FAVORITE)->getValue() ?: [];
		$userId = $this->getContext()->getUserId();
		if (!in_array($userId, $favoriteListParam, true))
		{
			$message->getParams()->get(Params::FAVORITE)->addValue($userId);
			$message->getParams()->save();
			\CIMMessageParam::SendPull($message->getMessageId(), [Params::FAVORITE]);
		}

		return new Result();
	}

	protected function deleteFromParam(Message $message): Result
	{
		$favoriteListParam = $message->getParams()->get(Params::FAVORITE)->getValue() ?: [];
		$userId = $this->getContext()->getUserId();
		if (in_array($userId, $favoriteListParam, true))
		{
			$message->getParams()->get(Params::FAVORITE)->unsetValue($userId);
			$message->getParams()->save();
			\CIMMessageParam::SendPull($message->getMessageId(), [Params::FAVORITE]);
		}

		return new Result();
	}
}