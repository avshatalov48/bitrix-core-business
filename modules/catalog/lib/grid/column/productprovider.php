<?php

namespace Bitrix\Catalog\Grid\Column;

use Bitrix\Main\Grid;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Catalog;
use Bitrix\Catalog\Access;
use Bitrix\Catalog\VatTable;
use Bitrix\Main\Grid\Column\Editable\ListConfig;
use Bitrix\Main\Grid\Column\Editable\MoneyConfig;
use CCatalogMeasure;

class ProductProvider extends CatalogProvider
{
	public function prepareColumns(): array
	{
		$useSkuSelector = $this->isSkuSelectorEnabled();

		$result = [];

		$result['TYPE'] = [
			'type' => Grid\Column\Type::DROPDOWN,
			'name' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_TYPE'),
			'title' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_TITLE_TYPE'),
			'necessary' => true,
			'editable' => false,
			'multiple' => false,
			'select' => [
				'TYPE',
				'BUNDLE',
			],
			'sort' => 'TYPE',
			'align' => 'right',
		];

		$result['AVAILABLE'] = [
			'type' => Grid\Column\Type::CHECKBOX,
			'name' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_AVAILABLE'),
			'title' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_TITLE_AVAILABLE'),
			'necessary' => true,
			'editable' => false,
			'multiple' => false,
			'sort' => 'AVAILABLE',
			'align' => 'center',
		];

		if ($useSkuSelector)
		{
			$result['PRODUCT'] = [
				'type' => Grid\Column\Type::CUSTOM,
				'name' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_PRODUCT'),
				'title' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_TITLE_PRODUCT'),
				'necessary' => true,
				'editable' => false,
				'multiple' => false,
				'select' => [],
				'sort' => 'NAME',
				'align' => 'left',
				'width' => 420, // TODO: enable support
			];
		}
		else
		{
			$result['PRODUCT'] = [
				'type' => Grid\Column\Type::TEXT,
				'name' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_PRODUCT'),
				'title' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_TITLE_PRODUCT'),
				'editable' => new Grid\Column\Editable\Config('NAME'),
				'sort' => 'NAME',
			];
		}

		$result = array_merge(
			$result,
			$this->getQuantityColumnsDescription(),
			$this->getPhysicalColumsDescription(),
			$this->getVatColumnsDescription(),
			$this->getPurchasingPriceColumnDescription()
		);

		return $this->createColumns($result);
	}

	public static function allowedShowQuantityColumns(): bool
	{
		if (!Loader::includeModule('crm'))
		{
			return true;
		}

		if (!Catalog\Config\State::isUsedInventoryManagement())
		{
			return true;
		}
		$allowedStores = Catalog\Access\AccessController::getCurrent()->getPermissionValue(
			Catalog\Access\ActionDictionary::ACTION_STORE_VIEW
		);
		if (!empty($allowedStores))
		{
			return true;
		}

		return false;
	}

	/**
	 * Returns true, if enable inventory managment and current user not have full store access.
	 *
	 * @return bool
	 */
	public static function needSummaryStoreAmountByPermissions(): bool
	{
		if (!Loader::includeModule('crm'))
		{
			return false;
		}

		if (!Catalog\Config\State::isUsedInventoryManagement())
		{
			return false;
		}

		$allowedStores = Access\AccessController::getCurrent()->getPermissionValue(
			Access\ActionDictionary::ACTION_STORE_VIEW
		);
		if (
			is_array($allowedStores)
			&& in_array(Access\Permission\PermissionDictionary::VALUE_VARIATION_ALL, $allowedStores, true)
		)
		{
			return false;
		}

		return true;
	}

	protected function getQuantityColumnsDescription(): array
	{
		$useSkuSelector = $this->isSkuSelectorEnabled();
		$allowProductEdit = $this->allowProductEdit();
		$useInventoryManagment = Catalog\Config\State::isUsedInventoryManagement();

		$result = [];

		if (static::allowedShowQuantityColumns())
		{
			$result['QUANTITY'] = [
				'type' => Grid\Column\Type::FLOAT,
				'name' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_PRODUCT_QUANTITY'),
				'necessary' => false,
				'editable' => $allowProductEdit && !$useInventoryManagment,
				'multiple' => false,
				'sort' => $useSkuSelector || static::needSummaryStoreAmountByPermissions() ? null : 'QUANTITY',
				'align' => 'right',
			];

			$result['QUANTITY_RESERVED'] = [
				'type' => Grid\Column\Type::FLOAT,
				'name' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_PRODUCT_QUANTITY_RESERVED'),
				'necessary' => false,
				'editable' => $allowProductEdit && !$useInventoryManagment,
				'multiple' => false,
				'sort' => null,
				'align' => 'right',
			];
		}

		$result['MEASURE'] = [
			'type' => Grid\Column\Type::DROPDOWN,
			'name' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_MEASURE'),
			'title' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_TITLE_MEASURE'),
			'necessary' => false,
			'editable' => $allowProductEdit ? $this->getMeasureEditable() : false,
			'multiple' => false,
			'sort' => $useSkuSelector ? null : 'MEASURE',
			'align' => 'right',
		];

		$result['QUANTITY_TRACE'] = [
			'type' => Grid\Column\Type::CHECKBOX,
			'name' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_QUANTITY_TRACE'),
			'title' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_TITLE_QUANTITY_TRACE'),
			'necessary' => false,
			'editable' => $allowProductEdit,
			'multiple' => false,
			'sort' => null,
			'align' => 'right',
		];

		$result['CAN_BUY_ZERO'] = [
			'type' => Grid\Column\Type::CHECKBOX,
			'name' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_CAN_BUY_ZERO'),
			'title' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_TITLE_CAN_BUY_ZERO'),
			'necessary' => false,
			'editable' => $allowProductEdit,
			'multiple' => false,
			'sort' => null,
			'align' => 'right',
		];

		return $result;
	}

