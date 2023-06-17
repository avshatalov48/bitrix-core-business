<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Catalog;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\v2\Barcode\Barcode;
use Bitrix\Catalog\v2\Property\Property;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Grid\Editor\Types;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Currency\Integration\IblockMoneyProperty;
use CIBlockPropertyXmlID;

class GridVariationForm extends VariationForm
{
	/** @var \Bitrix\Catalog\v2\Sku\BaseSku */
	protected $entity;

	protected static ?array $usedHeaders = null;
	protected static ?array $headers = null;

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

	public function isReadOnly(): bool
	{
		return !$this->isAllowedEditFields();
	}

	public static function getGridCardSettingsItems(): array
	{
		$result = [];
		$result['VAT_INCLUDED'] = [
			'ITEMS' => [
				static::formatFieldName('VAT_ID'),
				static::formatFieldName('VAT_INCLUDED'),
			],
			'TITLE' => Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_SETTINGS_TITLE_VAT_INCLUDED'),
			'DESCRIPTION' => Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_SETTINGS_DESC_VAT_INCLUDED'),
		];
		if (AccessController::getCurrent()->check(ActionDictionary::ACTION_PRODUCT_PURCHASE_INFO_VIEW))
		{
			$result['PURCHASING_PRICE_FIELD'] = [
				'ITEMS' => [
					static::formatFieldName('PURCHASING_PRICE_FIELD'),
				],
				'TITLE' => Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_SETTINGS_TITLE_PURCHASING_PRICE_FIELD'),
				'DESCRIPTION' => Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_SETTINGS_DESC_PURCHASING_PRICE_FIELD'),
			];
		}
		$result['MEASUREMENTS'] = [
			'ITEMS' => [
				static::formatFieldName('HEIGHT'),
				static::formatFieldName('LENGTH'),
				static::formatFieldName('WIDTH'),
				static::formatFieldName('WEIGHT'),
			],
			'TITLE' => Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_SETTINGS_TITLE_MEASUREMENTS'),
			'DESCRIPTION' => Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_SETTINGS_DESC_MEASUREMENTS'),
		];
		$result['MEASURE_RATIO'] = [
			'ITEMS' => [
				static::formatFieldName('MEASURE_RATIO'),
			],
			'TITLE' => Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_SETTINGS_TITLE_MEASURE_RATIO'),
			'DESCRIPTION' => Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_SETTINGS_DESC_MEASURE_RATIO'),
		];

		return $result;
	}

	public function setGridSettings(string $settingId, $selected, array $currentHeaders = []): AjaxJson
	{
		$headers = static::getHeaderIdsBySettingId($settingId);

		if (!empty($headers))
		{
			$options = new \Bitrix\Main\Grid\Options($this->getVariationGridId());
			$allUsedColumns = $options->getUsedColumns();

			if (empty($allUsedColumns))
			{
				$allUsedColumns = $currentHeaders;
			}

			if ($selected === 'true')
			{
				// sort new columns by default grid column sort
				$defaultHeaders = array_column($this->getGridHeaders(), 'id');
				$currentHeadersInDefaultPosition = array_values(
					array_intersect($defaultHeaders, array_merge($allUsedColumns, $headers))
				);
				$headers = array_values(array_intersect($defaultHeaders, $headers));

				foreach ($headers as $header)
				{
					$insertPosition = array_search($header, $currentHeadersInDefaultPosition, true);
					array_splice($allUsedColumns, $insertPosition, 0, $header);
				}
			}
			else
			{
				$allUsedColumns = array_diff($allUsedColumns, $headers);
			}

			$options->setColumns(implode(',', $allUsedColumns));
			$options->save();
		}

		return AjaxJson::createSuccess();
	}

