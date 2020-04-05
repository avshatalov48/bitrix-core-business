<?php


namespace Bitrix\Catalog\Controller;


use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;

final class Store extends Controller
{
	//region Actions
	public function getFieldsAction()
	{
		$view = $this->getViewManager()
			->getView($this);

		return ['STORE'=>$view->prepareFieldInfos(
			$view->getFields()
		)];
	}

	public function listAction($select=[], $filter=[], $order=[], PageNavigation $pageNavigation)
	{
		return new Page('STORES',
			$this->getList($select, $filter, $order, $pageNavigation),
			$this->count($filter)
		);
	}

	public function getAction($id)
	{
		$r = $this->exists($id);
		if($r->isSuccess())
		{
			return ['STORE'=>$this->get($id)];
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
			$r->addError(new Error('Store is not exists'));

		return $r;
	}

	protected function getEntityTable()
	{
		return new StoreTable();
	}

	protected function checkModifyPermissionEntity()
	{
		$r = $this->checkReadPermissionEntity();
		if($r->isSuccess())
		{
			if (!static::getGlobalUser()->CanDoOperation('catalog_store'))
			{
				$r->addError(new Error('Access Denied', 200040300020));
			}
		}

		return $r;
	}

	protected function checkReadPermissionEntity()
	{
		$r = new Result();

		if (!(static::getGlobalUser()->CanDoOperation('catalog_read') || static::getGlobalUser()->CanDoOperation('catalog_store')))
		{
			$r->addError(new Error('Access Denied', 200040300010));
		}
		return $r;
	}
}