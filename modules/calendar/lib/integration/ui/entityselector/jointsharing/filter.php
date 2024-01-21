<?php

namespace Bitrix\Calendar\Integration\UI\EntitySelector\JointSharing;

use Bitrix\UI\EntitySelector\BaseFilter;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;


class Filter extends BaseFilter
{
	public function __construct()
	{
		parent::__construct();
	}

	public function isAvailable(): bool
	{
		return $GLOBALS['USER']->isAuthorized();
	}

	public function apply(array $items, Dialog $dialog): void
	{
		$currentUserId = \CCalendar::GetCurUserId();

		foreach ($items as $item)
		{
			if (!($item instanceof Item))
			{
				continue;
			}

			if ($item->getId() === $currentUserId)
			{
				$item->setDeselectable(false);
			}
		}
	}
}