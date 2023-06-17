<?php

namespace Bitrix\Im\V2\Link;

use Bitrix\Im\V2\Link;
use Bitrix\Im\V2\Rest\PopupDataAggregatable;
use Bitrix\Im\V2\Rest\RestConvertible;

interface LinkRestConvertible extends Link, RestConvertible, PopupDataAggregatable
{
	/**
	 * Returns an array containing the id of the link and the id of the linked entity, or an array of such arrays
	 * @return array
	 */
	public function toRestFormatIdOnly(): array;
}