<?php

namespace Bitrix\Catalog\Controller\Userfield;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Document\StoreDocumentTableManager;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;

class Document extends \Bitrix\Catalog\Controller\Controller
{
	protected const ITEM = 'DOCUMENT';
	protected const LIST = 'DOCUMENTS';

	public function updateAction($documentId, $fields): ?array
	{
		global $USER_FIELD_MANAGER;

		$existsResult = $this->exists($documentId);
		if (!$existsResult->isSuccess())
		{
			$this->addErrors($existsResult->getErrors());
			return null;
		}

		$documentType = $fields['DOC_TYPE'];
		unset($fields['DOC_TYPE']);

		$permissionCheckResult = $this->checkUpdatePermissionByType($documentType);
		if (!$permissionCheckResult->isSuccess())
		{
			$this->addErrors($permissionCheckResult->getErrors());
			return null;
		}

		$entityId = self::getEntityIdForType($documentType);
		if (!$entityId)
		{
			return [];
		}

		$fields = $this->prepareFileFieldsForUpdate((int)$documentId, $documentType, $fields);

		$USER_FIELD_MANAGER->Update($entityId, $documentId, $fields);

		return [static::ITEM => $this->get($documentId)];
	}

	private function prepareFileFieldsForUpdate(int $documentId, string $documentType, array $fields): array
	{
		$entityId = self::getEntityIdForType($documentType);

		global $USER_FIELD_MANAGER;
		$preparedFields = $fields;
		$userFields = $USER_FIELD_MANAGER->GetUserFields($entityId, $documentId);
		foreach ($preparedFields as $fieldId => $field)
		{
			if (isset($userFields[$fieldId]) && $userFields[$fieldId]['USER_TYPE_ID'] === 'file')
			{
				if ($userFields[$fieldId]['MULTIPLE'] === 'Y')
				{
					$preparedFields[$fieldId][] = [
						'old_id' => $userFields[$fieldId]['VALUE'],
					];
				}
				else
				{
					$preparedFields[$fieldId]['old_id'] = $userFields[$fieldId]['VALUE'];
				}
			}
		}

		return $preparedFields;
	}

	public function getAction($documentId)
	{
		$this->addError(new Error('The get method is not implemented; please use the list method and filter by documentId'));

		return null;
	}

	public function listAction(PageNavigation $pageNavigation, $select = [], $filter = [], $order = []): ?Page
	{
		if (!in_array('DOC_TYPE', $select, true))
		{
			$this->addError(new Error('The documentType field is not specified in the select parameter'));
			return null;
		}

		if (!isset($filter['DOC_TYPE']))
		{
			$this->addError(new Error('The documentType field is not specified in the filter parameter'));
			return null;
		}

		$documentType = $filter['DOC_TYPE'];
		unset($filter['DOC_TYPE']);

		$permissionCheckResult = $this->checkReadPermissionByType($documentType);
		if (!$permissionCheckResult->isSuccess())
		{
			$this->addErrors($permissionCheckResult->getErrors());
			return null;
		}

		$tableClass = StoreDocumentTableManager::getTableClassByType($documentType);
		if (!$tableClass)
		{
			return null;
		}

		$result = $tableClass::getList([
			'select' => $select,
			'filter' => $filter,
			'order' => $order,
			'offset' => $pageNavigation->getOffset(),
			'limit' => $pageNavigation->getLimit(),
		])->fetchAll();
		
		return new Page(static::LIST, $result, $tableClass::getCount($filter));
	}

	public function addAction($fields)
	{
		$this->addError(new Error('The add method is not implemented; please use the update method to set the values'));

		return null;
	}

	public function deleteAction($fields)
	{
		$this->addError(new Error('The delete method is not implemented; please use the update method to unset the values'));

		return null;
	}

	protected static function getUserFieldsForType(string $docType): array
	{
		global $USER_FIELD_MANAGER;
		static $fields = [];

		$entityId = self::getEntityIdForType($docType);
		if (!$entityId)
		{
			return [];
		}

		if (!isset($fields[$docType]))
		{
			$fields[$docType] = $USER_FIELD_MANAGER->GetUserFields($entityId, 0);
		}

		return $fields[$docType];
	}

	protected static function getEntityIdForType(string $docType): string
	{
		return StoreDocumentTableManager::getUfEntityIds()[$docType] ?? '';
	}

	protected function get($id)
	{
		global $USER_FIELD_MANAGER;

		$documentType = StoreDocumentTable::getRow([
			'select' => ['DOC_TYPE'],
			'filter' => ['=ID' => $id]
		])['DOC_TYPE'];
		if (!$documentType)
		{
			return [];
		}

		$entityId = self::getEntityIdForType($documentType);
		if (!$entityId)
		{
			return [];
		}

		$values = $USER_FIELD_MANAGER->GetUserFields($entityId, $id);

		$result = [
			'ID' => $id,
			'DOC_TYPE' => $documentType,
		];

		foreach ($values as $value)
		{
			$result['FIELD_' . $value['ID']] = $value['VALUE'];
		}

		return $result;
	}

	protected function exists($id)
	{
		$r = new Result();
		if(isset($this->get($id)['ID']) === false)
		{
			$r->addError(new Error('The specified document does not exist'));
		}

		return $r;
	}

	protected function checkReadPermissionEntity()
	{
		return new Result();
	}

	protected function checkModifyPermissionEntity()
	{
		return new Result();
	}

	protected function checkReadPermissionByType(string $documentType): Result
	{
		$result = new Result();
		$accessController = AccessController::getCurrent();
		$basePermission =
			$accessController->check(ActionDictionary::ACTION_CATALOG_READ)
			&& $accessController->check(ActionDictionary::ACTION_INVENTORY_MANAGEMENT_ACCESS)
		;
		$typePermission = $accessController->checkByValue(
			ActionDictionary::ACTION_STORE_DOCUMENT_VIEW,
			$documentType
		);
		if (!($basePermission && $typePermission))
		{
			$result->addError(new Error('Access Denied'));
		}

		return $result;
	}

	protected function checkUpdatePermissionByType(string $documentType): Result
	{
		$readPermissionCheckResult = $this->checkReadPermissionByType($documentType);
		if (!$readPermissionCheckResult->isSuccess())
		{
			return $readPermissionCheckResult;
		}

		$result = new Result();
		$accessController = AccessController::getCurrent();
		$modifyPermission = $accessController->checkByValue(
			ActionDictionary::ACTION_STORE_DOCUMENT_MODIFY,
			$documentType
		);
		if (!$modifyPermission)
		{
			$result->addError(new Error('Access Denied'));
		}

		return $result;
	}
}
