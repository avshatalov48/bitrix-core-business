<?php

namespace Bitrix\Im\V2\Link\Favorite;

use Bitrix\Im\Model\EO_LinkFavorite_Collection;
use Bitrix\Im\Model\LinkFavoriteTable;
use Bitrix\Im\V2\Common\SidebarFilterProcessorTrait;
use Bitrix\Im\V2\Link\BaseLinkCollection;
use Bitrix\Im\V2\Link\Reminder\ReminderPopupItem;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Entity;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Service\Locator;
use Bitrix\Main\ORM\Query\Query;

/**
 * @implements \IteratorAggregate<int,FavoriteItem>
 * @method FavoriteItem offsetGet($key)
 */
class FavoriteCollection extends BaseLinkCollection
{
	use SidebarFilterProcessorTrait;

	public static function getCollectionElementClass(): string
	{
		return FavoriteItem::class;
	}

	public static function find(
		array $filter = [],
		array $order = ['ID' => 'DESC'],
		?int $limit = null,
		?Context $context = null
	): self
	{
		$context = $context ?? Locator::getContext();

		$messageOrder = ['ID' => 'DESC'];

		if (isset($order['ID']))
		{
			$messageOrder['ID'] = $order['ID'];
		}

		$query = LinkFavoriteTable::query()
			->setSelect(['MESSAGE', 'ID', 'CHAT_ID', 'AUTHOR_ID', 'DATE_CREATE', 'MESSAGE_ID'])
			->where('AUTHOR_ID', $context->getUserId())
			->setOrder($messageOrder)
		;
		if (isset($limit))
		{
			$query->setLimit($limit);
		}
		static::processFilters($query, $filter, $messageOrder);

		return static::initByEntityCollection($query->fetchCollection());
	}

	public static function initByEntityCollection(EO_LinkFavorite_Collection $entityCollection): self
	{
		$favoriteMessageCollection = new static();

		foreach ($entityCollection as $entity)
		{
			$favoriteMessageCollection[] = FavoriteItem::initByEntity($entity);
		}

		return $favoriteMessageCollection;
	}

	public static function getByMessage(Message $message): self
	{
		$entities = LinkFavoriteTable::query()
			->setSelect(['ID', 'CHAT_ID', 'AUTHOR_ID', 'DATE_CREATE', 'MESSAGE_ID'])
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

	public function getMessageCollection(): MessageCollection
	{
		$messageCollection = new MessageCollection();

		foreach ($this as $favoriteMessage)
		{
			$messageCollection->add($favoriteMessage->getEntity());
		}

		return $messageCollection;
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		$messages = $this->getMessageCollection()->fillAllForRest();
		$data = new PopupData([
			new Entity\User\UserPopupItem(),
			new Entity\File\FilePopupItem(),
			new ReminderPopupItem($messages->getReminders())
		], $excludedList);
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
	}
}