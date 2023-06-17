<?php

namespace Bitrix\Im\V2\Link\Reminder;

use Bitrix\Im\Model\EO_LinkReminder;
use Bitrix\Im\Model\LinkReminderTable;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Link\BaseLinkItem;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Rest\RestEntity;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\Type\DateTime;

/**
 * @method Message getEntity()
 */
class ReminderItem extends BaseLinkItem
{
	use ContextCustomer;

	protected DateTime $dateRemind;
	protected bool $isReminded;

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
		return LinkReminderTable::class;
	}

	public static function getEntityClassName(): string
	{
		return Message::class;
	}

	protected static function getEntityIdFieldName(): string
	{
		return 'MESSAGE_ID';
	}

	public static function getRestEntityName(): string
	{
		return 'link';
	}

	public static function initByEntity(EO_LinkReminder $entity): self
	{
		$reminder = new static($entity);

		if ($entity->getMessage() !== null)
		{
			$reminder->setEntity(new Message($entity->getMessage()));
		}

		return $reminder;
	}

	public static function createFromMessage(Message $message, ?Context $context = null): self
	{
		$reminder = new static();
		$reminder->setContext($context);

		$reminder
			->setEntity($message)
			->setAuthorId($reminder->getContext()->getUserId())
			->setChatId($message->getChatId())
		;

		return $reminder;
	}

	public static function getByMessageAndUserId(Message $message, int $userId): ?self
	{
		$entity = LinkReminderTable::query()
			->setSelect(['ID', 'CHAT_ID', 'AUTHOR_ID', 'DATE_CREATE', 'MESSAGE_ID', 'DATE_REMIND', 'IS_REMINDED'])
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

	public static function linkEntityToMessage(RestEntity $entity, Message $message): BaseLinkItem
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
		$message = null;
		if (!isset($option['WITHOUT_MESSAGES']) || $option['WITHOUT_MESSAGES'] === 'N')
		{
			$message = $this->getEntity()->toRestFormat();
		}
		return [
			'id' => $this->getPrimaryId(),
			'messageId' => $this->getMessageId(),
			'chatId' => $this->getChatId(),
			'authorId' => $this->getAuthorId(),
			'dateCreate' => $this->getDateCreate()->format('c'),
			'dateRemind' => $this->getDateRemind()->format('c'),
			'isReminded' => $this->isReminded(),
			'message' => $message,
		];
	}

	public function getMessageId(): ?int
	{
		return $this->getEntityId();
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		$excludedList[] = ReminderPopupItem::class;

		return parent::getPopupData($excludedList);
	}

	protected static function mirrorDataEntityFields(): array
	{
		$additionalFields = [
			'DATE_REMIND' => [
				'field' => 'dateRemind',
				'set' => 'setDateRemind', /** @see ReminderItem::setDateRemind */
				'get' => 'getDateRemind', /** @see ReminderItem::getDateRemind */
			],
			'IS_REMINDED' => [
				'field' => 'isReminded',
				'set' => 'setIsReminded', /** @see ReminderItem::setIsReminded */
				'get' => 'isReminded', /** @see ReminderItem::isReminded */
			],
		];

		return array_merge(parent::mirrorDataEntityFields(), $additionalFields);
	}

	//region Getters & setters

	public function getDateRemind(): DateTime
	{
		return $this->dateRemind;
	}

	public function setDateRemind(DateTime $dateRemind): ReminderItem
	{
		$this->dateRemind = $dateRemind;
		return $this;
	}

	public function isReminded(): bool
	{
		return $this->isReminded;
	}

	public function setIsReminded(bool $isReminded): ReminderItem
	{
		$this->isReminded = $isReminded;
		return $this;
	}

	//endregion
}