	protected function getPhysicalColumsDescription(): array
	{
		$useSkuSelector = $this->isSkuSelectorEnabled();
		$allowProductEdit = $this->allowProductEdit();

		$result = [];

		$result['WEIGHT'] = [
			'type' => Grid\Column\Type::FLOAT,
			'name' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_WEIGHT'),
			'necessary' => false,
			'editable' => $allowProductEdit,
			'multiple' => false,
			'sort' => $useSkuSelector ? null : 'WEIGHT',
			'align' => 'right',
		];

		$result['WIDTH'] = [
			'type' => Grid\Column\Type::FLOAT,
			'name' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_WIDTH'),
			'necessary' => false,
			'editable' => $allowProductEdit,
			'multiple' => false,
			'sort' => $useSkuSelector ? null : 'WIDTH',
			'align' => 'right',
		];

		$result['LENGTH'] = [
			'type' => Grid\Column\Type::FLOAT,
			'name' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_LENGTH'),
			'necessary' => false,
			'editable' => $allowProductEdit,
			'multiple' => false,
			'sort' => $useSkuSelector ? null : 'LENGTH',
			'align' => 'right',
		];

		$result['HEIGHT'] = [
			'type' => Grid\Column\Type::FLOAT,
			'name' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_HEIGHT'),
			'necessary' => false,
			'editable' => $allowProductEdit,
			'multiple' => false,
			'sort' => $useSkuSelector ? null : 'HEIGHT',
			'align' => 'right',
		];

		return $result;
	}

	protected function getVatColumnsDescription(): array
	{
		$allowProductEdit = $this->allowProductEdit();

		$result = [];

		$result['VAT_INCLUDED'] = [
			'type' => Grid\Column\Type::CHECKBOX,
			'name' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_VAT_INCLUDED'),
			'necessary' => false,
			'editable' => $allowProductEdit,
			'multiple' => false,
			'sort' => null,
			'align' => 'right',
		];

		$result['VAT_ID'] = [
			'type' => Grid\Column\Type::DROPDOWN,
			'name' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_VAT_ID'),
			'necessary' => false,
			'editable' => $allowProductEdit ? $this->getVatEditable() : false,
			'multiple' => false,
			'align' => 'right',
			'partial' => true,
		];

		return $result;
	}

	protected function getPurchasingPriceColumnDescription(): array
	{
		if (!$this->accessController->check(Access\ActionDictionary::ACTION_PRODUCT_PURCHASE_INFO_VIEW))
		{
			return [];
		}

		$result = [];

		$result['PURCHASING_PRICE'] = [
			'type' => Grid\Column\Type::MONEY,
			'name' => Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_PURCHASING_PRICE'),
			'necessary' => false,
			'editable' => Catalog\Config\State::isUsedInventoryManagement() ? false : new MoneyConfig('PURCHASING_PRICE'),
			'multiple' => false,
			'select' => [
				'PURCHASING_PRICE',
				'PURCHASING_CURRENCY',
			],
			'sort' => 'PURCHASING_PRICE',
			'align' => 'right',
		];

		return $result;
	}

	private function getMeasureEditable(): Grid\Column\Editable\ListConfig
	{
		$items = [];

		$rows = CCatalogMeasure::getList();
		while ($row = $rows->Fetch())
		{
			$items[$row['ID']] = $row['MEASURE_TITLE'];
		}

		return new ListConfig('MEASURE', $items);
	}

	private function getVatEditable(): Grid\Column\Editable\ListConfig
	{
		$items = [];

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
			$items['0'] = Loc::getMessage('PRODUCT_COLUMN_PROVIDER_FIELD_VAT_EDITABLE_ITEMS_NOT_SELECTED');
		}

		foreach ($rows as $row)
		{
			$id = $row['ID'];
			$items[$id] = $row['NAME'];
		}

		return new ListConfig('VAT_ID', $items);
	}
}
