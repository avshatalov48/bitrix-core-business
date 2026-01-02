<?php

namespace Bitrix\Rest\V3\Interaction\Request;

use Bitrix\Rest\V3\Structures\FieldsStructure;
use Bitrix\Rest\V3\Structures\Filtering\FilterStructure;

class UpdateRequest extends Request
{
	public ?int $id = null;

	public FieldsStructure $fields;

	public ?FilterStructure $filter = null;
}
