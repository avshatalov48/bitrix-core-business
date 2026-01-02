<?php

namespace Bitrix\Rest\V3\Interaction\Request;

use Bitrix\Rest\V3\Structures\Filtering\FilterStructure;
use Bitrix\Rest\V3\Structures\Ordering\OrderStructure;
use Bitrix\Rest\V3\Structures\SelectStructure;
use Bitrix\Rest\V3\Structures\PaginationStructure;

class ListRequest extends Request
{
	public ?SelectStructure $select = null;

	public ?FilterStructure $filter = null;

	public ?OrderStructure $order = null;

	public ?PaginationStructure $pagination = null;
}
