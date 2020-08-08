<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$publicMode = $adminPage->publicMode;

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

\Bitrix\Main\Loader::includeModule('sale');

if(!CBXFeatures::IsFeatureEnabled('SaleRecurring'))
{
	require($DOCUMENT_ROOT."/bitrix/modules/main/include/prolog_admin_after.php");

	ShowError(GetMessage("SALE_FEATURE_NOT_ALLOW"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_sale_recurring";

$oSort = new CAdminUiSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminUiList($sTableID, $oSort);

$filterFields = array(
	array(
		"id" => "USER_USER",
		"name" => GetMessage('SRA_USER'),
		"filterable" => "%",
		"default" => true
	),
	array(
		"id" => "USER_ID",
		"name" => GetMessage('SRA_USER_ID'),
		"type" => "custom_entity",
		"selector" => array("type" => "user"),
		"filterable" => ""
	),
	array(
		"id" => "USER_LOGIN",
		"name" => GetMessage("SRA_USER_LOGIN"),
		"filterable" => ""
	),
	array(
		"id" => "CANCELED",
		"name" => GetMessage("SRA_CANCELED"),
		"type" => "list",
		"items" => array(
			"Y" => GetMessage("SRA_YES"),
			"N" => GetMessage("SRA_NO")
		),
		"filterable" => ""
	),
	array(
		"id" => "PRIOR_DATE",
		"name" => GetMessage("SRA_LAST_UPDATE"),
		"type" => "date",
		"filterable" => ""
	),
	array(
		"id" => "NEXT_DATE",
		"name" => GetMessage("SRA_NEXT_UPDATE"),
		"type" => "date",
		"filterable" => ""
	),
	array(
		"id" => "ORDER_ID",
		"name" => GetMessage("SRA_BASE_ORDER"),
		"filterable" => ""
	),
	array(
		"id" => "SUCCESS_PAYMENT",
		"name" => GetMessage("SRA_SUCCESSFULL"),
		"type" => "list",
		"items" => array(
			"Y" => GetMessage("SRA_YES"),
			"N" => GetMessage("SRA_NO")
		),
		"filterable" => ""
	),
);

$arFilter = array();

$lAdmin->AddFilter($filterFields, $arFilter);

if (($arID = $lAdmin->GroupAction()) && $saleModulePermissions >= "U")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = Array();

		$dbResultList = CSaleRecurring::GetList(
			array($by => $order),
			$arFilter,
			false,
			false,
			array("ID")
		);
		while ($arRecurringList = $dbResultList->Fetch())
			$arID[] = $arRecurringList['ID'];
	}

	foreach ($arID as $ID)
	{
		if ($ID == '')
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);

				if ($saleModulePermissions >= "W")
				{
					$DB->StartTransaction();

					if (!CSaleRecurring::Delete($ID))
					{
						$DB->Rollback();

						if ($ex = $APPLICATION->GetException())
							$lAdmin->AddGroupError($ex->GetString(), $ID);
						else
							$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("SRA_ERROR_DELETE")), $ID);
					}

					$DB->Commit();
				}
				else
				{
					$lAdmin->AddGroupError(GetMessage("SRA_NO_PERMS2DELETE"), $ID);
				}

				break;

			case "cancel":
			case "uncancel":
				$arFields = array(
						"CANCELED" => (($_REQUEST['action']=="cancel") ? "Y" : "N")
					);
				if ($_REQUEST['action'] != "cancel")
					$arFields["REMAINING_ATTEMPTS"] = (Defined("SALE_PROC_REC_ATTEMPTS") ? SALE_PROC_REC_ATTEMPTS : 3);

				if (!CSaleRecurring::Update($ID, $arFields))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(str_replace("#ID#", $id, GetMessage("SRA_ERROR_UPDATE")), $ID);
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


$dbResultList = CSaleRecurring::GetList(
	array($by => $order),
	$arFilter,
	false,
	false,
	array("*")
);

