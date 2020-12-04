<?php


namespace Bitrix\Calendar\ICal\Builder;


use Bitrix\Calendar\ICal\Basic\Content;

interface BuilderComponent
{
	public function setContent(): Content;
}