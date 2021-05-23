<?php


namespace Bitrix\Calendar\ICal\Basic;


class AttachProperty
{
	public $link;
	public $name = '';

	public function __construct(string $link, string $name = '')
	{
		$this->link = $link;
		$this->name = $name;
	}
}