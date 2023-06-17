<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Catalog\v2\Contractor\Provider\Manager;
use Bitrix\Crm\Integration\Catalog\Contractor\StoreDocumentContractorTable;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;

class DocumentContractor extends Controller
{
	/**
	 * @inheritDoc
	 */
	protected function processBeforeAction(Action $action): ?bool
	{
		$result = parent::processBeforeAction($action);

		if (
			empty($this->getErrors())
			&& !Manager::isActiveProviderByModule('crm')
		)
		{
			$this->addError(new Error('Contractors should be provided by CRM'));

			return null;
		}

		return $result;
	}

	// region Actions

	/**
	 * Get fields of document-contactor bindings:
	 * - id
	 * - documentId
	 * - entityTypeId (Contact / Company)
	 * - entityId
	 *
	 * @return array
	 */
	public function getFieldsAction(): array
	{
		return [
			'DOCUMENT_CONTRACTOR' => $this->getViewFields(),
		];
	}

	/**
	 * Add document-contactor binding
	 * Required fields: documentId, entityTypeId, entityId
	 *
	 * @param array $fields
	 * @return array|null
	 */
	public function addAction(array $fields): ?array
	{
		$canModify = $this->checkDocumentAccess(ActionDictionary::ACTION_STORE_DOCUMENT_MODIFY);
		if (!$canModify->isSuccess())
		{
			return null;
		}

		$checkFieldsResult = $this->checkFields($fields);
		if (!$checkFieldsResult->isSuccess())
		{
			$this->addErrors($checkFieldsResult->getErrors());

			return null;
		}

		$addResult = StoreDocumentContractorTable::add([
			'DOCUMENT_ID' => (int)$fields['DOCUMENT_ID'],
			'ENTITY_ID' => (int)$fields['ENTITY_ID'],
			'ENTITY_TYPE_ID' => (int)$fields['ENTITY_TYPE_ID'],
		]);

		if (!$addResult->isSuccess())
		{
			$this->addErrors($checkFieldsResult->getErrors());

			return null;
		}

		return [
			'DOCUMENT_CONTRACTOR' => $this->get($addResult->getId()),
		];
	}

	/**
	 * Delete document-contactor binding by id
	 *
	 * @param int $id
	 * @return bool|null
	 */
	public function deleteAction(int $id): ?bool
	{
		$existsResult = $this->exists($id);
		if (!$existsResult->isSuccess())
		{
			$this->addError(new Error('Binding was not found'));

			return null;
		}

		$canModify = $this->checkDocumentAccess(ActionDictionary::ACTION_STORE_DOCUMENT_MODIFY);
		if (!$canModify->isSuccess())
		{
			return null;
		}

		$deleteResult = StoreDocumentContractorTable::delete($id);
		if (!$deleteResult)
		{
			$this->addErrors($deleteResult->getErrors());

			return null;
		}

		return true;
	}

	/**
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 * @param PageNavigation|null $pageNavigation
	 * @return Page
	 */
	public function listAction(
		PageNavigation $pageNavigation,
		array $select = [],
		array $filter = [],
		array $order = []
	): Page
	{
		return new Page(
			'DOCUMENT_CONTRACTOR',
			$this->getList($select, $filter, $order, $pageNavigation),
			$this->count($filter)
		);
	}

	// end region Actions

	/**
	 * Check fields before add binding
	 *
	 * @param array $fields
	 * @return Result
	 */
	private function checkFields(array $fields): Result
	{
		$result = new Result();
		$documentId = (int)$fields['DOCUMENT_ID'];
		$entityTypeId = (int)$fields['ENTITY_TYPE_ID'];
		$entityId = (int)$fields['ENTITY_ID'];

		if (!$documentId)
		{
			$result->addError(new Error('Store document was not found'));

			return $result;
		}

		$document = StoreDocumentTable::getRow([
			'select' => [
				'ID',
				'DOC_TYPE',
				'STATUS',
			],
			'filter' => [
				'ID' => $documentId,
			],
		]);

		if (!$document)
		{
			$result->addError(new Error('Store document was not found'));

			return $result;
		}

		if ($document['DOC_TYPE'] !== StoreDocumentTable::TYPE_ARRIVAL)
		{
			$result->addError(new Error('Type of store document is wrong'));
		}

		if ($document['STATUS'] === 'Y')
		{
			$result->addError(new Error('Unable to edit conducted document'));
		}

		if (
			$entityTypeId !== \CCrmOwnerType::Contact
			&& $entityTypeId !== \CCrmOwnerType::Company
		)
		{
			$result->addError(new Error('Wrong entity type id'));
		}

		if (!$entityId)
		{
			$result->addError(new Error('Wrong entity id'));
		}

		$bindingExists = $this->existsByFilter([
			'DOCUMENT_ID' => $documentId,
			'ENTITY_TYPE_ID' => $entityTypeId,
			'ENTITY_ID' => $entityId,
		]);
		if ($bindingExists->isSuccess())
		{
			$result->addError(new Error('This contractor has been already bound to this document'));
		}

		if ($entityTypeId === \CCrmOwnerType::Company)
		{
			$documentCompanyBinding = StoreDocumentContractorTable::getRow([
				'select' => ['ID'],
				'filter' => [
					'DOCUMENT_ID' => $documentId,
					'ENTITY_TYPE_ID' => \CCrmOwnerType::Company,
				],
			]);

			if (!empty($documentCompanyBinding))
			{
				$result->addError(new Error('This document already has a Company contractor'));
			}
		}

		return $result;
	}

	protected function checkReadPermissionEntity(): Result
	{
		$result = $this->checkDocumentAccess(ActionDictionary::ACTION_STORE_DOCUMENT_VIEW);
		if (!$result->isSuccess())
		{
			return $result;
		}

		$canReadDocument =
			$this->accessController->check(Controller::CATALOG_STORE)
			&& $this->accessController->check(Controller::CATALOG_READ);

		if (!$canReadDocument)
		{
			$result->addError(new Error('Access denied'));
		}

		return $result;
	}

	protected function checkModifyPermissionEntity(): Result
	{
		$result = $this->checkReadPermissionEntity();
		if (!$result->isSuccess())
		{
			return $result;
		}

		return $this->checkDocumentAccess(ActionDictionary::ACTION_STORE_DOCUMENT_MODIFY);
	}

	protected function checkPermissionEntity($name, $arguments = []): Result
	{
		return new Result();
	}

	/**
	 * Checks access to arrival document.
	 *
	 * @param string $action
	 * @return Result
	 */
	private function checkDocumentAccess(string $action): Result
	{
		$result = new Result();
		$can = $this->accessController->checkByValue($action, StoreDocumentTable::TYPE_ARRIVAL);
		if (!$can)
		{
			$result->addError(new Error('Access denied'));

			return $result;
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function getEntityTable(): StoreDocumentContractorTable
	{
		return new StoreDocumentContractorTable;
	}
}