<?php

namespace Bitrix\Catalog\Filter\DataProvider;

use Bitrix\Catalog\Filter\DataProvider\Currency\CurrencyListItems;
use Bitrix\Catalog\Filter\DataProvider\Measure\MeasureListItems;
use Bitrix\Catalog\Filter\DataProvider\Settings\ProductSettings;
use Bitrix\Catalog\Filter\PrefixableDataProviderTrait;
use Bitrix\Catalog\ProductTable;
use Bitrix\Iblock\Filter\DataProvider\Element\ElementFilterFields;
use Bitrix\Iblock\Filter\DataProvider\ElementDataProvider;
use Bitrix\Main\Filter\Field;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\Type;

Loader::requireModule('iblock');

/**
 * @method ProductSettings getSettings()
 */
class ProductDataProvider extends ElementDataProvider
{
	use MeasureListItems;
	use CurrencyListItems;
	use PrefixableDataProviderTrait;

	private const VARIATION_PREFIX = 'VARIATION_';

	private ?ElementFilterFields $variationFields;

	public function __construct(ProductSettings $settings)
	{
		parent::__construct($settings);

		$variationIblockId = $settings->getVariationIblockId();
		if ($variationIblockId > 0)
		{
			$this->variationFields = new ElementFilterFields($variationIblockId, false, false);
		}
	}

	private function isWithVariations(): bool
	{
		return isset($this->variationFields);
	}

	public function prepareFields()
	{
		$result = parent::prepareFields();

		$result['TYPE'] = $this->createField('TYPE', [
			'type' => 'list',
			'default' => false,
			'partial' => true,
		]);
		$result['AVAILABLE'] = $this->createField('AVAILABLE', [
			'type' => 'checkbox',
			'default' => false,
		]);
		$result['QUANTITY'] = $this->createField('QUANTITY', [
			'type' => 'number',
			'default' => false,
		]);
		$result['QUANTITY_RESERVED'] = $this->createField('QUANTITY_RESERVED', [
			'type' => 'number',
			'default' => false,
		]);
		$result['PURCHASING_PRICE'] = $this->createField('PURCHASING_PRICE', [
			'type' => 'number',
			'default' => false,
		]);
		$result['PURCHASING_CURRENCY'] = $this->createField('PURCHASING_CURRENCY', [
			'type' => 'list',
			'default' => false,
			'partial' => true,
		]);
		$result['WEIGHT'] = $this->createField('WEIGHT', [
			'type' => 'number',
			'default' => false,
		]);
		$result['WIDTH'] = $this->createField('WIDTH', [
			'type' => 'number',
			'default' => false,
		]);
		$result['LENGTH'] = $this->createField('LENGTH', [
			'type' => 'number',
			'default' => false,
		]);
		$result['HEIGHT'] = $this->createField('HEIGHT', [
			'type' => 'number',
			'default' => false,
		]);
		$result['MEASURE'] = $this->createField('MEASURE', [
			'type' => 'list',
			'default' => false,
			'partial' => true,
		]);

		if ($this->isWithVariations())
		{
			$result += $this->prepareVariationElementFields();
		}

		return $result;
	}

	/**
	 * Fields of variations.
	 *
	 * @return Field[]
	 */
	private function prepareVariationElementFields(): array
	{
		$result = [];

		/*
		$fields = $this->variationFields->getElementFieldsParams();
		foreach ($fields as $id => $params)
		{
			$result[$id] = $this->createField($id, $params);
		}
		*/

		$properties = $this->variationFields->getElementPropertiesParams();
		foreach ($properties as $id => $params)
		{
			$result[$id] = $this->createField($id, $params);
		}

		$nameWithPrefixTemplate = Loc::getMessage('CATALOG_FILTER_PRODUCT_DATAPROVIDER_NAME_WITH_PREFIX');

		return $this->prepareFieldsByPrefix(self::VARIATION_PREFIX, $result, $nameWithPrefixTemplate);
	}

	/**
	 * @inheritDoc
	 */
	public function prepareFieldData($fieldID)
	{
		if ($fieldID === 'PURCHASING_CURRENCY')
		{
			return [
				'items' => $this->getCurrencyListItems(),
			];
		}
		elseif ($fieldID === 'MEASURE')
		{
			return [
				'items' => $this->getMeasureListItems(),
			];
		}
		elseif ($fieldID === 'TYPE')
		{
			return [
				'items' => ProductTable::getProductTypes(true),
				'params' => [
					'multiple' => true,
				],
			];
		}

		$result = parent::prepareFieldData($fieldID);
		if (isset($result))
		{
			return $result;
		}

		if ($this->isWithVariations())
		{
			return $this->prepareVariationFieldData($fieldID);
		}

		return null;
	}

	private function prepareVariationFieldData(string $fieldIdWithPrefix): ?array
	{
		$fieldID = $this->removePrefix(self::VARIATION_PREFIX, $fieldIdWithPrefix);

		return $this->variationFields->getPropertyDescription($fieldID);
	}

	/**
	 * @inheritDoc
	 */
	protected function getFieldName($fieldID)
	{
		return Loc::getMessage('CATALOG_FILTER_PRODUCT_DATAPROVIDER_FIELD_' . $fieldID) ?? parent::getFieldName($fieldID);
	}

	/**
	 * @inheritDoc
	 */
	public function prepareFilterValue(array $rawFilterValue): array
	{
		$rawFilterValue = parent::prepareFilterValue($rawFilterValue);

		if (!$this->isWithVariations() || empty($rawFilterValue))
		{
			return $rawFilterValue;
		}

		[$variationFilterValue, $rawFilterValue] = $this->splitPrefixFilterValues(self::VARIATION_PREFIX, $rawFilterValue);

		if (!empty($variationFilterValue))
		{
			$variationFilterValue = $this->variationFields->prepareFilterValue($variationFilterValue);

			$settings = $this->getSettings();
			$variationFilterValue['IBLOCK_ID'] = $settings->getVariationIblockId();
			$rawFilterValue['SUBQUERY'] = [
				'FIELD' => 'PROPERTY_' .  $settings->getLinkPropertyId(),
				'FILTER' => $variationFilterValue,
			];
			unset($settings);
		}

		return $rawFilterValue;
	}
}
