<?php

namespace Bitrix\Iblock\Integration\UI\EntityEditor;

final class FrendlyPropertyProvider extends PropertyProvider
{
	public function getEntityFields(): array
	{
		$fields = parent::getEntityFields();
		$fields = $this->removeDifficultFields($fields);

		return $fields;
	}

	public function getAdditionalFields(): array
	{
		$fields = parent::getAdditionalFields();
		$fields = $this->removeDifficultFields($fields);

		return $fields;
	}

	private function removeDifficultFields(array $fields): array
	{
		$names = array_fill_keys([
			'FILTERABLE',
			'WITH_DESCRIPTION',
			'COL_COUNT',
			'ROW_COUNT',
			'MULTIPLE_CNT',
			'SECTION_PROPERTY',
			'FEATURES[iblock:LIST_PAGE_SHOW]',
			'FEATURES[iblock:DETAIL_PAGE_SHOW]',
		], true);

		return array_filter($fields, static fn(array $field) => !isset($names[$field['name']]));
	}
}
