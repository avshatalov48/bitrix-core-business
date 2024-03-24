<?php

namespace Bitrix\Socialnetwork\Space\Toolbar\Composition\Item;

use Bitrix\Socialnetwork\Space\Toolbar\Composition\AbstractCompositionItem;

class Crm extends AbstractCompositionItem
{
	protected string $moduleId = 'crm_shared';

	public function isHidden(): bool
	{
		return true;
	}
}