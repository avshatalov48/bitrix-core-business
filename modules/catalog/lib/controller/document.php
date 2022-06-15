<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Main\Engine\CurrentUser;

use Bitrix\Main\Engine;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Catalog\RestView;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use CCatalogDocs;

class Document extends Engine\Controller
{
	private const LIST_COUNT_DEFAULT = 50;
	private const LIST_COUNT_LIMIT = 500;

	private const REST_AVAILABLE_DOC_TYPE = [
		StoreDocumentTable::TYPE_ARRIVAL,
		StoreDocumentTable::TYPE_STORE_ADJUSTMENT,
	];

	private function getDocumentData($documentIds)
	{
		$documentTitlesRes = StoreDocumentTable::getList(['select' => ['ID', 'DOC_TYPE', 'TITLE'], 'filter' => ['ID' => $documentIds]]);
		$documentTitles = [];
		while ($document = $documentTitlesRes->fetch())
		{
			$documentTitles[$document['ID']] = [
				'TITLE' => $document['TITLE'],
				'DOC_TYPE' => $document['DOC_TYPE'],
			];
		}

		return $documentTitles;
	}

	public function conductListAction($documentIds)
	{
		if (!$this->checkDocumentWriteRights())
		{
			$this->addError(new Error(Loc::getMessage('DOCUMENT_CONTROLLER_NO_RIGHTS_ERROR')));
			return [];
		}

		if (!\Bitrix\Catalog\Component\UseStore::isUsed())
		{
			$this->addError(new Error(Loc::getMessage('DOCUMENT_CONTROLLER_MANAGEMENT_NOT_ENABLED')));
			return [];
		}

		global $APPLICATION;

		$documentData = $this->getDocumentData($documentIds);
		$userId = CurrentUser::get()->getId();
		foreach ($documentIds as $documentId)
		{
			$isConducted = \CCatalogDocs::conductDocument($documentId, $userId);
			$documentTitle = $documentData[$documentId]['TITLE'] ?: StoreDocumentTable::getTypeList(true)[$documentData[$documentId]['DOC_TYPE']];
			if (!$isConducted)
			{
				if ($APPLICATION->GetException())
				{
					$this->addError(
						new Error(Loc::getMessage(
							'DOCUMENT_CONTROLLER_CONDUCT_ERROR',
							[
								'#DOC_TITLE#' => htmlspecialcharsbx($documentTitle),
								'#ERROR#' => htmlspecialcharsbx($APPLICATION->GetException()->GetString()),
							]
						))
					);
					$APPLICATION->ResetException();
				}
				else
				{
					$this->addError(
						new Error(Loc::getMessage('CATALOG_CONTROLLER_DOCUMENT_CONDUCT_GENERAL_ERROR',
							[
								'#DOC_TITLE#' => htmlspecialcharsbx($documentTitle),
							]
						))
					);
				}
			}
		}

		return [];
	}

	public function cancelListAction($documentIds)
	{
		if (!$this->checkDocumentWriteRights())
		{
			$this->addError(new Error(Loc::getMessage('DOCUMENT_CONTROLLER_NO_RIGHTS_ERROR')));
			return [];
		}

		if (!\Bitrix\Catalog\Component\UseStore::isUsed())
		{
			$this->addError(new Error(Loc::getMessage('DOCUMENT_CONTROLLER_MANAGEMENT_NOT_ENABLED')));
			return [];
		}

		global $APPLICATION;

		$documentData = $this->getDocumentData($documentIds);
		$userId = CurrentUser::get()->getId();
		foreach ($documentIds as $documentId)
		{
			$isCancelled = \CCatalogDocs::cancellationDocument($documentId, $userId);
			if (!$isCancelled)
			{
				if ($APPLICATION->GetException())
				{
					$documentTitle = $documentData[$documentId]['TITLE'] ?: StoreDocumentTable::getTypeList(true)[$documentData[$documentId]['DOC_TYPE']];
					$this->addError(
						new Error(Loc::getMessage(
							'DOCUMENT_CONTROLLER_CANCEL_ERROR',
							[
								'#DOC_TITLE#' => htmlspecialcharsbx($documentTitle),
								'#ERROR#' => htmlspecialcharsbx($APPLICATION->GetException()->GetString()),
							]
						))
					);
					$APPLICATION->ResetException();
				}
				else
				{
					$documentTitle = $documentData[$documentId]['TITLE'] ?: StoreDocumentTable::getTypeList(true)[$documentData[$documentId]['DOC_TYPE']];
					$this->addError(
						new Error(Loc::getMessage(
							'CATALOG_CONTROLLER_DOCUMENT_CANCEL_ERROR',
							[
								'#ERROR#' => htmlspecialcharsbx($documentTitle),
							]
						))
					);
				}
			}
		}
	}

