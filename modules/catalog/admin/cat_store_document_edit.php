<?php
/** @global CMain $APPLICATION */

use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SiteTable;
use Bitrix\Main\UserTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Type;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\UI\FileInput;
use Bitrix\Main\Web\PostDecodeFilter;
use Bitrix\Main\Web\Json;
use Bitrix\Catalog;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Currency;
use Bitrix\Catalog\v2\Contractor\Provider\Manager;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/prolog.php');

Loc::loadMessages(__FILE__);

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

function showStoreDocumentDate($field, string $name, bool $isNew, bool $readOnly): string
{
	$showDate = null;
	if ($field instanceof Type\DateTime)
	{
		$showDate = $field->toString();
	}
	if ($readOnly)
	{
		$showDate = (string)$showDate;

		return $showDate === ''
			? Loc::getMessage('CAT_DOC_MESS_EMPTY_DATE')
			: $showDate
		;
	}

	if (isset($_POST[$name]) && is_string($_POST[$name]))
	{
		$sourceDate = $_POST[$name];
	}
	else
	{
		$sourceDate = $showDate;
		if ($sourceDate === null)
		{
			$sourceDate = '';
			if ($isNew)
			{
				$sourceDate = date(Type\Date::convertFormatToPhp(CSite::GetDateFormat('FULL')));
			}
		}
	}

	return CalendarDate(
		$name,
		$sourceDate,
		'form_catalog_document_form',
		'15',
		'class="typeinput"'
	);
}

function getStoreListForControl(?int $value, array $activeStores, array $allStores, int $defaultStoreId): string
{
	$result = '';
	$nonSelect = false;
	$list = [];

	if ($value !== null && !isset($allStores[$value]))
	{
		$value = null;
		$nonSelect = true;
		$list['-'] = [
			'ID' => '-',
			'TITLE' => GetMessage('CAT_DOC_MESS_STORE_IS_NOT_SELECT'),
		];
	}
	foreach (array_keys($activeStores) as $storeId)
	{
		$list[$storeId] = $activeStores[$storeId];
	}
	if ($value !== null && !isset($activeStores[$value]))
	{
		$list[$value] = $allStores[$value];
	}
	if ($value !== null)
	{
		$selectedId = $value;
	}
	else
	{
		$selectedId = $nonSelect
			? '-'
			: $defaultStoreId
		;
	}

	foreach ($list as $storeId => $row)
	{
		$result .= '<option value="' . $storeId . '"'
			. ($storeId === $selectedId ? ' selected' : '') . '>'
			. $row['TITLE']
			. '</option>'
		;
	}

	return $result;
}

function getRequiredFieldCssClass(array $fields, string $fieldName): string
{
	return isset($fields[$fieldName]['required']) && $fields[$fieldName]['required'] === 'Y'
		? ' class="adm-detail-required-field"'
		: ''
	;
}

$selfFolderUrl = $adminPage->getSelfFolderUrl();
$listUrl = $selfFolderUrl."cat_store_document_list.php?lang=".LANGUAGE_ID;
$listUrl = $adminSidePanelHelper->editUrlToPublicPage($listUrl);
$userSearchUrl = $selfFolderUrl . 'user_search.php?lang=' . LANGUAGE_ID . '&JSFUNC=setResponsible';

$currentUser = CurrentUser::get();

Loader::includeModule('catalog');

