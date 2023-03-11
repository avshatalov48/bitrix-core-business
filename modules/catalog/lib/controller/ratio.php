<?php


namespace Bitrix\Catalog\Controller;


use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\MeasureRatioTable;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;

final class Ratio extends Controller
{
	//region Actions
	public function getFieldsAction(): array
	{
		return ['RATIO' => $this->getViewFields()];
	}

	/**
	 * @param $select
	 * @param $filter
	 * @param $order
	 * @param PageNavigation $pageNavigation
	 * @return Page
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function listAction(PageNavigation $pageNavigation, array $select = [], array $filter = [], array $order = []): Page
	{
		return new Page(
			'RATIOS',
			$this->getList($select, $filter, $order, $pageNavigation),
			$this->count($filter)
		);
	}

	public function getAction($id)
	{
		$r = $this->exists($id);
		if($r->isSuccess())
		{
			return ['RATIO'=>$this->get($id)];
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}
	//endregion

	protected function getEntityTable()
	{
		return new MeasureRatioTable();
	}

	protected function exists($id)
	{
		$r = new Result();
		if(isset($this->get($id)['ID']) == false)
			$r->addError(new Error('Ratio is not exists'));

		return $r;
	}

	protected function checkModifyPermissionEntity()
	{
		$r = new Result();

		if (!$this->accessController->check(ActionDictionary::ACTION_PRICE_GROUP_EDIT))
		{
			$r->addError(new Error('Access Denied', 200040300020));
		}

		return $r;
	}

	protected function checkReadPermissionEntity()
	{
		$r = new Result();

		if (
			!(
				$this->accessController->check(ActionDictionary::ACTION_CATALOG_READ)
				|| $this->accessController->check(ActionDictionary::ACTION_PRICE_GROUP_EDIT)
			)
		)
		{
			$r->addError(new Error('Access Denied', 200040300010));
		}
		return $r;
	}
}
