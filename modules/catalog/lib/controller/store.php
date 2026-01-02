<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Internal\Service\RestValidator\Entity;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Engine;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use CCatalogStore;

final class Store extends Controller
{
	use ListAction; // default listAction realization
	use CheckExists; // default implementation of existence check

	/**
	 * @inheritDoc
	 */
	protected function processBeforeAction(Engine\Action $action): ?bool
	{
		$result = new Result();

		switch ($action->getName())
		{
			case 'add':
			case 'update':
				$result = $this->processBeforeModify($action);
				break;
			case 'list':
				$result = $this->processBeforeList($action);
				break;
		}

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return parent::processBeforeAction($action);
	}

	protected function processBeforeModify(Engine\Action $action): Result
	{
		$arguments = $action->getArguments();
		$fields = $arguments['fields'] ?? null;
		if (is_array($fields))
		{
			$validator = new Entity\StoreValidator();
			$result = $validator->run($fields);
			if (!$result->isSuccess())
			{
				return $result;
			}
		}

		return new Result();
	}

	protected function processBeforeList(Engine\Action $action): Result
	{
		$arguments = $action->getArguments();
		$filter = $arguments['filter'] ?? [];
		if (!is_array($filter))
		{
			$result = new Result();
			$result->addError(new Error('Incorrect filter format'));

			return $result;
		}

		$validator = new Entity\StoreFilterValidator();

		return $validator->run($filter);
	}

	//region Actions
	public function getFieldsAction(): array
	{
		return [$this->getServiceItemName() => $this->getViewFields()];
	}

	/**
	 * public function listAction
	 * @see ListAction::listAction
	 */

	public function getAction($id): ?array
	{
		$id = (int)$id;

		if (!$this->exists($id)->isSuccess())
		{
			$this->addErrorEntityNotExists();

			return null;
		}

		$storeInfo = $this->get($id);

		if (!$this->checkSpecificStoreReadRight($id))
		{
			$this->addError($this->getErrorReadAccessDenied());

			return null;
		}

		return [
			$this->getServiceItemName() => $storeInfo,
		];
	}

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

		if (!$this->checkSpecificStoreModifyRights($id)
		)
		{
			$this->addError($this->getErrorModifyAccessDenied());

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

		if (!$this->checkSpecificStoreModifyRights($id))
		{
			$this->addError($this->getErrorModifyAccessDenied());

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

		$innerFilter = [
			'LOGIC' => 'OR',
		];

		if ($accessFilter)
		{
			$innerFilter[] = $accessFilter;

			if ($this->checkModifyPermissionEntity()->isSuccess())
			{
				$innerFilter[] = ['=USER_ID' => $this->accessController->getUser()->getUserId()];
			}
		}

		$params['filter'] = [
			$innerFilter,
			$params['filter'],
		];

		return $params;
	}

	protected function getErrorCodeEntityNotExists(): string
	{
		return ErrorCode::STORE_ENTITY_NOT_EXISTS;
	}

	protected function checkSpecificStoreReadRight(int $storeId): bool
	{
		$creatorId = StoreTable::getStoreCreatorId($storeId);

		return
			$this->accessController->checkByValue(ActionDictionary::ACTION_STORE_VIEW, (string)$storeId)
			|| (
				$this->accessController->check(ActionDictionary::ACTION_STORE_MODIFY)
				&& $creatorId === $this->accessController->getUser()->getUserId()
			)
		;
	}

	protected function checkSpecificStoreModifyRights(int $storeId): bool
	{
		return
			$this->accessController->check(ActionDictionary::ACTION_STORE_MODIFY)
			&& $this->checkSpecificStoreReadRight($storeId)
		;
	}
}
