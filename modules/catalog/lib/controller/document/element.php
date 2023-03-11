<?php

namespace Bitrix\Catalog\Controller\Document;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Main\Engine;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;
use CCatalogStoreDocsElement;
use Bitrix\Catalog;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\Model\StoreDocument;
use Bitrix\Catalog\Access\Model\StoreDocumentElement;
use Bitrix\Catalog\Controller\Controller;

class Element extends Controller
{
	private const DOCUMENT_STATUS_ALLOWED = 0;
	private const DOCUMENT_STATUS_CONDUCT = -1;
	private const DOCUMENT_STATUS_ABSENT = -2;

	//region Actions
	/**
	 * @return array
	 */
	public function getFieldsAction(): array
	{
		return ['DOCUMENT_ELEMENT' => $this->getViewFields()];
	}

	/**
	 * @param array $fields
	 * @return array|null
	 */
	public function addAction(array $fields): ?array
	{
		$documentId = (int)($fields['DOC_ID'] ?? 0);
		if (!$documentId)
		{
			$this->setDocumentNotFoundError();

			return null;
		}

		$documentFields = $this->getDocumentFields($documentId);
		if (!$documentFields)
		{
			$this->setDocumentNotFoundError();

			return null;
		}
		elseif (!$this->checkDocumentAccess(ActionDictionary::ACTION_STORE_DOCUMENT_MODIFY, $documentFields))
		{
			return null;
		}
		elseif (!$this->checkStoresAccess(0, $fields))
		{
			return null;
		}

		switch ($this->getDocumentStatusById($documentId))
		{
			case self::DOCUMENT_STATUS_ALLOWED:
				$addResult = CCatalogStoreDocsElement::add($fields);
				if ($addResult)
				{
					return ['DOCUMENT_ELEMENT' => $this->get($addResult)];
				}

				$this->addError(new Error('Error of adding new document element'));
				break;

			case self::DOCUMENT_STATUS_CONDUCT:
				$this->setDocumentConductError();
				break;

			default:
				$this->setDocumentNotFoundError();
				break;
		}

		return null;
	}

	/**
	 * @param int $id
	 * @param array $fields
	 * @return array|null
	 */
	public function updateAction(int $id, array $fields): ?array
	{
		$documentFields = $this->getDocumentFieldsByElementId($id);
		if (!$documentFields)
		{
			$this->setDocumentNotFoundError();

			return null;
		}
		elseif (!$this->checkDocumentAccess(ActionDictionary::ACTION_STORE_DOCUMENT_MODIFY, $documentFields))
		{
			return null;
		}
		elseif (!$this->checkStoresAccess($id, $fields))
		{
			return null;
		}

		switch ($this->getDocumentStatusByElementId($id))
		{
			case self::DOCUMENT_STATUS_ALLOWED:
				$result = CCatalogStoreDocsElement::update($id, $fields);

				if ($result)
				{
					return ['DOCUMENT_ELEMENT' => $this->get($id)];
				}

				$this->addError(new Error('Error of modifying new document'));
				break;

			case self::DOCUMENT_STATUS_CONDUCT:
				$this->setDocumentConductError();
				break;

			default:
				$this->setDocumentNotFoundError();
				break;
		}

		return null;
	}

	/**
	 * @param int $id
	 * @return bool|null
	 */
	public function deleteAction(int $id): ?bool
	{
		$documentFields = $this->getDocumentFieldsByElementId($id);
		if (!$documentFields)
		{
			$this->setDocumentNotFoundError();

			return null;
		}
		elseif (!$this->checkDocumentAccess(ActionDictionary::ACTION_STORE_DOCUMENT_MODIFY, $documentFields))
		{
			return null;
		}
		elseif (!$this->checkStoresAccess($id))
		{
			return null;
		}

		switch ($this->getDocumentStatusByElementId($id))
		{
			case self::DOCUMENT_STATUS_ALLOWED:
				$result = CCatalogStoreDocsElement::delete($id);
				if ($result)
				{
					return true;
				}

				$this->addError(new Error('Error of deleting document'));
				break;

			case self::DOCUMENT_STATUS_CONDUCT:
				$this->setDocumentConductError();
				break;

			default:
				$this->setDocumentNotFoundError();
				break;
		}

		return null;
	}

	/**
	 * @param array $order
	 * @param array $filter
	 * @param array $select
	 * @param PageNavigation $pageNavigation
	 * @return Page
	 */
	public function listAction(
		PageNavigation $pageNavigation,
		array $order = [],
		array $filter = [],
		array $select = []
	): Page
	{
		$filter['@DOCUMENT.DOC_TYPE'] = array_keys(Catalog\Controller\Document::getAvailableRestDocumentTypes());

		$accessFilter = AccessController::getCurrent()->getEntityFilter(
			ActionDictionary::ACTION_STORE_DOCUMENT_VIEW,
			get_class($this->getEntityTable())
		);
		if ($accessFilter)
		{
			// combines through a new array so that the `OR` condition does not bypass the access filter.
			$filter = [
				$accessFilter,
				$filter,
			];
		}

		return new Page(
			'DOCUMENT_ELEMENTS',
			$this->getList($select, $filter, $order, $pageNavigation),
			$this->count($filter)
		);
	}

