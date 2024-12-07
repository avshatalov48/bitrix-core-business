<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$publicMode = $adminPage->publicMode;
$selfFolderUrl = $adminPage->getSelfFolderUrl();

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

\Bitrix\Main\Loader::includeModule('sale');

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_sale_account";

$oSort = new CAdminUiSorting($sTableID, "ID", "asc");

$lAdmin = new CAdminUiList($sTableID, $oSort);

$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());

$listCurrency = array();
$currencyList = Bitrix\Currency\CurrencyManager::getCurrencyList();
foreach ($currencyList as $currencyId => $currencyName)
{
	$listCurrency[$currencyId] = $currencyName;
}

$filterFields = array(
	array(
		"id" => "USER_USER",
		"name" => GetMessage('SAA_USER'),
		"filterable" => "%",
		"quickSearch" => "%",
		"default" => true
	),
	array(
		"id" => "USER_ID",
		"name" => GetMessage('SAA_USER_ID'),
		"type" => "custom_entity",
		"selector" => array("type" => "user"),
		"filterable" => ""
	),
	array(
		"id" => "USER_LOGIN",
		"name" => GetMessage("SAA_USER_LOGIN"),
		"filterable" => ""
	),
	array(
		"id" => "CURRENCY",
		"name" => GetMessage("SAA_CURRENCY"),
		"type" => "list",
		"items" => $listCurrency,
		"filterable" => ""
	),
	array(
		"id" => "LOCKED",
		"name" => GetMessage("SAA_LOCKED"),
		"type" => "list",
		"items" => array(
			"Y" => GetMessage("SAA_YES"),
			"N" => GetMessage("SAA_NO")
		),
		"filterable" => ""
	),
);

$arFilter = array();

$lAdmin->AddFilter($filterFields, $arFilter);

if (($arID = $lAdmin->GroupAction()) && $saleModulePermissions >= "U")
{
	if ($lAdmin->IsGroupActionToAll())
	{
		$arID = Array();
		$dbResultList = CSaleUserAccount::GetList(
			array($by => $order),
			$arFilter,
			false,
			false,
			array("ID")
		);
		while ($arAccountList = $dbResultList->Fetch())
			$arID[] = $arAccountList['ID'];
	}

	foreach ($arID as $ID)
	{
		if ($ID == '')
			continue;

		switch ($lAdmin->GetAction())
		{
			case "delete":
				@set_time_limit(0);

				if ($saleModulePermissions >= "W")
				{
					if ($arDelAccount = CSaleUserAccount::GetByID($ID))
					{
						$DB->StartTransaction();

						if (CSaleUserAccount::UpdateAccount($arDelAccount["USER_ID"], -$arDelAccount["CURRENT_BUDGET"], $arDelAccount["CURRENCY"], "DEL_ACCOUNT", 0))
						{
							if (!CSaleUserAccount::Delete($ID))
							{
								$DB->Rollback();

								if ($ex = $APPLICATION->GetException())
									$lAdmin->AddGroupError($ex->GetString(), $ID);
								else
									$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("SAA_ERROR_DELETE")), $ID);
							}
							else
							{
								$DB->Commit();
							}
						}
						else
						{
							$DB->Rollback();

							if ($ex = $APPLICATION->GetException())
								$lAdmin->AddGroupError($ex->GetString(), $ID);
							else
								$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("SAA_ERROR_MONEY")), $ID);
						}
					}
					else
					{
						if ($ex = $APPLICATION->GetException())
							$lAdmin->AddGroupError($ex->GetString(), $ID);
						else
							$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("SAA_ERROR_GET")), $ID);
					}
				}
				else
				{
					$lAdmin->AddGroupError(GetMessage("SAA_NO_PERMS2DELETE"), $ID);
				}

				break;
			case "unlock":

				if (!CSaleUserAccount::UnLockByID($ID))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("SAA_ERROR_UNLOCK")), $ID);
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

global $by, $order;

$dbResultList = CSaleUserAccount::GetList(array($by => $order), $arFilter, false, false, array("*"));

