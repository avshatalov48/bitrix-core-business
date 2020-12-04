<?php


namespace Bitrix\Calendar\ICal\Builder;


use Bitrix\Calendar\ICal\Basic\Observance;

class DaylightObservances extends Observance implements BuilderComponent
{
	public function getType(): string
	{
		return 'DAYLIGHT';
	}
}