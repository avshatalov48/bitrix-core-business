<?php

namespace Bitrix\Iblock\UI\Input;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type;
use Bitrix\Main\UI;
use Bitrix\Iblock;

class Element
{
	public static function renderSelector(array $property, array|int|null $values, array $config): string
	{
		$rowId = trim((string)($config['ROW_ID'] ?? ''));
		$fieldName = trim((string)($config['FIELD_NAME'] ?? ''));
		if ($fieldName === '')
		{
			return '';
		}

		if (($property['PROPERTY_TYPE'] ?? '') !== Iblock\PropertyTable::TYPE_ELEMENT)
		{
			return '';
		}

		$containerId =
			$rowId . ($rowId !== '' ? '_' : '')
			. $fieldName . '_container'
		;

		if (!is_array($values))
		{
			$values = !empty($values) ? [$values] : [];
		}
		Type\Collection::normalizeArrayValuesByInt($values, false);

		$multiple = ($property['MULTIPLE'] ?? 'N') === 'Y';

		$config['SEARCH_TITLE'] = (string)($config['SEARCH_TITLE'] ?? '');
		if ($config['SEARCH_TITLE'] === '')
		{
			$config['SEARCH_TITLE'] = Loc::getMessage('IBLOCK_UI_INPUT_ELEMENT_SELECTOR_SEARCH_TITLE');
		}
		$config['SEARCH_SUBTITLE'] = (string)($config['SEARCH_SUBTITLE'] ?? '');
		if ($config['SEARCH_SUBTITLE'] === '')
		{
			$config['SEARCH_SUBTITLE'] = Loc::getMessage('IBLOCK_UI_INPUT_ELEMENT_SELECTOR_SEARCH_SUBTITLE');
		}
		// TODO: replace entityId value to constant
		$config['ENTITY_ID'] = (string)($config['ENTITY_ID'] ?? 'iblock-element');

		$config = \CUtil::PhpToJSObject(
			[
				'containerId' => $containerId,
				'fieldName' => $fieldName . ($multiple ? '[]' : ''),
				'multiple' => $multiple,
				'selectedItems' => $values,
				'iblockId' => (int)($property['LINK_IBLOCK_ID'] ?? 0),
				'userType' => (string)($property['USER_TYPE'] ?? ''),
				'entityId' => $config['ENTITY_ID'],
				'searchMessages' => [
					'title' => $config['SEARCH_TITLE'],
					'subtitle' => $config['SEARCH_SUBTITLE'],
				],
			],
			false,
			true,
			true
		);

		UI\Extension::load('iblock.field-selector');

		return <<<HTML
			<div id="$containerId"></div>
			<script>
			(function() {
				const selector = new BX.Iblock.FieldSelector({$config});
				selector.render();
			})();
			</script>
HTML;
	}
}