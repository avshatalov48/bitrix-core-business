<?php

namespace Bitrix\Rest\V3\Dto\Mapping;

use Bitrix\Rest\V3\Dto\Dto;
use Bitrix\Rest\V3\Dto\DtoCollection;

abstract class Mapper
{
	final public function mapOne(mixed $item, array $fields = []): Dto
	{
		$collection = $this->mapCollection([$item], $fields);

		return $collection->first();
	}

	abstract public function mapCollection(array $items, array $fields = []): DtoCollection;
}
