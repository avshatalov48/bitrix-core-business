<?php

use Bitrix\Catalog;
use Bitrix\Catalog\Component\UseStore;
use Bitrix\Catalog\Integration\PullManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog\v2\Contractor;

IncludeModuleLangFile(__FILE__);

class CAllCatalogDocs
{
	static $types = [
		Catalog\StoreDocumentTable::TYPE_ARRIVAL => "CCatalogArrivalDocs",
		Catalog\StoreDocumentTable::TYPE_STORE_ADJUSTMENT => "CCatalogStoreAdjustmentDocs",
		Catalog\StoreDocumentTable::TYPE_MOVING => "CCatalogMovingDocs",
		Catalog\StoreDocumentTable::TYPE_RETURN => "CCatalogReturnsDocs",
		Catalog\StoreDocumentTable::TYPE_DEDUCT => "CCatalogDeductDocs",
		Catalog\StoreDocumentTable::TYPE_UNDO_RESERVE => "CCatalogUnReservedDocs",
	];

	public const DELETE_CONDUCTED_ERROR = 1;
	private const STORE_CONTROL_DISABLED_CONDUCT_ERROR = 'store_control_disabled_conduct';

	public const CONDUCTED = 'Y';
	public const CANCELLED = 'C';

	/**
	 * @param $id
	 * @param $arFields
	 * @return bool
	 */
	public static function update($id, $arFields)
	{
		/** @global CDataBase $DB */
		global $DB;
		global $APPLICATION;
		$id = (int)$id;

		if ($id <= 0)
		{
			return false;
		}

		$oldFields = Catalog\StoreDocumentTable::getById($id)->fetch();
		if (empty($oldFields))
		{
			return false;
		}
		$allOldFields = $oldFields;

		$isConducted = $oldFields['STATUS'] === 'Y';
		$isStatusChangingToUnconducted = isset($arFields['STATUS']) && $arFields['STATUS'] === 'N';
		if ($isConducted && !$isStatusChangingToUnconducted)
		{
			$APPLICATION->ThrowException(GetMessage('CAT_DOC_SAVE_CONDUCTED_DOCUMENT'));
			return false;
		}

		foreach(GetModuleEvents("catalog", "OnBeforeDocumentUpdate", true) as $arEvent)
			if(ExecuteModuleEventEx($arEvent, array($id, &$arFields)) === false)
				return false;

		if(array_key_exists('DATE_CREATE',$arFields))
			unset($arFields['DATE_CREATE']);
		if(array_key_exists('DATE_MODIFY', $arFields))
			unset($arFields['DATE_MODIFY']);
		if(array_key_exists('DATE_STATUS', $arFields))
			unset($arFields['DATE_STATUS']);
		if(array_key_exists('CREATED_BY', $arFields))
			unset($arFields['CREATED_BY']);

		$arFields['~DATE_MODIFY'] = $DB->GetNowFunction();

		if (!static::checkFields('UPDATE', $arFields))
		{
			return false;
		}

		$oldFields = array_intersect_key($oldFields, $arFields);

		$strUpdate = $DB->PrepareUpdate("b_catalog_store_docs", $arFields);

		if(!empty($strUpdate))
		{
			$strSql = "update b_catalog_store_docs set ".$strUpdate." where ID = ".$id;
			if(!$DB->Query($strSql, true, "File: ".__FILE__."<br>Line: ".__LINE__))
				return false;

			if(isset($arFields["ELEMENT"]))
			{
				foreach($arFields["ELEMENT"] as $arElement)
				{
					if(is_array($arElement))
						CCatalogStoreDocsElement::update($arElement["ID"], $arElement);
				}
			}

			foreach(GetModuleEvents("catalog", "OnDocumentUpdate", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($id, $arFields, $oldFields));
		}

		if (isset($arFields['DOCUMENT_FILES']) && is_array($arFields['DOCUMENT_FILES']))
		{
			static::saveFiles($id, $arFields['DOCUMENT_FILES']);
		}

		$item = [
			'id' => $id,
			'data' => [
				'fields' => $arFields,
				'oldFields' => $allOldFields,
			],
		];

		PullManager::getInstance()->sendDocumentsUpdatedEvent(
			[
				$item,
			]
		);

		return true;
	}

	protected static function saveFiles(int $documentId, array $files)
	{
		if (empty($files))
		{
			return;
		}

		// load current file list
		$existingFiles = [];
		$fileMap = [];
		$iterator = Catalog\StoreDocumentFileTable::getList([
			'select' => [
				'ID',
				'FILE_ID',
			],
			'filter' => [
				'=DOCUMENT_ID' => $documentId,
			],
		]);
		while ($row = $iterator->fetch())
		{
			$id = (int)$row['ID'];
			$fileId = (int)$row['FILE_ID'];
			$existingFiles[$id] = [
				'ID' => $id,
				'FILE_ID' => $fileId,
			];
			$fileMap[$fileId] = $id;
		}
		unset($iterator, $row);

		// convert the new list of files to array format for each line if needed
		$files = static::convertFileList($fileMap, $files);
		if (empty($files))
		{
			return;
		}

		// checking that the passed set of document files is full
		foreach (array_keys($existingFiles) as $rowId)
		{
			if (!isset($files[$rowId]))
			{
				$files[$rowId] = $existingFiles[$rowId];
			}
		}

		// process file list
		$parsed = [];
		foreach ($files as $rowId => $row)
		{
			// replace or delete existing file
			if (
				is_int($rowId)
				&& is_array($row)
				&& isset($existingFiles[$rowId])
			)
			{
				// delete file
				if (
					isset($row['DEL'])
					&& $row['DEL'] === 'Y'
				)
				{
					$resultInternal = Catalog\StoreDocumentFileTable::delete($rowId);
					if ($resultInternal->isSuccess())
					{
						CFile::Delete($existingFiles[$rowId]['FILE_ID']);
					}
				}
				// replace file
				elseif (
					isset($row['FILE_ID'])
				)
				{
					if ($row['FILE_ID'] !== $existingFiles[$rowId]['FILE_ID'])
					{
						$resultInternal = Catalog\StoreDocumentFileTable::update(
							$rowId,
							[
								'FILE_ID' => $row['FILE_ID'],
							]
						);
						if ($resultInternal->isSuccess())
						{
							CFile::Delete($existingFiles[$rowId]['FILE_ID']);
						}
					}
				}
			}
			// save new file
			elseif (
				preg_match('/^n[0-9]+$/', $rowId, $parsed)
				&& is_array($row)
			)
			{
				// file already saved from external code
				if (isset($row['FILE_ID']))
				{
					$resultInternal = Catalog\StoreDocumentFileTable::add([
						'DOCUMENT_ID' => $documentId,
						'FILE_ID' => $row['FILE_ID'],
					]);
					if ($resultInternal->isSuccess())
					{
						$id = (int)$resultInternal->getId();
						$fileMap[$row['FILE_ID']] = $id;
						$existingFiles[$id] = [
							'ID' => $id,
							'FILE_ID' => $row['FILE_ID'],
						];
					}
				}
				// save uploaded file
				elseif (
					isset($row['FILE_UPLOAD'])
					&& is_array($row['FILE_UPLOAD'])
				)
				{
					$row['FILE_UPLOAD']['MODULE_ID'] = 'catalog';
					$fileId = (int)CFile::SaveFile(
						$row['FILE_UPLOAD'],
						'catalog',
						false,
						true
					);
					if ($fileId > 0)
					{
						$resultInternal = Catalog\StoreDocumentFileTable::add([
							'DOCUMENT_ID' => $documentId,
							'FILE_ID' => $fileId,
						]);
						if ($resultInternal->isSuccess())
						{
							$id = (int)$resultInternal->getId();
							$fileMap[$fileId] = $id;
							$existingFiles[$id] = [
								'ID' => $id,
								'FILE_ID' => $fileId,
							];
						}
					}
				}
			}
		}
	}

	protected static function convertFileList(array $fileMap, array $files): array
	{
		$formatArray = false;
		$formatOther = false;
		foreach ($files as $value)
		{
			if (is_array($value))
			{
				$formatArray = true;
			}
			else
			{
				$formatOther = true;
			}
		}
		unset($value);

		if ($formatArray && $formatOther)
		{
			return [];
		}

		if ($formatArray)
		{
			return $files;
		}

		$counter = 0;
		$list = array_values(array_unique($files));
		$files = [];
		$parsed = [];
		foreach ($list as $value)
		{
			if (!is_string($value))
			{
				continue;
			}
			if (preg_match('/^delete([0-9]+)$/', $value, $parsed))
			{
				$value = (int)$parsed[1];
				if (isset($fileMap[$value]))
				{
					$id = $fileMap[$value];
					$files[$id] = [
						'DEL' => 'Y',
					];
				}
			}
			elseif (preg_match('/^[0-9]+$/', $value, $parsed))
			{
				$value = (int)$value;
				if (isset($fileMap[$value]))
				{
					$id = $fileMap[$value];
					$files[$id] = [
						'ID' => $id,
						'FILE_ID' => $value,
					];
				}
				else
				{
					$id = 'n' . $counter;
					$counter++;
					$files[$id] = [
						'ID' => null,
						'FILE_ID' => $value,
					];
				}
			}
		}
		unset($value, $list);
		unset($id, $counter);

		return $files;
	}

	/**
	 * @param $id
	 * @return bool
	 */
	public static function delete($id)
	{
		global $DB;
		$id = (int)$id;
		if ($id <= 0)
		{
			return false;
		}

		$iterator = Catalog\StoreDocumentTable::getList([
			'select' => [
				'ID',
				'STATUS',
			],
			'filter' => [
				'=ID' => $id,
			],
		]);
		$document = $iterator->fetch();
		if (empty($document))
		{
			return false;
		}
		unset($iterator);

		if ($document['STATUS'] === 'Y')
		{
			$GLOBALS['APPLICATION']->ThrowException(
				Loc::getMessage('CAT_DOC_WRONG_STATUS'),
				self::DELETE_CONDUCTED_ERROR
			);
			return false;
		}

		$events = GetModuleEvents('catalog', 'OnBeforeDocumentDelete', true);
		foreach($events as $event)
		{
			ExecuteModuleEventEx($event, [$id]);
		}

		$DB->Query("DELETE FROM b_catalog_store_docs WHERE ID = ".$id, true);

		static::deleteDocumentFiles($id);
		Catalog\StoreDocumentElementTable::deleteByDocument($id);
		Catalog\StoreDocumentBarcodeTable::deleteByDocument($id);

		$contractorsProvider = Contractor\Provider\Manager::getActiveProvider();
		if ($contractorsProvider)
		{
			$contractorsProvider::onAfterDocumentDelete($id);
		}

		// First and second event - only for compatibility. Attention - order cannot change
		$eventList = [
			'OnDocumentBarcodeDelete',
			'OnDocumentElementDelete',
			'OnDocumentDelete',
		];

		foreach ($eventList as $eventName)
		{
			foreach (GetModuleEvents('catalog', $eventName, true) as $event)
			{
				ExecuteModuleEventEx($event, [$id]);
			}
		}

		$item = [
			'id' => $id,
		];
		PullManager::getInstance()->sendDocumentDeletedEvent(
			[
				$item,
			]
		);

		return true;
	}

	protected static function deleteDocumentFiles(int $documentId): void
	{
		$documentFiles = Catalog\StoreDocumentFileTable::getList([
			'select' => ['FILE_ID'],
			'filter' => ['=DOCUMENT_ID' => $documentId],
		])->fetchAll();

		$fileIds = array_column($documentFiles, 'FILE_ID');
		foreach ($fileIds as $fileId)
		{
			CFile::Delete($fileId);
		}

		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		$connection->queryExecute(
			'delete from ' . $helper->quote(Catalog\StoreDocumentFileTable::getTableName())
			. ' where ' . $helper->quote('DOCUMENT_ID') . ' = ' . $documentId
		);
	}

	/**
	 * @param $action
	 * @param $arFields
	 * @return bool
	 */
	protected static function checkFields($action, &$arFields)
	{
		global $DB;
		global $APPLICATION;

		if((($action == 'ADD') || isset($arFields["DOC_TYPE"])) && $arFields["DOC_TYPE"] == '' && !isset(self::$types[$arFields["DOC_TYPE"]]))
		{
			$APPLICATION->ThrowException(GetMessage("CAT_DOC_WRONG_TYPE"));
			return false;
		}
		if((($action == 'ADD') || isset($arFields["SITE_ID"])) && $arFields["SITE_ID"] == '' )
		{
			$APPLICATION->ThrowException(GetMessage("CAT_DOC_WRONG_SITE_ID"));
			return false;
		}
		if((($action == 'ADD') || isset($arFields["RESPONSIBLE_ID"])) && $arFields["RESPONSIBLE_ID"] == '' )
		{
			$APPLICATION->ThrowException(GetMessage("CAT_DOC_WRONG_RESPONSIBLE"));
			return false;
		}
		if ($action == 'ADD' || array_key_exists('STATUS', $arFields))
		{
			$arFields['STATUS'] = (isset($arFields['STATUS']) && 'Y' === $arFields['STATUS'] ? 'Y' : 'N');
		}
		if(isset($arFields["STATUS"]))
		{
			$arFields['~DATE_STATUS'] = $DB->GetNowFunction();
		}
		if(isset($arFields["DATE_DOCUMENT"]) && (!$DB->IsDate($arFields["DATE_DOCUMENT"])))
		{
			unset($arFields["DATE_DOCUMENT"]);
			$arFields['~DATE_DOCUMENT'] = $DB->GetNowFunction();
		}
		return true;
	}

	/**
	 * @param $documentId
	 * @param int $userId
	 * @return bool|string
	 */
	public static function conductDocument($documentId, $userId = 0)
	{
		global $APPLICATION;

		if (!UseStore::isUsed())
		{
			$APPLICATION->ThrowException(
				Loc::getMessage('CAT_DOC_CONDUCT_UNCONDUCT_NOT_AVAILABLE'),
				self::STORE_CONTROL_DISABLED_CONDUCT_ERROR,
			);
			return false;
		}

		$documentId = (int)$documentId;
		$userId = (int)$userId;
		$currency = null;
		$contractorId = 0;

		$docTypes = CCatalogDocs::getList(
			[],
			[
				'ID' => $documentId,
			],
			false,
			false,
			[
				'ID',
				'DOC_TYPE',
				'CURRENCY',
				'CONTRACTOR_ID',
				'STATUS',
			]
		);

		if($docType = $docTypes->Fetch())
		{
			if ($docType['STATUS'] !== self::CONDUCTED)
			{
				/** @var \CCatalogDocsTypes $documentClass */
				$documentClass = self::$types[$docType['DOC_TYPE']];

				if($docType['CURRENCY'] <> '')
				{
					$currency = $docType['CURRENCY'];
				}

				if($docType['CONTRACTOR_ID'] <> '')
				{
					$contractorId = $docType['CONTRACTOR_ID'];
				}

				$result = $documentClass::conductDocument($documentId, $userId, $currency, $contractorId);
				if($result)
				{
					$docFields = [
						'STATUS' => self::CONDUCTED,
					];

					if($userId > 0)
					{
						$docFields['STATUS_BY'] = $userId;
						$docFields['MODIFIED_BY'] = $userId;
					}

					if(!self::update($documentId, $docFields))
					{
						return false;
					}
				}

				if ($result !== false)
				{
					AddEventToStatFile('catalog', 'conductDocument', 'success', $docType['DOC_TYPE']);
				}

				return $result;
			}

			$APPLICATION->ThrowException(Loc::getMessage('CAT_DOC_STATUS_ALREADY_YES'));
		}

		return false;
	}

	/**
	 * @param $documentId
	 * @param int $userId
	 * @return array|bool|string
	 */
	public static function cancellationDocument($documentId, $userId = 0)
	{
		global $APPLICATION;

		if (!UseStore::isUsed())
		{
			$APPLICATION->ThrowException(
				GetMessage('CAT_DOC_CONDUCT_UNCONDUCT_NOT_AVAILABLE'),
				self::STORE_CONTROL_DISABLED_CONDUCT_ERROR
			);
			return false;
		}

		$result = false;
		$documentId = (int)$documentId;
		$userId = (int)$userId;
		$docType = null;
		$dbDocType = CCatalogDocs::getList(
			array(),
			array("ID" => $documentId),
			false,
			false,
			array('ID', 'DOC_TYPE', 'STATUS')
		);
		if($arDocType = $dbDocType->Fetch())
		{
			$docType = $arDocType["DOC_TYPE"];
			if($arDocType["STATUS"] !== "Y")
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("CAT_DOC_ERROR_CANCEL_STATUS"));
				return false;
			}
			/** @var \CCatalogDocsTypes $documentClass */
			$documentClass = self::$types[$arDocType["DOC_TYPE"]];

			$result = $documentClass::cancellationDocument($documentId, $userId);
			if($result !== false)
			{
				$arDocFields = [
					'STATUS' => 'N',
					'WAS_CANCELLED' => 'Y',
				];

				if ($userId > 0)
				{
					$arDocFields["STATUS_BY"] = $userId;
				}
				if (!self::update($documentId, $arDocFields))
				{
					return false;
				}
			}
		}

