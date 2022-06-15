<?php

use Bitrix\Catalog;
use Bitrix\Catalog\Component\UseStore;
use Bitrix\Catalog\Integration\PullManager;
use Bitrix\Main\Localization\Loc;

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

		//TODO: need refactor next methods
		\CCatalogStoreDocsBarcode::OnBeforeDocumentDelete($id);
		\CCatalogStoreDocsElement::OnDocumentBarcodeDelete($id);

		$events = GetModuleEvents('catalog', 'OnDocumentDelete', true);
		foreach($events as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$id]);
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
			$arFields['STATUS'] = ('Y' == $arFields['STATUS'] ? 'Y' : 'N');
		}
		if(isset($arFields["STATUS"]))
		{
			$arFields['~DATE_STATUS'] = $DB->GetNowFunction();
		}
		if(isset($arFields["DATE_DOCUMENT"]) && (!$DB->IsDate($arFields["DATE_DOCUMENT"])))
		{
			unset($arFields["DATE_DOCUMENT"]);
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

		$result = '';
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
			$dbStoreDocs = Catalog\StoreDocumentTable::getList(['select' => ['ID'], 'filter' => ['ELEMENTS.ELEMENT_ID' => $productID]]);
			if ($arStoreDocs = $dbStoreDocs->fetch())
			{
				$APPLICATION->ThrowException(GetMessage("CAT_DOC_ERROR_ELEMENT_IN_DOCUMENT_EXT_2"));
				return false;
			}
		}
		return true;
	}
}
