<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

final class Store extends Controller
{
	use ListAction; // default listAction realization
	use GetAction; // default getAction realization
	use CheckExists; // default implementation of existence check

	//region Actions
	public function getFieldsAction(): array
	{
		return [$this->getServiceItemName() => $this->getViewFields()];
	}

	/**
	 * public function listAction
	 * @see ListAction::listAction
	 */

	/**
	 * public function getAction
	 * @see GetAction::getAction
	 */

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

	protected function getEntityTable()
	{
		return new StoreTable();
	}

	protected function checkModifyPermissionEntity()
	{
		$r = new Result();

		if (!$this->accessController->check(ActionDictionary::ACTION_STORE_MODIFY))
		{
			$r->addError($this->getErrorModifyAccessDenied());
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
			$r->addError($this->getErrorReadAccessDenied());
		}
		return $r;
	}

	/**
	 * @inheritDoc
	 * @param array $params
	 * @return array
	 */
	protected function modifyListActionParameters(array $params): array
	{
		$accessFilter = $this->accessController->getEntityFilter(
			ActionDictionary::ACTION_STORE_VIEW,
			get_class($this->getEntityTable())
		);
		if ($accessFilter)
		{
			$params['filter'] = [
				$accessFilter,
				$params['filter'],
			];
		}

		return $params;
	}
}
