<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Catalog\ProductTable;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Component\ParameterSigner;

class VariationForm extends BaseForm
{
	/** @var \Bitrix\Catalog\v2\Sku\BaseSku */
	protected $entity;

	public function getControllers(): array
	{
		$controllers = parent::getControllers();

		$controllers[] = [
			'name' => 'VARIATION_GRID_CONTROLLER',
			'type' => 'variation_grid',
			'config' => [
				'reloadUrl' => '/bitrix/components/bitrix/catalog.productcard.variation.grid/list.ajax.php',
				'signedParameters' => $this->getVariationGridSignedParameters(),
				'gridId' => $this->getVariationGridId(),
			],
		];
		$controllers[] = [
			'name' => 'IBLOCK_SECTION_CONTROLLER',
			'type' => 'iblock_section',
			'config' => [],
		];

		return $controllers;
	}

	public function collectFieldConfigs(): array
	{
		$config = parent::collectFieldConfigs();

		$config['right']['elements'][] = [
			'name' => 'variation_grid',
			'title' => 'Variation grid',
			'type' => 'included_area',
			'data' => [
				'isRemovable' => false,
				'type' => 'component',
				'componentName' => $this->getVariationGridComponentName(),
				'action' => 'getProductGrid',
				'mode' => 'ajax',
				'signedParametersName' => 'VARIATION_GRID_SIGNED_PARAMETERS',
			],
			'sort' => 100,
		];

		return $config;
	}

	protected function getVariationGridComponentName(): string
	{
		return 'bitrix:catalog.productcard.variation.grid';
	}

	protected function getVariationGridParameters(): array
	{
		return [
			'IBLOCK_ID' => $this->params['IBLOCK_ID'],
			'PRODUCT_ID' => $this->params['PRODUCT_ID'],
			'VARIATION_ID_LIST' => [
				$this->params['VARIATION_ID']
			],
			'COPY_PRODUCT_ID' => $this->params['COPY_PRODUCT_ID'] ?? null,
			'EXTERNAL_FIELDS' => $this->params['EXTERNAL_FIELDS'] ?? null,
			'PATH_TO' => $this->params['PATH_TO'] ?? [],
		];
	}

	protected function getVariationGridSignedParameters(): string
	{
		return ParameterSigner::signParameters(
			$this->getVariationGridComponentName(),
			$this->getVariationGridParameters()
		);
	}

	protected function showCatalogProductFields(): bool
	{
		return true;
	}

	protected function showSpecificCatalogParameters(): bool
	{
		return true;
	}

	public static function formatFieldName($name): string
	{
		return $name;
	}

	protected function buildDescriptions(): array
	{
		return array_merge(
			parent::buildDescriptions(),
			$this->getPriceDescriptions(),
			$this->getMeasureRatioDescription()
		);
	}

	protected function getPriceDescriptions(): array
	{
		$descriptions = [];
		$priceTypeList = \CCatalogGroup::GetListArray();

		if (!empty($priceTypeList))
		{
			foreach ($priceTypeList as $priceType)
			{
				$title = htmlspecialcharsbx(!empty($priceType['NAME_LANG']) ? $priceType['NAME_LANG'] : $priceType['NAME']);

				if ($priceType['BASE'] === 'Y')
				{
					$basePriceFieldName = static::formatFieldName(BaseForm::PRICE_FIELD_PREFIX.'BASE');
					$descriptions[] = $this->preparePriceDescription([
						'NAME' => $basePriceFieldName.'_FIELD',
						'TYPE_ID' => (int)$priceType['ID'],
						'TITLE' => Loc::getMessage(
							'CATALOG_C_F_VARIATION_SETTINGS_BASE_PRICE',
							['#PRICE_NAME#' => $title]
						),
						'PRICE_FIELD' => $basePriceFieldName,
						'CURRENCY_FIELD' => static::formatFieldName(BaseForm::CURRENCY_FIELD_PREFIX.'BASE'),
					]);
				}

				$priceFieldName = static::formatFieldName(BaseForm::PRICE_FIELD_PREFIX.$priceType['ID']);
				$descriptions[] = $this->preparePriceDescription([
					'NAME' => $priceFieldName.'_FIELD',
					'TYPE_ID' => (int)$priceType['ID'],
					'TITLE' => $title,
					'PRICE_FIELD' => $priceFieldName,
					'CURRENCY_FIELD' => static::formatFieldName(BaseForm::CURRENCY_FIELD_PREFIX.$priceType['ID']),
				]);
			}
		}

		$purchasingPriceFieldName = static::formatFieldName('PURCHASING_PRICE');
		$descriptions[] = $this->preparePriceDescription([
			'NAME' => $purchasingPriceFieldName.'_FIELD',
			'TYPE_ID' => 'PURCHASING_PRICE',
			'TITLE' => Loc::getMessage('CATALOG_C_F_VARIATION_SETTINGS_PURCHASING_PRICE_FIELD_TITLE'),
			'PRICE_FIELD' => $purchasingPriceFieldName,
			'CURRENCY_FIELD' => static::formatFieldName('PURCHASING_CURRENCY'),
		]);

		return $descriptions;
	}