	/**
	 * @deprecated
	 *
	 * @return array|null
	 */
	public function fieldsAction(): ?array
	{
		return [$this->getViewFields()];
	}

	protected function getDefaultPreFilters(): array
	{
		return array_merge(
			parent::getDefaultPreFilters(),
			[
				new ActionFilter\Scope(ActionFilter\Scope::REST),
			]
		);
	}

	/**
	 * @inheritDoc
	 */
	protected function getEntityTable()
	{
		return new \Bitrix\Catalog\StoreDocumentElementTable();
	}

	/**
	 * @inheritDoc
	 */
	protected function checkModifyPermissionEntity()
	{
		$r = new Result();

		if (!AccessController::getCurrent()->check(Controller::CATALOG_STORE))
		{
			$r->addError(new Error(
				Controller::ERROR_ACCESS_DENIED,
				'ERROR_DOCUMENT_RIGHTS'
			));
		}

		return $r;
	}

	protected function checkReadPermissionEntity()
	{
		$r = new Result();

		if (
			!AccessController::getCurrent()->check(Controller::CATALOG_STORE)
			&& !AccessController::getCurrent()->check(Controller::CATALOG_READ)
		)
		{
			$r->addError(new Error(
				Controller::ERROR_ACCESS_DENIED,
				'ERROR_DOCUMENT_RIGHTS'
			));
		}

		return $r;
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return Result
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	protected function checkPermissionEntity($name, $arguments=[])
	{
		if ($name === 'fields')
		{
			return $this->checkGetFieldsPermissionEntity();
		}

		return parent::checkPermissionEntity($name, $arguments);
	}

	private function getDocumentStatusById(int $documentId): int
	{
		return $this->getDocumentStatus(
			$this->getDocumentFields($documentId)
		);
	}

	private function getDocumentFields(int $documentId): ?array
	{
		return Catalog\StoreDocumentTable::getRow([
			'select' => [
				'ID',
				'STATUS',
				'DOC_TYPE',
			],
			'filter' => [
				'=ID' => $documentId,
			],
		]);
	}

	private function getDocumentStatusByElementId(int $elementId): int
	{
		return $this->getDocumentStatus(
			$this->getDocumentFieldsByElementId($elementId)
		);
	}

	public function getDocumentFieldsByElementId(int $elementId): ?array
	{
		$row = Catalog\StoreDocumentElementTable::getRow([
			'select' => [
				'STORE_DOCUMENT_ID' => 'DOC_ID',
				'STATUS' => 'DOCUMENT.STATUS',
				'DOC_TYPE' => 'DOCUMENT.DOC_TYPE',
			],
			'filter' => [
				'=ID' => $elementId,
			],
		]);
		if ($row)
		{
			$row['ID'] = $row['STORE_DOCUMENT_ID'];
			unset($row['STORE_DOCUMENT_ID']);
		}

		return $row;
	}

	private function getDocumentStatus($row): int
	{
		if (empty($row) || !is_array($row))
		{
			return self::DOCUMENT_STATUS_ABSENT;
		}

		$documentTypes = Catalog\Controller\Document::getAvailableRestDocumentTypes();
		if (!isset($documentTypes[$row['DOC_TYPE']]))
		{
			return self::DOCUMENT_STATUS_ABSENT;
		}

		return ($row['STATUS'] === 'N'
			? self::DOCUMENT_STATUS_ALLOWED
			: self::DOCUMENT_STATUS_CONDUCT
		);
	}

	private function setDocumentRightsError(): void
	{
		$this->addError(new \Bitrix\Main\Error(
			Controller::ERROR_ACCESS_DENIED,
			'ERROR_DOCUMENT_RIGHTS'
		));
	}

	private function setDocumentNotFoundError(): void
	{
		$this->addError(new \Bitrix\Main\Error(
			'Document not found',
			'ERROR_DOCUMENT_STATUS'
		));
	}

	private function setDocumentConductError(): void
	{
		$this->addError(new \Bitrix\Main\Error(
			'Conducted document',
			'ERROR_DOCUMENT_STATUS'
		));
	}

	/**
	 * Check access to document.
	 *
	 * @param string $action
	 * @param array $documentFields
	 *
	 * @return bool
	 */
	private function checkDocumentAccess(string $action, array $documentFields): bool
	{
		$can = AccessController::getCurrent()->check(
			$action,
			StoreDocument::createFromArray($documentFields)
		);
		if (!$can)
		{
			$this->setDocumentRightsError();

			return false;
		}

		return true;
	}

	/**
	 * Check access to stores.
	 *
	 * @param int $id
	 * @param array $fields
	 *
	 * @return bool
	 */
	private function checkStoresAccess(int $id, array $fields = []): bool
	{
		$fields['ID'] = $id;

		$can = AccessController::getCurrent()->check(
			ActionDictionary::ACTION_STORE_VIEW,
			StoreDocumentElement::createFromArray($fields)
		);
		if (!$can)
		{
			$this->setDocumentRightsError();

			return false;
		}

		return true;
	}
}
