<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\v2\Property\Property;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Grid\Editor\Types;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

class GridVariationForm extends VariationForm
{
	/** @var \Bitrix\Catalog\v2\Sku\BaseSku */
	protected $entity;

	protected $headers = [];

	public static function formatFieldName($name): string
	{
		return BaseForm::GRID_FIELD_PREFIX.parent::formatFieldName($name);
	}

	public static function preparePropertyName(string $name = ''): string
	{
		$name = parent::preparePropertyName($name);

		return static::formatFieldName($name);
	}

	protected function prepareFieldName(string $name): string
	{
		$name = parent::prepareFieldName($name);

		return static::formatFieldName($name);
	}

	protected function getPropertyDescription(Property $property): array
	{
		$description = parent::getPropertyDescription($property);

		if ($description['editable'])
		{
			switch ($description['type'])
			{
				case 'multilist':
				case 'list':
					$dropdownItems = [];
					if (!empty($description['data']['items']) && is_array($description['data']['items']))
					{
						foreach ($description['data']['items'] as $item)
						{
							$dropdownItems[$item['VALUE']] = $item['NAME'];
						}
					}
					$description['editable'] = [
						'TYPE' => Types::DROPDOWN,
						'items' => $dropdownItems,
					];
					break;
				case 'custom':
					$description['editable'] = [
						'TYPE' => Types::CUSTOM,
						'NAME' => $description['data']['edit'] ?? $description['name'],
						// 'HTML' => $description['data']['edit'] ?? $description['name'],
					];
					break;
				case 'boolean':
					$description['editable'] = ['TYPE' => Types::CHECKBOX];
					break;
				case 'date':
					$description['editable'] = ['TYPE' => Types::DATE];
					break;
				default:
					$description['editable'] = ['TYPE' => Types::TEXT];
			}
		}

		return $description;
	}

	public function getColumnValues(): array
	{
		$values = parent::getValues();

		foreach ($this->getGridHeaders() as $description)
		{
			$name = $description['id'];
			$currentValue = $values[$name] ?? '';

			switch ($description['type'])
			{
				case 'string':
				case 'text':
					if (!empty($values[$name]))
					{
						$values[$name] = HtmlFilter::encode($values[$name]);
					}
					break;
				case 'number':
					$values[$name] = (float)($values[$name] ?: 0);
					break;
				case 'multilist':
					if (is_array($currentValue))
					{
						$formatted = [];

						foreach ($currentValue as $multipleItemValue)
						{
							$formatted[] = HtmlFilter::encode($description['editable']['items'][$multipleItemValue]);
						}

						$values[$name] = $formatted;
					}
					break;
				case 'boolean':
					$code = ($currentValue === 'Y') ? 'YES' : 'NO';
					$values[$name] = Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_VALUE_'.$code);
					break;
				case 'list':
					$values[$name] = HtmlFilter::encode($description['editable']['items'][$currentValue]);
					break;
				case 'custom':
					$values[$name] = $values[$description['data']['view']];
					break;
			}

			if (is_array($values[$name]))
			{
				$values[$name] = implode(', ', $values[$name]);
			}
		}

		return $values;
	}