$dbResultList = new CAdminUiResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => "/bitrix/admin/sale_recurring_admin.php"));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", 	"sort"=>"id", "default"=>true),
	array("id"=>"USER_ID","content"=>GetMessage("SRA_USER1"), "sort"=>"user_id", "default"=>true),
	array("id"=>"CANCELED", "content"=>GetMessage('SRA_CANC'),	"sort"=>"canceled", "default"=>true),
	array("id"=>"PRIOR_DATE", "content"=>GetMessage("SRA_LAST_RENEW"),  "sort"=>"prior_date", "default"=>true),
	array("id"=>"NEXT_DATE", "content"=>GetMessage("SRA_NEXT_RENEW"),  "sort"=>"next_date", "default"=>true),
	array("id"=>"SUCCESS_PAYMENT", "content"=>GetMessage("SRA_SUCCESS_PAY"),  "sort"=>"success_payment", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

if ($publicMode)
{
	$pathToUser = COption::GetOptionString("main", "TOOLTIP_PATH_TO_USER", false);
	$pathToUser = ($pathToUser ? $pathToUser : SITE_DIR."company/personal/user/#user_id#/");
}

while ($arRecurring = $dbResultList->NavNext(false))
{
	$row =& $lAdmin->AddRow($arRecurring["ID"], $arRecurring);

	$row->AddField("ID", $arRecurring["ID"]);

	$urlToUser = "/bitrix/admin/user_edit.php?ID=".$arRecurring["USER_ID"]."&lang=".LANGUAGE_ID;
	if ($publicMode)
	{
		$urlToUser = str_replace(array("#user_id#"), $arRecurring["USER_ID"], $pathToUser);
	}

	$fieldValue  = "[<a href=\"".$urlToUser."\">".$arRecurring["USER_ID"]."</a>] ";
	$fieldValue .= htmlspecialcharsEx($arRecurring["USER_NAME"].(($arRecurring["USER_NAME"] == '' ||
				$arRecurring["USER_LAST_NAME"] == '') ? "" : " ").$arRecurring["USER_LAST_NAME"])."<br>";
	$fieldValue .= htmlspecialcharsEx($arRecurring["USER_LOGIN"])."&nbsp;&nbsp;&nbsp; ";
	$fieldValue .= "<a href=\"mailto:".htmlspecialcharsbx($arRecurring["USER_EMAIL"])."\">".
		htmlspecialcharsEx($arRecurring["USER_EMAIL"])."</a>";
	$row->AddField("USER_ID", $fieldValue);

	$row->AddField("CANCELED", (($arRecurring["CANCELED"]=="Y") ? GetMessage("SRA_YES") : GetMessage("SRA_NO")));
	$row->AddField("PRIOR_DATE", $arRecurring["PRIOR_DATE"]."&nbsp;");
	$row->AddField("NEXT_DATE", $arRecurring["NEXT_DATE"]."&nbsp;");

	if ($arRecurring["SUCCESS_PAYMENT"] == "Y")
		$fieldValue = GetMessage("SRA_YES");
	else
		$fieldValue = GetMessage("SRA_UNSECCESS").$arRecurring["REMAINING_ATTEMPTS"]."";
	$row->AddField("SUCCESS_PAYMENT", $fieldValue);
	
	$arActions = Array();
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => GetMessage("SRA_UPDATE_ALT"),
		"ACTION" => $lAdmin->ActionRedirect("sale_recurring_edit.php?ID=".$arRecurring["ID"]."&lang=".LANGUAGE_ID.""),
		"DEFAULT" => true
	);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("SRA_DELETE_ALT1"),
			"ACTION" => "if(confirm('".GetMessage('SRA_DELETE_CONF')."')) ".
				$lAdmin->ActionDoGroup($arRecurring["ID"], "delete")
		);
	}

	$row->AddActions($arActions);
}

$lAdmin->AddGroupActionTable(
	array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
		"cancel" => GetMessage("SRAN_CANCEL_REC"),
		"uncancel" => GetMessage("SRAN_UNCANCEL_REC")
	)
);

if ($saleModulePermissions >= "W")
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("SRAN_ADD_NEW"),
			"LINK" => "sale_recurring_edit.php?lang=".LANGUAGE_ID,
			"ICON" => "btn_new",
			"TITLE" => GetMessage("SRAN_ADD_NEW_ALT")
		),
	);
	$lAdmin->setContextSettings(array("pagePath" => "/bitrix/admin/sale_recurring_admin.php"));
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(GetMessage("SRA_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>