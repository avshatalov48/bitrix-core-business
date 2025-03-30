<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use CCatalogStore;

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
		$result = CCatalogStore::Add($fields);
		if (!$result)
		{
			global $APPLICATION;
			$exception = $APPLICATION->GetException();
			$error = $exception instanceof \CApplicationException ? $exception->GetString() : 'Unknown error';
			$this->addError(new Error($error));
			$APPLICATION->ResetException();

			return null;
		}

		return [
			$this->getServiceItemName() => $this->get($result),
		];
	}

	public function updateAction(int $id, array $fields)
	{
		$existsResult = $this->exists($id);
		if (!$existsResult->isSuccess())
		{
			$this->addErrors($existsResult->getErrors());

			return null;
		}

		$result = CCatalogStore::Update($id, $fields);
		if (!$result)
		{
			global $APPLICATION;
			$exception = $APPLICATION->GetException();
			$error = $exception instanceof \CApplicationException ? $exception->GetString() : 'Unknown error';
			$this->addError(new Error($error));
			$APPLICATION->ResetException();

			return null;
		}

		return [
			$this->getServiceItemName() => $this->get($result),
		];
	}

	public function deleteAction(int $id)
	{
		$existsResult = $this->exists($id);
		if (!$existsResult->isSuccess())
		{
			$this->addErrors($existsResult->getErrors());

			return null;
		}

		$result = CCatalogStore::Delete($id);
		if (!$result)
		{
			global $APPLICATION;
			$exception = $APPLICATION->GetException();
			$error = $exception instanceof \CApplicationException ? $exception->GetString() : 'Unknown error';
			$this->addError(new Error($error));
			$APPLICATION->ResetException();

			return null;
		}

		return true;
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

	protected function getErrorCodeEntityNotExists(): string
	{
		return ErrorCode::STORE_ENTITY_NOT_EXISTS;
	}
}
