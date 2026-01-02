<?php

namespace Bitrix\Rest\V3\Interaction\Response;

use Bitrix\Rest\V3\Dto\Dto;

class GetResponse extends ResponseWithRelations
{
	public array $item;

	/**
	 * @param Dto $item
	 */
	public function __construct(Dto $item)
	{
		$this->item = $item->toArray();
	}
}