		if ($result !== false)
		{
			AddEventToStatFile('catalog', 'cancelDocument', 'success', $docType);
		}

		return $result;
	}

	public static function OnIBlockElementDelete($productID)
	{
		global $DB;
		$productID = (int)$productID;
		if($productID > 0)
		{
			$dbDeleteElements = CCatalogStoreDocsElement::getList(array(), array("ELEMENT_ID" => $productID), false, false, array('ID'));
			while($arDeleteElements = $dbDeleteElements->fetch())
			{
				CCatalogStoreDocsElement::delete($arDeleteElements["ID"]);
			}
			return $DB->Query("delete from b_catalog_store_barcode where PRODUCT_ID = ".$productID, true);
		}
		return true;
	}

	public static function OnCatalogStoreDelete($storeID)
	{
		global $DB;
		$storeID = (int)$storeID;
		if ($storeID <= 0)
			return false;

		return $DB->Query("delete from b_catalog_store_barcode where STORE_ID = ".$storeID, true);
	}

	public static function OnBeforeIBlockElementDelete($productID)
	{
		global $APPLICATION;

		$productID = (int)$productID;
		if ($productID > 0)
		{
			$iterator = Catalog\StoreDocumentElementTable::getList([
				'select' => [
					'ELEMENT_ID',
					'ELEMENT_NAME' => 'ELEMENT.NAME',
				],
				'filter' => [
					'=ELEMENT_ID' => $productID,
				],
				'limit' => 1,
			]);
			$row = $iterator->fetch();
			unset($iterator);
			if (!empty($row))
			{
				$APPLICATION->ThrowException(GetMessage(
					'CAT_DOC_ERROR_ELEMENT_IN_DOCUMENT_EXISTS',
					[
						'#ID#' => $row['ELEMENT_ID'],
						'#NAME#' => $row['ELEMENT_NAME'],
					]
				));

				return false;
			}
		}

		return true;
	}
}
