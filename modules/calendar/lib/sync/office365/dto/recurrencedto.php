<?php

namespace Bitrix\Calendar\Sync\Office365\Dto;

class RecurrenceDto extends Dto
{
	/** @var RecurrencePatternDto */
	public $pattern;

	/** @var RecurrenceRangeDto */
	public $range;

	/**
	 * @return array[]
	 */
	protected function getComplexPropertyMap(): array
	{
		return [
			'pattern' => [
				'class' => RecurrencePatternDto::class,
				'isMandatory' => true,
			],
			'range' => [
				'class' => RecurrenceRangeDto::class,
				'isMandatory' => true,
			],
		];
	}
}
