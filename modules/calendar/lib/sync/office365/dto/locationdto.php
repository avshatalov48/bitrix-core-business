<?php

namespace Bitrix\Calendar\Sync\Office365\Dto;

class LocationDto extends Dto
{
	/** @var string */
	public $displayName;

	/** @var string */
	public $locationType;

	/** @var string */
	public $uniqueIdType;

	/** @var string */
	public $uniqueId;

	/** @var object */
	public $address;

	/** @var object */
	public $coordinates;
}
