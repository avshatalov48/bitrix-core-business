<?php


namespace Bitrix\Catalog\Controller;


use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;

final class Store extends Controller
{
	//region Actions
	public function getFieldsAction(): array
	{
		return ['STORE' => $this->getViewFields()];
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
			'STORES',
			$this->getList($select, $filter, $order, $pageNavigation),
			$this->count($filter)
		);
	}

	public function addAction(array $fields)
	{
		$view = $this->getViewManager()
			->getView($this);
		$fields = $view->internalizeFieldsAdd($fields);

		$res = $this->add($fields);
		if ($res->isSuccess())
		{
			$result = $res->getId();
		}
		else
		{
			$result = [
				'error' => 'ERROR_ADD',
				'error_description' => implode(
					'. ',
					$res->getErrorMessages()
				),
			];
		}

		return $result;
	}

	public function updateAction(int $id, array $fields)
	{
		$view = $this->getViewManager()
			->getView($this);
		$fields = $view->internalizeFieldsUpdate($fields);

		$res = $this->update($id, $fields);
		if (!is_null($res) && $res->isSuccess())
		{
			$result = $res->getId();
		}
		else
		{
			$result = [
				'error' => 'ERROR_UPDATE',
				'error_description' => implode(
					'. ',
					$this->getErrors()
				),
			];
		}

		return $result;
	}

	public function deleteAction(int $id)
	{
		$res = $this->delete($id);
		if (!is_null($res) && $res->isSuccess())
		{
			$result = 'Y';
		}
		else
		{
			$result = [
				'error' => 'ERROR_DELETE',
				'error_description' => implode(
					'. ',
					$this->getErrors()
				),
			];
		}

		return $result;
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
		$r = new Result();

		if (!$this->accessController->check(ActionDictionary::ACTION_STORE_MODIFY))
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
				|| $this->accessController->check(ActionDictionary::ACTION_STORE_VIEW)
				|| $this->accessController->check(ActionDictionary::ACTION_STORE_MODIFY)
			)
		)
		{
			$r->addError(new Error('Access Denied', 200040300010));
		}
		return $r;
	}
}
