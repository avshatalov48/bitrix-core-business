<?php

namespace Bitrix\Catalog\Controller\Document;

use Bitrix\Main\Engine;
use Bitrix\Main\Engine\ActionFilter;
use CCatalogStoreDocsElement;
use Bitrix\Catalog\RestView;
use Bitrix\Catalog;

class Element extends Engine\Controller
{
	private const LIST_COUNT_DEFAULT = 50;
	private const LIST_COUNT_LIMIT = 500;

	private const REST_AVAILABLE_DOC_TYPE = [
		Catalog\StoreDocumentTable::TYPE_ARRIVAL,
		Catalog\StoreDocumentTable::TYPE_STORE_ADJUSTMENT,
	];

	private const DOCUMENT_STATUS_ALLOWED = 0;
	private const DOCUMENT_STATUS_CONDUCT = -1;
	private const DOCUMENT_STATUS_ABSENT = -2;

	public function addAction(array $fields): ?int
	{
		if (!$this->checkDocumentWriteRights())
		{
			$this->setDocumentRightsError();

			return null;
		}

		$view = new RestView\DocumentElement();
		$fields = $view->internalizeFieldsAdd($fields);

		$result = null;
		$documentId = (int)($fields['DOC_ID'] ?? 0);
		switch ($this->getDocumentStatusById($documentId))
		{
			case self::DOCUMENT_STATUS_ALLOWED:
				$result = CCatalogStoreDocsElement::add($fields);
				if ($result === false)
				{
					$result = null;
				}
				break;
			case self::DOCUMENT_STATUS_CONDUCT:
				$this->setDocumentConductError();
				break;
			default:
				$this->setDocumentNotFoundError();
				break;
		}

		return $result;
	}

	public function updateAction(int $id, array $fields): ?bool
	{
		if (!$this->checkDocumentWriteRights())
		{
			$this->setDocumentRightsError();

			return null;
		}

		$view = new RestView\DocumentElement();
		$fields = $view->internalizeFieldsUpdate($fields);

		$result = null;
		switch ($this->getDocumentStatusByElementId($id))
		{
			case self::DOCUMENT_STATUS_ALLOWED:
				$result = CCatalogStoreDocsElement::update($id, $fields);
				if ($result === false)
				{
					$result = null;
				}
				break;
			case self::DOCUMENT_STATUS_CONDUCT:
				$this->setDocumentConductError();
				break;
			default:
				$this->setDocumentNotFoundError();
				break;
		}

		return $result;
	}

	public function deleteAction(int $id): ?bool
	{
		if (!$this->checkDocumentWriteRights())
		{
			$this->setDocumentRightsError();

			return null;
		}

		$result = null;
		switch ($this->getDocumentStatusByElementId($id))
		{
			case self::DOCUMENT_STATUS_ALLOWED:
				$result = CCatalogStoreDocsElement::delete($id);
				if ($result === false)
				{
					$result = null;
				}
				break;
			case self::DOCUMENT_STATUS_CONDUCT:
				$this->setDocumentConductError();
				break;
			default:
				$this->setDocumentNotFoundError();
				break;
		}

		return $result;
	}

	public function listAction(
		array $order = [],
		array $filter = [],
		array $select = [],
		int $offset = 0,
		int $limit = self::LIST_COUNT_DEFAULT
	): ?array
	{
		if (!$this->checkDocumentReadRights())
		{
			$this->setDocumentRightsError();

			return null;
		}

		if ($limit <= 0)
		{
			$limit = self::LIST_COUNT_DEFAULT;
		}
		elseif ($limit > self::LIST_COUNT_LIMIT)
		{
			$limit = self::LIST_COUNT_LIMIT;
		}

		$result = [];
		$view = new RestView\DocumentElement();
		$data = $view->internalizeFieldsList(
			[
				'order' => $order,
				'filter' => $filter,
				'select' => $select,
			]
		);
		$page = 1 + (int)($offset / $limit);

		$filter = $data['filter'] ?? [];
		$filter['@DOCUMENT.DOC_TYPE'] = self::REST_AVAILABLE_DOC_TYPE;

		AddMessage2Log($filter);

		$res = CCatalogStoreDocsElement::getList(
			$data['order'] ?? [],
			$filter,
			false,
			[
				'nPageSize' => $limit,
				'iNumPage' => $page,
			],
			$data['select'] ?? [],
		);

		while ($element = $res->fetch())
		{
			$result[] = $element;
		}

		if ($offset >= 0)
		{
			$result['total'] = $res->nSelectedCount;
			if ($res->nSelectedCount > $offset + $limit)
			{
				$result['next'] = $page * $limit;
			}
		}

		return $result;
	}

	public function fieldsAction(): ?array
	{
		if (!$this->checkDocumentReadRights())
		{
			$this->setDocumentRightsError();

			return null;
		}

		$view = new RestView\DocumentElement();
		return $view->getFields();
	}

	protected function getDefaultPreFilters()
	{
		return array_merge(
			parent::getDefaultPreFilters(),
			[
				new ActionFilter\Scope(ActionFilter\Scope::REST),
			]
		);
	}

	private function checkDocumentWriteRights(): bool
	{
		return Engine\CurrentUser::get()->canDoOperation(Catalog\Controller\Controller::CATALOG_STORE);
	}

	private function checkDocumentReadRights(): bool
	{
		$currentUser = Engine\CurrentUser::get();

		return
			$currentUser->canDoOperation(Catalog\Controller\Controller::CATALOG_STORE)
			|| $currentUser->canDoOperation(Catalog\Controller\Controller::CATALOG_READ)
			;
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

		if (!in_array($row['DOC_TYPE'], self::REST_AVAILABLE_DOC_TYPE, true))
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
			Catalog\Controller\Controller::ERROR_ACCESS_DENIED,
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
