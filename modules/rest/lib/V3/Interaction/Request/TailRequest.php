<?php

namespace Bitrix\Rest\V3\Interaction\Request;

use Bitrix\Rest\V3\Structures\CursorStructure;
use Bitrix\Rest\V3\Structures\Filtering\FilterStructure;
use Bitrix\Rest\V3\Structures\SelectStructure;

class TailRequest extends Request
{
	public ?SelectStructure $select = null;

	public ?FilterStructure $filter = null;

	public ?CursorStructure $cursor = null;
}
