<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\Model\StoreDocument;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;
use CApplicationException;
use CCatalogDocs;
use CMain;

class Document extends Controller
{
	//region Actions
	/**
	 * @return array
	 */
	public function getFieldsAction(): array
	{
		return ['DOCUMENT' => $this->getViewFields()];
	}

	/**
	 * @param array $documentIds
	 *
	 * @return null|bool
	 */
	public function conductListAction(array $documentIds): ?bool
	{
		if (!\Bitrix\Catalog\Component\UseStore::isUsed())
		{
			$this->addError(new Error(Loc::getMessage('DOCUMENT_CONTROLLER_MANAGEMENT_NOT_ENABLED')));

			return null;
		}

		global $APPLICATION;

		$documentData = $this->getDocumentData($documentIds);

		$userId = CurrentUser::get()->getId();
		foreach ($documentIds as $documentId)
		{
			$document = $documentData[$documentId] ?? null;
			if (!$document)
			{
				$this->addError(
					new Error(Loc::getMessage(
						'CATALOG_CONTROLLER_DOCUMENT_CONDUCT_GENERAL_ERROR',
						[
							'#ERROR#' => Loc::getMessage('CATALOG_CONTROLLER_DOCUMENT_NOT_FOUND'),
						]
					))
				);
				continue;
			}

			$documentTitle = $document['TITLE'] ?: StoreDocumentTable::getTypeList(true)[$document['DOC_TYPE']];

			$can = $this->accessController->check(
				ActionDictionary::ACTION_STORE_DOCUMENT_CONDUCT,
				StoreDocument::createFromArray($document)
			);
			if (!$can)
			{
				$this->addError(
					new Error(Loc::getMessage('CATALOG_CONTROLLER_DOCUMENT_CONDUCT_ERROR',
						[
							'#DOC_TITLE#' => htmlspecialcharsbx($documentTitle),
							'#ERROR#' => Loc::getMessage('DOCUMENT_CONTROLLER_NO_RIGHTS_ERROR'),
						]
					))
				);
				continue;
			}

			$isConducted = \CCatalogDocs::conductDocument($documentId, $userId);
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

		if (!$this->errorCollection->isEmpty())
		{
			return null;
		}

		return true;
	}

	/**
	 * @param $documentIds
	 *
	 * @return bool|null
	 */
	public function cancelListAction(array $documentIds): ?bool
	{
		if (!\Bitrix\Catalog\Component\UseStore::isUsed())
		{
			$this->addError(new Error(Loc::getMessage('DOCUMENT_CONTROLLER_MANAGEMENT_NOT_ENABLED')));

			return null;
		}

		global $APPLICATION;

		$documentData = $this->getDocumentData($documentIds);
		$userId = CurrentUser::get()->getId();
		foreach ($documentIds as $documentId)
		{
			$document = $documentData[$documentId] ?? null;
			if (!$document)
			{
				$this->addError(
					new Error(Loc::getMessage(
						'CATALOG_CONTROLLER_DOCUMENT_CANCEL_ERROR',
						[
							'#ERROR#' => Loc::getMessage('CATALOG_CONTROLLER_DOCUMENT_NOT_FOUND'),
						]
					))
				);
				continue;
			}

			$documentTitle = $document['TITLE'] ?: StoreDocumentTable::getTypeList(true)[$document['DOC_TYPE']];

			$can = $this->accessController->check(
				ActionDictionary::ACTION_STORE_DOCUMENT_CONDUCT,
				StoreDocument::createFromArray($document)
			);
			if (!$can)
			{
				$this->addError(
					new Error(Loc::getMessage('DOCUMENT_CONTROLLER_CANCEL_ERROR',
						[
							'#DOC_TITLE#' => htmlspecialcharsbx($documentTitle),
							'#ERROR#' => Loc::getMessage('DOCUMENT_CONTROLLER_NO_RIGHTS_ERROR'),
						]
					))
				);
				continue;
			}

			$isCancelled = \CCatalogDocs::cancellationDocument($documentId, $userId);
			if (!$isCancelled)
			{
				if ($APPLICATION->GetException())
				{
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

		if (!$this->errorCollection->isEmpty())
		{
			return null;
		}

		return true;
	}

	private function prepareFieldsAdd($fields)
	{
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
	 * @return null|array
	 */
	public function addAction(array $fields): ?array
	{
		$docType = $fields['DOC_TYPE'] ?? null;

		$availableTypes = self::getAvailableRestDocumentTypes();
		if (!isset($availableTypes[$docType]))
		{
			$this->addError(new Error('DOC_TYPE isn\'t available'));

			return null;
		}

		if (!$this->accessController->check(ActionDictionary::ACTION_STORE_DOCUMENT_MODIFY, StoreDocument::createFromArray($fields)))
		{
			$this->addError(
				new Error(Loc::getMessage('DOCUMENT_CONTROLLER_NO_RIGHTS_ERROR'))
			);

			return null;
		}

		$fields = $this->prepareFieldsAdd($fields);
		$addResult = CCatalogDocs::add($fields);
		if (!$addResult)
		{
			global $APPLICATION;
			if ($APPLICATION->GetException())
			{
				$exception = $APPLICATION->GetException();
				$this->addError(new Error($exception->GetString()));
				$APPLICATION->ResetException();

				return null;
			}
		}

		return ['DOCUMENT' => $this->get($addResult)];
	}

	/**
	 * @deprecated
	 *
	 * Return documents fields.
	 *
	 * @return array
	 */
	public function fieldsAction(): array
	{
		return [$this->getViewFields()];
	}

	/**
	 * Updates document.
	 * @param int $id
	 * @param array $fields
	 *
	 * @return null|array
	 */
	public function updateAction(int $id, array $fields): ?array
	{
		if (!$this->checkDocumentAccess(ActionDictionary::ACTION_STORE_DOCUMENT_MODIFY, $id))
		{
			return null;
		}

		$user = CurrentUser::get();
		if (!array_key_exists('MODIFIED_BY', $fields) || !$user->isAdmin())
		{
			$fields['MODIFIED_BY'] = $user->getId();
		}

		$result = CCatalogDocs::update($id, $fields);
		if (!$result)
		{
			global $APPLICATION;
			if ($APPLICATION->GetException())
			{
				$exception = $APPLICATION->GetException();
				$this->addError(new Error($exception->GetString()));
				$APPLICATION->ResetException();

				return null;
			}
		}

		return ['DOCUMENT' => $this->get($id)];
	}

	/**
	 * @param array $documentIds
	 *
	 * @return bool|null
	 */
	public function deleteListAction(array $documentIds): ?bool
	{
		global $APPLICATION;

		/**
		 * @var CMain $APPLICATION
		 */

		$documentData = $this->getDocumentData($documentIds);

		foreach ($documentIds as $documentId)
		{
			$exception = null;
			$document = $documentData[(int)$documentId] ?? null;

			if (!$document)
			{
				$this->addError(
					new Error(Loc::getMessage(
						'DOCUMENT_CONTROLLER_DELETE_ERROR',
						[
							'#DOC_TITLE#' => "#{$documentId}",
							'#ERROR#' => Loc::getMessage('CATALOG_CONTROLLER_DOCUMENT_NOT_FOUND'),
						]
					))
				);
				continue;
			}

			$can = $this->accessController->check(
				ActionDictionary::ACTION_STORE_DOCUMENT_DELETE,
				StoreDocument::createFromArray($document)
			);
			if ($can)
			{
				\CCatalogDocs::delete($documentId);

				$exception = $APPLICATION->GetException();
			}
			else
			{
				$exception = new CApplicationException(
					Loc::getMessage('DOCUMENT_CONTROLLER_NO_RIGHTS_ERROR')
				);
			}

			if ($exception)
			{
				$documentTitle = $document['TITLE'] ?: StoreDocumentTable::getTypeList(true)[$document['DOC_TYPE']];
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

		return $this->errorCollection->isEmpty() ? true : null;
	}

	/**
	 * Deletes document by id.
	 * @param int $id
	 *
	 * @return null|bool
	 */
	public function deleteAction(int $id): ?bool
	{
		if (!$this->checkDocumentAccess(ActionDictionary::ACTION_STORE_DOCUMENT_DELETE, $id))
		{
			return null;
		}

		$deleteResult = CCatalogDocs::delete($id);
		if (!$deleteResult)
		{
			$message = Loc::getMessage('CATALOG_CONTROLLER_DOCUMENT_NOT_FOUND');

			$this->addError(
				new Error($message)
			);

			return null;
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

			return null;
		}

		return $deleteResult;
	}

	/**
	 * Returns list of document.
	 *
	 * @param array $order
	 * @param array $filter
	 * @param array $select
	 * @param PageNavigation $pageNavigation
	 *
	 * @return array
	 */
	public function listAction(
		PageNavigation $pageNavigation,
		array $order = [],
		array $filter = [],
		array $select = []
	): Page
	{
		// set available types for REST
		$filter = [
			'=DOC_TYPE' => array_keys(self::getAvailableRestDocumentTypes()),
			$filter,
		];

		$accessFilter = $this->accessController->getEntityFilter(
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

		return new Page('DOCUMENTS',
			$this->getList($select, $filter, $order, $pageNavigation),
			$this->count($filter)
		);
	}

	/**
	 * Conducts document.
	 *
	 * @param int $id
	 *
	 * @return bool|null
	 */
	public function conductAction(int $id): ?bool
	{
		return $this->confirmAction($id);
	}

	/**
	 * Conducts document.
	 *
	 * @deprecated
	 *
	 * @param int $id
	 *
	 * @return bool|null
	 */
	public function confirmAction(int $id): ?bool
	{
		if (!$this->checkDocumentAccess(ActionDictionary::ACTION_STORE_DOCUMENT_CONDUCT, $id))
		{
			return null;
		}

		$document = StoreDocumentTable::getById($id)->fetch();
		if (!$document)
		{
			$message = Loc::getMessage('CATALOG_CONTROLLER_DOCUMENT_NOT_FOUND');
			$this->addError(
				new Error($message)
			);

			return null;
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

			return null;
		}

		return true;
	}

	/**
	 * Cancel document.
	 *
	 * @param int $id
	 *
	 * @return bool|null
	 */
	public function cancelAction(int $id): ?bool
	{
		return $this->unconfirmAction($id);
	}

	/**
	 * Cancellations document.
	 *
	 * @param int $id
	 *
	 * @return bool|null
	 */
	public function unconfirmAction(int $id): ?bool
	{
		if (!$this->checkDocumentAccess(ActionDictionary::ACTION_STORE_DOCUMENT_CONDUCT, $id))
		{
			return null;
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

			return null;
		}

		return true;
	}

	/**
	 * @return array
	 */
	public static function getAvailableRestDocumentTypes(): array
	{
		$types = StoreDocumentTable::getTypeList(true);
		unset($types[StoreDocumentTable::TYPE_UNDO_RESERVE]);

		return $types;
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return Result
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	protected function checkPermissionEntity($name, $arguments=[])
	{
		$name = mb_strtolower($name);
		if ($name === 'fields')
		{
			return $this->checkGetFieldsPermissionEntity();
		}

		if (in_array($name, ['conductlist', 'cancellist', 'unconfirm', 'confirm', 'conduct', 'cancel'], true))
		{
			return $this->checkUpdatePermissionEntity();
		}

		if ($name === 'deletelist')
		{
			return $this->checkDeletePermissionEntity();
		}

		return parent::checkPermissionEntity($name, $arguments);
	}

	/**
	 * @inheritDoc
	 */
	protected function getEntityTable()
	{
		return new \Bitrix\Catalog\StoreDocumentTable();
	}

	protected function checkModifyPermissionEntity()
	{
		$r = $this->checkReadPermissionEntity();
		if ($r->isSuccess())
		{
			if (!$this->accessController->check(Controller::CATALOG_STORE))
			{
				$message = Loc::getMessage('DOCUMENT_CONTROLLER_NO_STORE_RIGHTS_ERROR');
				$r->addError(new Error($message));
			}
		}

		return $r;
	}

	protected function checkReadPermissionEntity()
	{
		$r = new Result();

		if (
			!$this->accessController->check(Controller::CATALOG_STORE)
			&& !$this->accessController->check(Controller::CATALOG_READ)
		)
		{
			$message = Loc::getMessage('DOCUMENT_CONTROLLER_NO_RIGHTS_ERROR');
			$r->addError(new Error($message));
		}

		return $r;
	}

	private function getDocumentData(array $documentIds): array
	{
		$documentTitlesRes = StoreDocumentTable::getList(['select' => ['ID', 'DOC_TYPE', 'TITLE'], 'filter' => ['ID' => $documentIds]]);
		$documentTitles = [];
		while ($document = $documentTitlesRes->fetch())
		{
			$documentTitles[$document['ID']] = [
				'ID' => $document['ID'],
				'TITLE' => $document['TITLE'],
				'DOC_TYPE' => $document['DOC_TYPE'],
			];
		}

		return $documentTitles;
	}

	/**
	 * Check access to document store and add error to controller.
	 *
	 * Adds error if user does not have access.
	 *
	 * @param string $action
	 * @param int $documentId
	 *
	 * @return bool false - if is access denied.
	 */
	private function checkDocumentAccess(string $action, int $documentId): bool
	{
		$can = $this->accessController->check($action, StoreDocument::createFromId($documentId));
		if (!$can)
		{
			$this->addError(
				new Error(Loc::getMessage('DOCUMENT_CONTROLLER_NO_RIGHTS_ERROR'))
			);

			return false;
		}

		return true;
	}
}