	protected static function getHeaderIdsBySettingId(string $settingId): array
	{
		$headers = [];
		switch ($settingId)
		{
			case 'MEASUREMENTS':
				$headers = [
					'WEIGHT',
					'WIDTH',
					'LENGTH',
					'HEIGHT',
				];
				break;
			case 'PURCHASING_PRICE_FIELD':
				if (AccessController::getCurrent()->check(ActionDictionary::ACTION_PRODUCT_PURCHASE_INFO_VIEW))
				{
					$headers = [
						'PURCHASING_PRICE_FIELD',
					];
				}
				break;
			case 'MEASURE_RATIO':
				$headers = [
					'MEASURE_RATIO',
				];
				break;
			case 'VAT_INCLUDED':
				$headers = [
					'VAT_INCLUDED',
					'VAT_ID'
				];
				break;
		}

		foreach ($headers as &$id)
		{
			$id = static::formatFieldName($id);
		}
		unset($id);

		return $headers;
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
					if ($description['multiple'] === true && $description['propertyCode'] !== 'MORE_PHOTO')
					{
						$description['editable'] = false;
					}
					else
					{
						$description['editable'] = [
							'TYPE' => Types::CUSTOM,
							'NAME' => $description['data']['edit'] ?? $description['name'],
							// 'HTML' => $description['data']['edit'] ?? $description['name'],
						];
					}
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

			$nonEditableUserTypes = [
				'ElementXmlID',
				'employee',
				'map_yandex',
				'map_google',
				'ECrm',
				'video',
				'HTML',
			];
			if (
				$description['settings']['PROPERTY_TYPE'] === PropertyTable::TYPE_ELEMENT
				|| $description['settings']['PROPERTY_TYPE'] === PropertyTable::TYPE_SECTION
				|| in_array($description['settings']['USER_TYPE'], $nonEditableUserTypes, true)
			)
			{
				$description['editable'] = false;
			}
		}

