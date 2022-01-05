<?php

namespace Bitrix\Socialnetwork\Component\LogList;

class FilterHandler
{
	private $filterItems = null;

	public function __construct($params)
	{
		$this->filterItems = $params['filterItems'];
	}

	public function OnBeforeSonetLogFilterFill(&$pageParamsToClear, &$itemsTop, &$items): void
	{
		$items = $this->filterItems;
	}
}
