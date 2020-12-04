<?php


namespace Bitrix\Calendar\ICal\Builder;


use Bitrix\Calendar\ICal\Basic\Observance;

class StandardObservances extends Observance implements BuilderComponent
{
	public function getType(): string
	{
		return 'STANDARD';
	}
}