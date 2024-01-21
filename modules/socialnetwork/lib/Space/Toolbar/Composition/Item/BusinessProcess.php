<?php

namespace Bitrix\Socialnetwork\Space\Toolbar\Composition\Item;

use Bitrix\Socialnetwork\Space\Toolbar\Composition\AbstractCompositionItem;

class BusinessProcess extends AbstractCompositionItem
{
	protected string $moduleId = 'bizproc';

	public function getBoundItem(): ?AbstractCompositionItem
	{
		return new ListElement();
	}
}