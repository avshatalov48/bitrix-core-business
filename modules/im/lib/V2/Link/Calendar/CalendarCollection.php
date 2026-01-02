<?php

namespace Bitrix\Im\V2\Link\Calendar;

use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Im\Model\LinkCalendarTable;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Common\SidebarFilterProcessorTrait;
use Bitrix\Im\V2\Entity\EntityCollection;
use Bitrix\Im\V2\Link\BaseLinkCollection;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Service\Locator;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;

/**
 * @implements \IteratorAggregate<int,CalendarItem>
 * @method CalendarItem offsetGet($key)
 */
class CalendarCollection extends BaseLinkCollection
{
	use SidebarFilterProcessorTrait;
	use ContextCustomer;

	protected ?EntityCollection $entities = null;

	public static function find(
		array $filter,
		array $order,
		?int $limit = null,
		?Context $context = null
	): self
	{
		if (!Loader::includeModule('calendar'))
		{
			return new static();
		}

		$context = $context ?? Locator::getContext();

		$calendarOrder = ['ID' => 'DESC'];

		if (isset($order['ID']))
		{
			$calendarOrder['ID'] = $order['ID'];
		}

		$query = LinkCalendarTable::query();
		$query
			->setSelect(['*'])
			->setOrder($calendarOrder)
		;
		if (isset($limit))
		{
			$query->setLimit($limit);
		}
		static::processFilters($query, $filter, $calendarOrder);

		$links = new static($query->fetchCollection());
		$links->setContext($context);

		return $links->fillEntities();
	}

	public function getEntities(): EntityCollection
	{
		if (isset($this->entities))
		{
			return $this->entities;
		}
		$this->entities = \Bitrix\Im\V2\Entity\Calendar\CalendarCollection::getByIds($this->getEntityIds(), $this->getContext());

		return $this->entities;
	}

	public function fillEntities(): self
	{
		$entities = $this->getEntities();

		foreach ($this as $link)
		{
			$id = $link->getEntityId();

			if (!isset($id) || ($entities->getById($id) === null))
			{
				continue;
			}

			$link->setEntity($entities->getById($id));
		}

		return $this;
	}

	public function save(bool $isGroupSave = false): Result
	{
		return parent::save(false);
	}

	protected static function processFilters(Query $query, array $filter, array $order): void
	{
		static::processSidebarFilters($query, $filter, $order);

		if (isset($filter['CALENDAR_DATE_FROM']))
		{
			$query->where('CALENDAR_DATE_FROM', '>=', $filter['CALENDAR_DATE_FROM']);
		}

		if (isset($filter['CALENDAR_DATE_TO']))
		{
			$query->where('CALENDAR_DATE_TO', '<=', $filter['CALENDAR_DATE_TO']);
		}

		if (isset($filter['SEARCH_TITLE']))
		{
			$query->withSearchByTitle($filter['SEARCH_TITLE']);
		}

		// temporary
		$query->registerRuntimeField(
			new Reference(
				'CALENDAR',
				EventTable::class,
				Join::on('this.CALENDAR_ID', 'ref.ID'),
				['join_type' => Join::TYPE_INNER]
			)
		);
	}

	public static function getCollectionElementClass(): string
	{
		return CalendarItem::class;
	}

	public static function getByMessages(MessageCollection $messages): self
	{
		$calendarCollection = LinkCalendarTable::query()
			->setSelect(['*'])
			->whereIn('MESSAGE_ID', $messages->getIds())
			->fetchCollection()
		;

		return new static($calendarCollection);
	}
}