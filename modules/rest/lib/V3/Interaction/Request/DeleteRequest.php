<?php

namespace Bitrix\Rest\V3\Interaction\Request;

use Bitrix\Rest\V3\Structures\Filtering\FilterStructure;

class DeleteRequest extends Request
{
	public ?int $id = null;

	public ?FilterStructure $filter = null;
}
