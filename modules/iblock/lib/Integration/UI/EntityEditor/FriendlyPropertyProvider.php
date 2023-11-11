<?php

namespace Bitrix\Iblock\Integration\UI\EntityEditor;

use Bitrix\Main\Localization\Loc;

final class FriendlyPropertyProvider extends PropertyProvider
{
	public const FEATURE_PUBLIC_PROPERTY = 'IS_PUBLIC';

	public function getEntityFields(): array
	{
		$fields = parent::getEntityFields();
		$fields[] = [
			'name' => self::FEATURE_PUBLIC_PROPERTY,
			'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_FRIENDLY_PROPERTY_FEATURE_PUBLIC'),
			'type' => 'boolean',
			'default_value' => 'Y',
		];
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
