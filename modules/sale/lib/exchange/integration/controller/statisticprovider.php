<?php


namespace Bitrix\Sale\Exchange\Integration\Controller;


use Bitrix\Main\Engine\Action;
use Bitrix\Main\Error;
use Bitrix\Rest\Integration\Controller\Base;
use Bitrix\Sale\Rest\View\SaleViewManager;
use Bitrix\Sale\Exchange\Integration\Entity\B24IntegrationStatProviderTable;
use Bitrix\Sale\Result;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\UI\PageNavigation;

class StatisticProvider extends Base
{
	//region Actions
	public function getFieldsAction()
	{
		$view = $this->getViewManager()
			->getView($this);

		return ['STATISTIC_PROVIDER'=>$view->prepareFieldInfos(
			$view->getFields()
		)];
	}

	/**
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 * @param PageNavigation|null $pageNavigation
	 * @return Page
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function listAction($select = [], $filter = [], $order = [], PageNavigation $pageNavigation = null): Page
	{
		return new Page('STATISTIC_PROVIDERS',
			$this->getList($select, $filter, $order, $pageNavigation),
			$this->count($filter)
		);
	}

	/**
	 * @param $id
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getAction($id)
	{
		$r = $this->exists($id);
		if($r->isSuccess())
		{
			return ['STATISTIC_PROVIDER'=>$this->get($id)];
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	/**
	 * @param array $fields
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function addAction(array $fields)
	{
		$r = $this->existsByFilter(['XML_ID'=>$fields['XML_ID']]);

		if($r->isSuccess() === false)
		{
			$r = parent::add($fields);
			if($r->isSuccess())
			{
				return ['STATISTIC_PROVIDER'=>$this->get($r->getPrimary())];
			}
		}
		else
		{
			$r->addError(new Error('Duplicate entry for key [xmlId]'));
		}


		$this->addErrors($r->getErrors());
		return null;
	}

	/**
	 * @param $id
	 * @param array $fields
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function updateAction($id, array $fields)
	{
		$r = $this->existsByFilter([
			'XML_ID'=>$fields['XML_ID'],
			'!ID'=>$id
		]);
		if($r->isSuccess() === false)
		{
			$r = parent::update($id, $fields);
			if($r->isSuccess())
			{
				return ['STATISTIC_PROVIDER'=>$this->get($id)];
			}
		}
		else
		{
			$r->addError(new Error('Duplicate entry for key [xmlId]'));
		}


		$this->addErrors($r->getErrors());
		return null;
	}

	/**
	 * @param $id
	 * @return bool|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function deleteAction($id)
	{
		$r = parent::delete($id);
		if($r->isSuccess())
		{
			return true;
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}
	//endregion

	protected function createViewManager(Action $action)
	{
		return new SaleViewManager($action);
	}

	protected function getEntityTable()
	{
		return new B24IntegrationStatProviderTable();
	}

	protected function checkReadPermissionEntity()
	{
		return new Result();
	}

	protected function checkCreatePermissionEntity()
	{
		return new Result();
	}

	protected function checkUpdatePermissionEntity()
	{
		return new Result();
	}

	protected function checkDeletePermissionEntity()
	{
		return new Result();
	}
}