		return $description;
	}

	protected function getUnavailableUserTypes(): array
	{
		return [
			'DiskFile',
			'TopicID',
		];
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

						foreach ($description['data']['items'] as $item)
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
					$code = '';
					if (
						$description['id'] === static::formatFieldName('ACTIVE')
						|| $description['id'] === static::formatFieldName('AVAILABLE')
						|| $description['id'] === static::formatFieldName('VAT_INCLUDED')
					)
					{
						$code = $currentValue === 'Y' ? 'YES' : 'NO';
					}
					else
					{
						$code = ($currentValue !== '') ? 'YES' : 'NO';
					}
					$values[$name] = Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_VALUE_' . $code);
					break;
				case 'list':
					if (isset($description['editable']['items']))
					{
						$values[$name] = HtmlFilter::encode($description['editable']['items'][$currentValue] ?? '');
						break;
					}
					foreach ($description['data']['items'] as $item)
					{
						if ($currentValue === $item['VALUE'])
						{
							$values[$name] = HtmlFilter::encode($item['NAME'] ?? '');
							break;
						}
					}
					break;
				case 'custom':
					$values[$name] = $values[$description['data']['view']];
					break;
				case 'user':
					$values[$name] = HtmlFilter::encode($values[$name . '_FORMATTED_NAME'] ?? '');
					break;
				case 'readOnlyVat':
					$currentVat = (int)$values[$name];
					$values[$name] = '';
					if ($currentVat > 0)
					{
						foreach ($this->getVats() as $vat)
						{
							if ((int)$vat['ID'] === $currentVat)
							{
								$values[$name] = htmlspecialcharsbx($vat['NAME']);
								break;
							}
						}
					}
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
				'sort' => 'NAME',
				'type' => 'string',
				'editable' =>
					$this->isAllowedEditFields()
						? [
							'TYPE' => Types::TEXT,
							'PLACEHOLDER' => Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_NEW_VARIATION_PLACEHOLDER'),
						]
						: false
				,
				'width' => $defaultWidth,
				'default' => false,
			],
		];

		$headers = array_merge(
			$headers,
			$this->getIblockPropertiesHeaders(),
			$this->getProductFieldHeaders(
				['ACTIVE', 'BARCODE', 'QUANTITY_COMMON', 'MEASURE', 'MEASURE_RATIO'],
				$defaultWidth
			),
			$this->getPurchasingPriceHeaders($defaultWidth),
			$this->getPricesHeaders($defaultWidth),
			$this->getProductFieldHeaders(
				[
					'AVAILABLE', 'VAT_ID', 'VAT_INCLUDED', 'QUANTITY', 'QUANTITY_RESERVED',
					'QUANTITY_TRACE', 'CAN_BUY_ZERO', // 'SUBSCRIBE',
					'WEIGHT', 'WIDTH', 'LENGTH', 'HEIGHT',
					'SHOW_COUNTER', 'CODE', 'TIMESTAMP_X', 'MODIFIED_BY',
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

	/**
	 * Columns that are sent in the grid request.
	 *
	 * @return array
	 */
	public function getGridSupportedAjaxColumns(): array
	{
		$columns = array_fill_keys(
			array_column($this->getGridHeaders(), 'id'),
			true
		);

		foreach ($this->getIblockPropertiesDescriptions() as $property)
		{
			$name = $property['name'];

			// files are not supported because new files are not sent in the request
			$isFile = $property['settings']['PROPERTY_TYPE'] === PropertyTable::TYPE_FILE;
			if ($isFile)
			{
				unset($columns[$name]);
			}
		}

		return array_keys($columns);
	}

	protected function getProductFieldHeaders(array $fields, int $defaultWidth): array
	{
		$headers = [];

		$numberFields = ['QUANTITY', 'QUANTITY_RESERVED', 'QUANTITY_COMMON', 'MEASURE_RATIO', 'WEIGHT', 'WIDTH', 'LENGTH', 'HEIGHT'];
		$numberFields = array_fill_keys($numberFields, true);

		$immutableFields = ['TIMESTAMP_X', 'MODIFIED_BY', 'DATE_CREATE', 'CREATED_USER_NAME', 'AVAILABLE'];
		$immutableFields = array_fill_keys($immutableFields, true);

		$defaultFields = ['QUANTITY', 'MEASURE', 'NAME', 'BARCODE'];
		$defaultFields = array_fill_keys($defaultFields, true);

		$sortableFields = [
			'QUANTITY' =>'QUANTITY',
			'AVAILABLE' =>'AVAILABLE',
			'WEIGHT' =>'WEIGHT',
			'ACTIVE' =>'ACTIVE',
			'MEASURE' =>'MEASURE',
			'TIMESTAMP_X' => 'TIMESTAMP_X',
			'MODIFIED_BY' => 'MODIFIED_BY',
			'DATE_CREATE' => 'CREATED',
			'CREATED_USER_NAME' => 'CREATED_BY',
			'CODE' => 'CODE',
			'EXTERNAL_ID' => 'EXTERNAL_ID',
			'XML_ID' => 'XML_ID',
			'TAGS' => 'TAGS',
			'SHOW_COUNTER' => 'SHOW_COUNTER',
			'SHOW_COUNTER_START' => 'SHOW_COUNTER_START',
			'PREVIEW_PICTURE' => 'HAS_PREVIEW_PICTURE',
			'DETAIL_PICTURE' => 'HAS_DETAIL_PICTURE',
		];

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

				case 'MODIFIED_BY':
					$type = 'user';
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
						$editable = [
							'TYPE' => Types::CHECKBOX,
						];
						break;
					case 'VAT_INCLUDED':
						$editable =
							$this->isPricesEditable()
								? ['TYPE' => Types::CHECKBOX]
								: false
						;
						break;

					case 'VAT_ID':
						if (!$this->isPricesEditable())
						{
							$editable = false;
							$type = 'readOnlyVat';

							break;
						}

						$defaultVat = $this->getDefaultVat();
						$vatList = [
							$defaultVat['ID'] => $defaultVat['NAME'],
						];

						if ($defaultVat['ID'] !== static::NOT_SELECTED_VAT_ID_VALUE && !Loader::includeModule('bitrix24'))
						{
							$vatList[static::NOT_SELECTED_VAT_ID_VALUE] = Loc::getMessage("CATALOG_PRODUCT_CARD_VARIATION_GRID_NOT_SELECTED");
						}

						foreach ($this->getVats() as $vat)
						{
							if ($vat['RATE'] === $defaultVat['RATE'] && $vat['EXCLUDE_VAT'] === $defaultVat['EXCLUDE_VAT'])
							{
								continue;
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

			$headerName =
				$code === 'MODIFIED_BY'
					? static::getHeaderName('USER_NAME')
					: static::getHeaderName($code)
			;

			$sortField = $sortableFields[$code] ?? false;

			$headers[] = [
				'id' => static::formatFieldName($code),
				'name' => $headerName['NAME'],
				'title' => $headerName['TITLE'],
				'sort' => $sortField,
				'locked' => false,
				'headerHint' => null,
				'type' => $type,
				'align' => $type === 'number' ? 'right' : 'left',
				'editable' => $editable,
				'width' => $defaultWidth,
				'default' => isset($defaultFields[$code]),
			];
		}

		return $headers;
	}

	/**
	 * Returns grid header's description for iblock properties.
	 *
	 * @return array
	 */
	protected function getIblockPropertiesHeaders(): array
	{
		$headers = [];

		foreach ($this->getIblockPropertiesDescriptions() as $property)
		{
			$isDirectory =
				$property['settings']['PROPERTY_TYPE'] === PropertyTable::TYPE_STRING
				&& $property['settings']['USER_TYPE'] === 'directory'
			;

			$sortField = "PROPERTY_{$property['propertyCode']}";
			if (
				$property['multiple']
				|| $property['propertyCode'] === 'CML2_LINK'
				|| $property['settings']['PROPERTY_TYPE'] === PropertyTable::TYPE_FILE
			)
			{
				$sortField = false;
			}

			$header = [
				'id' => $property['name'],
				'name' => $property['title'],
				'title' => $property['title'],
				'type' => $property['type'],
				'align' => $property['type'] === 'number' ? 'right' : 'left',
				'sort' => $sortField,
				'default' => $property['propertyCode'] === self::MORE_PHOTO,
				'data' => $property['data'],
				'width' => $isDirectory ? 160 : null,
				'editable' => $property['editable'],
			];
			if (!empty($property['isEnabledOfferTree']))
			{
				$header['hint'] = Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_OFFER_TREE_HINT');
			}

			if (
				$property['settings']['PROPERTY_TYPE'] === PropertyTable::TYPE_FILE
				&& $property['multiple'] === true
				&& $property['propertyCode'] !== 'MORE_PHOTO'
			)
			{
				$header['hint'] = Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_FILE_MULTIPLE_HINT');
			}

			$headers[] = $header;
		}

		return $headers;
	}

	/**
	 * Returns list with purchasing price grid header.
	 *
	 * @param int|null $defaultWidth
	 * @return array
	 */
	protected function getPurchasingPriceHeaders(?int $defaultWidth): array
	{
		$headers = [];

		if ($this->isPurchasingPriceAllowed())
		{
			$headerName = static::getHeaderName('PURCHASING_PRICE');

			$headers[] = [
				'id' => static::formatFieldName('PURCHASING_PRICE_FIELD'),
				'name' => $headerName['NAME'],
				'title' => $headerName['TITLE'],
				'sort' => 'PURCHASING_PRICE',
				'type' => 'money',
				'align' => 'right',
				'editable' =>
					!State::isUsedInventoryManagement() && $this->isAllowedEditFields()
						? [
							'TYPE' => Types::MONEY,
							'CURRENCY_LIST' => CurrencyManager::getSymbolList(),
							'HTML_ENTITY' => true,
						]
						: false
				,
				'width' => $defaultWidth,
				'default' => false,
			];
		}

		return $headers;
	}

	/**
	 * Returns grid headers list for price types.
	 *
	 * @param int|null $defaultWidth
	 * @return array
	 */
	protected function getPricesHeaders(?int $defaultWidth): array
	{
		$headers = [];

		$currencyList = CurrencyManager::getSymbolList();

		foreach (Catalog\GroupTable::getTypeList() as $priceType)
		{
			$columnName = $priceType['NAME_LANG'] ?? $priceType['NAME'];

			$priceId = static::formatFieldName(BaseForm::PRICE_FIELD_PREFIX.$priceType['ID'].'_FIELD');
			$headers[] = [
				'id' => $priceId,
				'name' => $columnName,
				'title' => $columnName,
				'sort' => 'SCALED_PRICE_'.$priceType['ID'],
				'type' => 'money',
				'align' => 'right',
				'editable' =>
					$this->isPricesEditable()
						? [
							'TYPE' => Types::MONEY,
							'CURRENCY_LIST' => $currencyList,
							'HTML_ENTITY' => true,
						]
						: false
				,
				'locked' => !$this->isPricesEditable(),
				'headerHint' =>
					$this->isPricesEditable()
						? null
						: Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_PRICE_EDIT_RESTRICTED_HINT')
				,
				'base' => $priceType['BASE'] === 'Y',
				'width' => $defaultWidth,
				'default' => $priceType['BASE'] === 'Y',
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
				$defaultHeaders = array_filter(
					$this->getGridHeaders(),
					static function ($header)
					{
						return ($header['default'] === true);
					}
				);

				self::$usedHeaders = array_column($defaultHeaders, 'id');
			}
		}

		$usedHeaders = self::$usedHeaders;
		$filteredDescriptions = array_filter(
			$this->getDescriptions(),
			static function ($description) use ($usedHeaders)
			{
				return in_array($description['name'], $usedHeaders, true);
			}
		);

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
				case 'boolean':
					$descriptionData = $description['data'] ?? [];
					$variants = $descriptionData['items'] ?? [];
					foreach ($variants as $variant)
					{
						if ($values[$name] === $variant['ID'])
						{
							$values[$name] = $variant['NAME'];
							break;
						}
					}
					break;
			}
		}

		return $values;
	}

	/**
	 * Leaves only the values of the grid fields used.
	 *
	 * For price fields, converts values to `['PRICE' => '...', 'CURRENCY' => '...']` format.
	 *
	 * @param array $dirtyValues
	 *
	 * @return array with fields values, and additional fields `PRICES` and `PROPERTIES`.
	 */
	public function prepareFieldsValues(array $dirtyValues): array
	{
		$result = [
			'PROPERTIES' => [],
			'BARCODES' => [],
			'PRICES' => [],
		];

		$pricePrefix = self::GRID_FIELD_PREFIX . self::PRICE_FIELD_PREFIX;
		$purchacingPricePrefix = self::GRID_FIELD_PREFIX . 'PURCHASING_PRICE';

		foreach ($this->getDescriptions() as $description)
		{
			$name = $description['name'];
			$value = $dirtyValues[$name] ?? null;
			if (!isset($value))
			{
				continue;
			}

			if (isset($description['propertyId']))
			{
				$type = $description['type'] ?? null;
				if ($type === 'multilist' && empty($value))
				{
					$value = [];
				}

				$propertyId = (int)$description['propertyId'];
				$result['PROPERTIES'][$propertyId] = $value;
			}
			elseif (mb_strpos($name, $pricePrefix) === 0)
			{
				if (
					is_array($value)
					&& isset($value['PRICE']['NAME'], $value['PRICE']['VALUE'], $value['CURRENCY']['VALUE'])
				)
				{
					$priceGroupId = str_replace($pricePrefix, '', $value['PRICE']['NAME']);
					if ($priceGroupId)
					{
						$result['PRICES'][$priceGroupId] = [
							'PRICE' => (float)$value['PRICE']['VALUE'],
							'CURRENCY' => (string)$value['CURRENCY']['VALUE'],
						];
					}
				}
			}
			elseif (mb_strpos($name, $purchacingPricePrefix) === 0)
			{
				if (is_array($value) && isset($value['PRICE']['VALUE'], $value['CURRENCY']['VALUE']))
				{
					$result['PURCHASING_PRICE'] = (float)$value['PRICE']['VALUE'];
					$result['PURCHASING_CURRENCY'] = (string)$value['CURRENCY']['VALUE'];
				}
			}
			elseif (isset($description['originalName']))
			{
				$name = $description['originalName'];
				$result[$name] = $value;
			}
			elseif (isset($description['entity']) && $description['entity'] === 'barcode')
			{
				if (is_array($value))
				{
					array_push($result['BARCODES'], ...$value);
				}
				else
				{
					$result['BARCODES'][] = $value;
				}
			}
		}

		return $result;
	}

	protected function getAdditionalValues(array $values, array $descriptions = null): array
	{
		$additionalValues = parent::getAdditionalValues($values, $descriptions);

		$numberFields = ['MEASURE_RATIO', 'QUANTITY'];
		foreach ($numberFields as $fieldName)
		{
			$fieldName = self::formatFieldName($fieldName);
			if (isset($values[$fieldName]) && $values[$fieldName] == 0)
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

		if (isset($field['originalName']) && $field['originalName'] === 'QUANTITY_COMMON')
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
				'editable' => $this->isAllowedEditFields(),
				'required' => false,
			],
		];
	}

	/**
	 * @return array
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