	private function prepareFields($fields)
	{
		if (array_key_exists('DOC_TYPE', $fields))
		{
			if (!in_array($fields['DOC_TYPE'], self::REST_AVAILABLE_DOC_TYPE, true))
			{
				return [
					'error' => 'ERROR_DOC_TYPE_VALUE',
					'error_description' => 'DOC_TYPE isn\'t available'
				];
			}
		}
		if (!array_key_exists('SITE_ID', $fields))
		{
			if (defined('SITE_ID'))
			{
				$fields['SITE_ID'] = SITE_ID;
			}
			else
			{
				$fields['SITE_ID'] = 's1';
			}
		}

		if (!CurrentUser::get()->isAdmin() || !array_key_exists('CREATED_BY', $fields))
		{
			$fields['CREATED_BY'] = CurrentUser::get()->getId();
		}

		if (!CurrentUser::get()->isAdmin() || !array_key_exists('MODIFIED_BY', $fields))
		{
			$fields['MODIFIED_BY'] = CurrentUser::get()->getId();
		}

		return $fields;
	}

	/**
	 * Adds new document.
	 * @param array $fields
	 *
	 * @return bool|int|array
	 */
	public function addAction(array $fields)
	{
		if (!$this->checkDocumentWriteRights())
		{
			$message = Loc::getMessage('DOCUMENT_CONTROLLER_NO_RIGHTS_ERROR');
			$this->addError(new Error($message));

			return [
				'error' => 'ERROR_DOCUMENT_RIGHT',
				'error_description' => $message,
			];
		}

		$view = new RestView\Document();
		$fields = $this->prepareFields($fields);
		if (!empty($fields['error']) && !empty($fields['error_description']))
		{
			$result = $fields;
		}
		else
		{
			$fields = $view->internalizeFieldsAdd($fields);
			$result = CCatalogDocs::add($fields);
			if (!$result)
			{
				global $APPLICATION;
				if ($APPLICATION->GetException())
				{
					$exception = $APPLICATION->GetException();
					$result = [
						'error' => 'ERROR_DOCUMENT_ADD',
						'error_description' => $exception->GetString(),
					];
					$APPLICATION->ResetException();
				}
			}
		}

		return $result;
	}

	/**
	 * Return documents fields.
	 *
	 * @return array
	 */
	public function fieldsAction(): array
	{
		if (!$this->checkDocumentReadRights())
		{
			return [];
		}

		$view = new RestView\Document();
		return $view->getFields();
	}

	/**
	 * Updates document.
	 * @param int $id
	 * @param array $fields
	 *
	 * @return bool|array
	 */
	public function updateAction(int $id, array $fields)
	{
		if (!$this->checkDocumentWriteRights())
		{
			$message = Loc::getMessage('DOCUMENT_CONTROLLER_NO_RIGHTS_ERROR');
			$this->addError(new Error($message));

			return [
				'error' => 'ERROR_DOCUMENT_RIGHT',
				'error_description' => $message,
			];
		}

		$view = new RestView\Document();
		$fields = $this->prepareFields($fields);
		if (!empty($fields['error']) && !empty($fields['error_description']))
		{
			$result = $fields;
		}
		else
		{
			$fields = $view->internalizeFieldsUpdate($fields);
			$result = CCatalogDocs::update($id, $fields);
			if (!$result)
			{
				global $APPLICATION;
				if ($APPLICATION->GetException())
				{
					$exception = $APPLICATION->GetException();
					$result = [
						'error' => 'ERROR_DOCUMENT_UPDATE',
						'error_description' => $exception->GetString(),
					];
					$APPLICATION->ResetException();
				}
			}
		}

		return $result;
	}

