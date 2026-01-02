<?php

namespace Bitrix\Iblock\UI\Input;

use Bitrix\Iblock\Integration\UI\EntitySelector\IblockPropertyElementProvider;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type;
use Bitrix\Main\UI;
use Bitrix\Main\Web\Json;

class Element
{
	protected static bool $uiIncluded;

	public static function renderSelector(array $property, array|int|string|null $values, array $config): string
	{
		if (!isset(self::$uiIncluded))
		{
			self::$uiIncluded = Loader::includeModule('ui');
		}
		if (!self::$uiIncluded)
		{
			return '';
		}

		$rowId = trim((string)($config['ROW_ID'] ?? ''));
		$fieldName = trim((string)($config['FIELD_NAME'] ?? ''));
		if ($fieldName === '')
		{
			return '';
		}

		if (($property['PROPERTY_TYPE'] ?? '') !== PropertyTable::TYPE_ELEMENT)
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

		$config['ENTITY_ID'] = (string)($config['ENTITY_ID'] ?? IblockPropertyElementProvider::ENTITY_ID);

		$config['CHANGE_EVENTS'] ??= [];
		if (!is_array($config['CHANGE_EVENTS']))
		{
			$config['CHANGE_EVENTS'] = is_string($config['CHANGE_EVENTS']) ? [$config['CHANGE_EVENTS']] : [];
		}

		$entityValues = [];
		foreach ($values as $value)
		{
			$entityValues[] = [
				$config['ENTITY_ID'],
				$value,
			];
		}

		$entities = [
			[
				'id' => $config['ENTITY_ID'],
				'dynamicLoad' => true,
				'dynamicSearch' => true,
				'options'=> [
					'iblockId' => (int)($property['LINK_IBLOCK_ID'] ?? 0),
					'propertyType' => (string)($property['USER_TYPE'] ?? ''),
				],
			]
		];

		$config = Json::encode([
			'containerId' => $containerId,
			'fieldName' => $fieldName . ($multiple ? '[]' : ''),
			'multiple' => $multiple,
			'collectionType' => 'int',
			'selectedItems' => $entityValues,
			'entities' => $entities,
			'searchMessages' => [
				'title' => $config['SEARCH_TITLE'],
				'subtitle' => $config['SEARCH_SUBTITLE'],
			],
			'changeEvents' => $config['CHANGE_EVENTS'],
		]);

		UI\Extension::load('ui.field-selector');

		return <<<HTML
			<div id="$containerId"></div>
			<script>
			(function() {
				const selector = new BX.UI.FieldSelector({$config});
				selector.render();
			})();
			</script>
			HTML
		;
	}
}
