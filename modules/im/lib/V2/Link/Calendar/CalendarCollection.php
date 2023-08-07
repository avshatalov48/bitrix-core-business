<?php

namespace Bitrix\Im\V2\Link\Calendar;

use Bitrix\Im\Model\LinkCalendarIndexTable;
use Bitrix\Im\Model\LinkCalendarTable;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Common\SidebarFilterProcessorTrait;
use Bitrix\Im\V2\Link\BaseLinkCollection;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Service\Locator;
use Bitrix\Main\ORM\Query\Query;

/**
 * @implements \IteratorAggregate<int,CalendarItem>
 * @method CalendarItem offsetGet($key)
 */
class CalendarCollection extends BaseLinkCollection
{
	use SidebarFilterProcessorTrait;
	use ContextCustomer;

	public static function find(
		array $filter,
		array $order,
		?int $limit = null,
		?Context $context = null
	): self
	{
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

		return $links->fillCalendarData();
	}

	public function fillCalendarData(): self
	{
		$calendarIds = $this->getEntityIds();

		$entities = \Bitrix\Im\V2\Entity\Calendar\CalendarCollection::initByIds($calendarIds, $this->getContext());

		foreach ($this as $link)
		{
			if ($entities->getById($link->getEntityId()) !== null)
			{
				$link->setEntity($entities->getById($link->getEntityId()));
			}
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
	}

	public static function getCollectionElementClass(): string
	{
		return CalendarItem::class;
	}
}