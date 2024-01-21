<?php

namespace Bitrix\Catalog\Grid\Panel\UI\Item\Group;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\Grid\Panel\ProductGroupAction;
use Bitrix\Catalog\Grid\ProductAction;
use Bitrix\Catalog\Product\SystemField;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\VatTable;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Iblock\Grid\Panel\UI\Actions\Helpers\ItemFinder;
use Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroup\BaseGroupChild;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Grid\Panel\Actions;
use Bitrix\Main\Filter\Filter;
use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\Grid\Panel\Snippet\Onchange;
use Bitrix\Main\Grid\Panel\Types;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\Result;
use CCatalogMeasure;
use CCatalogSku;

Loader::requireModule('iblock');

final class SetParametersGroupChild extends BaseGroupChild
{
	use ItemFinder;

	private Entity $productEntity;
	private bool $isCatalogTypeProduct;

	public static function getId(): string
	{
		return 'product_field';
	}

	public function getName(): string
	{
		return Loc::getMessage('CATALOG_GRID_PANEL_UI_PRODUCT_ACTION_SET_PARAMETERS_NAME');
	}

	private function isUsedInventoryManagement(): bool
	{
		return State::isUsedInventoryManagement();
	}

	private function canChangePurchasingPrice(): bool
	{
		return AccessController::getCurrent()->check(ActionDictionary::ACTION_PRODUCT_PURCHASE_INFO_VIEW);
	}

	private function isCatalogTypeProduct(): bool
	{
		if (!isset($this->isCatalogTypeProduct))
		{
			$this->isCatalogTypeProduct = false;

			$info = CCatalogSku::GetInfoByIBlock($this->getIblockId());
			if (isset($info['CATALOG_TYPE']))
			{
				$this->isCatalogTypeProduct = $info['CATALOG_TYPE'] === CCatalogSku::TYPE_PRODUCT;
			}
		}

		return $this->isCatalogTypeProduct;
	}

	private function getRequestFields(HttpRequest $request): ?array
	{
		$controls = $request->getPost('controls');
		if (!is_array($controls))
		{
			return null;
		}

		$fieldName = $controls[self::getId()] ?? null;
		$fieldValue = $controls[$fieldName] ?? null;
		if (empty($fieldName) || !is_string($fieldName) || !isset($fieldValue))
		{
			return null;
		}
		elseif (!$this->isAvailableField($fieldName))
		{
			return null;
		}

		$fields = [
			$fieldName => $fieldValue,
		];

		if ($fieldName !== 'PURCHASING_PRICE')
		{
			return $fields;
		}

		$fieldValue =  $controls['PURCHASING_CURRENCY'] ?? null;
		if (empty($fieldValue) || !is_string($fieldValue))
		{
			return null;
		}
		$fields['PURCHASING_CURRENCY'] = $fieldValue;

		return $fields;
	}

	public function processRequest(HttpRequest $request, bool $isSelectedAllRows, ?Filter $filter = null): ?Result
	{
		$result = new Result();

		$fields = $this->getRequestFields($request);
		if ($fields === null)
		{
			return null;
		}

		[$elementIds, $sectionIds] = $this->prepareItemIds($request, $isSelectedAllRows, $filter);

		if ($elementIds)
		{
			$result->addErrors(
				ProductAction::updateElementList($this->getIblockId(), $elementIds, $fields)->getErrors()
			);
		}

		if ($sectionIds)
		{
			$result->addErrors(
				ProductAction::updateSectionList($this->getIblockId(), $sectionIds, $fields)->getErrors()
			);
		}

		return $result;
	}

	protected function getOnchange(): Onchange
	{
		$confirmMessage = Loc::getMessage('CATALOG_GRID_PANEL_UI_PRODUCT_ACTION_SET_PARAMETERS_CONFIRM');

		return new Onchange([
			[
				'ACTION' => Actions::RESET_CONTROLS,
			],
			[
				'ACTION' => Actions::CREATE,
				'DATA' => [
					$this->getParametersDropdownControl(),
					(new Snippet)->getSendSelectedButton($confirmMessage),
				],
			],
		]);
	}

	private function getProductEntity(): Entity
	{
		$this->productEntity ??= ProductTable::getEntity();

		return $this->productEntity;
	}

	private function getParametersDropdownControl(): array
	{
		$items = [];

		if (!$this->isCatalogTypeProduct())
		{
			$items[] = $this->getInputDropdownItem('WEIGHT');
			$items[] = $this->getSelectDropdownItem('MEASURE', $this->getMeasureDropdownItems());
			$items[] = $this->getSelectDropdownItem('SUBSCRIBE', $this->getStatusDropdownItems(
				Option::get('catalog', 'default_subscribe') !== 'N'
			));

			$vatItems = $this->getVatIdDropdownItems();
			if (!empty($vatItems))
			{
				$items[] = $this->getSelectDropdownItem('VAT_ID', $vatItems);
			}
			$items[] = $this->getSelectDropdownItem('VAT_INCLUDED', $this->getStatusDropdownItems());

			if (!$this->isUsedInventoryManagement())
			{
				$items[] = $this->getInputDropdownItem('QUANTITY');
				$items[] = $this->getSelectDropdownItem('QUANTITY_TRACE', $this->getStatusDropdownItems(
					Option::get('catalog', 'default_quantity_trace') === 'Y'
				));
				$items[] = $this->getSelectDropdownItem('CAN_BUY_ZERO', $this->getStatusDropdownItems(
					Option::get('catalog', 'default_can_buy_zero') === 'Y'
				));

				if ($this->canChangePurchasingPrice())
				{
					$row = $this->getPriceDropdownItem([
						'VALUE' => 'PURCHASING_PRICE',
						'UNIT' => 'PURCHASING_CURRENCY',
					]);
					if ($row !== null)
					{
						$items[] = $row;
					}
					unset($row);
				}
			}
		}

		$items = $this->appendSystemFieldItems($items);

		return [
			'ID' => 'product_field',
			'NAME' => self::getId(),
			'TYPE' => Types::DROPDOWN,
			'ITEMS' => $items,
		];
	}

