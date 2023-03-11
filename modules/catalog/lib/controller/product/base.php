<?php

namespace Bitrix\Catalog\Controller\Product;

use Bitrix\Catalog\Controller\Product;
use Bitrix\Main\Engine;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;

abstract class Base extends Product
{
	protected const TYPE = '';

	abstract protected function getAllowedProductTypes();

	protected function processBeforeAction(Action $action)
	{
		$arguments = $action->getArguments();
		$name = $action->getName();

		if ($name === 'getfieldsbyfilter')
		{
			$arguments['filter']['productType'] = static::TYPE;
			$action->setArguments($arguments);
		}

		return parent::processBeforeAction($action);
	}

	private function isAllowedProductTypeByIBlockId(Engine\Action $action): Result
	{
		$arguments = $action->getArguments();
		$fields = $arguments['fields'];

		/** @var \Bitrix\Catalog\RestView\Product $view */
		$view = $this->getViewManager()->getView($this);
		return $view->isAllowedProductTypeByIBlockId(static::TYPE, $fields['iblockId']);
	}

	protected function processBeforeAdd(Engine\Action $action): Result
	{
		$r = parent::processBeforeAdd($action);

		if ($r->isSuccess())
		{
			$r = $this->isAllowedProductTypeByIBlockId($action);
		}

		return $r;
	}

	protected function processBeforeUpdate(Action $action): Result
	{
		$r = parent::processBeforeUpdate($action);

		if ($r->isSuccess())
		{
			$r = $this->isAllowedProductTypeByIBlockId($action);
		}

		return $r;
	}

	protected function fillKeyResponse($result): ?array
	{
		return is_null($result) ? $result : [$this->getServiceItemName() => current($result)];
	}

	public function addAction($fields): ?array
	{
		$fields['TYPE'] = static::TYPE;
		$result = parent::addAction($fields);

		return $this->fillKeyResponse($result);
	}

	public function updateAction($id, array $fields): ?array
	{
		$result = parent::updateAction($id, $fields);

		return $this->fillKeyResponse($result);
	}

	/**
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 * @param PageNavigation $pageNavigation
	 * @return Page|null
	 */
	public function listAction(PageNavigation $pageNavigation, array $select = [], array $filter = [], array $order = []): ?Page
	{
		/** @var \Bitrix\Catalog\RestView\Product $view */
		$view = $this->getViewManager()->getView($this);
		$r = $view->isAllowedProductTypeByIBlockId(static::TYPE, $filter['IBLOCK_ID']);
		if (!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());

			return null;
		}

		$list = $this->getAllowedProductTypes();

		if (isset($filter['TYPE']))
		{
			$filter['TYPE']  = in_array($filter['TYPE'], $list, true) ? $filter['TYPE'] : $list;
		}
		else
		{
			$filter['TYPE'] = $list;
		}

		return parent::listAction($pageNavigation, $select, $filter, $order);
	}

	protected function get($id)
	{
		$result = parent::get($id);

		if (empty($result) === false)
		{
			if (in_array($result['TYPE'], $this->getAllowedProductTypes()))
			{
				return $result;
			}
		}

		return false;
	}
}