<?php

namespace Bitrix\Rest\V3\Interaction\Response;

use Bitrix\Rest\V3\Dto\DtoCollection;

class ListResponse extends ResponseWithRelations
{
	public array $items;

	public function __construct(DtoCollection $items)
	{
		$this->items = $items->toArray();
	}
}
