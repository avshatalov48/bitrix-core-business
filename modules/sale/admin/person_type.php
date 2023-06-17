<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

\Bitrix\Main\Loader::includeModule('sale');

$publicMode = $adminPage->publicMode;
$selfFolderUrl = $adminPage->getSelfFolderUrl();

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$sTableID = "tbl_sale_person_type";
$oSort = new CAdminUiSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminUiList($sTableID, $oSort);

$listSite = array();
$sitesQueryObject = CSite::getList("sort", "asc", array("ACTIVE" => "Y"));
while ($site = $sitesQueryObject->fetch())
{
	$listSite[$site["LID"]] = $site["NAME"]." [".$site["LID"]."]";
}

$filterFields = array(
	array(
		"id" => "NAME",
		"name" => GetMessage("PERS_TYPE_NAME"),
		"filterable" => "%",
		"quickSearch" => "%"
	),
	array(
		"id" => "LID",
		"name" => GetMessage("LANG_FILTER_NAME"),
		"type" => "list",
		"items" => $listSite,
		"filterable" => "",
		"default" => true
	),
	array(
		"id" => "ACTIVE",
		"name" => GetMessage("SALE_FIELD_ACTIVE"),
		"type" => "list",
		"items" => array(
			"Y" => GetMessage("SALE_YES"),
			"N" => GetMessage("SALE_NO")
		),
		"filterable" => "",
		"default" => true
	)
);

$arFilter = array();

$lAdmin->AddFilter($filterFields, $arFilter);

if ($lAdmin->EditAction() && $saleModulePermissions >= "W")
{
	foreach ($FIELDS as $ID => $arFields)
	{
		$ID = intval($ID);

		if (!$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();

		if (!CSalePersonType::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(GetMessage("SPTAN_ERROR_UPDATE"), $ID);

			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
		}
	}
}

if (($arID = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$dbResultList = CSalePersonType::GetList($by, $order, $arFilter);
		while ($arResult = $dbResultList->Fetch())
			$arID[] = $arResult['ID'];
	}

	foreach ($arID as $ID)
	{
		if ($ID == '')
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);

				$DB->StartTransaction();

				if (!CSalePersonType::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("SPTAN_ERROR_DELETE"), $ID);
				}
				else
				{
					$DB->Commit();
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

$dbResultList = CSalePersonType::GetList($by, $order, $arFilter);

$dbResultList = new CAdminUiResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => $selfFolderUrl."sale_person_type.php"));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>GetMessage("PERS_TYPE_ID"), "sort"=>"id", "default"=>true),
	array("id"=>"NAME","content"=>GetMessage("PERS_TYPE_NAME"), "sort"=>"name", "default"=>true),
	array("id"=>"CODE","content"=>GetMessage("PERS_TYPE_CODE"), "sort"=>"code"),
	array("id"=>"ACTIVE", "content"=>GetMessage("PERS_ACTIVE"), "sort"=>"ACTIVE", "default"=>true),
	array("id"=>"LID", "content"=>GetMessage('PERS_TYPE_LID'), "sort"=>false, "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage("PERS_TYPE_SORT"), "sort"=>"sort", "default"=>true),
	array("id"=>"PROPS", "content"=>GetMessage("PERS_PROPS"), "sort"=>"", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$arLangs = array();
$dbLangsList = CLang::GetList();
while ($arLang = $dbLangsList->Fetch())
	$arLangs[$arLang["LID"]] = "[".htmlspecialcharsbx($arLang["LID"])."] ".htmlspecialcharsbx($arLang["NAME"]);

while ($arPersonType = $dbResultList->NavNext(false))
{
	$editUrl = $selfFolderUrl."sale_person_type_edit.php?ID=".$arPersonType["ID"]."&lang=".LANGUAGE_ID;
	$editUrl = $adminSidePanelHelper->editUrlToPublicPage($editUrl);
	$row =& $lAdmin->AddRow($arPersonType["ID"], $arPersonType, $editUrl, GetMessage("SPTAN_UPDATE_ALT"));
	$row->AddField("ID","<a href=\"".$editUrl."\">".$arPersonType["ID"]."</a>");
	$row->AddInputField("NAME", array("size" => "30"));
	$LIDS = "";
	foreach($arPersonType["LIDS"] as $v)
		$LIDS .= $arLangs[$v]."<br />";
	$row->AddField("LID", $LIDS);
	$row->AddInputField("SORT", array("size" => "3"));
	$row->AddCheckField("ACTIVE");

	$fieldValue = "";
	if (in_array("PROPS", $arVisibleColumns))
	{
		$numProps = CSaleOrderProps::GetList(
			array(),
			array("PERSON_TYPE_ID" => $arPersonType["ID"]),
			array()
		);
		$numProps = intval($numProps);

		if ($numProps > 0)
		{
			if ($publicMode)
				$fieldValue = $numProps;
			else
				$fieldValue = "<a href=\"".$selfFolderUrl."sale_order_props.php?lang=".LANGUAGE_ID."&set_filter=Y&filter_person_type_id=".
					$arPersonType["ID"]."\" title=\"".GetMessage("SPTAN_VIEW_PROPS")."\">".$numProps."</a>";
		}
		else
		{
			$fieldValue = "0";
		}
	}
	$row->AddField("PROPS", $fieldValue);

	$arActions = Array();
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => GetMessage("SPTAN_UPDATE_ALT"),
		"LINK" => $editUrl,
		"DEFAULT" => true
	);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("SPTAN_DELETE_ALT1"),
			"ACTION" => "if(confirm('".GetMessage('PERS_TYPE_DEL_CONF')."')) ".$lAdmin->ActionDoGroup($arPersonType["ID"], "delete")
		);
	}

	$row->AddActions($arActions);
}

$lAdmin->AddGroupActionTable(
	array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE")
	)
);

if ($saleModulePermissions == "W")
{
	$addUrl = $selfFolderUrl."sale_person_type_edit.php?lang=".LANGUAGE_ID;
	$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
	$aContext = array(
		array(
			"TEXT" => GetMessage("SPTAN_ADD_NEW"),
			"LINK" => $addUrl,
			"TITLE" => GetMessage("SPTAN_ADD_NEW_ALT"),
			"ICON" => "btn_new"
		),
	);
	$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."sale_person_type.php"));
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("PERSON_TYPE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
if (!$publicMode && \Bitrix\Sale\Update\CrmEntityCreatorStepper::isNeedStub())
{
	$APPLICATION->IncludeComponent("bitrix:sale.admin.page.stub", ".default");
}
else
{
	$lAdmin->DisplayFilter($filterFields);
	$lAdmin->DisplayList();
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