	public function deleteListAction($documentIds)
	{
		if (!$this->checkDocumentWriteRights())
		{
			$this->addError(new Error(Loc::getMessage('DOCUMENT_CONTROLLER_NO_RIGHTS_ERROR')));
			return [];
		}

		global $APPLICATION;

		$documentData = $this->getDocumentData($documentIds);
		foreach ($documentIds as $documentId)
		{
			\CCatalogDocs::delete($documentId);
			if ($APPLICATION->GetException())
			{
				$documentTitle = $documentData[$documentId]['TITLE'] ?: StoreDocumentTable::getTypeList(true)[$documentData[$documentId]['DOC_TYPE']];
				$exception = $APPLICATION->GetException();
				if ($exception->GetID() === CCatalogDocs::DELETE_CONDUCTED_ERROR)
				{
					$this->addError(
						new Error(Loc::getMessage(
							'DOCUMENT_CONTROLLER_DELETE_CONDUCTED_ERROR',
							[
								'#DOC_TITLE#' => htmlspecialcharsbx($documentTitle),
							]
						))
					);
				}
				else
				{
					$this->addError(
						new Error(Loc::getMessage(
							'DOCUMENT_CONTROLLER_DELETE_ERROR',
							[
								'#DOC_TITLE#' => htmlspecialcharsbx($documentTitle),
								'#ERROR#' => htmlspecialcharsbx($exception->GetString()),
							]
						))
					);
				}

				$APPLICATION->ResetException();
			}
		}
	}

	/**
	 * Deletes document by id.
	 * @param int $id
	 *
	 * @return bool|array
	 */
	public function deleteAction(int $id)
	{
		if (!$this->checkDocumentWriteRights())
		{
			$message = Loc::getMessage('DOCUMENT_CONTROLLER_NO_RIGHTS_ERROR');
			$this->addError(new Error($message));

			return [
				'error' => 'ERROR_DOCUMENT_RIGHT',
				'error_description' => $message,
			];
		}

		$list = CCatalogDocs::getList(
			[],
			[
				'=ID' => $id
			],
			false,
			false,
			[
				'ID',
			]
		);
		if ($item = $list->fetch())
		{
			$res = CCatalogDocs::delete($id);
		}
		else
		{
			$message = Loc::getMessage('CATALOG_CONTROLLER_DOCUMENT_NOT_FOUND');

			$this->addError(
				new Error($message)
			);

			return [
				'error' => 'ERROR_DELETE_DOCUMENT',
				'error_description' => $message,
			];
		}

		global $APPLICATION;
		if ($exception = $APPLICATION->getException())
		{
			if ($exception->getID() === CCatalogDocs::DELETE_CONDUCTED_ERROR)
			{
				$message = Loc::getMessage(
					'DOCUMENT_CONTROLLER_DELETE_CONDUCTED_ERROR',
					[
						'#DOC_TITLE#' => $id,
					]
				);
			}
			else
			{
				$message = Loc::getMessage(
					'DOCUMENT_CONTROLLER_DELETE_ERROR',
					[
						'#DOC_TITLE#' => $id,
						'#ERROR#' => htmlspecialcharsbx($exception->getString()),
					]
				);
			}

			$this->addError(
				new Error($message)
			);

			$APPLICATION->ResetException();

			return [
				'error' => 'ERROR_DELETE_DOCUMENT',
				'error_description' => $message,
			];
		}

		return $res;
	}

