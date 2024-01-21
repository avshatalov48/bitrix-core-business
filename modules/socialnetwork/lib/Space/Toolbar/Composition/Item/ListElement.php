<?php

namespace Bitrix\Socialnetwork\Space\Toolbar\Composition\Item;

use Bitrix\Socialnetwork\Space\Toolbar\Composition\AbstractCompositionItem;

class ListElement extends AbstractCompositionItem
{
	protected string $moduleId = 'lists';

	public function isHidden(): bool
	{
		return true;
	}
}