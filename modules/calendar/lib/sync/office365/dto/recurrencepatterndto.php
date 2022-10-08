<?php

namespace Bitrix\Calendar\Sync\Office365\Dto;

class RecurrencePatternDto extends Dto
{
	/** @var string */
	public $type;

	/** @var integer */
	public $interval;

	/** @var integer */
	public $month;

	/** @var integer */
	public $dayOfMonth;

	/** @var array */
	public $daysOfWeek;

	/** @var string */
	public $firstDayOfWeek;

	/** @var string */
	public $index;

	/**
	 * @param $value
	 * @param bool $filterEmptyValue
	 * @return array|bool|float|int|string|void
	 */
	protected function prepareValue($value, bool $filterEmptyValue)
	{
		return parent::prepareValue($value, true);
	}
}