	/**
	 * Returns list of document.
	 *
	 * @param array $order
	 * @param array $filter
	 * @param array $select
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return array
	 */
	public function listAction(
		array $order = [],
		array $filter = [],
		array $select = [],
		int $offset = 0,
		int $limit = self::LIST_COUNT_DEFAULT
	): array
	{
		if (!$this->checkDocumentReadRights())
		{
			return [];
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
		$view = new RestView\Document();
		$data = $view->internalizeFieldsList(
			[
				'order' => $order,
				'filter' => $filter,
				'select' => $select,
			]
		);
		$page = 1 + (int)($offset / $limit);

		$res = CCatalogDocs::getList(
			$data['order'],
			$data['filter'],
			false,
			[
				'nPageSize' => $limit,
				'iNumPage' => $page,
			],
			$data['select'],
		);

		while ($document = $res->fetch())
		{
			$result[] = $document;
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

	/**
	 * Conducts document.
	 *
	 * @param int $id
	 *
	 * @return bool|array
	 */
	public function confirmAction(int $id)
	{
		if (!$this->checkDocumentWriteRights())
		{
			$message = Loc::getMessage('DOCUMENT_CONTROLLER_NO_RIGHTS_ERROR');
			$this->addError(new Error($message));

			return [
				'error' => 'ERROR_DOCUMENT_RIGHT',
				'error_description' => $message,
			];
		}

		$document = StoreDocumentTable::getById($id)->fetch();
		if (!$document)
		{
			$message = Loc::getMessage('CATALOG_CONTROLLER_DOCUMENT_NOT_FOUND');
			$this->addError(
				new Error($message)
			);

			return [
				'error' => 'ERROR_DOCUMENT_NOT_FOUND',
				'error_description' => $message,
			];
		}

		$userId = CurrentUser::get()->getId();

		if (!CCatalogDocs::conductDocument($id, $userId))
		{
			$error = '';

			global $APPLICATION;
			if ($APPLICATION->GetException())
			{
				$error = $APPLICATION->GetException()->GetString();
				$APPLICATION->ResetException();
			}

			$message = Loc::getMessage(
				'CATALOG_CONTROLLER_DOCUMENT_CONDUCT_ERROR',
				[
					'#ERROR#' => $error,
				]
			);

			$this->addError(new Error($message));

			return [
				'error' => 'ERROR_CONFIRM_DOCUMENT',
				'error_description' => $message,
			];
		}

		return true;
	}

	/**
	 * Cancellations document.
	 *
	 * @param int $id
	 *
	 * @return bool|array
	 */
	public function unconfirmAction(int $id)
	{
		if (!$this->checkDocumentWriteRights())
		{
			$message = Loc::getMessage('DOCUMENT_CONTROLLER_NO_RIGHTS_ERROR');
			$this->addError(new Error($message));

			return [
				'error' => 'ERROR_DOCUMENT_RIGHT',
				'error_description' => $message,
			];
		}

		$userId = CurrentUser::get()->getId();
		if (!CCatalogDocs::cancellationDocument($id, $userId))
		{
			$error = '';

			global $APPLICATION;
			if ($APPLICATION->GetException())
			{
				$error = $APPLICATION->GetException()->GetString();
				$APPLICATION->ResetException();
			}

			$message = Loc::getMessage(
				'CATALOG_CONTROLLER_DOCUMENT_CANCEL_ERROR',
				[
					'#ERROR#' => $error,
				]
			);
			$this->addError(new Error($message));

			return [
				'error' => 'ERROR_UNCONFIRM_DOCUMENT',
				'error_description' => $message,
			];
		}

		return true;
	}

	private function checkDocumentWriteRights(): bool
	{
		return \Bitrix\Main\Engine\CurrentUser::get()->canDoOperation(Controller::CATALOG_STORE);
	}

	private function checkDocumentReadRights(): bool
	{
		$currentUser = \Bitrix\Main\Engine\CurrentUser::get();

		return
			$currentUser->canDoOperation(Controller::CATALOG_STORE)
			|| $currentUser->canDoOperation(Controller::CATALOG_READ)
		;
	}
}
