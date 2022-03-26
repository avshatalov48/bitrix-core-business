<?php

namespace Bitrix\Mail\Collection;

use Bitrix\Mail\Item\Base as Item;

class MessageAccess extends Immutable
{
	public function createItem(array $array)
	{
		return \Bitrix\Mail\Item\MessageAccess::fromArray($array);
	}

	public function ensureItem(Item $item, $throwException = true)
	{
		if ($item instanceof \Bitrix\Mail\Item\MessageAccess) {
			return true;
		}
		return parent::ensureItem($item, $throwException);
	}
}