$dbResultList = new CAdminUiResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => $selfFolderUrl."sale_account_admin.php"));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", 	"sort"=>"id", "default"=>true),
	array("id"=>"USER_ID","content"=>GetMessage("SAA_USER1"), "sort"=>"user_id", "default"=>true),
	array("id"=>"CURRENT_BUDGET", "content"=>GetMessage('SAA_SUM'),	"sort"=>"current_budget", "default"=>true),
	array("id"=>"LOCKED", "content"=>GetMessage("SAAN_LOCK_ACCT"),  "sort"=>"locked", "default"=>true),
	array("id"=>"TRANSACT", "content"=>GetMessage("SAAN_TRANSACT"),  "sort"=>"", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arAccount = $dbResultList->NavNext(false))
{
	$editUrl = $selfFolderUrl."sale_account_edit.php?ID=".$arAccount['ID']."&lang=".LANGUAGE_ID;
	$editUrl = $adminSidePanelHelper->editUrlToPublicPage($editUrl);
	$row =& $lAdmin->AddRow($arAccount["ID"], $arAccount, $editUrl, GetMessage("SAA_UPDATE_ALT"));

	$row->AddField("ID", $arAccount["ID"]);

	$urlToUser = $selfFolderUrl."user_edit.php?ID=".$arAccount["USER_ID"]."&lang=".LANGUAGE_ID;
	if ($publicMode)
	{
		$urlToUser = $selfFolderUrl."sale_buyers_profile.php?USER_ID=".$arAccount["USER_ID"]."&lang=".LANGUAGE_ID;
		$urlToUser = $adminSidePanelHelper->editUrlToPublicPage($urlToUser);
	}
	$fieldValue = "[<a href=\"".$urlToUser."\" title=\"".GetMessage("SAA_USER_INFO")."\">".$arAccount["USER_ID"]."</a>] ";

	$fieldValue .= htmlspecialcharsEx($arAccount["USER_NAME"].(($arAccount["USER_NAME"] == '' ||
		$arAccount["USER_LAST_NAME"] == '') ? "" : " ").$arAccount["USER_LAST_NAME"])."<br>";
	$fieldValue .= htmlspecialcharsEx($arAccount["USER_LOGIN"])."&nbsp;&nbsp;&nbsp; ";
	$fieldValue .= "<a href=\"mailto:".htmlspecialcharsbx($arAccount["USER_EMAIL"])."\" title=\"".
		GetMessage("SAA_MAILTO")."\">".htmlspecialcharsEx($arAccount["USER_EMAIL"])."</a>";
	$row->AddField("USER_ID", $fieldValue);

	$row->AddField("CURRENT_BUDGET", SaleFormatCurrency($arAccount["CURRENT_BUDGET"], $arAccount["CURRENCY"]));
	$row->AddField("LOCKED", (($arAccount["LOCKED"] != "Y") ? GetMessage("SAA_NO") : GetMessage("SAA_YES")));

	$fieldValue = "";
	if (in_array("TRANSACT", $arVisibleColumns))
	{
		$numTrans = CSaleUserTransact::GetList(
			array(),
			array(
				"USER_ID" => $arAccount["USER_ID"],
				"CURRENCY" => $arAccount["CURRENCY"]
			),
			array()
		);
		if (intval($numTrans) > 0)
		{
			$urlToTransact = "sale_transact_admin.php?lang=".LANGUAGE_ID;
			if ($publicMode)
			{
				$urlToTransact = $selfFolderUrl."sale_transact_admin/";
			}
			$urlToTransact = CHTTP::urlAddParams($urlToTransact,
				array(
					"USER_ID" => $arAccount["USER_ID"],
					"CURRENCY" => $arAccount["CURRENCY"],
					"apply_filter" => "Y"
				)
			);
			$fieldValue .= "<a href=\"".$urlToTransact."\" title=\"".GetMessage("SAA_TRANS_TITLE")."\">";
			$fieldValue .= intval($numTrans);
			$fieldValue .= "</a>";
		}
		else
		{
			$fieldValue .= 0;
		}
	}
	$row->AddField("TRANSACT", $fieldValue);

	$arActions = Array();
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => GetMessage("SAA_UPDATE_ALT"),
		"LINK" => $editUrl,
		"DEFAULT" => true
	);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("SAA_DELETE_ALT"),
			"ACTION" => "javascript:if(confirm('".GetMessage('SAA_DELETE_CONFIRM')."')) ".$lAdmin->ActionDoGroup($arAccount["ID"], "delete"));
	}

	$row->AddActions($arActions);
}


$arFooterArray = array(
	array(
		"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
		"value" => $dbResultList->SelectedRowsCount()
	),
);

$dbAccountCurrency = CSaleUserAccount::GetList(
		array("CURRENCY" => "ASC"),
		$arFilter,
		array("CURRENCY", "SUM" => "CURRENT_BUDGET"),
		false,
		array("CURRENCY", "SUM" => "CURRENT_BUDGET")
	);
while ($arAccountCurrency = $dbAccountCurrency->Fetch())
{
	$arFooterArray[] = array(
		"title" => GetMessage("SAA_ITOG")." ".$arAccountCurrency["CURRENCY"].":",
		"value" => SaleFormatCurrency($arAccountCurrency["CURRENT_BUDGET"], $arAccountCurrency["CURRENCY"])
	);
}

$order_sum = "";
foreach($arFooterArray as $val)
{
	$order_sum .= $val["title"]." ".$val["value"]."<br />";
}
$lAdmin->sEpilogContent = "<script>setTimeout(function(){if (document.getElementById('order_sum'))document.getElementById('order_sum').innerHTML = '".CUtil::JSEscape($order_sum)."';}, 10);</script>";


$lAdmin->AddGroupActionTable(
	array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
		"unlock" => GetMessage("SAAN_UNLOCK_DO")
	)
);

$aContext = array();
if ($saleModulePermissions >= "W")
{
	$addUrl = $selfFolderUrl."sale_account_edit.php?lang=".LANGUAGE_ID;
	$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
	$aContext = array(
		array(
			"TEXT" => GetMessage("SAAN_ADD_NEW"),
			"LINK" => $addUrl,
			"TITLE" => GetMessage("SAAN_ADD_NEW_ALT"),
			"ICON" => "btn_new"
		),
	);
}
$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."sale_account_admin.php"));
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(GetMessage("SAA_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
if (!$publicMode && \Bitrix\Sale\Update\CrmEntityCreatorStepper::isNeedStub())
{
	$APPLICATION->IncludeComponent("bitrix:sale.admin.page.stub", ".default");
}
else
{
	$filterParams = [
		'CONFIG' => [
			'popupWidth' => 800,
		],
		'USE_CHECKBOX_LIST_FOR_SETTINGS_POPUP' => \Bitrix\Main\ModuleManager::isModuleInstalled('ui'),
		'ENABLE_FIELDS_SEARCH' => 'Y',
	];
	$lAdmin->DisplayFilter($filterFields, $filterParams);

	$listParams = [
		'USE_CHECKBOX_LIST_FOR_SETTINGS_POPUP' => \Bitrix\Main\ModuleManager::isModuleInstalled('ui'),
		'ENABLE_FIELDS_SEARCH' => 'Y',
	];
	$lAdmin->DisplayList($listParams);

	echo BeginNote();
	?><span id="order_sum"><?php print_r($order_sum); ?></span><?php
	echo EndNote();
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
