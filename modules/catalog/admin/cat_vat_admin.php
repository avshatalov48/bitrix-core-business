<?php
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;


require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/catalog/prolog.php');

Loc::loadMessages(__FILE__);

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$publicMode = $adminPage->publicMode;
$selfFolderUrl = $adminPage->getSelfFolderUrl();

CModule::IncludeModule("catalog");

$accessController = AccessController::getCurrent();
if (!($accessController->check(ActionDictionary::ACTION_CATALOG_READ) || $accessController->check(ActionDictionary::ACTION_VAT_EDIT)))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$bReadOnly = !$accessController->check(ActionDictionary::ACTION_VAT_EDIT);

if ($ex = $APPLICATION->GetException())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$strError = $ex->GetString();
	ShowError($strError);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$sTableID = "tbl_catalog_vat";

$oSort = new CAdminUiSorting($sTableID, 'C_SORT', 'ASC');
$lAdmin = new CAdminUiList($sTableID, $oSort);
$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());

$filterFields = [
	[
		'id' => 'ID',
		'name' => 'ID',
		'filterable' => '',
		'default' => true,
	],
	[
		'id' => 'ACTIVE',
		'name' => Loc::getMessage('CVAT_FILTER_ACTIVE'),
		'type' => 'list',
		'items' => [
			'Y' => Loc::getMessage('CVAT_YES'),
			'N' => Loc::getMessage('CVAT_NO'),
		],
		'filterable' => '',
	],
	[
		'id' => 'NAME',
		'name' => Loc::getMessage('CVAT_FILTER_NAME'),
		'filterable' => '%',
		'quickSearch' => '%',
	],
	[
		'id' => 'RATE',
		'name' => Loc::getMessage('CVAT_FILTER_RATE'),
		'filterable' => '',
	],
	[
		'id' => 'XML_ID',
		'name' => Loc::getMessage('CVAT_FILTER_XML_ID'),
		'filterable' => '',
	],
];

$arFilter = [];

$lAdmin->AddFilter($filterFields, $arFilter);

if ($lAdmin->EditAction() && !$bReadOnly)
{
	foreach ($_POST['FIELDS'] as $ID => $arFields)
	{
		$ID = (int)$ID;

		if ($ID <= 0 || !$lAdmin->IsUpdated($ID))
		{
			continue;
		}

		$DB->StartTransaction();
		if (!CCatalogVat::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
			{
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			}
			else
			{
				$lAdmin->AddUpdateError(str_replace("#ID#", $ID, Loc::getMessage("ERROR_UPDATE_VAT")), $ID);
			}

			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
		}
	}
}

switch ($by)
{
	case 'ID':
		$vatListOrder = [
			'ID' => $order,
		];
		break;
	case 'RATE':
		$vatListOrder = [
			'EXCLUDE_VAT' => ($order === 'DESC' ? 'ASC' : 'DESC'),
			'RATE' => $order,
			'ID' => 'ASC',
		];
		break;
	default:
		$vatListOrder = [
			$by => $order,
			'ID' => 'ASC'
		];
		break;
}

if (($arID = $lAdmin->GroupAction()) && !$bReadOnly)
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = [];
		$dbResultList = CCatalogVat::GetListEx(
			[],
			$arFilter,
			false,
			false,
			['ID']
		);

		while ($arResult = $dbResultList->Fetch())
		{
			$arID[] = $arResult['ID'];
		}
		unset($dbResultList);
	}

	foreach ($arID as $ID)
	{
		if ($ID == '')
		{
			continue;
		}

		switch ($_REQUEST['action'])
		{
			case "delete":
				$DB->StartTransaction();
				if (!CCatalogVat::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
					{
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					}
					else
					{
						$lAdmin->AddGroupError(str_replace("#ID#", $ID, Loc::getMessage("ERROR_DELETE_VAT")), $ID);
					}
				}
				else
				{
					$DB->Commit();
				}
				break;
			case "activate":
			case "deactivate":
				$arFields = [
					"ACTIVE" => (($_REQUEST['action'] == "activate") ? "Y" : "N")
				];
				if (!CCatalogVat::Update($ID, $arFields))
				{
					if ($ex = $APPLICATION->GetException())
					{
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					}
					else
					{
						$lAdmin->AddGroupError(str_replace("#ID#", $ID, Loc::getMessage("ERROR_UPDATE_VAT")), $ID);
					}
				}
				break;
		}
	}
	if ($lAdmin->hasGroupErrors())
	{
		$adminSidePanelHelper->sendJsonErrorResponse($lAdmin->getGroupErrors());
	}
	else
	{
		$adminSidePanelHelper->sendSuccessResponse();
	}
}

