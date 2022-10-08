<?php

namespace Bitrix\Calendar\Sync\Office365\Dto;

class DateTimeDto extends Dto
{
	/** @var string like "dateTime": "2022-01-18T21:30:00.0000000"*/
	public $dateTime;

	/** @var string */
	public $timeZone;
}
