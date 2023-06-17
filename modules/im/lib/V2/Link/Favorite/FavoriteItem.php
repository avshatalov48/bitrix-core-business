<?php

namespace Bitrix\Im\V2\Link\Favorite;

use Bitrix\Im\Model\EO_LinkFavorite;
use Bitrix\Im\Model\LinkFavoriteTable;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Link\BaseLinkItem;
use Bitrix\Im\V2\Link\Reminder\ReminderPopupItem;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Entity;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Main\NotImplementedException;

/**
 * @method Message getEntity()()
 */
class FavoriteItem extends BaseLinkItem
{
	use ContextCustomer;

	public function __construct($source = null)
	{
		$this->initByDefault();

		if (!empty($source))
		{
			$this->load($source);
		}
	}

	public static function getDataClass(): string
	{
		return LinkFavoriteTable::class;
	}

	protected static function getEntityIdFieldName(): string
	{
		return 'MESSAGE_ID';
	}

	public static function getEntityClassName(): string
	{
		return Message::class;
	}

	public static function getRestEntityName(): string
	{
		return 'link';
	}

	public static function initByEntity(EO_LinkFavorite $entity): self
	{
		$favoriteMessage = new static($entity);

		if ($entity->getMessage() !== null)
		{
			$favoriteMessage->setEntity(new Message($entity->getMessage()));
		}

		return $favoriteMessage;
	}

	public static function createFromMessage(Message $message, ?Context $context = null): self
	{
		$favoriteMessage = new static();
		$favoriteMessage->setContext($context);

		$favoriteMessage
			->setEntity($message)
			->setAuthorId($favoriteMessage->getContext()->getUserId())
			->setChatId($message->getChatId())
		;

		return $favoriteMessage;
	}

	public static function getByMessageAndUserId(Message $message, int $userId): ?self
	{
		if ($message->getMessageId() === null)
		{
			return null;
		}

		$entity = LinkFavoriteTable::query()
			->setSelect(['CHAT_ID', 'AUTHOR_ID', 'DATE_CREATE', 'MESSAGE_ID'])
			->where('MESSAGE_ID', $message->getMessageId())
			->where('AUTHOR_ID', $userId)
			->setLimit(1)
			->fetchObject()
		;

		if ($entity === null)
		{
			return null;
		}

		return static::initByEntity($entity)->setEntity($message);
	}

	public static function linkEntityToMessage(Entity $entity, Message $message): BaseLinkItem
	{
		throw new NotImplementedException();
	}

	public function setMessageInfo(Message $message): BaseLinkItem
	{
		$this->setEntity($message);

		return $this;
	}

	public function setMessageId(?int $messageId): BaseLinkItem
	{
		$this->setEntityId($messageId);

		return parent::setMessageId($messageId);
	}

	public function toRestFormat(array $option = []): array
	{
		return [
			'id' => $this->getPrimaryId(),
			'messageId' => $this->getMessageId(),
			'chatId' => $this->getChatId(),
			'authorId' => $this->getAuthorId(),
			'dateCreate' => $this->getDateCreate()->format('c'),
			'message' => $this->getEntity()->toRestFormat(),
		];
	}

	public function getMessageId(): ?int
	{
		return $this->getEntityId();
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		$data = new PopupData([
			new Entity\User\UserPopupItem(),
			new Entity\File\FilePopupItem(),
			new ReminderPopupItem()
		], $excludedList);

		return $data->merge(parent::getPopupData($excludedList));
	}
}