	private function appendSystemFieldItems(array $items): array
	{
		$options = [
			'ENTITY_ID' => '',
			'IBLOCK_ID' => $this->getIblockId(),
		];

		$productGroupAction = new class($options) extends ProductGroupAction
		{
			public function getFormRowFieldName(string $field): string
			{
				return $field;
			}
		};

		$systemFieldsItems = SystemField::getGroupActions($productGroupAction);
		if (!empty($systemFieldsItems))
		{
			array_push($items, ...$systemFieldsItems);
		}

		return $items;
	}

	private function getInputDropdownItem(string $fieldName): array
	{
		$field = $this->getProductEntity()->getField($fieldName);

		return [
			'VALUE' => $fieldName,
			'NAME' => $field->getTitle(),
			'ONCHANGE' => [
				[
					'ACTION' => Actions::RESET_CONTROLS,
				],
				[
					'ACTION' => Actions::CREATE,
					'DATA' => [
						[
							'ID' => 'product_field_text_' . $fieldName,
							'NAME' => $fieldName,
							'TYPE' => Types::TEXT,
							'VALUE' => '',
						],
					],
				],
			],
		];
	}

	private function getSelectDropdownItem(string $fieldName, array $dropdownItems): array
	{
		$field = $this->getProductEntity()->getField($fieldName);

		return [
			'VALUE' => $fieldName,
			'NAME' => $field->getTitle(),
			'ONCHANGE' => [
				[
					'ACTION' => Actions::RESET_CONTROLS,
				],
				[
					'ACTION' => Actions::CREATE,
					'DATA' => [
						[
							'ID' => 'product_field_dropdown_' . $fieldName,
							'NAME' => $fieldName,
							'TYPE' => Types::DROPDOWN,
							'VALUE' => '',
							'ITEMS' => $dropdownItems,
						],
					],
				],
			],
		];
	}

	private function getPriceDropdownItem(array $fields): ?array
	{
		if (!isset($fields['VALUE']) || !isset($fields['UNIT']))
		{
			return null;
		}
		$fieldName = $fields['VALUE'];
		$field = $this->getProductEntity()->getField($fieldName);

		$currencyItems = [];
		foreach (CurrencyManager::getCurrencyList() as $currencyId => $currencyName)
		{
			$currencyItems[] = [
				'VALUE' => $currencyId,
				'NAME' => $currencyName
			];
		}

		return [
			'VALUE' => $fieldName,
			'NAME' => $field->getTitle(),
			'ONCHANGE' => [
				[
					'ACTION' => Actions::RESET_CONTROLS,
				],
				[
					'ACTION' => Actions::CREATE,
					'DATA' => [
						[
							'ID' => 'product_field_price_' . $fieldName,
							'NAME' => $fieldName,
							'TYPE' => Types::TEXT,
							'VALUE' => '',
						],
						[
							'ID' => 'product_field_currency_' . $fieldName,
							'NAME' => $fields['UNIT'],
							'TYPE' => Types::DROPDOWN,
							'VALUE' => '',
							'ITEMS' => $currencyItems,
						],
					],
				],
			],
		];
	}

	private function getStatusDropdownItems(?bool $default = null): array
	{
		$result = [];

		if (isset($default))
		{
			$result[] = [
				'NAME' => Loc::getMessage('CATALOG_GRID_PANEL_UI_PRODUCT_ACTION_SET_PARAMETERS_DEFAULT_VALUE', [
					'#VALUE#' => $default ? Loc::getMessage('MAIN_YES') : Loc::getMessage('MAIN_NO'),
				]),
				'VALUE' => ProductTable::STATUS_DEFAULT,
			];
		}

		$result[] = [
			'NAME' => Loc::getMessage('MAIN_YES'),
			'VALUE' => ProductTable::STATUS_YES,
		];
		$result[] = [
			'NAME' => Loc::getMessage('MAIN_NO'),
			'VALUE' => ProductTable::STATUS_NO,
		];

		return $result;
	}

	private function getVatIdDropdownItems(): array
	{
		$result = [];

		$rows = VatTable::getList([
			'select' => [
				'ID',
				'NAME',
			],
			'filter' => [
				'=ACTIVE' => 'Y',
			],
			'order' => [
				'SORT' => 'ASC',
				'ID' => 'ASC',
			],
		]);

		if ($rows->getSelectedRowsCount() > 0)
		{
			$result[] = [
				'VALUE' => '0',
				'NAME' => Loc::getMessage('CATALOG_GRID_PANEL_UI_PRODUCT_ACTION_SET_PARAMETERS_NOT_SELECTED'),
			];
		}

		foreach ($rows as $row)
		{
			$result[] = [
				'VALUE' => $row['ID'],
				'NAME' => $row['NAME'],
			];
		}

		return $result;
	}

	private function getMeasureDropdownItems(): array
	{
		$result = [];

		$rows = CCatalogMeasure::getList();
		while ($row = $rows->Fetch())
		{
			$result[] = [
				'VALUE' => $row['ID'],
				'NAME' => $row['MEASURE_TITLE'],
			];
		}

		return $result;
	}

	private function isAvailableField(string $fieldName): bool
	{
		$dropdownItems = $this->getParametersDropdownControl()['ITEMS'];
		$dropdownItemsNames = array_column($dropdownItems, 'VALUE');

		return in_array($fieldName, $dropdownItemsNames, true);
	}
}