	public function getGridHeaders(): array
	{
		if (!empty($this->headers))
		{
			return $this->headers;
		}

		$defaultWidth = 130;

		$headers = [
			[
				'id' => static::formatFieldName('NAME'),
				'name' => Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_HEADER_NAME'),
				'sort' => false,
				'type' => 'string',
				'editable' => [
					'TYPE' => Types::TEXT,
					'PLACEHOLDER' => Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_NEW_PRODUCT_PLACEHOLDER'),
				],
				'width' => $defaultWidth,
				'default' => false,
			],
		];

		foreach ($this->getIblockPropertiesDescriptions() as $property)
		{
			$isDirectory = $property['settings']['PROPERTY_TYPE'] === PropertyTable::TYPE_STRING
				&& $property['settings']['USER_TYPE'] === 'directory';
			$headers[] = [
				'id' => $property['name'],
				'name' => $property['title'],
				'type' => $property['type'],
				'sort' => false,
				'default' => $property['index'] === self::MORE_PHOTO,
				'data' => $property['data'],
				'width' => $isDirectory ? 160 : null,
				'editable' => $property['editable'],
			];
		}

		$currencyList = CurrencyManager::getCurrencyList();
		$purchasingPriceName = static::formatFieldName('PURCHASING_PRICE_FIELD');
		$headers[] = [
			'id' => $purchasingPriceName,
			'name' => Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_HEADER_PURCHASING_PRICE'),
			'sort' => false,
			'type' => 'money',
			'editable' => [
				'TYPE' => Types::MONEY,
				'CURRENCY_LIST' => $currencyList,
			],
			'width' => $defaultWidth,
			'default' => false,
		];

		$priceTypeList = \CCatalogGroup::GetListArray();

		if (!empty($priceTypeList))
		{
			foreach ($priceTypeList as $priceType)
			{
				$columnName = htmlspecialcharsbx(!empty($priceType['NAME_LANG']) ? $priceType['NAME_LANG'] : $priceType['NAME']);

				if ($priceType['BASE'] === 'Y')
				{
					$basePriceName = static::formatFieldName(BaseForm::PRICE_FIELD_PREFIX.'BASE_FIELD');
					$headers[] = [
						'id' => $basePriceName,
						'name' => Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_BASE_PRICE_VALUE', [
							'#PRICE_NAME#' => $columnName,
						]),
						'sort' => false, // 'SCALED_PRICE_'.$priceType['ID'],
						'type' => 'money',
						'editable' => [
							'TYPE' => Types::MONEY,
							'CURRENCY_LIST' => $currencyList,
						],
						'base' => true,
						'width' => $defaultWidth,
						'default' => true,
					];
				}
				$priceName = static::formatFieldName(BaseForm::PRICE_FIELD_PREFIX.$priceType['ID'].'_FIELD');
				$headers[] = [
					'id' => $priceName,
					'name' => $columnName,
					'sort' => false,
					'type' => 'money',
					'editable' => [
						'TYPE' => Types::MONEY,
						'CURRENCY_LIST' => $currencyList,
					],
					'width' => $defaultWidth,
					'default' => false,
				];
			}
		}

		$fields = [
			'ACTIVE', 'QUANTITY', 'MEASURE', 'MEASURE_RATIO', 'AVAILABLE',
			'VAT_ID', 'VAT_INCLUDED', 'QUANTITY_RESERVED',
			'QUANTITY_TRACE', 'CAN_BUY_ZERO', // 'SUBSCRIBE',
			'WEIGHT', 'WIDTH', 'LENGTH', 'HEIGHT',
			'SHOW_COUNTER', 'CODE', 'TIMESTAMP_X', 'USER_NAME',
			'DATE_CREATE', 'EXTERNAL_ID', 'BAR_CODE',
			// 'TAGS', 'DISCOUNT', 'STORE', 'PRICE_TYPE',
		];

		$defaultFields = [
			'QUANTITY', 'MEASURE', 'NAME',
		];

		$immutableFields = [
			'TIMESTAMP_X', 'USER_NAME', 'DATE_CREATE', 'CREATED_USER_NAME',
		];

		foreach ($fields as $code)
		{
			$type = 'string';
			$editable = false;
			if (!in_array($code, $immutableFields, true))
			{
				$editable = [
					'TYPE' => Types::TEXT,
				];
			}

			switch ($code)
			{
				case 'ACTIVE':
				case 'VAT_INCLUDED':
				case 'AVAILABLE':
					$type = 'boolean';
					$editable = [
						'TYPE' => Types::CHECKBOX,
					];
					break;

				case 'VAT_ID':
					$vatList = [];
					$type = 'list';
					$vatRaws = \Bitrix\Catalog\VatTable::getList([
						'select' => ['ID', 'NAME'],
						'filter' => ['=ACTIVE' => 'Y'],
					]);
					foreach ($vatRaws as $vat)
					{
						$vatList[$vat['ID']] = htmlspecialcharsbx($vat['NAME']);
					}
					$editable = [
						'TYPE' => Types::DROPDOWN,
						'items' => $vatList,
					];
					break;

				case 'MEASURE':
					$measureList = [];
					$type = 'list';
					$measureRaws = \Bitrix\Catalog\MeasureTable::getList([
						'select' => ['ID', 'CODE', 'MEASURE_TITLE'],
					]);

					foreach ($measureRaws as $measure)
					{
						$measureList[$measure['ID']] = htmlspecialcharsbx($measure['MEASURE_TITLE']);
						if (empty($measureList[$measure['ID']]))
						{
							$measureList[$measure['ID']] = \CCatalogMeasureClassifier::getMeasureTitle(
								$measure["CODE"],
								'MEASURE_TITLE'
							);
						}
					}
					$editable = [
						'TYPE' => Types::DROPDOWN,
						'items' => $measureList,
					];
					break;

				case 'QUANTITY':
					$type = 'number';
					if (State::isUsedInventoryManagement())
					{
						$editable = false;
					}
					else
					{
						$editable = [
							'TYPE' => Types::NUMBER,
							'PLACEHOLDER' => 0,
						];
					}
					break;

				case 'MEASURE_RATIO':
					$type = 'number';
					$editable = [
						'TYPE' => Types::NUMBER,
						'PLACEHOLDER' => 0,
					];
					break;

				case 'QUANTITY_TRACE':
				case 'CAN_BUY_ZERO':
				case 'SUBSCRIBE':
					$type = 'list';

					$items = [];
					foreach ($this->getCatalogEnumFields($code) as $field)
					{
						$items[$field['VALUE']] = $field['NAME'];
					}

					$editable = [
						'TYPE' => Types::DROPDOWN,
						'items' => $items,
					];
					break;
			}

			$headers[] = [
				'id' => static::formatFieldName($code),
				'name' => Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_HEADER_'.$code),
				'sort' => false,
				'type' => $type,
				'editable' => $editable,
				'width' => $defaultWidth,
				'default' => in_array($code, $defaultFields, true),
			];
		}

		$this->headers = $headers;

		return $this->headers;
	}

	public function getValues(): array
	{
		$values = parent::getValues();

		foreach ($this->getDescriptions() as $description)
		{
			$name = $description['name'];
			$currentValue = $values[$name] ?? '';

			switch ($description['type'])
			{
				case 'multilist':
					if (is_array($currentValue))
					{
						$formatted = [];

						foreach ($currentValue as $multipleItemValue)
						{
							$formatted[] = $description['editable']['items'][$multipleItemValue];
						}

						$values[$name] = implode(', ', $formatted);
					}
					break;
				case 'custom':
					$values[$name] = $values[$description['data']['view']];
					break;
				case 'money':
					$descriptionData = $description['data'];
					$values[$name] = [
						'PRICE' => [
							'NAME' => $descriptionData['amount'],
							'VALUE' => $values[$descriptionData['amount']],
						],
						'CURRENCY' => [
							'NAME' => $descriptionData['currency']['name'],
							'VALUE' => $values[$descriptionData['currency']['name']],
						],
					];
					break;
				default:
					if (is_array($values[$name]))
					{
						$values[$name] = implode(', ', $values[$name]);
					}
			}
		}

		return $values;
	}

	protected function getAdditionalValues(array $values): array
	{
		$additionalValues = parent::getAdditionalValues($values);

		$numberFields = ['MEASURE_RATIO', 'QUANTITY'];
		foreach ($numberFields as $fieldName)
		{
			$fieldName = self::formatFieldName($fieldName);
			if ($values[$fieldName] == 0)
			{
				$additionalValues[$fieldName] = null;
			}
		}

		return $additionalValues;
	}

	protected function getPropertySettings(Property $property): array
	{
		$settings = parent::getPropertySettings($property);
		$settings['GRID_MODE'] = true;

		return $settings;
	}

	protected function getFilePropertyViewHtml($value): string
	{
		$fileCount = 0;

		// single scalar property
		if (!empty($value) && !is_array($value))
		{
			$value = [$value];
		}

		if (is_array($value))
		{
			$fileCount = min(count($value), 3);
			$value = reset($value);
		}

		$imageSrc = '';

		if (!empty($value))
		{
			$image = \CFile::GetFileArray($value);
			if ($image)
			{
				$imageSrc = $image['SRC'];
			}
		}

		switch ($fileCount)
		{
			case 3:
				$multipleClass = ' ui-image-input-img-block-multiple';
				break;

			case 2:
				$multipleClass = ' ui-image-input-img-block-double';
				break;

			case 0:
				$multipleClass = ' ui-image-input-img-block-empty';
				break;

			case 1:
			default:
				$multipleClass = '';
				break;
		}

		if ($imageSrc)
		{
			$imageSrc = " src=\"{$imageSrc}\"";
		}

		return <<<HTML
<div class="ui-image-input-img-block{$multipleClass}">
	<div class="ui-image-input-img-block-inner">
		<div class="ui-image-input-img-item">
			<img class="ui-image-input-img"{$imageSrc}>
		</div>
	</div>
</div>
HTML;
	}

	protected function getFilePropertyInputName(array $property): string
	{
		$inputName = $property['name'] ?? '';

		if (isset($property['settings']['MULTIPLE']) && $property['settings']['MULTIPLE'] === 'Y')
		{
			$inputName .= '_n#IND#';
		}

		return $inputName;
	}

	protected function getFieldValue(array $field)
	{
		if ($field['entity'] === 'money')
		{
			return $this->getMoneyFieldValue($field);
		}

		return parent::getFieldValue($field);
	}

	protected function getMoneyFieldValue(array $field)
	{
		if ($field['priceTypeId'] === 'PURCHASING_PRICE')
		{
			$price = $this->entity->getField('PURCHASING_PRICE') ?? 0;
			$currency = $this->entity->getField('PURCHASING_CURRENCY');
		}
		else
		{
			$priceItem = $this->entity
				->getPriceCollection()
				->findByGroupId($field['priceTypeId'])
			;
			$price = $priceItem ? $priceItem->getPrice() : 0;
			$currency = $priceItem ? $priceItem->getCurrency() : null;
		}

		$currency = $currency ?? CurrencyManager::getBaseCurrency();

		return \CCurrencyLang::CurrencyFormat($price, $currency, true);
	}
}