<?php

namespace Bitrix\Iblock\Public\Service\RestValidator\Trait;

trait IblockFilterOperationTrait
{
	protected function prepareFilterField(string $field): array
	{
		return \CIBlock::MkOperationFilter($field);
	}
}
