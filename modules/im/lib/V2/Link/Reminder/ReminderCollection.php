<?php

namespace Bitrix\Im\V2\Link\Reminder;

use Bitrix\Im\Model\EO_LinkReminder_Collection;
use Bitrix\Im\Model\LinkReminderTable;
use Bitrix\Im\V2\Collection;
use Bitrix\Im\V2\Common\SidebarFilterProcessorTrait;
use Bitrix\Im\V2\Entity\File\FileCollection;
use Bitrix\Im\V2\Entity\File\FilePopupItem;
use Bitrix\Im\V2\Link\BaseLinkCollection;
use Bitrix\Im\V2\Link\LinkItem;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Service\Locator;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\DateTime;

/**
 * @method ReminderItem next()
 * @method ReminderItem current()
 * @method ReminderItem offsetGet($offset)
 */
class ReminderCollection extends BaseLinkCollection
{
	use SidebarFilterProcessorTrait;

	public static function getCollectionElementClass(): string
	{
		return ReminderItem::class;
	}

	public static function find(
		array $filter,
		array $order,
		?int $limit = null,
		?Context $context = null
	): self
	{
		$context = $context ?? Locator::getContext();

		$reminderOrder = ['ID' => 'DESC'];

		if (isset($order['ID']))
		{
			$reminderOrder['ID'] = $order['ID'];
		}

		$query = LinkReminderTable::query()
			->setSelect(['ID', 'CHAT_ID', 'AUTHOR_ID', 'DATE_CREATE', 'MESSAGE_ID', 'DATE_REMIND', 'IS_REMINDED', 'MESSAGE'])
			->where('AUTHOR_ID', $context->getUserId())
			->setOrder($reminderOrder)
		;
		if (isset($limit))
		{
			$query->setLimit($limit);
		}
		static::processFilters($query, $filter, $reminderOrder);

		return static::initByEntityCollection($query->fetchCollection());
	}

	public static function initByEntityCollection(EO_LinkReminder_Collection $entityCollection): self
	{
		$reminderCollection = new static();

		foreach ($entityCollection as $entity)
		{
			$reminderCollection[] = ReminderItem::initByEntity($entity);
		}

		return $reminderCollection;
	}

	public static function getByMessage(Message $message): self
	{
		$entities = LinkReminderTable::query()
			->setSelect(['ID', 'CHAT_ID', 'AUTHOR_ID', 'DATE_CREATE', 'MESSAGE_ID', 'DATE_REMIND', 'IS_REMINDED'])
			->where('MESSAGE_ID', $message->getMessageId())
			->fetchCollection()
		;

		if ($entities === null)
		{
			return new static();
		}

		$links = static::initByEntityCollection($entities);

		foreach ($links as $link)
		{
			$link->setEntity($message);
		}

		return $links;
	}

	public static function getByMessagesAndAuthorId(MessageCollection $messages, int $userId): self
	{
		$links = static::getByMessageIdsAndAuthorId($messages->getIds(), $userId);

		foreach ($links as $link)
		{
			$link->setEntity($messages[$link->getMessageId()]);
		}

		return $links;
	}

	public static function getByMessageIdsAndAuthorId(array $messageIds, int $userId): self
	{
		if (empty($messageIds))
		{
			return new static();
		}

		$entities = LinkReminderTable::query()
			->setSelect(['ID', 'CHAT_ID', 'AUTHOR_ID', 'DATE_CREATE', 'MESSAGE_ID', 'DATE_REMIND', 'IS_REMINDED'])
			->whereIn('MESSAGE_ID', $messageIds)
			->where('AUTHOR_ID', $userId)
			->fetchCollection()
		;

		if ($entities === null)
		{
			return new static();
		}

		return static::initByEntityCollection($entities);
	}

	public static function getNeedReminded(): self
	{
		$entities = LinkReminderTable::query()
			->setSelect(['ID', 'CHAT_ID', 'AUTHOR_ID', 'DATE_CREATE', 'MESSAGE_ID', 'DATE_REMIND', 'IS_REMINDED', 'MESSAGE'])
			->where('DATE_REMIND', '<', new DateTime())
			->where('IS_REMINDED', false)
			->fetchCollection()
		;

		if ($entities === null)
		{
			return new static();
		}

		return static::initByEntityCollection($entities);
	}

	public function getMessageCollection(): MessageCollection
	{
		$messageCollection = new MessageCollection();

		foreach ($this as $reminderItem)
		{
			$messageCollection->add($reminderItem->getEntity());
		}

		return $messageCollection;
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		$excludedList[] = ReminderPopupItem::class;

		if (!in_array(FilePopupItem::class, $excludedList, true))
		{
			$this->getMessageCollection()->fillFiles();
		}

		return parent::getPopupData($excludedList);
	}

	public function toRestFormat(array $option = []): array
	{
		if (!isset($option['WITHOUT_MESSAGES']) || $option['WITHOUT_MESSAGES'] !== 'Y')
		{
			$this->getMessageCollection()->fillAllForRest();
		}

		return parent::toRestFormat($option);
	}

	protected static function processFilters(Query $query, array $filter, array $order): void
	{
		static::processSidebarFilters(
			$query,
			$filter,
			$order,
			['AUTHOR_ID' => 'MESSAGE.AUTHOR_ID', 'DATE_CREATE' => 'MESSAGE.DATE_CREATE']
		);

		if (isset($filter['SEARCH_MESSAGE']))
		{
			$query->whereLike('MESSAGE.MESSAGE', "%{$filter['SEARCH_MESSAGE']}%");
		}

		if (isset($filter['IS_REMINDED']))
		{
			$query->where('IS_REMINDED', (bool)$filter['IS_REMINDED']);
		}
	}
}