$headers = [];
$headers[] = [
	'id' => 'ID',
	'content' => 'ID',
	'sort' => 'ID',
	'default'=>true,
];
$headers[] = [
	'id' => 'C_SORT',
	'content' => Loc::getMessage('CVAT_SORT'),
	'sort' => 'C_SORT',
	'default' => true,
];
$headers[] = [
	'id' => 'ACTIVE',
	'content' => Loc::getMessage('CVAT_ACTIVE'),
	'sort' => 'ACTIVE',
	'default' => true,
];
$headers[] = [
	'id' => 'NAME',
	'content' => Loc::getMessage('CVAT_NAME'),
	'sort' => 'NAME',
	'default' => true,
];
$headers[] = [
	'id' => 'RATE',
	'content' => Loc::getMessage('CVAT_RATE'),
	'sort' => 'RATE',
	'default' => true,
];
$headers[] = [
	'id' => 'XML_ID',
	'content' => Loc::getMessage('CVAT_XML_ID'),
	'sort' => 'XML_ID',
	'default' => false,
];
$lAdmin->AddHeaders($headers);
unset($headers);

$arSelectFields = $lAdmin->GetVisibleHeaderColumns();
if (!in_array('ID', $arSelectFields))
{
	$arSelectFields[] = 'ID';
}
$arSelectFields[] = 'EXCLUDE_VAT';
$arSelectFields[] = 'ACTIVE';
$arSelectFields = array_values(array_unique($arSelectFields));

$arNavParams = (isset($_REQUEST["mode"]) && 'excel' == $_REQUEST["mode"]
	? false
	: ["nPageSize" => CAdminUiResult::GetNavSize($sTableID)]
);

$dbResultList = CCatalogVat::GetListEx(
	$vatListOrder,
	$arFilter,
	false,
	$arNavParams,
	$arSelectFields
);

$dbResultList = new CAdminUiResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => $selfFolderUrl."cat_vat_admin.php"));

while ($arVAT = $dbResultList->Fetch())
{
	$editUrl = $selfFolderUrl."cat_vat_edit.php?ID=".$arVAT["ID"]."&lang=".LANGUAGE_ID;
	$editUrl = $adminSidePanelHelper->editUrlToPublicPage($editUrl);
	$arVAT['ID'] = (int)$arVAT['ID'];
	$row =& $lAdmin->AddRow($arVAT['ID'], $arVAT, $editUrl);

	$row->AddField("ID", $arVAT['ID']);

	$excludeVat = $arVAT['EXCLUDE_VAT'] === 'Y';

	if ($bReadOnly)
	{
		$row->AddCheckField("ACTIVE", false);
		$row->AddInputField("NAME", false);
		$row->AddViewField("C_SORT", false);
		$row->AddViewField('XML_ID', false);
	}
	else
	{
		$row->AddCheckField("ACTIVE");
		$row->AddInputField("NAME", ["size" => 30]);
		$row->AddInputField("C_SORT", ["size" => 5]);
		if (!$excludeVat)
		{
			$row->AddInputField("RATE", ["size" => 5]);
		}
		$row->AddInputField(
			'XML_ID',
			[
				'size' => 50,
			]
		);
	}

	if (!$excludeVat)
	{
		$row->AddViewField("RATE", htmlspecialcharsbx($arVAT['RATE'] . " %"));
	}
	else
	{
		$row->AddViewField('RATE', htmlspecialcharsbx(Loc::getMessage('CVAT_EXCLUDE_VAT')));
	}

	$arActions = [];
	$arActions[] = [
		"ICON" => "edit",
		"TEXT" => $bReadOnly ? Loc::getMessage('CVAT_VIEW_ALT') : Loc::getMessage('CVAT_EDIT_ALT'),
		"LINK" => $editUrl,
		"DEFAULT" => true,
	];

	if (!$bReadOnly)
	{
		if ($arVAT['ACTIVE'] === 'N')
		{
			$arActions[] = [
				'ICON' => 'activate',
				'TEXT' => Loc::getMessage('CVAT_VAT_ACTIVATE'),
				'ACTION' => $lAdmin->ActionDoGroup($arVAT['ID'], 'activate')
			];
		}
		else
		{
			$arActions[] = [
				'ICON' => 'deactivate',
				'TEXT' => Loc::getMessage('CVAT_VAT_DEACTIVATE'),
				'ACTION' => $lAdmin->ActionDoGroup($arVAT['ID'], 'deactivate')
			];
		}
	}

	if (!$bReadOnly)
	{
		$arActions[] = ["SEPARATOR" => true];
		$arActions[] = [
			"ICON" => "delete",
			"TEXT" => Loc::getMessage("CVAT_DELETE_ALT"),
			"ACTION" => "if(confirm('"
				. CUtil::JSEscape(Loc::getMessage('CVAT_DELETE_CONF'))
				. "')) "
				. $lAdmin->ActionDoGroup($arVAT['ID'], 'delete')
		];
	}

	$row->AddActions($arActions);
}

if (!$bReadOnly)
{
	$lAdmin->AddGroupActionTable([
		'edit' => true,
		'delete' => true,
		"activate" => Loc::getMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate" => Loc::getMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
	]);
}

if (!$bReadOnly)
{
	$addUrl = $selfFolderUrl."cat_vat_edit.php?lang=".LANGUAGE_ID;
	$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
	$aContext = [
		[
			"TEXT" => Loc::getMessage("CVAT_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => $addUrl,
			"TITLE" => Loc::getMessage("CVAT_ADD_NEW_ALT"),
		],
	];
	$lAdmin->setContextSettings(["pagePath" => $selfFolderUrl."cat_vat_admin.php"]);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("CVAT_PAGE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
