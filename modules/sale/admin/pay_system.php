<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;

\Bitrix\Main\Loader::includeModule('sale');

$publicMode = $adminPage->publicMode;
$selfFolderUrl = $adminPage->getSelfFolderUrl();

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$sTableID = "tbl_sale_pay_system";
$instance = \Bitrix\Main\Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();

$oSort = new CAdminUiSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminUiList($sTableID, $oSort);

$listPersonType = array();
$personTypeQueryObject = CSalePersonType::GetList(array("SORT" => "ASC", "NAME" => "ASC"), array());
while ($personType = $personTypeQueryObject->GetNext())
{
	$listPersonType[$personType["ID"]] = $personType["NAME"]." (".implode(", ", $personType["LIDS"]).")";
}

$filterFields = array(
	array(
		"id" => "NAME",
		"name" => GetMessage("SALE_NAME"),
		"filterable" => "%",
		"quickSearch" => "%"
	),
	array(
		"id" => "ACTIVE",
		"name" => GetMessage("SALE_F_ACTIVE"),
		"type" => "list",
		"items" => array(
			"Y" => GetMessage("SALE_YES"),
			"N" => GetMessage("SALE_NO")
		),
		"filterable" => "",
		"default" => true
	),
	array(
		"id" => "PERSON_TYPE_ID",
		"name" => GetMessage("SALE_F_PERSON_TYPE"),
		"type" => "list",
		"items" => $listPersonType,
		"filterable" => "",
		"params" => array("multiple" => "Y"),
	)
);

$filter = array();

$lAdmin->AddFilter($filterFields, $filter);

$personTypeId = [];
if (!empty($filter["PERSON_TYPE_ID"]))
{
	$personTypeId = $filter["PERSON_TYPE_ID"];
	unset($filter["PERSON_TYPE_ID"]);
}

