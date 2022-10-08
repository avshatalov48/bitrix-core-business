<?php

namespace Bitrix\Catalog\Controller\Document;

use Bitrix\Main\Engine;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;
use CCatalogStoreDocsElement;
use Bitrix\Catalog;
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
		array $order = [],
		array $filter = [],
		array $select = [],
		PageNavigation $pageNavigation
	): Page
	{
		$filter['@DOCUMENT.DOC_TYPE'] = array_keys(Catalog\Controller\Document::getAvailableRestDocumentTypes());
		AddMessage2Log($filter);

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
		$r = $this->checkReadPermissionEntity();
		if (!$r->isSuccess())
		{
			return null;
		}

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
		$r = $this->checkReadPermissionEntity();
		if($r->isSuccess())
		{
			if (!Engine\CurrentUser::get()->canDoOperation(Controller::CATALOG_STORE))
			{
				$this->setDocumentRightsError();
			}
		}

		return $r;
	}

	protected function checkReadPermissionEntity()
	{
		$r = new Result();

		$currentUser = Engine\CurrentUser::get();

		if (
			!$currentUser->canDoOperation(Controller::CATALOG_STORE)
			&& !$currentUser->canDoOperation(Controller::CATALOG_READ)
		)
		{
			$this->setDocumentRightsError();
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
		$iterator = Catalog\StoreDocumentTable::getList([
			'select' => [
				'STORE_DOCUMENT_ID' => 'ID',
				'STATUS',
				'DOC_TYPE',
			],
			'filter' => [
				'=ID' => $documentId,
			],
		]);
		$row = $iterator->fetch();
		unset($iterator);

		return $this->getDocumentStatus($row);
	}

	private function getDocumentStatusByElementId(int $elementId): int
	{
		$iterator = Catalog\StoreDocumentElementTable::getList([
			'select' => [
				'STORE_DOCUMENT_ID' => 'DOC_ID',
				'STATUS' => 'DOCUMENT.STATUS',
				'DOC_TYPE' => 'DOCUMENT.DOC_TYPE',
			],
			'filter' => [
				'=ID' => $elementId,
			],
		]);
		$row = $iterator->fetch();
		unset($iterator);

		return $this->getDocumentStatus($row);
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
}
