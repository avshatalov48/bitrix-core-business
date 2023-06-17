<?php

namespace Bitrix\Im\V2\Rest;

interface PopupDataItem extends RestConvertible
{
	/**
	 * @param static $item
	 * @return $this
	 */
	public function merge(self $item): self;
}