<?php


namespace Bitrix\Calendar\ICal\Parser;


use Bitrix\Calendar\ICal\Basic\Content;
use Bitrix\Calendar\ICal\Basic\Observance;
use Bitrix\Main\Type\DateTime;

class StandardObservances extends Observance implements ParserComponent
{
	public function getType(): string
	{
		return 'STANDARD';
	}

	public function getContent()
	{
		// TODO: Implement getContent() method.
	}
}