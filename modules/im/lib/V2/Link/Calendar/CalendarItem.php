<?php

namespace Bitrix\Im\V2\Link\Calendar;

use Bitrix\Im\Model\LinkCalendarIndexTable;
use Bitrix\Im\Model\LinkCalendarTable;
use Bitrix\Im\Model\EO_LinkCalendar;
use Bitrix\Im\V2\Entity;
use Bitrix\Im\V2\Link\BaseLinkItem;
use Bitrix\Im\V2\Rest\RestEntity;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Type\DateTime;

/**
 * @method Entity\Calendar\CalendarItem getEntity()
 */
class CalendarItem extends BaseLinkItem
{
	protected string $title;
	protected DateTime $dateFrom;
	protected DateTime $dateTo;

	/**
	 * @param int|array|EO_LinkCalendar|null $source
	 */
	public function __construct($source = null)
	{
		$this->initByDefault();

		if (!empty($source))
		{
			$this->load($source);
		}
	}

	/**
	 * @param RestEntity|Entity\Calendar\CalendarItem $entity
	 * @return static
	 */
	public function setEntity(RestEntity $entity): self
	{
		$this->setTitle($entity->getTitle())->setDateTo($entity->getDateTo())->setDateFrom($entity->getDateFrom());

		return parent::setEntity($entity);
	}

	public static function getByCalendarId(int $id, bool $fillEntity = true): ?self
	{
		$ormObject = LinkCalendarTable::query()
			->setSelect(['*'])
			->where('CALENDAR_ID', $id)
			->setLimit(1)
			->fetchObject()
		;

		if ($ormObject === null)
		{
			return null;
		}

		if ($fillEntity)
		{
			return (new static($ormObject))->setEntity(Entity\Calendar\CalendarItem::initById($id));
		}

		return new static($ormObject);
	}

	public function save(): Result
	{
		$result = parent::save();
		if ($result->isSuccess())
		{
			LinkCalendarIndexTable::indexInBackground([$this->getId()]);
		}

		return $result;
	}

	public function delete(): Result
	{
		LinkCalendarIndexTable::delete($this->getPrimaryId());

		return parent::delete();
	}

	public static function getDataClass(): string
	{
		return LinkCalendarTable::class;
	}

	protected static function getEntityIdFieldName(): string
	{
		return 'CALENDAR_ID';
	}

	public static function getEntityClassName(): string
	{
		return Entity\Calendar\CalendarItem::class;
	}

	public static function getRestEntityName(): string
	{
		return 'link';
	}

	protected static function mirrorDataEntityFields(): array
	{
		$additionalFields = [
			'CALENDAR_TITLE' => [
				'field' => 'title',
				'set' => 'setTitle', /** @see CalendarItem::setType */
				'get' => 'getTitle', /** @see CalendarItem::getType */
			],
			'CALENDAR_DATE_FROM' => [
				'field' => 'dateFrom',
				'set' => 'setDateFrom', /** @see CalendarItem::setDateFrom */
				'get' => 'getDateFrom', /** @see CalendarItem::getDateFrom */
			],
			'CALENDAR_DATE_TO' => [
				'field' => 'dateTo',
				'set' => 'setDateTo', /** @see CalendarItem::setDateTo */
				'get' => 'getDateTo', /** @see CalendarItem::getDateTo */
			]
		];

		return array_merge(parent::mirrorDataEntityFields(), $additionalFields);
	}

	//region Setters & getters

	public function getTitle(): string
	{
		return $this->title;
	}

	public function setTitle(string $title): CalendarItem
	{
		$this->title = $title;
		return $this;
	}

	public function getDateFrom(): DateTime
	{
		return $this->dateFrom;
	}

	public function setDateFrom(DateTime $dateFrom): CalendarItem
	{
		$this->dateFrom = $dateFrom;
		return $this;
	}

	public function getDateTo(): DateTime
	{
		return $this->dateTo;
	}

	public function setDateTo(DateTime $dateTo): CalendarItem
	{
		$this->dateTo = $dateTo;
		return $this;
	}

	//endregion
}