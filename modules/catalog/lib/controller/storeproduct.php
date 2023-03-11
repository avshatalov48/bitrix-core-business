<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;

final class StoreProduct extends Controller
{
	protected const ITEM = 'STORE_PRODUCT';
	protected const LIST = 'STORE_PRODUCTS';

	//region Actions
	public function getFieldsAction(): array
	{
		return [self::ITEM => $this->getViewFields()];
	}

	public function listAction(PageNavigation $pageNavigation, array $select = [], array $filter = [], array $order = []): Page
	{
		$accessFilter = $this->accessController->getEntityFilter(
			ActionDictionary::ACTION_STORE_VIEW,
			get_class($this->getEntityTable())
		);
		if ($accessFilter)
		{
			$filter = [
				$accessFilter,
				$filter,
			];
		}

		return new Page(
			self::LIST,
			$this->getList($select, $filter, $order, $pageNavigation),
			$this->count($filter)
		);
	}

	public function getAction($id)
	{
		$r = $this->exists($id);
		if($r->isSuccess())
		{
			return [self::ITEM => $this->get($id)];
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}
	//endregion

	protected function exists($id)
	{
		$r = new Result();
		if(isset($this->get($id)['ID']) == false)
			$r->addError(new Error('Entity is not exists'));

		return $r;
	}

	protected function getEntityTable()
	{
		return new StoreProductTable();
	}

	protected function checkReadPermissionEntity()
	{
		$r = new Result();

		if (!(
			$this->accessController->check(ActionDictionary::ACTION_CATALOG_READ)
			|| $this->accessController->check(ActionDictionary::ACTION_STORE_VIEW)
		))
		{
			$r->addError(new Error('Access Denied', 200040300010));
		}
		return $r;
	}
}
