<?php

namespace Bitrix\Calendar\Sync\Converters;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Section\Section;

/** @deprecated  */
interface Converter
{
	public function convertEvent(): Event;

	public function convertSection(): Section;

}
