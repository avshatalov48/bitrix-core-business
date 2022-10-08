<?php

namespace Bitrix\Catalog\Controller\Product;

use Bitrix\Catalog\Controller\Product;
use Bitrix\Catalog\ProductTable;
use Bitrix\Main\Engine;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;

class Sku extends Product
{
	protected const ITEM = 'SKU';
	protected const LIST = 'UNITS';

	protected function processBeforeAction(Engine\Action $action)
	{
		$arguments = $action->getArguments();
		$name = $action->getName();

		if($name == 'getfieldsbyfilter')
		{
			$arguments['filter']['productType'] = ProductTable::TYPE_SKU;
			$action->setArguments($arguments);
		}

		return parent::processBeforeAction($action);
	}

	protected function processBeforeUpdate(Engine\Action $action): Result
	{
		$r = parent::processBeforeUpdate($action);

		if ($r->isSuccess())
		{
			$arguments = $action->getArguments();
			$fields = $arguments['fields'];

			/** @var \Bitrix\Catalog\RestView\Product $view */
			$view = $this->getViewManager()->getView($this);
			$r = $view->isAllowedProductTypeByIBlockId(ProductTable::TYPE_SKU, $fields['iblockId']);
		}

		return $r;
	}

	public function addAction($fields): ?array
	{
		/** @var \Bitrix\Catalog\RestView\Product $view */
		$view = $this->getViewManager()->getView($this);
		$r = $view->isAllowedProductTypeByIBlockId(ProductTable::TYPE_EMPTY_SKU, $fields['IBLOCK_ID']);
		if (!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}

		$fields['TYPE'] = ProductTable::TYPE_EMPTY_SKU;

		return parent::addAction($fields);
	}

	public function listAction($select = [], $filter = [], $order = [], PageNavigation $pageNavigation): ?Page
	{
		/** @var \Bitrix\Catalog\RestView\Product $view */
		$view = $this->getViewManager()->getView($this);
		$r = $view->isAllowedProductTypeByIBlockId(ProductTable::TYPE_SKU, $filter['IBLOCK_ID']);
		if (!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}

		$list = [ProductTable::TYPE_SKU, ProductTable::TYPE_EMPTY_SKU];

		if (isset($filter['TYPE']))
		{
			$filter['TYPE']  = in_array($filter['TYPE'], $list) ? $filter['TYPE']:$list;
		}
		else
		{
			$filter['TYPE'] = $list;
		}

		return parent::listAction($select, $filter, $order, $pageNavigation);
	}
}