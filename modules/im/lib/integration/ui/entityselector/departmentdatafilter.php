<?php
namespace Bitrix\Im\Integration\UI\EntitySelector;

use Bitrix\UI\EntitySelector\BaseFilter;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;

class DepartmentDataFilter extends BaseFilter
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
		foreach ($items as $item)
		{
			if (!($item instanceof Item))
			{
				continue;
			}

			$itemColor = \Bitrix\Im\Color::getColorByNumber($item->getId());
			$item->setAvatarOptions(['color' => $itemColor]);
		}
	}
}
