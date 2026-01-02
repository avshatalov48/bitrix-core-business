<?php

namespace Bitrix\Iblock\Public\Service\RestValidator\Trait;

trait OrmFilterOperationTrait
{
	protected function prepareFilterField(string $field): array
	{
		$parser = new \CSQLWhere();

		return $parser->MakeOperation($field);
	}
}
