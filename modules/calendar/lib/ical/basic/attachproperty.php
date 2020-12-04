<?php


namespace Bitrix\Calendar\ICal\Basic;


class AttachProperty
{

	public $url;
	public $name = null;

	public function __construct(array $attach)
	{
		$this->url = $attach['link'];
		$this->name = $attach['name'];
	}
}