<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Grid\Editor\Types;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog\v2\Property\Property;

class GridServiceForm extends GridVariationForm
{
	protected function buildDescriptions(): array
	{
		$result = parent::buildDescriptions();

		return $this->modifyDescriptions($result);
	}

	protected function getPropertyDescription(Property $property): array
	{
		$description = parent::getPropertyDescription($property);
		if ($property->getCode() === BaseForm::MORE_PHOTO)
		{
			$description['title'] = Loc::getMessage('CATALOG_SERVICE_CARD_VARIATION_GRID_HEADER_NAME_MORE_PHOTO');
		}
		$description['isEnabledOfferTree'] = false;

		return $description;
	}

	protected function buildIblockPropertiesDescriptions(): array
	{
		$propertyDescriptions = [];

		foreach ($this->entity->getPropertyCollection() as $property)
		{
			if ($property->isActive() && $property->getCode() === BaseForm::MORE_PHOTO)
			{
				$propertyDescriptions[] = $this->getPropertyDescription($property);
			}
		}

		return $propertyDescriptions;
	}

	public function loadGridHeaders(): array
	{
		$defaultWidth = 130;

		$headers = [];

		$headers = array_merge(
			$headers,
			$this->getIblockPropertiesHeaders(),
			$this->getProductFieldHeaders(
				[
					'ACTIVE',
					'AVAILABLE',
					'MEASURE',
				],
				$defaultWidth
			),
			$this->getPurchasingPriceHeaders($defaultWidth),
			$this->getPricesHeaders($defaultWidth),
			$this->getProductFieldHeaders(
				[
					'VAT_ID', 'VAT_INCLUDED',
					'SHOW_COUNTER', 'CODE', 'TIMESTAMP_X', 'MODIFIED_BY',
					'DATE_CREATE', 'XML_ID',
				],
				$defaultWidth
			)
		);

		self::$headers = $headers;

		return $headers;
	}

	protected function getProductFieldHeaders(array $fields, int $defaultWidth): array
	{
		$result = parent::getProductFieldHeaders($fields, $defaultWidth);

		return $this->modifyProductFieldHeaders($result);
	}

	protected function modifyProductFieldHeaders(array $headers): array
	{
		$index = $this->getIndexFieldDescription($headers, 'id', static::formatFieldName('AVAILABLE'));
		if ($index !== null)
		{
			$row = $headers[$index];
			$row['editable'] = $this->isAllowedEditFields()
				? [
					'TYPE' => Types::CHECKBOX,
				]
				: false;
			$row['default'] = true;
			$headers[$index] = $row;
		}

		return array_values($headers);
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
					$this->isAllowedEditFields()
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

	public function getVariationGridId(): string
	{
		$iblockInfo = ServiceContainer::getIblockInfo($this->entity->getIblockId());

		if ($iblockInfo)
		{
			return 'catalog-product-service-grid-' . $iblockInfo->getProductIblockId();
		}

		return 'catalog-product-service-grid';
	}

	public static function getGridCardSettingsItems(): array
	{
		$result = [];
		$result['VAT_INCLUDED'] = [
			'ITEMS' => [
				static::formatFieldName('VAT_ID'),
				static::formatFieldName('VAT_INCLUDED'),
			],
			'TITLE' => Loc::getMessage('CATALOG_SERVICE_CARD_VARIATION_GRID_SETTINGS_TITLE_VAT_INCLUDED'),
			'DESCRIPTION' => Loc::getMessage('CATALOG_SERVICE_CARD_VARIATION_GRID_SETTINGS_DESC_VAT_INCLUDED'),
		];
		if (AccessController::getCurrent()->check(ActionDictionary::ACTION_PRODUCT_PURCHASE_INFO_VIEW))
		{
			$result['PURCHASING_PRICE_FIELD'] = [
				'ITEMS' => [
					static::formatFieldName('PURCHASING_PRICE_FIELD'),
				],
				'TITLE' => Loc::getMessage('CATALOG_SERVICE_CARD_VARIATION_GRID_SETTINGS_TITLE_PURCHASING_PRICE'),
				'DESCRIPTION' => Loc::getMessage('CATALOG_SERVICE_CARD_VARIATION_GRID_SETTINGS_DESC_PURCHASING_PRICE'),
			];
		}

		return $result;
	}

	protected static function getHeaderIdsBySettingId(string $settingId): array
	{
		$headers = [];
		switch ($settingId)
		{
			case 'PURCHASING_PRICE_FIELD':
				if (AccessController::getCurrent()->check(ActionDictionary::ACTION_PRODUCT_PURCHASE_INFO_VIEW))
				{
					$headers = [
						'PURCHASING_PRICE_FIELD',
					];
				}
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

	protected static function getHeaderName(string $code): array
	{
		$headerName = Loc::getMessage('CATALOG_SERVICE_CARD_VARIATION_GRID_HEADER_NAME_' . $code);
		$headerTitle = Loc::getMessage('CATALOG_SERVICE_CARD_VARIATION_GRID_HEADER_TITLE_' . $code);

		return [
			'NAME' => $headerName,
			'TITLE' => $headerTitle ?? $headerName,
		];
	}

	private function modifyDescriptions(array $descriptions): array
	{
		$descriptions = $this->modifyAvailableDescription($descriptions);

		return array_values($descriptions);
	}

	private function modifyAvailableDescription(array $descriptions): array
	{
		$index = $this->getIndexFieldDescription($descriptions, 'originalName', 'AVAILABLE');
		if ($index !== null)
		{
			$row = $descriptions[$index];
			$row['editable'] = $this->isAllowedEditFields();
			$row['defaultValue'] = 'Y';
			$descriptions[$index] = $row;
		}

		return $descriptions;
	}

	private function getIndexFieldDescription(array $description, string $key, string $fieldName): ?int
	{
		$result = null;

		foreach (array_keys($description) as $index)
		{
			if ($description[$index][$key] === $fieldName)
			{
				$result = $index;
				break;
			}
		}

		return $result;
	}
}