if (($ids = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	if ($request->get('action_target')=='selected')
	{
		$ids = array();
		$dbRes = \Bitrix\Sale\Internals\PaySystemActionTable::getList(
			array(
				'select' => array('ID'),
				'filter' => $filter,
				'order' => array(ToUpper($by) => ToUpper($order))
			)
		);

		while ($arResult = $dbRes->fetch())
			$ids[] = $arResult['ID'];
	}

	foreach ($ids as $id)
	{
		if ((int)$id <= 0)
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				$dbRes = \Bitrix\Sale\Internals\PaymentTable::getList(array('filter' => array('PAY_SYSTEM_ID' => $id)));
				if ($dbRes->fetch())
				{
					$lAdmin->AddGroupError(Loc::getMessage("SALE_DELETE_ERROR"), $id);
					continue 2;
				}

				$result = \Bitrix\Sale\PaySystem\Manager::delete($id);
				if (!$result->isSuccess())
				{
					if ($result->getErrorMessages())
						$lAdmin->AddGroupError(join(', ', $result->getErrorMessages()), $id);
					else
						$lAdmin->AddGroupError(GetMessage("SPSAN_ERROR_DELETE"), $id);
				}

				break;

			case "activate":
			case "deactivate":

				$arFields = array(
					"ACTIVE" => (($_REQUEST['action'] == 'activate') ? 'Y' : 'N')
				);

				$result = \Bitrix\Sale\Internals\PaySystemActionTable::update($id, $arFields);
				if (!$result->isSuccess())
				{
					if ($result->getErrorMessages())
						$lAdmin->AddGroupError(join(', ', $result->getErrorMessages()), $id);
					else
						$lAdmin->AddGroupError(GetMessage("SPSAN_ERROR_UPDATE"), $id);
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

$filter['ENTITY_REGISTRY_TYPE'] = \Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER;

$params = array(
	'select' => array('ID', 'NAME', 'SORT', 'DESCRIPTION', 'ACTIVE', 'ACTION_FILE', 'LOGOTIP', 'PS_MODE'),
	'filter' => $filter
);

global $by, $order;
if (isset($by) && ToUpper($by) != 'LID' && ToUpper($by) != 'CURRENCY')
	$params['order'] = array(ToUpper($by) => ToUpper($order));

$dbRes = \Bitrix\Sale\Internals\PaySystemActionTable::getList($params);

$result = array();

while ($paySystem = $dbRes->fetch())
{
	if (!empty($personTypeId))
	{
		$dbRestriction = \Bitrix\Sale\Internals\ServiceRestrictionTable::getList(array(
			'filter' => array(
				'SERVICE_ID' => $paySystem['ID'],
				'SERVICE_TYPE' => \Bitrix\Sale\Services\PaySystem\Restrictions\Manager::SERVICE_TYPE_PAYMENT,
				'=CLASS_NAME' => '\\'.\Bitrix\Sale\Services\PaySystem\Restrictions\PersonType::class
			)
		));

		while ($restriction = $dbRestriction->fetch())
		{
			if (!CSalePaySystemAction::checkRestriction($restriction,
				array("PERSON_TYPE_ID" => $personTypeId)))
			{
				continue(2);
			}
		}
	}

	$result[] = $paySystem;
}

$dbRes = new CDBResult();
$dbRes->InitFromArray($result);

$dbRes = new CAdminUiResult($dbRes, $sTableID);
$dbRes->NavStart();

$lAdmin->SetNavigationParams($dbRes, array("BASE_LINK" => $selfFolderUrl."sale_pay_system.php"));

$lAdmin->AddHeaders(array(
	array("id"=>"SORT", "content"=>GetMessage("SALE_SORT"),  "sort"=>"SORT", "default"=>true),
	array("id"=>"NAME","content"=>GetMessage("SALE_NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"DESCRIPTION", "content"=>GetMessage("SALE_H_DESCRIPTION"), "default"=>true),
	array("id"=>"LOGOTIP", "content"=>GetMessage("SALE_LOGOTIP"),  "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage("SALE_ACTIVE"),  "sort"=>"ACTIVE", "default"=>true),
	array("id"=>"PERSON_TYPES", "content"=>GetMessage("SALE_H_PERSON_TYPES"), "default"=>false),
	array("id"=>"LID", "content"=>GetMessage('SALE_LID'), "default"=>false),
	array("id"=>"ACTION_FILE", "content"=>GetMessage("SALE_H_ACTION_FILES"), "default"=>false),
	array("id"=>"ID", "content"=>"ID", 	"sort"=>"ID", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arCCard = $dbRes->NavNext(false))
{
	$editUrl = $selfFolderUrl."sale_pay_system_edit.php?ID=".$arCCard["ID"]."&lang=".LANGUAGE_ID;
	$editUrl = $adminSidePanelHelper->editUrlToPublicPage($editUrl);
	if ($publicMode)
	{
//		$editUrl = "/crm/configs/ps/edit/".$arCCard["ID"]."/";
	}
	$row =& $lAdmin->AddRow($arCCard["ID"], $arCCard, $editUrl, GetMessage("SALE_EDIT_DESCR"));

	$row->AddField("ID", "<a href=\"".$editUrl."\">".$arCCard["ID"]."</a>");
	$row->AddField("NAME", $arCCard["NAME"], false, false);
	$row->AddField("ACTIVE", (($arCCard["ACTIVE"]=="Y") ? GetMessage("SPS_YES") : GetMessage("SPS_NO")));
	$row->AddField("SORT", $arCCard["SORT"], false, false);

	if ($arCCard["LOGOTIP"] > 0)
	{
		$logoFileArray = CFile::GetFileArray($arCCard["LOGOTIP"]);
		$arCCard["LOGOTIP"] = CFile::ShowImage($logoFileArray, 100, 100, "border=0", "", false);
	}

	$row->AddField("LOGOTIP", $arCCard["LOGOTIP"]);
	$row->AddField("DESCRIPTION", $arCCard["DESCRIPTION"], false, false);

	$pTypes = '';
	$aFiles = '';

	$dbRestriction = \Bitrix\Sale\Internals\ServiceRestrictionTable::getList(array(
		'select' => array('PARAMS'),
		'filter' => array(
			'SERVICE_ID' => $arCCard["ID"],
			'SERVICE_TYPE' => \Bitrix\Sale\Services\PaySystem\Restrictions\Manager::SERVICE_TYPE_PAYMENT,
			'=CLASS_NAME' => '\\'.\Bitrix\Sale\Services\PaySystem\Restrictions\PersonType::class
		)
	));

	 if ($restriction = $dbRestriction->fetch())
	 {
		 $ptRes = \Bitrix\Sale\PersonTypeTable::getList(array('select' => array('NAME'), 'filter' => array('ID' => $restriction['PARAMS']['PERSON_TYPE_ID'])));
		 while ($personType = $ptRes->fetch())
		    $pTypes .= "<div>".$personType['NAME']."</div>";
	 }

	$row->AddField("PERSON_TYPES", $pTypes);

	$dbRestriction = \Bitrix\Sale\Internals\ServiceRestrictionTable::getList(array(
		'select' => array('PARAMS'),
		'filter' => array(
			'SERVICE_ID' => $arCCard["ID"],
			'SERVICE_TYPE' => \Bitrix\Sale\Services\PaySystem\Restrictions\Manager::SERVICE_TYPE_PAYMENT,
			'=CLASS_NAME' => '\Bitrix\Sale\Services\PaySystem\Restrictions\Site'
		)
	));

	$pSite = '';
	if ($restriction = $dbRestriction->fetch())
	{
		$siteRes = \Bitrix\Main\SiteTable::getList(array('select' => array('NAME', 'LID'), 'filter' => array('LID' => $restriction['PARAMS']['SITE_ID'], 'LANGUAGE_ID' => $context->getLanguage())));
		while ($site = $siteRes->fetch())
			$pSite .= "<div>".$site['NAME']." (".$site['LID'].")</div>";
	}

	$row->AddField("LID", $pSite);

	$description = \Bitrix\Sale\PaySystem\Manager::getHandlerDescription($arCCard["ACTION_FILE"], $arCCard["PS_MODE"]);
	$row->AddField("ACTION_FILE", $description['NAME']);

	$arActions = array(
		array(
			"ICON" => "edit",
			"TEXT" => GetMessage("SALE_EDIT"),
			"TITLE" => GetMessage("SALE_EDIT_DESCR"),
			"LINK" => $editUrl,
			"DEFAULT" => true,
		),
	);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("SALE_DELETE"),
			"TITLE" => GetMessage("SALE_DELETE_DESCR"),
			"ACTION" => "if(confirm('".GetMessage('SALE_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($arCCard["ID"], "delete"),
		);
	}

	$row->AddActions($arActions);
}

if ($saleModulePermissions == "W")
{

	$lAdmin->AddGroupActionTable(
		array(
			"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
			"activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
			"deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
		)
	);

	$addUrl = $selfFolderUrl."sale_pay_system_edit.php?lang=".LANGUAGE_ID;
	$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
	$aContext = array(
		array(
			"TEXT" => GetMessage("SPSAN_ADD_NEW"),
			"TITLE" => GetMessage("SPSAN_ADD_NEW_ALT"),
			"LINK" => $addUrl,
//			"LINK" => ($publicMode ? "/crm/configs/ps/add/" : "sale_pay_system_edit.php?lang=".LANGUAGE_ID),
			"ICON" => "btn_new",
			//"PUBLIC" => ($publicMode ? true : false)
		),
	);
	/** @global CUser $USER */
	global $USER;
	if ($USER->CanDoOperation("install_updates") && !$publicMode)
	{
		$aContext[] = array(
			"TEXT" => GetMessage("SPSAN_MARKETPLACE_ADD_NEW"),
			"TITLE" => GetMessage("SPSAN_MARKETPLACE_ADD_NEW_ALT"),
			"LINK" => "update_system_market.php?category=35&lang=".LANGUAGE_ID,
			"ICON" => "btn"
		);
	}
	$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."sale_pay_system.php"));
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("SALE_SECTION_TITLE"));
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