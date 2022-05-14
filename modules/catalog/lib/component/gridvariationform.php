<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\v2\Barcode\Barcode;
use Bitrix\Catalog\v2\Property\Property;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Grid\Editor\Types;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Currency\Integration\IblockMoneyProperty;

class GridVariationForm extends VariationForm
{
	/** @var \Bitrix\Catalog\v2\Sku\BaseSku */
	protected $entity;

	protected static $usedHeaders;
	protected static $headers;

	protected function prepareFieldName(string $name): string
	{
		$name = parent::prepareFieldName($name);

		return static::formatFieldName($name);
	}

	public static function formatFieldName($name): string
	{
		return BaseForm::GRID_FIELD_PREFIX.parent::formatFieldName($name);
	}

	public static function preparePropertyName(string $name = ''): string
	{
		$name = parent::preparePropertyName($name);

		return static::formatFieldName($name);
	}

	protected function buildDescriptions(): array
	{
		return array_merge(
			parent::buildDescriptions(),
			$this->getCommonQuantityDescription(),
			$this->getBarcodeDescription()
		);
	}

	protected function getPropertyDescription(Property $property): array
	{
		$description = parent::getPropertyDescription($property);

		if ($description['editable'])
		{
			switch ($description['type'])
			{
				case 'multilist':
					$dropdownItems = [];

					if (!empty($description['data']['items']) && is_array($description['data']['items']))
					{
						$dropdownItems = $description['data']['items'];
					}

					$description['editable'] = [
						'TYPE' => Types::MULTISELECT,
						'DATA' => [
							'ITEMS' => $dropdownItems,
						]
					];
					break;
				case 'list':
					$dropdownItems = [];

					if (!$description['required'])
					{
						$dropdownItems[] = Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_NOT_SELECTED');
					}

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
				case 'datetime':
					$description['editable'] = ['TYPE' => Types::DATE];
					break;
				case 'money':
					$description['editable'] = [
						'TYPE' => Types::MONEY,
						'CURRENCY_LIST' => CurrencyManager::getSymbolList(),
						'HTML_ENTITY' => true,
					];
					break;
				default:
					$description['editable'] = ['TYPE' => Types::TEXT];
			}
		}

		return $description;
	}

	protected function buildIblockPropertiesDescriptions(): array
	{
		$propertyDescriptions = [];

		foreach ($this->entity->getPropertyCollection() as $property)
		{
			$propertyDescriptions[] = $this->getPropertyDescription($property);
		}

		return $propertyDescriptions;
	}

	public function getColumnValues(bool $allowDefaultValues = true): array
	{
		$values = $this->getShowedValues($allowDefaultValues);

		foreach ($this->getGridHeaders() as $description)
		{
			$name = $description['id'];
			if (!isset($values[$name]))
			{
				continue;
			}

			$currentValue = $values[$name];

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
						$items = [];

						foreach ($description['editable']['DATA']['ITEMS'] as $item)
						{
							$items[$item['VALUE']] = $item['HTML'] ?? HtmlFilter::encode($item['NAME']);
						}

						foreach ($currentValue as $multipleItemValue)
						{
							$formatted[] = $items[$multipleItemValue];
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
				case 'barcode':
					$barcodes =
						is_array($values[$name])
							? array_column($values[$name], 'BARCODE')
							: null
					;

					$values[$name] = $barcodes ? htmlspecialcharsbx(implode(', ', $barcodes)) : '';
					break;
				case 'money':
					if (isset($description['data']['isProductProperty']) && $description['data']['isProductProperty'])
					{
						$separatedValues = IblockMoneyProperty::getSeparatedValues($values[$name]);
						$amount = (float)($separatedValues['AMOUNT'] . '.' . $separatedValues['DECIMALS']);
						$currency = $separatedValues['CURRENCY'];
						$values[$name] = \CCurrencyLang::CurrencyFormat($amount, $currency, true);
					}
					break;
			}

			if (is_array($values[$name]))
			{
				$values[$name] = implode(', ', $values[$name]);
			}
		}

		return $values;
	}