$accessController = AccessController::getCurrent();
if (!$accessController->check(ActionDictionary::ACTION_STORE_VIEW))
{
	$APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

if (Manager::getActiveProvider())
{
	LocalRedirect($listUrl);
}

$canModify = $accessController->check(ActionDictionary::ACTION_STORE_VIEW);
$bReadOnly = !$canModify;

$publicMode = $adminPage->publicMode;
$canViewUserList = (
	$currentUser->canDoOperation('view_subordinate_users')
	|| $currentUser->canDoOperation('view_all_users')
	|| $currentUser->canDoOperation('edit_all_users')
	|| $currentUser->canDoOperation('edit_subordinate_users')
);

if ($publicMode)
{
	$canViewUserList = false;
}

$request = Context::getCurrent()->getRequest();
if ($request->isAjaxRequest())
{
	$request->addFilter(new PostDecodeFilter);
}

$isAjaxDocumentRequest = $request->get('AJAX_MODE') === 'Y';

if (
	$request->isPost()
	&& $request->getPost('BARCODE_AJAX') === 'Y'
	&& check_bitrix_sessid()
)
{
	$result = [];
	$barcode = $request->getPost('BARCODE');
	$barcode = trim(is_string($barcode) ? $barcode : '');
	if ($barcode !== '')
	{
		$iterator = Catalog\StoreBarcodeTable::getList([
			'select' => [
				'ID',
				'PRODUCT_ID',
				'BARCODE',
			],
			'filter' => [
				'=BARCODE' => $barcode,
				'@PRODUCT.TYPE' => [
					Catalog\ProductTable::TYPE_PRODUCT,
					Catalog\ProductTable::TYPE_OFFER,
				]
			],
			'limit' => 1,
		]);
		$row = $iterator->fetch();
		if (!empty($row))
		{
			$result = [
				'id' => (int)$row['PRODUCT_ID'],
				'barcode' => $row['BARCODE'],
			];
		}
		unset($row, $iterator);
	}

	header('Content-Type: application/json');
	CMain::FinalActions(Json::encode($result));
}

$ID = (int)($request->get('ID') ?? 0);
if ($ID < 0)
{
	$ID = 0;
}

$userId = (int)$currentUser->getId();
$docType = (string)($request->get('DOCUMENT_TYPE') ?? '');

$defaultValues = [
	'ID' => 0,
	'TITLE' => '',
	'SITE_ID' => '',
	'DOC_TYPE' => $docType,
	'DOC_NUMBER' => '',
	'CONTRACTOR_ID' => '',
	'DATE_MODIFY' => '',
	'DATE_CREATE' => '',
	'CREATED_BY' => '',
	'MODIFIED_BY' => '',
	'RESPONSIBLE_ID' => $userId,
	'CURRENCY' => Currency\CurrencyManager::getBaseCurrency(),
	'STATUS' => 'N',
	'WAS_CANCELLED' => 'N',
	'DATE_STATUS' => '',
	'DATE_DOCUMENT' => '',
	'STATUS_BY' => '',
	'TOTAL' => '',
	'COMMENTARY' => '',
	'ITEMS_ORDER_DATE' => '',
	'ITEMS_RECEIVED_DATE' => '',
	'DOCUMENT_FILES' => [],
];

$fields = $defaultValues;
if ($ID > 0)
{
	$fields = Catalog\StoreDocumentTable::getRowById($ID);
	if ($fields === null)
	{
		$ID = 0;
		$fields = $defaultValues;
	}
	else
	{
		$docType = $fields['DOC_TYPE'];

		$fields['DOCUMENT_FILES'] = [];
		$iterator = Catalog\StoreDocumentFileTable::getList([
			'select' => [
				'ID',
				'FILE_ID',
			],
			'filter' => [
				'=DOCUMENT_ID' => $ID,
			],
		]);
		while ($row = $iterator->fetch())
		{
			$fields['DOCUMENT_FILES'][$row['ID']] = $row['FILE_ID'];
		}
		unset($row, $iterator);
	}
}

$isDocumentConduct = $fields['STATUS'] === 'Y';
if ($isDocumentConduct)
{
	$bReadOnly = true;
}

$listDocType = Catalog\StoreDocumentTable::getTypeList(true);

if (!isset($listDocType[$docType]))
{
	$adminSidePanelHelper->localRedirect($listUrl);
	LocalRedirect($listUrl);
}

/** @var array $typeFieldList */
$typeFieldList = CCatalogStoreControlUtil::getTypeFields($docType);
if (empty($typeFieldList))
{
	$adminSidePanelHelper->sendSuccessResponse("close");
	$adminSidePanelHelper->localRedirect($listUrl);
	LocalRedirect($listUrl);
}
$documentFields = $typeFieldList['DOCUMENT'];
$elementFields = $typeFieldList['ELEMENT'];
unset($typeFieldList);

$shopSites = [];
$allSites = [];

$siteIterator = SiteTable::getList([
	'select' => [
		'LID',
		'NAME',
		'SORT',
	],
	'filter' => [
		'=ACTIVE' => 'Y',
	],
	'order' => [
		'SORT' => 'ASC',
		'LID' => 'ASC',
	],
]);
while ($site = $siteIterator->fetch())
{
	$saleSite = Option::get('sale', 'SHOP_SITE_'.$site['LID']);
	if ($site['LID'] == $saleSite)
	{
		$shopSites[] = [
			'ID' => $site['LID'],
			'NAME' => $site['NAME'],
		];
	}
	$allSites[] = [
		'ID' => $site['LID'],
		'NAME' => $site['NAME'],
	];
}
unset($saleSite, $site, $siteIterator);

if (empty($shopSites))
{
	$shopSites = $allSites;
}
unset($allSites);

$rsContractors = CCatalogContractor::GetList();
$arContractors = [];
while($arContractor = $rsContractors->Fetch())
{
	$arContractors[] = $arContractor;
}
unset($arContractor, $rsContractors);

$arResult = [];

$allStores = [];
$activeStores = [];
$defaultStoreId = 0;
$iterator = Catalog\StoreTable::getList([
	'select' => [
		'ID',
		'IS_DEFAULT',
		'TITLE',
		'ADDRESS',
		'SORT',
		'ACTIVE',
	],
	'order' => [
		'IS_DEFAULT' => 'DESC',
		'SORT' => 'ASC',
	],
]);
while ($row = $iterator->fetch())
{
	$row['ID'] = (int)$row['ID'];
	$row['TITLE'] = (string)$row['TITLE'];
	$row['TITLE'] .= ($row['TITLE'] !== '' ? ' (' .$row['ADDRESS'] . ')' : $row['ADDRESS']);
	$row['TITLE'] = htmlspecialcharsbx($row['TITLE']);

	$allStores[$row['ID']] = $row;
	if ($row['ACTIVE'] === 'Y')
	{
		$activeStores[$row['ID']] = $row;
	}
	if ($row['IS_DEFAULT'] === 'Y')
	{
		$defaultStoreId = $row['ID'];
	}
}
unset($row, $iterator);

$errorList = '';
$error = false;
$arGeneral = [];
if (
	$request->isPost()
	&& $request->getPost('Update') === 'Y'
	&& $canModify
	&& check_bitrix_sessid()
)
{
	$currentAction = '';
	if ($isDocumentConduct)
	{
		if ($request->getPost('cancellation') !== null)
		{
			$currentAction = 'cancellation';
		}
	}
	else
	{
		if ($request->getPost('save_document') !== null)
		{
			$currentAction = 'save';
		}
		elseif ($request->getPost('save_and_conduct') !== null)
		{
			$currentAction = 'conduct';
		}
	}
	$saveAction = $currentAction === 'save';
	$conductAction = $currentAction === 'conduct';
	$cancelAction = $currentAction === 'cancellation';

	if ($saveAction || $conductAction)
	{
		$arGeneral = [
			'DOC_TYPE' => $docType,
			'MODIFIED_BY' => $userId,
		];

		$stringList = [
			'SITE_ID',
			'TITLE',
			'DOC_NUMBER',
			'COMMENTARY',
			'CURRENCY',
		];
		foreach ($stringList as $fieldId)
		{
			$value = $request->getPost($fieldId);
			if (is_string($value))
			{
				$arGeneral[$fieldId] = $value;
			}
		}

		$dateList = [
			'DATE_DOCUMENT',
			'ITEMS_ORDER_DATE DATETIME NULL',
			'ITEMS_RECEIVED_DATE',
		];
		foreach ($dateList as $fieldId)
		{
			$value = $request->getPost($fieldId);
			if (is_string($value) && Type\DateTime::tryParse($value) !== null)
			{
				$arGeneral[$fieldId] = $value;
			}
		}

		$userList = [
			'RESPONSIBLE_ID',
			'CONTRACTOR_ID',
		];
		foreach ($userList as $fieldId)
		{
			$value = $request->getPost($fieldId);
			if (is_string($value))
			{
				$value = (int)$value;
				if ($value > 0)
				{
					$arGeneral[$fieldId] = $value;
				}
			}
		}

		$floatList = [
			'TOTAL',
		];
		foreach ($floatList as $fieldId)
		{
			$value = $request->getPost($fieldId);
			if (is_string($value))
			{
				$arGeneral[$fieldId] = (float)$value;
			}
		}

		$fileList = [
			'DOCUMENT_FILES',
		];
		$fileValues = \CCatalogStoreControlUtil::getMultipleFilesFromPost($request, $fileList);
		if (!empty($fileValues) && is_array($fileValues))
		{
			foreach ($fileList as $fieldId)
			{
				if (isset($fileValues[$fieldId]))
				{
					$arGeneral[$fieldId] = $fileValues[$fieldId];
				}
			}
		}

		$arGeneral = array_intersect_key($arGeneral, $documentFields);

		if ($ID > 0)
		{
			$result = CCatalogDocs::update($ID, $arGeneral);
		}
		else
		{
			$arGeneral['CREATED_BY'] = $userId;
			$ID = CCatalogDocs::add($arGeneral);
			$result = $ID !== false;
			if (!$result)
			{
				$ID = 0;
			}
		}
		if (!$result)
		{
			$error = true;
			$ex = $APPLICATION->GetException();
			if ($ex)
			{
				$errorList = $ex->GetString();
			}
			else
			{
				$errorList = Loc::getMessage('CAT_DOC_ERR_SAVE_COMMON_UNKNOWN');
			}
		}

		if (!$error)
		{
			$dbElement = CCatalogStoreDocsElement::getList(
				[],
				[
					'=DOC_ID' => $ID,
				],
				false,
				false,
				[
					'ID',
				]
			);
			while ($arElement = $dbElement->Fetch())
			{
				CCatalogStoreDocsElement::delete($arElement['ID']);
			}
			unset($arElement, $dbElement);

			$dbDocsBarcode = CCatalogStoreDocsBarcode::getList(
				[],
				[
					'=DOC_ID' => $ID,
				],
				false,
				false,
				['ID']
			);
			while ($arDocsBarcode = $dbDocsBarcode->Fetch())
			{
				CCatalogStoreDocsBarcode::delete($arDocsBarcode['ID']);
			}
			unset($arDocsBarcode, $dbDocsBarcode);

			$arProducts = $request->getPost('PRODUCT');
			if (!empty($arProducts) && is_array($arProducts))
			{
				foreach ($arProducts as $key => $val)
				{
					$storeTo = $val["STORE_TO"];
					$storeFrom = $val["STORE_FROM"];

					$arAdditional = [
						"AMOUNT" => $val["AMOUNT"],
						"ELEMENT_ID" => $val["PRODUCT_ID"],
						"PURCHASING_PRICE" => $val["PURCHASING_PRICE"],
						"STORE_TO" => $storeTo,
						"STORE_FROM" => $storeFrom,
						"ENTRY_ID" => $key,
						"DOC_ID" => $ID,
					];

					if (!empty($val['BASE_PRICE']))
					{
						$arAdditional['BASE_PRICE'] = $val['BASE_PRICE'];
					}

					$docElementId = CCatalogStoreDocsElement::add($arAdditional);
					if ($docElementId && isset($val["BARCODE"]))
					{
						$arBarcode = [];
						if (!empty($val["BARCODE"]))
						{
							$arBarcode = explode(', ', $val["BARCODE"]);
						}

						if (!empty($arBarcode))
						{
							foreach ($arBarcode as $barCode)
							{
								CCatalogStoreDocsBarcode::add([
									"BARCODE" => $barCode,
									"DOC_ELEMENT_ID" => $docElementId,
									"DOC_ID" => $ID,
								]);
							}
						}
					}
				}
			}
			unset($arProducts);

			if ($saveAction)
			{
				$saveDocumentUrl = $selfFolderUrl . "cat_store_document_edit.php?lang=" . LANGUAGE_ID . "&ID=" . $ID;
				if ($adminSidePanelHelper->isPublicSidePanel())
				{
					$saveDocumentUrl = CHTTP::urlAddParams($saveDocumentUrl,
						["IFRAME" => "Y", "IFRAME_TYPE" => "SIDE_SLIDER"]);
				}
				$adminSidePanelHelper->sendSuccessResponse("apply", ["ID" => $ID, 'reloadUrl' => $saveDocumentUrl]);
				$saveDocumentUrl = $adminSidePanelHelper->editUrlToPublicPage($saveDocumentUrl);
				$adminSidePanelHelper->localRedirect($listUrl);
				LocalRedirect($saveDocumentUrl);
			}
		}
	}

	if (!$error)
	{
		if ($conductAction || $cancelAction)
		{
			$conn = Application::getConnection();
			$conn->startTransaction();

			$result = false;
			if ($conductAction)
			{
				$result = CCatalogDocs::conductDocument($ID, $userId);
			}
			elseif ($cancelAction)
			{
				$result = CCatalogDocs::cancellationDocument($ID, $userId);
			}

			if ($result === true)
			{
				$conn->commitTransaction();
			}
			else
			{
				$conn->rollbackTransaction();
			}

			if ($result !== true)
			{
				$TAB_TITLE = $listDocType[$docType];
				$APPLICATION->SetTitle(str_replace("#ID#", $ID, Loc::getMessage("CAT_DOC_TITLE_EDIT_EXT"))
					. ". "
					. $TAB_TITLE
					. ".");
				$ex = $APPLICATION->GetException();
				if (is_object($ex))
				{
					$strError = $ex->GetString();
				}
				else
				{
					$strError = Loc::getMessage('CAT_DOC_ERR_CHANGE_STATUS_UNKNOWN');
				}
				if (!empty($result) && is_array($result))
				{
					$strError .= CCatalogStoreControlUtil::showErrorProduct($result);
				}
				$adminSidePanelHelper->sendJsonErrorResponse($strError);
				require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
				CAdminMessage::ShowMessage($strError);
				$error = true;
			}
			else
			{
				$documentUrl = $selfFolderUrl
					. "cat_store_document_edit.php?lang="
					. LANGUAGE_ID
					. "&ID="
					. $ID
					. "&IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER&publicSidePanel=Y";
				$adminSidePanelHelper->sendSuccessResponse("base", ['documentUrl' => $documentUrl]);
				$adminSidePanelHelper->localRedirect($listUrl);
				LocalRedirect($listUrl);
			}
		}
	}
}

if ($request->getPost('dontsave') !== null)
{
	$adminSidePanelHelper->sendSuccessResponse("close");
	$adminSidePanelHelper->localRedirect($listUrl);
	LocalRedirect($listUrl);
}

$sTableID = "b_catalog_store_docs_".$docType;
$oSort = new CAdminSorting($sTableID, "ID", "ASC");
$lAdmin = new CAdminList($sTableID, $oSort);

if ($ID > 0 || $isAjaxDocumentRequest)
{
	$arAllDocumentElement = [];

	$arResult = $fields;

	if (!$isAjaxDocumentRequest)
	{
		$dbDocumentElement = CCatalogStoreDocsElement::getList(
			[
				'ID' => 'ASC',
			],
			[
				'DOC_ID' => $ID,
			],
			false,
			false,
			[
				'ID',
				'STORE_FROM',
				'STORE_TO',
				'ELEMENT_ID',
				'AMOUNT',
				'BASE_PRICE',
				'PURCHASING_PRICE',
				'IS_MULTIPLY_BARCODE',
				'RESERVED',
			]
		);
		while($arDocumentElements = $dbDocumentElement->Fetch())
		{
			$arDocumentElements['ID'] = (int)$arDocumentElements['ID'];
			$arDocumentElements['ELEMENT_ID'] = (int)$arDocumentElements['ELEMENT_ID'];
			if ($arDocumentElements['STORE_FROM'] !== null)
			{
				$arDocumentElements['STORE_FROM'] = (int)$arDocumentElements['STORE_FROM'];
				if ($arDocumentElements['STORE_FROM'] <= 0)
				{
					$arDocumentElements['STORE_FROM'] = null;
				}
			}
			if ($arDocumentElements['STORE_TO'] !== null)
			{
				$arDocumentElements['STORE_TO'] = (int)$arDocumentElements['STORE_TO'];
				if ($arDocumentElements['STORE_TO'] <= 0)
				{
					$arDocumentElements['STORE_TO'] = null;
				}
			}

			$arAllDocumentElement[] = $arDocumentElements;
		}
		unset($arDocumentElements, $dbDocumentElement);
	}
	else
	{
		$requestProducts = $request->getPost('PRODUCT');
		$newElements = $request->getPost('ELEMENT_ID');
		$existsProducts = is_array($requestProducts);
		$existsNewElements = is_array($newElements);
		if ($existsProducts || $existsNewElements)
		{
			$arElements = [];
			if ($existsProducts)
			{
				$arElements = $requestProducts;
			}
			if ($existsNewElements)
			{
				foreach ($newElements as $row)
				{
					if (empty($row) || !is_array($row))
					{
						continue;
					}
					if (!isset($row['id']))
					{
						continue;
					}

					$arElements[] = [
						'PRODUCT_ID' => $row['id'],
						'SELECTED_BARCODE' => $row['barcode'] ?? '',
						'AMOUNT' => $row['quantity'] ?? 1,
					];
				}
			}
			$arAllAddedProductsId = [];
			$arAjaxElementInfo = [];
			foreach ($arElements as $eachAddElement)
			{
				if (isset($eachAddElement['PRODUCT_ID']))
				{
					$arAllAddedProductsId[] = (int)$eachAddElement['PRODUCT_ID'];
				}
			}
			$iterator = Catalog\ProductTable::getList([
				'select' => [
					'ID',
					'BARCODE_MULTI',
					'QUANTITY_RESERVED',
					'PURCHASING_PRICE',
					'PURCHASING_CURRENCY',
				],
				'filter' => [
					'@ID' => $arAllAddedProductsId,
				],
			]);
			while ($arElement = $iterator->fetch())
			{
				$arAjaxElementInfo[$arElement['ID']] = [
					'IS_MULTIPLY_BARCODE' => $arElement['BARCODE_MULTI'],
					'RESERVED' => $arElement['QUANTITY_RESERVED'],
					'PURCHASING_PRICE' => $arElement['PURCHASING_PRICE'],
					'PURCHASING_CURRENCY' => $arElement['PURCHASING_CURRENCY'],
				];
			}
			unset($arElement, $iterator);
			if (!empty($arElements))
			{
				foreach ($arElements as &$arAjaxElement)
				{
					$elementId = (int)$arAjaxElement['PRODUCT_ID'];
					$arAjaxElement['ELEMENT_ID'] = $elementId;
					if ($arAjaxElement['SELECTED_BARCODE'] == '')
					{
						$arAjaxElement['SELECTED_BARCODE'] = $arAjaxElement['BARCODE'];
					}
					$arAjaxElement['BARCODE'] = [$arAjaxElement['BARCODE']];
					if (!empty($arAjaxElementInfo[$elementId]))
					{
						$arAjaxElement['IS_MULTIPLY_BARCODE'] = $arAjaxElementInfo[$elementId]['IS_MULTIPLY_BARCODE'];
						$arAjaxElement['RESERVED'] = $arAjaxElementInfo[$elementId]['RESERVED'];
						if (
							(float)$arAjaxElement['PURCHASING_PRICE'] <= 0
							&& (float)$arAjaxElementInfo[$elementId]['PURCHASING_PRICE'] > 0
						)
						{
							$arAjaxElement['PURCHASING_PRICE'] = $arAjaxElementInfo[$elementId]['PURCHASING_PRICE'];
							$arAjaxElement['PURCHASING_CURRENCY'] = $arAjaxElementInfo[$elementId]['PURCHASING_CURRENCY'];
						}
					}
					if (isset($arAjaxElement['STORE_FROM']))
					{
						$arAjaxElement['STORE_FROM'] = (int)$arAjaxElement['STORE_FROM'];
						if ($arAjaxElement['STORE_FROM'] <= 0)
						{
							$arAjaxElement['STORE_FROM'] = null;
						}
					}
					if (isset($arAjaxElement['STORE_TO']))
					{
						$arAjaxElement['STORE_TO'] = (int)$arAjaxElement['STORE_TO'];
						if ($arAjaxElement['STORE_TO'] <= 0)
						{
							$arAjaxElement['STORE_TO'] = null;
						}
					}
					unset($elementId);
				}
				unset($arAjaxElement);
			}

			$arAllDocumentElement = $arElements;
		}
	}

	foreach($arAllDocumentElement as $arDocumentElement)
	{
		$arElement = [];
		$arElementBarcode = [];
		$isMultiSingleBarcode = $selectedBarcode = false;
		foreach($arDocumentElement as $key => $value)
		{
			$arElement[$key] = $value;
		}

		if($arDocumentElement["IS_MULTIPLY_BARCODE"] == 'N')
		{
			if(isset($arElement["BARCODE"]))
				unset($arElement["BARCODE"]);
			$dbDocumentStoreBarcode = CCatalogStoreBarCode::getList(
				[],
				[
					'PRODUCT_ID' => $arDocumentElement['ELEMENT_ID'],
				]
			);
			while($arDocumentStoreBarcode = $dbDocumentStoreBarcode->Fetch())
			{
				$arElementBarcode[] = $arDocumentStoreBarcode["BARCODE"];
			}
			if(count($arElementBarcode) > 1)
			{
				$isMultiSingleBarcode = true;

				if($bReadOnly)
				{
					$arElementBarcode = [];
				}
			}
		}

		if($arDocumentElement["IS_MULTIPLY_BARCODE"] == 'Y' || $isMultiSingleBarcode)
		{
			$dbDocumentElementBarcode = CCatalogStoreDocsBarcode::getList(
				[],
				[
					'DOC_ELEMENT_ID' => $arDocumentElement["ID"],
				],
				false,
				false,
				[
					'BARCODE',
				]
			);
			while($arDocumentElementBarcode = $dbDocumentElementBarcode->Fetch())
			{
				if($isMultiSingleBarcode)
				{
					$selectedBarcode = $arDocumentElementBarcode["BARCODE"];
					if(empty($arElementBarcode))
						$arElementBarcode[] = $arDocumentElementBarcode["BARCODE"];
				}
				else
				{
					$arElementBarcode[] = $arDocumentElementBarcode["BARCODE"];
				}
			}
		}

		if(!isset($arElement["BARCODE"]))
			$arElement["BARCODE"] = $arElementBarcode;
		if(!isset($arElement["SELECTED_BARCODE"]))
			$arElement["SELECTED_BARCODE"] = $selectedBarcode;

		if (!isset($arElement['BASE_PRICE']))
		{
			$priceResult = Catalog\Model\Price::getList([
				'select' => [
					'ID',
					'PRICE',
				],
				'filter' => [
					'=PRODUCT_ID' => $arElement['PRODUCT_ID'],
					'=CATALOG_GROUP_ID' => Catalog\GroupTable::getBasePriceTypeId(),
					'=CURRENCY' => $arResult['CURRENCY'],
				],
				'order' => [
					'ID' => 'ASC',
				],
				'limit' => 1,
			]);
			$priceData = $priceResult->fetch();
			if ($priceData)
			{
				$arElement['BASE_PRICE'] = $priceData['PRICE'];
			}
		}

		$arResult["ELEMENT"][] = $arElement;
	}
}

if (!$accessController->check(ActionDictionary::ACTION_STORE_VIEW))
{
	$isDocumentConduct = false;
}

$aContext = array();
if(!$bReadOnly)
{
	$aContext = array(
		array(
			"TEXT" => Loc::getMessage("CAT_DOC_FIND_ITEMS"),
			"ICON" => "btn_new",
			"TITLE" => Loc::getMessage("CAT_DOC_FIND_ITEMS"),
			"ONCLICK" => "addProductSearch(1);",
		),
		array(
			"HTML" => Loc::getMessage(
				"CAT_DOC_LINK_FIND",
				array("#LINK#" => '<a href="javascript:void(0);" onClick="findBarcodeDivHider()">'.Loc::getMessage('CAT_DOC_BARCODE_FIND_LINK').'</a>')
			),
		),
		array(
			"HTML" => '<div id="cat_barcode_find_div" style="display: none;">'.
						'<input type="text" id="CAT_DOC_BARCODE_FIND" style="margin: 0 10px;">'.
						'<a href="javascript:void(0);" class="adm-btn" onclick="productSearch(BX(\'CAT_DOC_BARCODE_FIND\').value);">'.Loc::getMessage('CAT_DOC_BARCODE_FIND').'</a>'.
						'</div>',
		),
	);
}

$useXmlId = Option::get('iblock', 'show_xml_id') === 'Y';

$visibleHeaderIds = [];
$arHeaders = array(
	array(
		"id" => "IMAGE",
		"content" => Loc::getMessage("CAT_DOC_PRODUCT_PICTURE"),
		"default" => true
	),
	array(
		"id" => "TITLE",
		"content" => Loc::getMessage("CAT_DOC_PRODUCT_NAME"),
		"default" => true
	),
);
if ($useXmlId)
{
	$arHeaders[] = array(
		"id" => "XML_ID",
		"content" => Loc::getMessage("CAT_DOC_PRODUCT_XML_ID"),
		"default" => true
	);
}
if (isset($elementFields["RESERVED"]))
{
	$arHeaders[] = array(
		"id" => "RESERVED",
		"content" => $elementFields["RESERVED"]["name"],
		"title" => $elementFields["RESERVED"]["title"],
		"default" => $elementFields["RESERVED"]["required"] === 'Y',
	);
	$visibleHeaderIds[] = "RESERVED";
}
if (isset($elementFields["BASE_PRICE"]))
{
	$arHeaders[] = array(
		"id" => "BASE_PRICE",
		"content" => $elementFields["BASE_PRICE"]["name"],
		"title" => $elementFields["BASE_PRICE"]["title"],
		"default" => $elementFields["BASE_PRICE"]["required"] === 'Y',
	);
	$visibleHeaderIds[] = "BASE_PRICE";
}
if (isset($elementFields["AMOUNT"]))
{
	$arHeaders[] = array(
		"id" => "AMOUNT",
		"content" => $elementFields["AMOUNT"]["name"],
		"title" => $elementFields["AMOUNT"]["title"],
		"default" => $elementFields["AMOUNT"]["required"] === 'Y',
	);
	$visibleHeaderIds[] = "AMOUNT";
}
if (isset($elementFields["NET_PRICE"]))
{
	$arHeaders[] = array(
		"id" => "PURCHASING_PRICE",
		"content" => $elementFields["NET_PRICE"]["name"],
		"title" => $elementFields["NET_PRICE"]["title"],
		"default" => $elementFields["NET_PRICE"]["required"] === 'Y',
	);
	$visibleHeaderIds[] = "PURCHASING_PRICE";
}
if (isset($elementFields["TOTAL"]))
{
	$arHeaders[] = array(
		"id" => "SUMM",
		"content" => Loc::getMessage("CAT_DOC_PRODUCT_SUMM"),
		"default" => $elementFields["TOTAL"]["required"] === 'Y',
	);
	$visibleHeaderIds[] = "SUMM";
}
if (isset($elementFields["STORE_FROM"]))
{
	$arHeaders[] = array(
		"id" => "STORE_FROM",
		"content" => $elementFields["STORE_FROM"]["name"],
		"title" => $elementFields["STORE_FROM"]["title"],
		"default" => $elementFields["STORE_FROM"]["required"] === 'Y',
	);
	$visibleHeaderIds[] = "STORE_FROM";
}
if (isset($elementFields["STORE_TO"]))
{
	$arHeaders[] = array(
		"id" => "STORE_TO",
		"content" => $elementFields["STORE_TO"]["name"],
		"title" => $elementFields["STORE_TO"]["title"],
		"default" => $elementFields["STORE_TO"]["required"] === 'Y',
	);
	$visibleHeaderIds[] = "STORE_TO";
}
if (isset($elementFields["BAR_CODE"]))
{
	$arHeaders[] = array(
		"id" => "BARCODE",
		"content" => $elementFields["BAR_CODE"]["name"],
		"title" => $elementFields["BAR_CODE"]["title"],
		"default" => $elementFields["BAR_CODE"]["required"] === 'Y',
	);
	$visibleHeaderIds[] = "BARCODE";
}

$lAdmin->AddHeaders($arHeaders);
if (!empty($visibleHeaderIds))
{
	foreach ($visibleHeaderIds as $headerId)
		$lAdmin->AddVisibleHeaderColumn($headerId);
	unset($headerId);
}

$isDisable = $bReadOnly ? " disabled" : "";
$maxId = 0;
if(is_array($arResult["ELEMENT"]))
{
	foreach($arResult["ELEMENT"] as $code => $value)
	{
		$isMultiply = ('Y' == $value["IS_MULTIPLY_BARCODE"]);
		$arProductInfo = CCatalogStoreControlUtil::getProductInfo($value["ELEMENT_ID"]);
		if(is_array($arProductInfo))
			$value = array_merge($value, $arProductInfo);

		$arRes['ID'] = (int)$code;
		$maxId = ($arRes['ID'] > $maxId) ? $arRes['ID'] : $maxId;

		$arRows[$arRes['ID']] = $row =& $lAdmin->AddRow($arRes['ID']);
		$row->AddViewField("IMAGE", CFile::ShowImage($value['DETAIL_PICTURE'], 80, 80, "border=0", "", true));
		if ($value['EDIT_PAGE_URL'])
		{
			$editPageUrl = $value['EDIT_PAGE_URL'];
			$editPageUrl = $adminSidePanelHelper->editUrlToPublicPage($editPageUrl);
			$value['EDIT_PAGE_URL'] = $editPageUrl;
		}
		$row->AddViewField("TITLE", '<a target="_top" href ="'.$value['EDIT_PAGE_URL'].'"> '.$value['NAME'].'</a><input value="'.$value['ELEMENT_ID'].'" type="hidden" name="PRODUCT['.$arRes['ID'].'][PRODUCT_ID]" id="PRODUCT_ID_'.$arRes['ID'].'">');
		if ($useXmlId)
		{
			$row->AddViewField('XML_ID', $value['XML_ID']);
		}
		$readOnly = ($isMultiply && !$bReadOnly) ? ' readonly' : '';
		if(isset($value['BARCODE']) && $isMultiply)
		{
			$barcodeCount = 0;
			$tmpBarcodeCount = count($value['BARCODE']);
			if (1 < $tmpBarcodeCount)
			{
				$barcodeCount = $tmpBarcodeCount;
			}
			elseif (1 == $tmpBarcodeCount)
			{
				if (isset($value['BARCODE'][0]) && $value['BARCODE'][0] != '')
					$barcodeCount = count(explode(', ', $value['BARCODE'][0]));
			}
			unset($tmpBarcodeCount);
		}
		elseif(!$isMultiply)
		{
			$barcodeCount = count($value['BARCODE']);
		}
		else
		{
			$barcodeCount = $value['AMOUNT'];
		}

		if (isset($elementFields["BASE_PRICE"]))
		{
			$row->AddViewField("BASE_PRICE", '<div> <input name="PRODUCT['.$arRes['ID'].'][BASE_PRICE]" onchange="recalculateRow('.$arRes['ID'].');" id="CAT_DOC_BASE_PRICE_'.$arRes['ID'].'" value="'.$value['BASE_PRICE'].'" type="text" size="10"'.$isDisable.'></div>');
		}
		if (isset($elementFields["AMOUNT"]))
		{
			$row->AddViewField("AMOUNT", '<div><input type="hidden" id="CAT_DOC_AMOUNT_HIDDEN_'.$arRes['ID'].'" value="'.$barcodeCount.'" onchange="recalculateRow('.$arRes['ID'].');"> <input name="PRODUCT['.$arRes['ID'].'][AMOUNT]" onchange="recalculateRow('.$arRes['ID'].');" id="CAT_DOC_AMOUNT_'.$arRes['ID'].'" value="'.$value['AMOUNT'].'" type="text" size="10"'.$isDisable.'></div>');
		}
		if (isset($elementFields["NET_PRICE"]))
		{
			$row->AddViewField("PURCHASING_PRICE", '<div> <input name="PRODUCT['.$arRes['ID'].'][PURCHASING_PRICE]" onchange="recalculateRow('.$arRes['ID'].');" id="CAT_DOC_PURCHASING_PRICE_'.$arRes['ID'].'" value="'.$value['PURCHASING_PRICE'].'" type="text" size="10"'.$isDisable.'></div>');
		}
		if (isset($elementFields["TOTAL"]))
		{
			$row->AddViewField("SUMM", '<div id="CAT_DOC_SUMM_'.$arRes['ID'].'">'.CCurrencyLang::CurrencyFormat((float)$value['AMOUNT'] * (float)$value['PURCHASING_PRICE'], $fields['CURRENCY']).'</div><input value="'.doubleval($value['AMOUNT']) * doubleval($value['PURCHASING_PRICE']).'" type="hidden" name="PRODUCT['.$arRes['ID'].'][SUMM]" id="PRODUCT_'.$arRes['ID'].'_SUMM">');
		}
		if (isset($elementFields["STORE_FROM"]))
		{
			$storeHtml = '<select style="max-width:300px; width:300px;"'
				. ' name="PRODUCT['.$arRes['ID'].'][STORE_FROM]"'
				. ' id="CAT_DOC_STORE_FROM_' . $arRes['ID'] . '"'
				. $isDisable . '>'
				. getStoreListForControl(
					$value['STORE_FROM'],
					$activeStores,
					$allStores,
					$defaultStoreId
				)
				. '</select>'
			;
			$row->AddViewField('STORE_FROM', $storeHtml);
			unset($storeHtml);
		}
		if (isset($elementFields["STORE_TO"]))
		{
			$storeHtml = '<select style="max-width:300px; width:300px;"'
				. ' name="PRODUCT[' . $arRes['ID'] . '][STORE_TO]"'
				. ' id="CAT_DOC_STORE_TO_' . $arRes['ID'] . '"'
				. $isDisable . '>'
				. getStoreListForControl(
					$value['STORE_TO'],
					$activeStores,
					$allStores,
					$defaultStoreId
				)
				. '</select>'
			;
			$row->AddViewField('STORE_TO', $storeHtml);
			unset($storeHtml);
		}
		if (isset($elementFields["RESERVED"]))
		{
			$row->AddViewField("RESERVED", '<div > <input readonly name="PRODUCT['.$arRes['ID'].'][RESERVED]" id="CAT_DOC_RESERVED_'.$arRes['ID'].'" value="'.$value['RESERVED'].'" type="text" size="10"'.$isDisable.'></div>');
		}
		if (isset($elementFields["BAR_CODE"]) && isset($value['BARCODE']) && is_array($value['BARCODE']))
		{
			$barcode = implode(", ", $value['BARCODE']);
			if($isMultiply)
			{
				$readOnly = ($bReadOnly) ? ' readonly' : '';
				$buttonValue = ($bReadOnly) ? Loc::getMessage('CAT_DOC_BARCODES_VIEW') : Loc::getMessage('CAT_DOC_BARCODES_ENTER');
				if(empty($barcode))
					$barcode = '';
				$inputBarcode = '<input type="button" value="'.$buttonValue.'" onclick="enterBarcodes('.$arRes['ID'].');"><input '.$readOnly.' type="hidden" value="'.htmlspecialcharsbx($barcode).'" type="text" name="PRODUCT['.$arRes['ID'].'][BARCODE]" id="PRODUCT['.$arRes['ID'].'][BARCODE]" onchange="recalculateRow('.$arRes['ID'].');" size="20">';
			}
			elseif(count($value['BARCODE']) < 2)
				$inputBarcode = htmlspecialcharsbx($barcode);
			else
			{
				$inputBarcode = '<select style="max-width:150px; width:150px;" id="PRODUCT['.$arRes['ID'].'][BARCODE]" name="PRODUCT['.$arRes['ID'].'][BARCODE]"> ';
				foreach($value['BARCODE'] as $singleCode)
				{
					$selected = ($value["SELECTED_BARCODE"] == $singleCode) ? ' selected' : '';
					$inputBarcode .= '<option value="'.htmlspecialcharsbx($singleCode).'"'.$selected.'>'.htmlspecialcharsbx($singleCode).'</option>';
				}
				$inputBarcode .= '</select>';
			}
			$row->AddViewField("BARCODE", '<div id="CAT_BARCODE_DIV_BIND_'.$arRes['ID'].'" align="center">'.$inputBarcode.'</div>');
		}
		$arActions = array();
		if (!$bReadOnly)
		{
			$arActions[] = array(
				"ICON" => "delete",
				"TEXT" => Loc::getMessage("CAT_DOC_DEL"),
				"ACTION" => "if(confirm('".CUtil::JSEscape(Loc::getMessage('CAT_DOC_CONFIRM_DELETE'))."')) deleteRow(".$arRes['ID'].")"
			);
			$arActions[] = [
				"ICON" => "copy",
				"TEXT" => Loc::getMessage("CAT_DOC_COPY"),
				"ACTION" => "copyRow(".CUtil::PhpToJSObject(['id' => $value['ELEMENT_ID'], 'parent' => $arRes['ID']]).")",
			];
		}
		$row->AddActions($arActions);
		$row->bReadOnly = true;
	}
	unset($row);
}

$lAdmin->AddGroupActionTable(
	array(
		'summ' => array(
			'type' => 'html',
			'value' => ''
		)
	),
	array("disable_action_target" => true)
);


$lAdmin->AddAdminContextMenu($aContext, false, true);
$lAdmin->CheckListMode();

if ($ID > 0)
{
	$pageTitleParams = [
		'#ID#' => $ID,
		'#TYPE#' => $listDocType[$docType],
	];
	if ($bReadOnly)
	{
		$pageTitle = Loc::getMessage(
			'CAT_DOC_TITLE_VIEW',
			$pageTitleParams
		);
	}
	else
	{
		$pageTitle = Loc::getMessage(
			'CAT_DOC_TITLE_EDIT',
			$pageTitleParams
		);
	}
	$APPLICATION->SetTitle($pageTitle);
	unset($pageTitle, $pageTitleParams);
}
else
{
	$APPLICATION->SetTitle(Loc::getMessage(
		'CAT_DOC_TITLE_NEW',
		['#TYPE#' => $listDocType[$docType]]
	));
}

if ($isAjaxDocumentRequest)
{
	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_js.php');
}
else
{
	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');
}
CJSCore::Init(array('file_input', 'currency'));
$APPLICATION->SetAdditionalCSS('/bitrix/panel/catalog/catalog_store_docs.css');

$aMenu = array(
	array(
		"TEXT" => Loc::getMessage("CAT_DOC_LIST_EXT"),
		"ICON" => "btn_list",
		"LINK" => $listUrl
	)
);

$context = new CAdminContextMenu($aMenu);
$context->Show();

if (!empty($errorList))
{
	CAdminMessage::ShowMessage([
		'DETAILS' => $errorList,
		'TYPE' => 'ERROR',
		'MESSAGE' => Loc::getMessage('CAT_DOC_ERR_SAVE'),
		'HTML' => true,
	]);
}

$currencyList = [];
$currencyIterator = Currency\CurrencyTable::getList([
	'select' => [
		'CURRENCY',
	],
]);
while ($currency = $currencyIterator->fetch())
{
	$currencyFormat = CCurrencyLang::GetFormatDescription($currency['CURRENCY']);
	$currencyList[] = [
		'CURRENCY' => $currency['CURRENCY'],
		'FORMAT' => [
			'FORMAT_STRING' => $currencyFormat['FORMAT_STRING'],
			'DEC_POINT' => $currencyFormat['DEC_POINT'],
			'THOUSANDS_SEP' => $currencyFormat['THOUSANDS_SEP'],
			'DECIMALS' => $currencyFormat['DECIMALS'],
			'THOUSANDS_VARIANT' => $currencyFormat['THOUSANDS_VARIANT'],
			'HIDE_ZERO' => $currencyFormat['HIDE_ZERO'],
		],
	];
}
unset($currencyFormat, $currency, $currencyIterator);

$actionUrl = $APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&DOCUMENT_TYPE=".htmlspecialcharsbx($docType);
$actionUrl = $adminSidePanelHelper->setDefaultQueryParams($actionUrl);

if (!$isAjaxDocumentRequest):
?>
<form enctype="multipart/form-data" method="POST" action="<?=$actionUrl?>" id="form_b_catalog_store_docs" name="form_b_catalog_store_docs" onsubmit="return checkBarcodeSearch();">
	<?php
	echo GetFilterHiddens("filter_");?>
	<input type="hidden" name="Update" value="Y">
	<input type="hidden" name="apply" value="Y">
	<input type="hidden" name="lang" value="<?php echo LANGUAGE_ID; ?>">
	<input type="hidden" name="ID" value="<?php echo $ID ?>">
	<input type="hidden" name="DOCUMENT_TYPE" id="DOCUMENT_TYPE" value="<?php echo htmlspecialcharsbx($docType);?>">
	<input type="hidden" name="productAdd" id="productAdd" value="N">
	<input value="<?=$maxId?>" type="hidden" id="ROW_MAX_ID">
	<?=bitrix_sessid_post()?>
	<div class="adm-detail-block" id="tabControl_layout">
		<div class="adm-detail-content-wrap">
			<div class="adm-detail-content-item-block">
				<table class="adm-detail-content-table edit-table" id="cat-doc-table">
					<tbody>
					<?php
					if (isset($documentFields['STATUS'])):
					?>
					<tr<?php echo getRequiredFieldCssClass($documentFields, 'STATUS'); ?>>
						<td style="width: 40%;" class="adm-detail-content-cell-l"><span class="cat-doc-status-left-<?=$fields['STATUS'];?>"><?=Loc::getMessage('CAT_DOC_STATUS')?>:</span></td>
						<td style="width: 60%;" class="adm-detail-content-cell-r">
							<span class="cat-doc-status-right-<?=$fields['STATUS']?>">
								<?php
								if ($fields['STATUS'] === 'Y')
								{
									$status = Catalog\StoreDocumentTable::STATUS_CONDUCTED;
								}
								else
								{
									$status = $fields['WAS_CANCELLED'] === 'Y'
										? Catalog\StoreDocumentTable::STATUS_CANCELLED
										: Catalog\StoreDocumentTable::STATUS_DRAFT
									;
								}
								echo Catalog\StoreDocumentTable::getStatusName($status);
								?>
							</span>
						</td>
					</tr>
					<?php
					endif;
					if (isset($documentFields['TITLE'])):
					?>
					<tr<?php echo getRequiredFieldCssClass($documentFields, 'TITLE'); ?>>
						<td style="width: 40%;" class="adm-detail-content-cell-l"><?=Loc::getMessage('CAT_DOC_TITLE')?>:</td>
						<td style="width: 60%;" class="adm-detail-content-cell-r">
							<input type="text" name="TITLE" value="<?=htmlspecialcharsbx($fields['TITLE']); ?>" <?=$isDisable?> maxlenght="255" size="50">
						</td>
					</tr>
					<?php
					endif;
					if (isset($documentFields['DOC_NUMBER'])):
					?>
					<tr<?php echo getRequiredFieldCssClass($documentFields, 'DOC_NUMBER'); ?>>
						<td style="width: 40%;" class="adm-detail-content-cell-l"><?=Loc::getMessage('CAT_DOC_DOC_NUMBER')?>:</td>
						<td style="width: 60%;" class="adm-detail-content-cell-r">
							<input type="text" name="DOC_NUMBER" value="<?=htmlspecialcharsbx($fields['DOC_NUMBER']); ?>" <?=$isDisable?> maxlenght="64" size="50">
						</td>
					</tr>
					<?php
					endif;
					if (isset($documentFields['DATE_DOCUMENT'])):
					?>
					<tr<?php echo getRequiredFieldCssClass($documentFields, 'DATE_DOCUMENT'); ?>>
						<td style="width: 40%;" class="adm-detail-content-cell-l"><?=Loc::getMessage('CAT_DOC_DATE_DOCUMENT')?>:</td>
						<td style="width: 60%;" class="adm-detail-content-cell-r">
							<?php
							echo showStoreDocumentDate(
								$fields['DATE_DOCUMENT'],
								'DATE_DOCUMENT',
								$ID === 0,
								$bReadOnly
							);
							?>
						</td>
					</tr>
					<?php
					endif;
					if (isset($documentFields['DOCUMENT_FILES'])):
					?>
					<tr<?php echo getRequiredFieldCssClass($documentFields, 'DOCUMENT_FILES'); ?>>
						<td style="width: 40%;" class="adm-detail-content-cell-l"><?= Loc::getMessage('CAT_DOC_DOCUMENT_FILES') ?></td>
						<td style="width: 60%;" class="adm-detail-content-cell-r">
						<?php
							$baseConfig = [
								'name' => 'DOCUMENT_FILES[n#IND#]',
								'description' => false,
								'allowUpload' => FileInput::UPLOAD_ANY_FILES,
								'allowUploadExt' => '',
							];
							if ($bReadOnly)
							{
								$uploadConfig = [
									'upload' => false,
									'medialib' => false,
									'fileDialog' => false,
									'cloud' => false,
									'delete' => false,
								];
							}
							else
							{
								$uploadConfig = [
									'upload' => true,
									'medialib' => false,
									'fileDialog' => true,
									'cloud' => false,
									'delete' => true,
								];
							}

							$fileInput = FileInput::createInstance(
								$baseConfig
								+ $uploadConfig
							);

							$showFiles = [];
							foreach ($fields['DOCUMENT_FILES'] as $fileRowId => $fileId)
							{
								$showFiles['DOCUMENT_FILES[' . $fileRowId . ']'] = $fileId;
							}

							echo $fileInput->show($showFiles, $error);
						?>
						</td>
					</tr>
					<?php
					endif;
					if (isset($documentFields['SITE_ID'])):
					?>
					<tr<?php echo getRequiredFieldCssClass($documentFields, 'SITE_ID'); ?>>
						<td style="width: 40%;" class="adm-detail-content-cell-l"><?= Loc::getMessage("CAT_DOC_SITE_ID") ?>:</td>
						<td style="width: 60%;" class="adm-detail-content-cell-r">
							<select id="SITE_ID" name="SITE_ID" <?=$isDisable?>>
								<?php
								foreach($shopSites as $key => $val)
								{
									$selected = ($val['ID'] == $fields['SITE_ID']) ? 'selected' : '';
									echo '<option ' . $selected . ' value="' . htmlspecialcharsbx($val['ID']) . '">'
										. htmlspecialcharsbx($val['NAME'] . ' (' . $val['ID'] . ')') . '</option>'
									;
								}
							?>
							</select>
						</td>
					</tr>
					<?php
					endif;
					if (isset($documentFields["CONTRACTOR_ID"])):
					?>
						<tr<?php echo getRequiredFieldCssClass($documentFields, 'CONTRACTOR_ID'); ?>>
							<td style="width: 40%;" class="adm-detail-content-cell-l"><?= Loc::getMessage("CAT_DOC_CONTRACTOR") ?>:</td>
							<td style="width: 60%;" class="adm-detail-content-cell-r">
								<?php
								if (!empty($arContractors) && is_array($arContractors)):?>
									<select style="max-width:300px"  name="CONTRACTOR_ID" <?=$isDisable?>>
										<?php
										foreach($arContractors as $key => $val)
										{
											$selected = ($val['ID'] == $fields['CONTRACTOR_ID']) ? 'selected' : '';
											$companyName = ($val["PERSON_TYPE"] == CONTRACTOR_INDIVIDUAL) ? htmlspecialcharsbx($val["PERSON_NAME"]) : htmlspecialcharsbx($val["COMPANY"]." (".$val["PERSON_NAME"].")");
											echo '<option ' . $selected . ' value="' . $val['ID'] . '">'
												. $companyName . '</option>'
											;
										}
									?>
									</select>
								<?php
								else:?>
									<?php
										$contractorEditUrl = $selfFolderUrl."cat_contractor_edit.php?lang=".LANGUAGE_ID;
										$contractorEditUrl = $adminSidePanelHelper->editUrlToPublicPage($contractorEditUrl);
									?>
									<a target="_top" href="<?=$contractorEditUrl?>"><?=Loc::getMessage("CAT_DOC_CONTRACTOR_ADD")?></a>
								<?php
								endif;?>
							</td>
						</tr>
					<?php
					endif;
					if (isset($documentFields["CURRENCY"])):
					?>
						<tr<?php echo getRequiredFieldCssClass($documentFields, 'CURRENCY'); ?>>
							<td style="width: 40%;" class="adm-detail-content-cell-l"><?= Loc::getMessage("CAT_DOC_CURRENCY") ?>:</td>
							<td style="width: 60%;" class="adm-detail-content-cell-r"><?php
								echo CCurrency::SelectBox("CURRENCY", $fields['CURRENCY'], "", true, "", 'onChange="recalculateAllRows();" id="CAT_CURRENCY_STORE"'.$isDisable);?>
							</td>
						</tr>
					<?php
					endif;
					if (isset($documentFields['ITEMS_ORDER_DATE'])):
					?>
					<tr<?php echo getRequiredFieldCssClass($documentFields, 'ITEMS_ORDER_DATE'); ?>>
						<td style="width: 40%;" class="adm-detail-content-cell-l"><?=Loc::getMessage('CAT_DOC_ITEMS_ORDER_DATE')?>:</td>
						<td style="width: 60%;" class="adm-detail-content-cell-r">
							<?php
							echo showStoreDocumentDate(
								$fields['ITEMS_ORDER_DATE'],
								'ITEMS_ORDER_DATE',
								$ID === 0,
								$bReadOnly
							);
							?>
						</td>
					</tr>
					<?php
					endif;
					if (isset($documentFields['ITEMS_RECEIVED_DATE'])):
					?>
					<tr<?php echo getRequiredFieldCssClass($documentFields, 'ITEMS_RECEIVED_DATE'); ?>>
						<td style="width: 40%;" class="adm-detail-content-cell-l"><?=Loc::getMessage('CAT_DOC_ITEMS_RECEIVED_DATE')?>:</td>
						<td style="width: 60%;" class="adm-detail-content-cell-r">
							<?php
							echo showStoreDocumentDate(
								$fields['ITEMS_RECEIVED_DATE'],
								'ITEMS_RECEIVED_DATE',
								$ID === 0,
								$bReadOnly
							)
							?>
						</td>
					</tr>
					<?php
					endif;
					if (isset($documentFields['RESPONSIBLE_ID'])):
					?>
					<tr<?php echo getRequiredFieldCssClass($documentFields, 'RESPONSIBLE_ID'); ?>>
						<td style="width: 40%;" class="adm-detail-content-cell-l"><?php echo Loc::getMessage('CAT_DOC_RESPONSIBLE_ID'); ?>:</td>
						<td style="width: 60%;" class="adm-detail-content-cell-r"><?php
							?><input type="text" size="7" id="RESPONSIBLE_ID" name="RESPONSIBLE_ID" value="<?php echo htmlspecialcharsbx($fields['RESPONSIBLE_ID']); ?>"><?php
							if ($canViewUserList)
							{
								?>&nbsp;<input type="button" id="RESPONSIBLE_ID_BTN" value="<?php
									echo htmlspecialcharsbx(Loc::getMessage('CAT_DOC_RESPONSIBLE_ID_BTN_VALUE'));
								?>" title="<?php
									echo htmlspecialcharsbx(Loc::getMessage('CAT_DOC_RESPONSIBLE_ID_BTN_TITLE'));
								?>"><?php
							}
							?>&nbsp;<span id="RESPONSIBLE_NAME"><?php
							if ($fields['RESPONSIBLE_ID'] > 0)
							{
								$userIterator = UserTable::getList([
									'select' => [
										'ID',
										'LOGIN',
										'NAME',
										'LAST_NAME',
										'SECOND_NAME',
										'EMAIL',
										'TITLE',
									],
									'filter' => [
										'=ID' => $fields['RESPONSIBLE_ID'],
									],
								]);
								$userData = $userIterator->fetch();
								unset($userIterator);
								if (!empty($userData))
								{
									echo CUser::FormatName(
										CSite::GetNameFormat(true),
										$userData,
										true,
										true
									);
								}
							}
							?></span><?php
						?></td>
					</tr>
					<?php
					endif;
					if (isset($documentFields['COMMENTARY'])):
						?>
						<tr<?php echo getRequiredFieldCssClass($documentFields, 'COMMENTARY'); ?>>
							<td style="width: 40%;" class="adm-detail-content-cell-l"><?php echo Loc::getMessage('CAT_DOC_COMMENT'); ?>:</td>
							<td style="width: 60%;" class="adm-detail-content-cell-r">
								<textarea cols="80" rows="4" class="typearea" name="COMMENTARY" <?=$isDisable?>><?php
									echo htmlspecialcharsbx($fields['COMMENTARY']);
								?></textarea>
							</td>
						</tr>
					<?php
					endif;
					?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php

$aTabs = array();

$tabControl = new CAdminTabControl("storeDocument_".$docType, $aTabs);
$tabControl->Begin();

?><div id="productgrid"><?php

endif;

$lAdmin->DisplayList();

if (!$isAjaxDocumentRequest):
		?></div><?php
$tabControl->Buttons(
	array(
		"disabled" => $bReadOnly,
		"btnSave" => false,
		"btnApply" => false,
		"btnCancel" => false,
		"back_url" => $listUrl,
	)
);

if ($adminSidePanelHelper->isSidePanelFrame())
{
	if(!$bReadOnly && !$isDocumentConduct)
	{
		?>
		<span style="display:inline-block; width:20px; height: 22px;"></span>
		<input type="button" class="adm-btn-save" name="save_and_conduct" value="<?php echo Loc::getMessage("CAT_DOC_ADD_CONDUCT_EXT") ?>">
		<input type="button" class="adm-btn" name="save_document" value="<?php echo Loc::getMessage("CAT_DOC_SAVE") ?>">
		<?php
	}
	elseif($isDocumentConduct)
	{
		?>
		<span class="hor-spacer"></span>
		<input type="button" class="adm-btn" name="cancellation" value="<?php echo Loc::getMessage("CAT_DOC_CANCELLATION_EXT") ?>">
		<?php
	}
	?>
	<input type="button" class="adm-btn" name="dontsave" value="<?php echo Loc::getMessage("CAT_DOC_CANCEL") ?>">
	<?php
}
else
{
	if(!$bReadOnly && !$isDocumentConduct)
	{
		?>
		<span style="display:inline-block; width:20px; height: 22px;"></span>
		<input type="submit" class="adm-btn-save" name="save_and_conduct" value="<?php echo Loc::getMessage("CAT_DOC_ADD_CONDUCT_EXT") ?>">
		<input type="submit" class="adm-btn" name="save_document" value="<?php echo Loc::getMessage("CAT_DOC_SAVE") ?>">
		<?php
	}
	elseif($isDocumentConduct)
	{
		?>
		<span class="hor-spacer"></span>
		<input type="hidden" name="cancellation" id="cancellation" value = "0">
		<input type="button" class="adm-btn" onclick="if(confirm('<?=Loc::getMessage("CAT_DOC_CANCELLATION_CONFIRM_EXT")?>')) {BX('cancellation').value = 1; BX('form_b_catalog_store_docs').submit();}" value="<?php echo Loc::getMessage("CAT_DOC_CANCELLATION_EXT") ?>">
		<?php
	}
	?>
	<input type="submit" class="adm-btn" name="dontsave" id="dontsave" value="<?php echo Loc::getMessage("CAT_DOC_CANCEL") ?>">
	<?php
}

$tabControl->End();
?></form>
<script type="text/javascript">
BX.Currency.setCurrencies(<?php echo CUtil::PhpToJSObject($currencyList, false, true, true); ?>);
if (typeof showTotalSum === 'undefined')
{
	function showTotalSum()
	{
		<?php if(isset($documentFields["TOTAL"])):?>
		if(BX('<?=$sTableID?>'))
		{
			if(BX('<?=$sTableID?>'+'_footer'))
			{
				BX('<?=$sTableID?>'+'_footer').appendChild((BX.create('DIV', {
					props : {
						id : "CAT_DOCUMENT_SUMM"
					},
					style : {
						paddingLeft: '30%',
						marginTop: '5px',
						verticalAlign: 'middle',
						display: 'inline-block'
					},
					children : [
						BX.create('span', {
							props : {
								id : "CAT_DOCUMENT_SUMM_SPAN"
							},
							text : '<?=CUtil::JSEscape(Loc::getMessage('CAT_DOC_TOTAL'))?>',
							style : {
								fontSize: '14px',
								fontWeight: 'bold'
							}
						}),
						BX.create('input', {
							props : {
								type : "hidden",
								name : "TOTAL",
								id : "CAT_DOCUMENT_SUM",
								value : 0
							}
						})
					]
				})));
				recalculateAllRows();
			}
		}
		<?php endif;?>
	}

	function recalculateAllRows()
	{
		var rowCount = BX('ROW_MAX_ID');
		if (BX.type.isElementNode(rowCount))
		{
			var maxId = parseInt(rowCount.value);
			for (var i = 0; i <= maxId; i++)
			{
				recalculateRow(i);
			}
		}
	}

	function deleteRow(id)
	{
		if(BX('PRODUCT_ID_'+id))
		{
			var trDelete = (BX('PRODUCT_ID_'+id).parentNode.parentNode);
			if(trDelete)
			{
				trDelete.parentNode.removeChild(trDelete);
				recalculateRow(0);
			}
		}
	}

	function findBarcodeDivHider()
	{
		var findBarcodeDiv = BX('cat_barcode_find_div');
		if(findBarcodeDiv)
		{
			if(findBarcodeDiv.style.display == 'none')
			{
				findBarcodeDiv.style.display = 'block';
				BX('CAT_DOC_BARCODE_FIND').focus();
			}
			else
				findBarcodeDiv.style.display = 'none'
		}
	}

	function addProductSearch()
	{
		var store = 0,
			lid = '',
			popup;
		if(BX("CAT_DOC_STORE_FROM"))
			store = BX("CAT_DOC_STORE_FROM").value;
		if(BX("SITE_ID"))
			lid = BX("SITE_ID").value;
		popup = makeProductSearchDialog({
			caller: 'storeDocs',
			lang: '<?=LANGUAGE_ID?>',
			site_id: lid,
			callback: 'addRow',
			store_id: store
		});
		popup.Show();
	}

	function makeProductSearchDialog(params)
	{
		var caller = params.caller || '',
			lang = params.lang || 'ru',
			site_id = params.site_id || '',
			callback = params.callback || '',
			store_id = params.store_id || '0';

		var popup = new BX.CDialog({
			content_url: '<?=$selfFolderUrl?>cat_product_search_dialog.php?lang=' + lang
				+ '&LID=' + site_id + '&caller=' + caller
				+ '&func_name=' + callback
				+ '&STORE_FROM_ID=' + store_id
				+ '&multiple_select=Y',
			height: Math.max(500, window.innerHeight-400),
			width: Math.max(800, window.innerWidth-400),
			draggable: true,
			resizable: true,
			min_height: 500,
			min_width: 800
		});
		BX.addCustomEvent(popup, 'onWindowRegister', BX.defer(function(){
			popup.Get().style.position = 'fixed';
			popup.Get().style.top = (parseInt(popup.Get().style.top) - BX.GetWindowScrollPos().scrollTop) + 'px';
		}));
		return popup;
	}

	function addRow(elements)
	{
		if (!BX.type.isArrayFilled(elements))
		{
			return;
		}

		var data = {
			lang: BX.message('LANGUAGE_ID'),
			sessid: BX.bitrix_sessid(),
			ID: <?php echo $ID; ?>,
			DOCUMENT_TYPE: '<?php echo CUtil::JSEscape($docType); ?>',
			AJAX_MODE: 'Y'
		};
		var obProductAdd = BX('productAdd');
		if (BX.type.isElementNode(obProductAdd) && !obProductAdd.disabled)
		{
			data.addProduct = 'Y';
		}

		data.ELEMENT_ID = elements;

		var rowCount = BX('ROW_MAX_ID');
		if (BX.type.isElementNode(rowCount))
		{
			rowCount.value = (parseInt(rowCount.value) + elements.length).toString();
		}
		var form = BX('form_b_catalog_store_docs');
		if (!BX.type.isElementNode(form))
		{
			return;
		}

		var products = document.getElementById('productgrid');

		if (!BX.type.isElementNode(products))
		{
			return;
		}

		var elements = products.querySelectorAll('table input,table select');

		var nameTemplate = /^PRODUCT\[(\d+)\]\[(\w+)\]/;

		elements.forEach(function(item){
			if (BX.Type.isStringFilled(item.name))
			{
				var name = item.name;
				if (name === 'ID[]')
				{
					return;
				}
				var parsed = name.match(nameTemplate);
				if (parsed === null)
				{
					return;
				}

				var usedItem = true;
				if (item.type === 'radio' || item.type === 'checkbox')
				{
					if (!item.checked)
					{
						usedItem = false;
					}
				}
				if (!usedItem)
				{
					return;
				}

				var productIndex = parsed[1];
				var fieldName = parsed[2];

				if (!('PRODUCT' in data))
				{
					data.PRODUCT = {};
				}
				if (!(productIndex in data.PRODUCT))
				{
					data.PRODUCT[productIndex] = {};
				}
				data.PRODUCT[productIndex][fieldName] = item.value;
			}
		});

		BX.showWait();
		BX.ajax.post('<?=$actionUrl?>' + '&mode=frame', data, addRowResult);
	}

	function addRowResult(result)
	{
		BX.closeWait();
		var products = document.getElementById('productgrid');
		if (!BX.type.isElementNode(products))
		{
			return;
		}
		products.innerHTML = result;

		recalculateAllRows();
	}

	function copyRow(element)
	{
		let obProductAdd = BX('productAdd');
		if (BX.type.isElementNode(obProductAdd))
		{
			obProductAdd.disabled = true;
		}

		let item = {
			id: element.id
		};

		let sourceQuantity = BX('CAT_DOC_AMOUNT_' + element.parent);
		if (BX.type.isElementNode(sourceQuantity))
		{
			item.quantity = sourceQuantity.value;
		}

		addRow([item]);
	}

	function productSearch(barcode)
	{
		var dateURL = '<?=bitrix_sessid_get()?>&BARCODE_AJAX=Y&BARCODE='+barcode+'&lang=<?php echo LANGUAGE_ID; ?>';

		BX.showWait();
		BX.ajax.post('<?=$actionUrl?>', dateURL, fSearchProductResult);
	}

	function fSearchProductResult(result)
	{
		BX.closeWait();

		BX("CAT_DOC_BARCODE_FIND").value = '';
		BX("CAT_DOC_BARCODE_FIND").focus();

		if (result.length > 0)
		{
			let res = eval( '('+result+')' );
			if (res['id'] > 0)
			{
				res['quantity'] = 1;
				let obProductAdd = BX('productAdd');
				if (BX.type.isElementNode(obProductAdd))
				{
					obProductAdd.disabled = true;
				}
				addRow([res]);
			}
		}
	}

	function enterBarcodes(id)
	{
		var amount;
		if(BX('CAT_DOC_AMOUNT_HIDDEN_'+id))
			amount = parseInt(BX('CAT_DOC_AMOUNT_HIDDEN_'+id).value, 10);
		else
			amount = 0;
		if(isNaN(amount))
			amount = 0;
		maxId = amount;

		var
			content = BX.create('DIV', {
				props: {id : 'BARCODE_DIV_'+id },
				children: [
					BX.create('input', {
						props : {
							className: "BARCODE_INPUT_GREY", id : "BARCODE_INPUT_" + id, value : ""
						}
					}),
					BX.create('input', {
						props : {
							type : 'button',
							className: "BARCODE_INPUT_button",
							id : "BARCODE_INPUT_BUTTON_" + id,
							value : '<?=CUtil::JSEscape(Loc::getMessage('CAT_DOC_ADD'))?>' /*disabled: (maxId >= BX('CAT_DOC_AMOUNT_'+id).value)*/
						},
						style : {
							marginLeft: '5px'
						},
						events : {
							click : function()
							{
								if(BX("BARCODE_INPUT_" + id).value.replace(/^\s+|\s+$/g, '') !== '' && !<?=intval($bReadOnly)?>)
								{
									amount = parseInt(BX('CAT_DOC_AMOUNT_HIDDEN_'+id).value, 10);
									if(isNaN(amount))
										amount = 0;
									for(var j = 0; j <= 100500; j++)
									{
										if(!BX("BARCODE["+id+"]["+j+"]"))
										{
											counter = j;
											break;
										}
									}
									BX('BARCODE_DIV_'+id).appendChild(BX.create('DIV', {
										props : {
											id : "BARCODE_DIV_INPUT_" + id
										},
										style : {
											padding: '6px'
										},
										children : [
											BX.create('span', {
												props : {
													id : "BARCODE_SPAN_INPUT_" + id
												},
												text : BX('BARCODE_INPUT_'+id).value.replace(/^\s+|\s+$/g, ''),
												style : {
													fontSize: '12'
												}
											}),
											BX.create('input', {
												props : {
													type : 'hidden',
													id : "BARCODE["+id+"]["+counter+"]",
													name : "BARCODE["+id+"]["+counter+"]",
													value : BX('BARCODE_INPUT_'+id).value
												}
											}),
											BX.create('a', {
												props : {
													className : 'split-delete-item',  tabIndex : '-1', href : 'javascript:void(0);', id : "BARCODE_DELETE["+id+"]["+counter+"]"
												},
												events : {
													click : function()
													{
														if(!<?=intval($bReadOnly)?>)
														{
															var deleteNode = this.parentNode;
															if(deleteNode)
																deleteNode.parentNode.removeChild(deleteNode);
															amount = parseInt(BX('CAT_DOC_AMOUNT_HIDDEN_'+id).value, 10);
															if(isNaN(amount))
																amount = 0;
															BX('CAT_DOC_AMOUNT_HIDDEN_'+id).value = amount - 1;
															if(BX("BARCODE_INPUT_BUTTON_" + id) && BX("CAT_DOC_AMOUNT_HIDDEN_" + id) && BX('CAT_DOC_AMOUNT_'+id).value > BX("CAT_DOC_AMOUNT_HIDDEN_" + id).value)
																BX("BARCODE_INPUT_BUTTON_" + id).disabled = false;
														}
													}
												},
												style : {
													marginLeft: '8px',
													verticalAlign: '-3'
												}
											})
										]
									}));
									BX('CAT_DOC_AMOUNT_HIDDEN_'+id).value = amount + 1;
									maxId = amount + 1;
									if(maxId >= BX('CAT_DOC_AMOUNT_'+id).value)
										BX("BARCODE_INPUT_BUTTON_" + id).disabled = true;
								}
								BX('BARCODE_INPUT_'+id).value = '';
								BX('BARCODE_INPUT_'+id).focus();
							}
						}
					})
				]
			}),
			formBarcodes = BX.PopupWindowManager.create("catalog-popup-barcodes-"+id, BX("CAT_BARCODE_DIV_BIND_"+id), {
				offsetTop : -50,
				offsetLeft : -50,
				autoHide : false,
				closeByEsc : true,
				closeIcon : false,
				draggable: {
					restrict: true
				},
				content : content
			});
		if(!BX("BARCODE_DIV_INPUT_"+id))
		{
			var savedBarcodes = '';
			if(BX("PRODUCT["+id+"][BARCODE]").value !== '')
				savedBarcodes = BX("PRODUCT["+id+"][BARCODE]").value.split(', ');
			if(savedBarcodes !== '')
			{
				var barCodeAmount = parseInt(BX('CAT_DOC_AMOUNT_HIDDEN_'+id).value);
				BX("BARCODE_INPUT_BUTTON_" + id).disabled = (savedBarcodes.length >= BX('CAT_DOC_AMOUNT_'+id).value);
				for(i in savedBarcodes)
				{
					if(savedBarcodes.hasOwnProperty(i) && savedBarcodes[i] != undefined && savedBarcodes[i] != '<?=Loc::getMessage('CAT_DOC_POPUP_TITLE')?>')
					{
						BX('BARCODE_DIV_'+id).appendChild(BX.create('DIV', {
							props : {
								id : "BARCODE_DIV_INPUT_" + id
							},
							style : {
								padding: '6px'
							},
							children : [
								BX.create('span', {
									props : {
										id : "BARCODE_SPAN_INPUT_" + id
									},
									text : savedBarcodes[i],
									style : {
										fontSize: '12'
									}
								}),
								BX.create('input', {
									props : {
										type : 'hidden',
										id : "BARCODE["+id+"]["+i+"]",
										name : "BARCODE["+id+"]["+i+"]",
										value : savedBarcodes[i]
									}
								}),
								BX.create('a', {
									props : {
										className : 'split-delete-item',  tabIndex : '-1', href : 'javascript:void(0);'
									},
									events : {
										click : function()
										{
											if(!<?=intval($bReadOnly)?>)
											{
												var deleteNode = this.parentNode;
												if(deleteNode)
													deleteNode.parentNode.removeChild(deleteNode);
												amount = parseFloat(BX('CAT_DOC_AMOUNT_HIDDEN_'+id).value);
												if(isNaN(amount))
													amount = 0;
												BX('CAT_DOC_AMOUNT_HIDDEN_'+id).value = amount - 1;
												if(BX("BARCODE_INPUT_BUTTON_" + id) && BX("CAT_DOC_AMOUNT_HIDDEN_" + id) && BX('CAT_DOC_AMOUNT_'+id).value > BX("CAT_DOC_AMOUNT_HIDDEN_" + id).value)
													BX("BARCODE_INPUT_BUTTON_" + id).disabled = false;
											}
										}
									},
									style : {
										marginLeft: '8px',
										verticalAlign: '-3'
									}
								})
							]
						}));
					}
				}
			}
		}

		formBarcodes.setButtons([
			<?php if(!$bReadOnly):?>
			new BX.PopupWindowButton({
				text : "<?=Loc::getMessage('CAT_DOC_SAVE')?>",
				className : "",
				events : {
					click : function()
					{
						var barcodes = '';
						if(maxId > 0)
						{
							for(var i = 0; i <= maxId; i++)
							{
								if(BX("BARCODE["+id+"]["+i+"]"))
								{
									if(barcodes !== '')
										barcodes = barcodes + ', ';
									if(BX("BARCODE["+id+"]["+i+"]").value !== '')
										barcodes = barcodes + BX("BARCODE["+id+"]["+i+"]").value;
								}
							}
						}

						BX("PRODUCT["+id+"][BARCODE]").value = barcodes;
						recalculateRow(id);
						formBarcodes.close();
					}
				}
			}),
			<?php else:?>
			new BX.PopupWindowButton({
				text : "<?=Loc::getMessage('CAT_DOC_CANCEL')?>",
				className : "",
				events : {
					click : function()
					{
						formBarcodes.close();
					}
				}
			})
			<?php endif;?>
		]);

		formBarcodes.show();
		if(BX('BARCODE_INPUT_'+id))
			BX('BARCODE_INPUT_'+id).focus();
		<?php if($bReadOnly):?>
		var addBarcodeButtons = document.querySelectorAll('.BARCODE_INPUT_button, .BARCODE_INPUT_GREY');
		[].forEach.call(addBarcodeButtons, function disableButtons(item) {
			item.disabled = true;
		});
		var addBarcodeDelBut = document.querySelectorAll('a.split-delete-item');
		[].forEach.call(addBarcodeDelBut, function hideElements(item) {
			item.style.display = 'none';
		});
		<?php endif;?>
	}

	function recalculateRow(id)
	{
		<?php if(isset($documentFields["TOTAL"])):?>

		var docType = '<?=$docType?>';

		var sumFieldName = 'CAT_DOC_PURCHASING_PRICE_' + id;
		if (docType === 'W')
		{
			sumFieldName = 'CAT_DOC_BASE_PRICE_' + id;
		}

		var amount = 0;
		var price = 0;
		if (BX('CAT_DOC_AMOUNT_'+id) && !isNaN(parseFloat(BX('CAT_DOC_AMOUNT_'+id).value)))
		{
			amount = parseFloat(BX('CAT_DOC_AMOUNT_'+id).value);
		}
		if (BX(sumFieldName) && !isNaN(parseFloat(BX(sumFieldName).value)))
		{
			price = parseFloat(BX(sumFieldName).value);
		}
		if (BX('CAT_DOC_SUMM_'+id))
		{
			BX('CAT_DOC_SUMM_'+id).innerHTML = BX.Currency.currencyFormat(amount * price, BX('CAT_CURRENCY_STORE').value, true);
		}
		if (BX('PRODUCT_'+id+'_SUMM'))
		{
			BX('PRODUCT_'+id+'_SUMM').value = (amount * price);
		}
		var maxId = BX('ROW_MAX_ID').value;
		var totalSum = 0;
		for (var i = 0; i <= maxId; i++)
		{
			if (BX('PRODUCT_'+i+'_SUMM'))
			{
				totalSum = totalSum + Number(BX('PRODUCT_'+i+'_SUMM').value);
			}
		}
		if (isNaN(totalSum))
		{
			totalSum = 0;
		}

		if (BX("CAT_DOCUMENT_SUMM_SPAN"))
		{
			BX("CAT_DOCUMENT_SUMM_SPAN").innerHTML = '<?=Loc::getMessage('CAT_DOC_TOTAL')?>' + ': ' + BX.Currency.currencyFormat(totalSum, BX('CAT_CURRENCY_STORE').value, true);
		}
		else
		{
			showTotalSum();
		}

		if (BX("CAT_DOCUMENT_SUM"))
		{
			BX("CAT_DOCUMENT_SUM").value = totalSum;
		}
		<?php endif;?>

		if (BX("BARCODE_INPUT_BUTTON_" + id) && BX("CAT_DOC_AMOUNT_HIDDEN_" + id) && BX('CAT_DOC_AMOUNT_'+id).value > BX("CAT_DOC_AMOUNT_HIDDEN_" + id).value)
		{
			BX("BARCODE_INPUT_BUTTON_" + id).disabled = false;
		}
		else if (BX("BARCODE_INPUT_BUTTON_" + id))
		{
			BX("BARCODE_INPUT_BUTTON_" + id).disabled = true;
		}
	}

	function checkBarcodeSearch()
	{
		if (BX("CAT_DOC_BARCODE_FIND").value !== '')
		{
			productSearch(BX('CAT_DOC_BARCODE_FIND').value);
			return false;
		}
		return true;
	}

	function selectResposible()
	{
		window.open('<?php echo CUtil::JSEscape($userSearchUrl); ?>', '', 'scrollbars=yes,resizable=yes,width=900,height=600');
	}

	function responsibleRequest()
	{
		var responsible = BX('RESPONSIBLE_ID');
		if (BX.type.isElementNode(responsible))
		{
			if (responsible.value !== '')
			{
				BX.showWait();
				BX.ajax.loadJSON(
					'<?php echo $selfFolderUrl; ?>get_user.php',
					{
						lang: BX.message('LANGUAGE_ID'),
						sessid: BX.bitrix_sessid(),
						ajax: 'Y',
						format: 'Y',
						raw: 'Y',
						ID: responsible.value
					},
					responsibleRequestResult,
					responsibleRequestFailure
				);
			}
		}
	}

	function responsibleRequestResult(result)
	{
		if (!BX.type.isPlainObject(result))
		{
			responsibleRequestFailure();
			return;
		}

		BX.closeWait();

		var responsible = BX('RESPONSIBLE_ID'),
			responsibleName = BX('RESPONSIBLE_NAME');

		if (
			BX.type.isElementNode(responsible)
			&& BX.type.isElementNode(responsibleName)
		)
		{
			responsibleName.innerHTML = (responsible.value === result.ID.toString()
				? BX.Text.encode(result.NAME)
				: ''
			);
		}
	}

	function responsibleRequestFailure()
	{
		BX.closeWait();
		var responsibleName = BX('RESPONSIBLE_NAME');
		if (BX.type.isElementNode(responsibleName))
		{
			responsibleName.innerHTML = '';
		}
	}

	function initSelectResponce()
	{
		var btn = BX('RESPONSIBLE_ID_BTN'),
			input = BX('RESPONSIBLE_ID');
		if (BX.type.isElementNode(btn))
		{
			BX.Event.bind(btn, 'click', selectResposible);
		}
		if (BX.type.isElementNode(input))
		{
			BX.Event.bind(input, 'change', responsibleRequest);
		}
	}

	function SUVsetResponsible(id)
	{
		var responsible = BX('RESPONSIBLE_ID');
		if (BX.type.isElementNode(responsible))
		{
			responsible.value = BX.Text.encode(id);
		}
		responsibleRequest();
	}
}
<?php
$readyFunc = array();
$readyFunc[] = 'initSelectResponce();';
if (isset($documentFields["TOTAL"]))
{
	$readyFunc[] = 'showTotalSum();';
}

if (!empty($readyFunc))
{
?>
	BX.ready(BX.defer(function(){
		<?php echo implode("\n", $readyFunc); ?>
	}));
<?php
}
unset($readyFunc);
?>
</script>
<?php
endif;
if ($isAjaxDocumentRequest)
{
	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin_js.php');
}
else
{
	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
}
