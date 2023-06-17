<?php

namespace Bitrix\Im\V2\Link\Pin;

use Bitrix\Im\Model\EO_LinkPin_Collection;
use Bitrix\Im\Model\LinkPinTable;
use Bitrix\Im\V2\Common\SidebarFilterProcessorTrait;
use Bitrix\Im\V2\Entity\File\FilePopupItem;
use Bitrix\Im\V2\Entity\User\UserPopupItem;
use Bitrix\Im\V2\Link\BaseLinkCollection;
use Bitrix\Im\V2\Link\Reminder\ReminderPopupItem;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Service\Locator;
use Bitrix\Main\ORM\Query\Query;

/**
 * @method PinItem next()
 * @method PinItem current()
 * @method PinItem offsetGet($offset)
 */
class PinCollection extends BaseLinkCollection
{
	use SidebarFilterProcessorTrait;

	public static function find(
		array $filter,
		array $order = ['ID' => 'DESC'],
		?int $limit = null,
		?Context $context = null
	): self
	{
		$context = $context ?? Locator::getContext();

		$pinOrder = ['ID' => 'DESC'];

		if (isset($order['ID']))
		{
			$pinOrder['ID'] = $order['ID'];
		}

		$query = LinkPinTable::query()
			->setSelect(['MESSAGE', 'ID', 'CHAT_ID', 'AUTHOR_ID', 'DATE_CREATE', 'MESSAGE_ID'])
			->setOrder($pinOrder)
		;
		if (isset($limit))
		{
			$query->setLimit($limit);
		}

		static::processFilters($query, $filter, $pinOrder);

		return static::initByEntityCollection($query->fetchCollection());
	}

	public static function initByEntityCollection(EO_LinkPin_Collection $entityCollection): self
	{
		$pinCollection = new static();

		foreach ($entityCollection as $entity)
		{
			$pinCollection[] = PinItem::initByEntity($entity);
		}

		return $pinCollection;
	}

	public static function getCollectionElementClass(): string
	{
		return PinItem::class;
	}

	public function getMessageCollection(): MessageCollection
	{
		$messageCollection = new MessageCollection();

		foreach ($this as $pin)
		{
			$messageCollection->add($pin->getEntity());
		}

		return $messageCollection;
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		$messages = $this->getMessageCollection()->fillAllForRest();
		$data = new PopupData([new UserPopupItem(), new FilePopupItem(), new ReminderPopupItem($messages->getReminders())], $excludedList);
		$excludedList[] = ReminderPopupItem::class;

		return $data->merge(parent::getPopupData($excludedList));
	}

	public function toRestFormat(array $option = []): array
	{
		$this->getMessageCollection()->fillAllForRest();

		return parent::toRestFormat($option);
	}

	protected static function processFilters(Query $query, array $filter, array $order): void
	{
		static::processSidebarFilters($query, $filter, $order);
	}
}