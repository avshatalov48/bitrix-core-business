<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\v2\Property\Property;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Component\ParameterSigner;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog;

class VariationForm extends BaseForm
{
	/** @var \Bitrix\Catalog\v2\Sku\BaseSku */
	protected $entity;

	public static function formatFieldName($name): string
	{
		return $name;
	}

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

	protected function getPropertyDescription(Property $property): array
	{
		$description = parent::getPropertyDescription($property);

		$propertyFeatureOfferTree = $property->getPropertyFeatureCollection()->findByFeatureId('OFFER_TREE');
		$offerTreeParams = $propertyFeatureOfferTree ? $propertyFeatureOfferTree->getSettings() : null;

		if ($offerTreeParams)
		{
			$description['isEnabledOfferTree'] = $offerTreeParams['IS_ENABLED'] === 'Y';
		}

		return $description;
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
		$variationIdList = null;
		$variationId = $this->params['VARIATION_ID'] ?? null;
		if (!empty($variationId))
		{
			$variationIdList = [$this->params['VARIATION_ID']];
		}

		return [
			'IBLOCK_ID' => $this->params['IBLOCK_ID'] ?? null,
			'PRODUCT_ID' => $this->params['PRODUCT_ID'] ?? null,
			'VARIATION_ID_LIST' => $variationIdList,
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

	protected function getCardSettingsItems(): array
	{
		return GridVariationForm::getGridCardSettingsItems();
	}

	protected function showCatalogProductFields(): bool
	{
		return true;
	}

	protected function showSpecificCatalogParameters(): bool
	{
		return true;
	}

	protected function buildDescriptions(): array
	{
		return array_merge(
			parent::buildDescriptions(),
			$this->getPriceDescriptions(),
			$this->getMeasureRatioDescription(),
			$this->getNameCodeDescription()
		);
	}

	protected function getPriceDescriptions(): array
	{
		$descriptions = [];
		$priceTypeList = Catalog\GroupTable::getTypeList();

		if (!empty($priceTypeList))
		{
			foreach ($priceTypeList as $priceType)
			{
				$title = !empty($priceType['NAME_LANG']) ? $priceType['NAME_LANG'] : $priceType['NAME'];
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
		if ($this->isPurchasingPriceAllowed())
		{
			$purchasingPriceDescription = $this->preparePriceDescription([
				'NAME' => $purchasingPriceFieldName.'_FIELD',
				'TYPE_ID' => 'PURCHASING_PRICE',
				'TITLE' => Loc::getMessage('CATALOG_C_F_VARIATION_SETTINGS_PURCHASING_PRICE_FIELD_TITLE'),
				'PRICE_FIELD' => $purchasingPriceFieldName,
				'CURRENCY_FIELD' => static::formatFieldName('PURCHASING_CURRENCY'),
			]);

			if (State::isUsedInventoryManagement())
			{
				$purchasingPriceDescription['editable'] = false;
			}

			$descriptions[] = $purchasingPriceDescription;
		}

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
			'editable' => $this->isPricesEditable(),
			'data' => [
				'affectedFields' => [
					$fields['PRICE_FIELD'],
					$fields['CURRENCY_FIELD'],
				],
				'currency' => [
					'name' => $fields['CURRENCY_FIELD'],
					'items' => $this->getCurrencyList(),
				],
				'amount' => $fields['PRICE_FIELD'],
				'formatted' => 'FORMATTED_'.$fields['PRICE_FIELD'].'_PRICE',
				'formattedWithCurrency' => 'FORMATTED_'.$fields['PRICE_FIELD'].'_WITH_CURRENCY',
			],
		];
	}

	protected function getMeasureRatioDescription(): array
	{
		return [
			[
				'entity' => 'measure_ratio',
				'name' => static::formatFieldName('MEASURE_RATIO'),
				'title' => Loc::getMessage('CATALOG_C_F_VARIATION_SETTINGS_MEASURE_RATIO_TITLE'),
				'type' => 'number',
				'editable' => $this->isAllowedEditFields(),
				'required' => false,
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

	protected function getHiddenPropertyCodes(): array
	{
		return [self::MORE_PHOTO];
	}

	protected function getPriceFieldValue(array $field)
	{
		if ($field['priceTypeId'] === 'PURCHASING_PRICE')
		{
			return $this->entity->getField('PURCHASING_PRICE');
		}

		$price = $this->entity
			->getPriceCollection()
			->findByGroupId($field['priceTypeId'])
		;

		return $price ? $price->getPrice() : null;
	}

	protected function getCurrencyFieldValue(array $field): string
	{
		$currency = null;

		if ($field['priceTypeId'] === 'PURCHASING_PRICE')
		{
			$currency = $this->entity->getField('PURCHASING_CURRENCY');
		}
		else
		{
			$price = $this->entity
				->getPriceCollection()
				->findByGroupId($field['priceTypeId'])
			;
			if ($price)
			{
				$currency = $price->getCurrency();
			}
		}


		return $currency ?: CurrencyManager::getBaseCurrency();
	}

	protected function getMeasureRatioFieldValue()
	{
		$measureRatio = $this->entity
			->getMeasureRatioCollection()
			->findDefault()
		;

		return $measureRatio ? $measureRatio->getRatio() : null;
	}

	protected function getAdditionalValues(array $values, array $descriptions = []): array
	{
		$additionalValues = parent::getAdditionalValues($values, $descriptions);
		foreach ($descriptions as $description)
		{
			if ($description['entity'] === 'money' && \Bitrix\Main\Loader::includeModule('currency'))
			{
				$amount = $this->getPriceFieldValue($description);
				$currency = $this->getCurrencyFieldValue($description);

				$descriptionData = $description['data'];
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

		return $fieldList;
	}
}