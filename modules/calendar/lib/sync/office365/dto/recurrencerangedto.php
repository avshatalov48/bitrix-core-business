<?php

namespace Bitrix\Calendar\Sync\Office365\Dto;

class RecurrenceRangeDto extends Dto
{
	/** @var string */
	public $type;

	/** @var string "2014-11-03"*/
	public $startDate;

	/** @var string */
	public $endDate;

	/** @var string */
	public $recurrenceTimeZone;

	/** @var integer */
	public $numberOfOccurrences;

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