	public function loadGridHeaders(): array
	{
		$defaultWidth = 130;

		$headerName = static::getHeaderName('NAME');

		$headers = [
			[
				'id' => static::formatFieldName('NAME'),
				'name' => $headerName['NAME'],
				'title' => $headerName['TITLE'],
				'sort' => false,
				'type' => 'string',
				'editable' => [
					'TYPE' => Types::TEXT,
					'PLACEHOLDER' => Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_NEW_VARIATION_PLACEHOLDER'),
				],
				'width' => $defaultWidth,
				'default' => false,
			],
		];

		foreach ($this->getIblockPropertiesDescriptions() as $property)
		{
			$isDirectory = $property['settings']['PROPERTY_TYPE'] === PropertyTable::TYPE_STRING
				&& $property['settings']['USER_TYPE'] === 'directory';
			$header = [
				'id' => $property['name'],
				'name' => $property['title'],
				'title' => $property['title'],
				'type' => $property['type'],
				'align' => $property['type'] === 'number' ? 'right' : 'left',
				'sort' => false,
				'default' => $property['propertyCode'] === self::MORE_PHOTO,
				'data' => $property['data'],
				'width' => $isDirectory ? 160 : null,
				'editable' => $property['editable'],
			];
			if (!empty($property['isEnabledOfferTree']))
			{
				$header['hint'] = Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_OFFER_TREE_HINT');
			}
			$headers[] = $header;
		}

		$headers = array_merge(
			$headers,
			$this->getProductFieldHeaders(
				['ACTIVE', 'BARCODE', 'QUANTITY_COMMON', 'MEASURE', 'MEASURE_RATIO'],
				$defaultWidth
			)
		);

		$currencyList = CurrencyManager::getSymbolList();
		$purchasingPriceName = static::formatFieldName('PURCHASING_PRICE_FIELD');

		$headerName = static::getHeaderName('PURCHASING_PRICE');

		$headers[] = [
			'id' => $purchasingPriceName,
			'name' => $headerName['NAME'],
			'title' => $headerName['TITLE'],
			'sort' => false,
			'type' => 'money',
			'align' => 'right',
			'editable' =>
				!State::isUsedInventoryManagement()
					? [
						'TYPE' => Types::MONEY,
						'CURRENCY_LIST' => $currencyList,
						'HTML_ENTITY' => true,
					]
					: false
			,
			'width' => $defaultWidth,
			'default' => false,
		];

		$priceTypeList = \CCatalogGroup::GetListArray();

		if (!empty($priceTypeList))
		{
			foreach ($priceTypeList as $priceType)
			{
				$columnName = !empty($priceType['NAME_LANG']) ? $priceType['NAME_LANG'] : $priceType['NAME'];

				$priceId = static::formatFieldName(BaseForm::PRICE_FIELD_PREFIX.$priceType['ID'].'_FIELD');
				$headers[] = [
					'id' => $priceId,
					'name' => $columnName,
					'title' => $columnName,
					'sort' => false, // 'SCALED_PRICE_'.$priceType['ID'],
					'type' => 'money',
					'align' => 'right',
					'editable' => [
						'TYPE' => Types::MONEY,
						'CURRENCY_LIST' => $currencyList,
						'HTML_ENTITY' => true,
					],
					'base' => $priceType['BASE'] === 'Y',
					'width' => $defaultWidth,
					'default' => $priceType['BASE'] === 'Y',
				];
			}
		}

		$headers = array_merge(
			$headers,
			$this->getProductFieldHeaders(
				[
					'AVAILABLE', 'VAT_ID', 'VAT_INCLUDED', 'QUANTITY', 'QUANTITY_RESERVED',
					'QUANTITY_TRACE', 'CAN_BUY_ZERO', // 'SUBSCRIBE',
					'WEIGHT', 'WIDTH', 'LENGTH', 'HEIGHT',
					'SHOW_COUNTER', 'CODE', 'TIMESTAMP_X', 'USER_NAME',
					'DATE_CREATE', 'XML_ID',
					// 'BAR_CODE', 'TAGS', 'DISCOUNT', 'STORE', 'PRICE_TYPE',
				],
				$defaultWidth
			)
		);

		self::$headers = $headers;

		return $headers;
	}