	protected function preparePriceDescription(array $fields = []): array
	{
		return [
			'name' => $fields['NAME'],
			'title' => $fields['TITLE'],
			'type' => 'money',
			'entity' => 'money',
			'priceTypeId' => $fields['TYPE_ID'],
			'editable' => true,
			'data' => [
				'affectedFields' => [
					$fields['PRICE_FIELD'],
					$fields['CURRENCY_FIELD'],
				],
				'currency' => [
					'name' => $fields['CURRENCY_FIELD'],
					'items' => self::getDescriptionCurrencyList(),
				],
				'amount' => $fields['PRICE_FIELD'],
				'formatted' => 'FORMATTED_'.$fields['PRICE_FIELD'].'_PRICE',
				'formattedWithCurrency' => 'FORMATTED_'.$fields['PRICE_FIELD'].'_WITH_CURRENCY',
			],
		];
	}

	private static function getDescriptionCurrencyList()
	{
		static $currencyList = null;
		if (!$currencyList)
		{
			$currencyList = [];
			foreach (CurrencyManager::getCurrencyList() as $currency => $currencyName)
			{
				$currencyList[] = [
					'VALUE' => $currency,
					'NAME' => htmlspecialcharsbx($currencyName),
				];
			}
		}

		return $currencyList;
	}

	protected function getMeasureRatioDescription(): array
	{
		return [
			[
				'entity' => 'measure_ratio',
				'name' => static::formatFieldName('MEASURE_RATIO'),
				'title' => Loc::getMessage('CATALOG_C_F_VARIATION_SETTINGS_MEASURE_RATIO_TITLE'),
				'type' => 'number',
				'editable' => true,
				'required' => true,
				'defaultValue' => 1,
			],
		];
	}

	protected function getFieldValue(array $field)
	{
		if ($field['entity'] === 'price')
		{
			return $this->getPriceFieldValue($field);
		}

		if ($field['entity'] === 'currency')
		{
			return $this->getCurrencyFieldValue($field);
		}

		if ($field['entity'] === 'measure_ratio')
		{
			return $this->getMeasureRatioFieldValue();
		}

		return parent::getFieldValue($field);
	}

	protected function getPriceFieldValue(array $field)
	{
		$price = $this->entity
			->getPriceCollection()
			->findByGroupId($field['priceTypeId'])
		;

		return $price ? $price->getPrice() : null;
	}

	protected function getCurrencyFieldValue(array $field)
	{
		$price = $this->entity
			->getPriceCollection()
			->findByGroupId($field['priceTypeId'])
		;

		return $price ? $price->getCurrency() : \Bitrix\Currency\CurrencyManager::getBaseCurrency();
	}

	protected function getMeasureRatioFieldValue()
	{
		$measureRatio = $this->entity
			->getMeasureRatioCollection()
			->findDefault()
		;

		return $measureRatio ? $measureRatio->getRatio() : null;
	}

	protected function getAdditionalValues(array $values): array
	{
		$additionalValues = parent::getAdditionalValues($values);
		foreach ($this->getDescriptions() as $description)
		{
			if ($description['entity'] === 'money' && \Bitrix\Main\Loader::includeModule('currency'))
			{
				$descriptionData = $description['data'];
				if ($description['priceTypeId'] === 'PURCHASING_PRICE')
				{
					$amount = $values[static::formatFieldName('PURCHASING_PRICE')];
					$currency = $values[static::formatFieldName('PURCHASING_CURRENCY')];
				}
				else
				{
					$amount = $this->getPriceFieldValue($description);
					$currency = $this->getCurrencyFieldValue($description);
				}

				$additionalValues[$descriptionData['currency']['name']] = $currency;
				$additionalValues[$descriptionData['amount']] = $amount;
				$additionalValues[$descriptionData['formatted']] = \CCurrencyLang::CurrencyFormat($amount, $currency, false);
				$additionalValues[$descriptionData['formattedWithCurrency']] = \CCurrencyLang::CurrencyFormat($amount, $currency, true);
			}
		}

		$additionalValues['VARIATION_GRID_SIGNED_PARAMETERS'] = $this->getVariationGridSignedParameters();

		return $additionalValues;
	}

	protected function getCatalogProductFieldsList(): array
	{
		$fieldList = parent::getCatalogProductFieldsList();
		$fieldList[] = 'AVAILABLE';
		$fieldList[] = 'PURCHASING_PRICE';
		$fieldList[] = 'PURCHASING_CURRENCY';

		return $fieldList;
	}
}