	public function getGridHeaders(): array
	{
		if (self::$headers)
		{
			return self::$headers;
		}

		return $this->loadGridHeaders();
	}

	protected function getProductFieldHeaders(array $fields, int $defaultWidth): array
	{
		$headers = [];

		$numberFields = ['QUANTITY', 'QUANTITY_RESERVED', 'QUANTITY_COMMON', 'MEASURE_RATIO', 'WEIGHT', 'WIDTH', 'LENGTH', 'HEIGHT'];
		$numberFields = array_fill_keys($numberFields, true);

		$immutableFields = ['TIMESTAMP_X', 'USER_NAME', 'DATE_CREATE', 'CREATED_USER_NAME', 'AVAILABLE'];
		$immutableFields = array_fill_keys($immutableFields, true);

		$defaultFields = ['QUANTITY', 'MEASURE', 'NAME', 'BARCODE'];
		$defaultFields = array_fill_keys($defaultFields, true);

		foreach ($fields as $code)
		{
			$type = isset($numberFields[$code]) ? 'number' : 'string';

			switch ($code)
			{
				case 'AVAILABLE':
				case 'ACTIVE':
				case 'VAT_INCLUDED':
					$type = 'boolean';
					break;

				case 'VAT_ID':
				case 'MEASURE':
				case 'QUANTITY_TRACE':
				case 'CAN_BUY_ZERO':
				case 'SUBSCRIBE':
					$type = 'list';
					break;
			}

			$editable = false;

			if (!isset($immutableFields[$code]))
			{
				$editable = [
					'TYPE' => Types::TEXT,
				];

				switch ($code)
				{
					case 'ACTIVE':
					case 'VAT_INCLUDED':
						$editable = [
							'TYPE' => Types::CHECKBOX,
						];
						break;

					case 'VAT_ID':
						$vatList = [
							'D' => Loc::getMessage("CATALOG_PRODUCT_CARD_VARIATION_GRID_DEFAULT",
								['#VALUE#' => Loc::getMessage("CATALOG_PRODUCT_CARD_VARIATION_GRID_NOT_SELECTED")]),
						];

						$iblockVatId = $this->entity->getIblockInfo()->getVatId();

						foreach ($this->getVats() as $vat)
						{
							if ((int)$vat['ID'] === $iblockVatId)
							{
								$vatList['D'] = Loc::getMessage(
									"CATALOG_PRODUCT_CARD_VARIATION_GRID_DEFAULT",
									['#VALUE#' => htmlspecialcharsbx($vat['NAME'])]
								);
							}
							$vatList[$vat['ID']] = htmlspecialcharsbx($vat['NAME']);
						}
						$editable = [
							'TYPE' => Types::DROPDOWN,
							'items' => $vatList,
						];
						break;

					case 'MEASURE':
						$measureList = [];

						foreach ($this->getMeasures() as $measure)
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
					case 'QUANTITY_RESERVED':
					case 'QUANTITY_COMMON':
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
						$editable = [
							'TYPE' => Types::NUMBER,
							'PLACEHOLDER' => 0,
						];
						break;

					case 'BARCODE':
						$editable = [
							'TYPE' => Types::CUSTOM,
						];
						$type = 'barcode';
						break;

					case 'QUANTITY_TRACE':
					case 'CAN_BUY_ZERO':
					case 'SUBSCRIBE':
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
			}

			$headerName = static::getHeaderName($code);

			$headers[] = [
				'id' => static::formatFieldName($code),
				'name' => $headerName['NAME'],
				'title' => $headerName['TITLE'],
				'sort' => false,
				'type' => $type,
				'align' => $type === 'number' ? 'right' : 'left',
				'editable' => $editable,
				'width' => $defaultWidth,
				'default' => isset($defaultFields[$code]),
			];
		}

		return $headers;
	}

	private function getShowedValues(bool $allowDefaultValues = true): array
	{
		if (!self::$usedHeaders)
		{
			$options = new \Bitrix\Main\Grid\Options($this->getVariationGridId());
			self::$usedHeaders = $options->getUsedColumns();

			if (!self::$usedHeaders)
			{
				$defaultHeaders = array_filter($this->getGridHeaders(), static function ($header) {
					return ($header['default'] === true);
				});

				self::$usedHeaders = array_column($defaultHeaders, 'id');
			}
		}

		$usedHeaders = self::$usedHeaders;
		$filteredDescriptions = array_filter($this->getDescriptions(), static function ($description) use ($usedHeaders) {
			return in_array($description['name'], $usedHeaders, true);
		});

		return parent::getValues($allowDefaultValues, $filteredDescriptions);
	}

	public function getValues(bool $allowDefaultValues = true, array $descriptions = null): array
	{
		$values = $this->getShowedValues($allowDefaultValues);

		foreach ($this->getDescriptions() as $description)
		{
			$name = $description['name'];

			if (!isset($values[$name]))
			{
				continue;
			}

			switch ($description['type'])
			{
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
			}
		}

		return $values;
	}

	protected function getAdditionalValues(array $values, array $descriptions = null): array
	{
		$additionalValues = parent::getAdditionalValues($values, $descriptions);

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

	protected function getImagePropertyViewHtml($value): string
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

		if ($field['entity'] === 'barcode')
		{
			return $this->getBarcodeValue();
		}

		if ($field['originalName'] === 'QUANTITY_COMMON')
		{
			return $this->getCommonQuantityFieldValue();
		}

		return parent::getFieldValue($field);
	}

	protected function getMoneyFieldValue(array $field)
	{
		if ($field['priceTypeId'] === 'PURCHASING_PRICE')
		{
			$price = $this->entity->getField('PURCHASING_PRICE');
			$currency = $this->entity->getField('PURCHASING_CURRENCY');
		}
		else
		{
			$priceItem = $this->entity
				->getPriceCollection()
				->findByGroupId($field['priceTypeId'])
			;
			$price = $priceItem ? $priceItem->getPrice() : null;
			$currency = $priceItem ? $priceItem->getCurrency() : null;
		}

		$currency = $currency ?? CurrencyManager::getBaseCurrency();

		return \CCurrencyLang::CurrencyFormat($price, $currency, true);
	}

	/**
	 * Return description of common quantity field
	 * @return array
	 */
	protected function getCommonQuantityDescription(): array
	{
		$commonQuantityName = 'QUANTITY_COMMON';
		return [
			[
				'entity' => 'product',
				'name' => $this->prepareFieldName($commonQuantityName),
				'originalName' => $commonQuantityName,
				'title' => Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_DESCRIPTION_COMMON_QUANTITY_TITLE'),
				'type' => 'number',
				'editable' => false,
				'required' => false,
				'placeholders' => null,
				'defaultValue' => null,
				'optionFlags' => 1, // showAlways
			]
		];
	}

	/**
	 * Return value of common quantity of the variation ( Available quantity + Reserved Quantity )
	 * @return float
	 */
	protected function getCommonQuantityFieldValue(): float
	{
		$quantity = (float)$this->entity->getField('QUANTITY');
		$quantityReserved = (float)$this->entity->getField('QUANTITY_RESERVED');
		return $quantity + $quantityReserved;
	}

	protected function getBarcodeDescription(): array
	{
		$headerName = static::getHeaderName('BARCODE');

		return [
			[
				'entity' => 'barcode',
				'name' => static::formatFieldName('BARCODE'),
				'title' => $headerName['NAME'],
				'type' => 'barcode',
				'multiple' => true,
				'editable' => true,
				'required' => false,
			],
		];
	}

	/**
	 * @return &string
	 */
	protected function getBarcodeValue(): array
	{
		$barcodes = [];
		foreach ($this->entity->getBarcodeCollection() as $barcodeItem)
		{
			$barcodes[] = [
				'ID' => $barcodeItem->getId(),
				'BARCODE' => $barcodeItem->getBarcode(),
			];
		}

		return $barcodes;
	}

	protected function getElementTableMap(): array
	{
		return ElementTable::getMap();
	}

	protected static function getHeaderName(string $code): array
	{
		$headerName = Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_HEADER_NAME_' . $code);
		$headerTitle = Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_HEADER_TITLE_' . $code);

		return [
			'NAME' => $headerName,
			'TITLE' => $headerTitle ?? $headerName,
		];
	}
}
