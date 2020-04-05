<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

use Bitrix\Main;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

if(!CBXFeatures::IsFeatureEnabled('SaleAccounts'))
{
	require($DOCUMENT_ROOT."/bitrix/modules/main/include/prolog_admin_after.php");

	ShowError(GetMessage("SALE_FEATURE_NOT_ALLOW"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

// functions
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/admin_tool.php");

IncludeModuleLangFile(__FILE__);

$request = Main\Context::getCurrent()->getRequest();
$usedProtocol = ($request->isHttps() ? 'https://' : 'http://');

$sTableID = "tbl_sale_basket";

$oSort = new CAdminSorting($sTableID, "DATE_UPDATE_MAX", "DESC");

$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_universal",
	"filter_user_id",
	"filter_fuser_id",
	"filter_login",
	"filter_price_all_from",
	"filter_price_all_to",
	"filter_quantity_all_from",
	"filter_quantity_all_to",
	"filter_cnt_from",
	"filter_cnt_to",
	"filter_basket_type",
	"filter_date_insert_from",
	"filter_date_insert_to",
	"filter_date_update_from",
	"filter_date_update_to",
	"filter_product_id",
	"filter_currency",
	"filter_lang",
	"filter_group_id",
);

$siteName = Array();
$serverName = Array();
$b = "sort";
$o = "asc";
$dbSite = CSite::GetList($b, $o, array());
while ($arSite = $dbSite->Fetch())
{
	$serverName[$arSite["LID"]] = $arSite["SERVER_NAME"];
	$siteName[$arSite["LID"]] = $arSite["NAME"];
	if (strlen($serverName[$arSite["LID"]]) <= 0)
	{
		if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME) > 0)
			$serverName[$arSite["LID"]] = SITE_SERVER_NAME;
		else
			$serverName[$arSite["LID"]] = COption::GetOptionString("main", "server_name", "");
	}
}
$arAccessibleSites = array();
$dbAccessibleSites = CSaleGroupAccessToSite::GetList(
		array(),
		array("GROUP_ID" => $GLOBALS["USER"]->GetUserGroupArray()),
		false,
		false,
		array("SITE_ID")
	);
while ($arAccessibleSite = $dbAccessibleSites->Fetch())
{
	if (!in_array($arAccessibleSite["SITE_ID"], $arAccessibleSites))
		$arAccessibleSites[] = $arAccessibleSite["SITE_ID"];
}

$lAdmin->InitFilter($arFilterFields);

$arFilter = array("ORDER_ID" => false);

if (IntVal($filter_user_id) > 0)
	$arFilter["USER_ID"] = IntVal($filter_user_id);
if (IntVal($filter_fuser_id) > 0)
	$arFilter["FUSER_ID"] = IntVal($filter_fuser_id);
if (strlen($filter_login) > 0)
	$arFilter["USER_LOGIN"] = $filter_login;
if (strlen($filter_currency) > 0)
	$arFilter["CURRENCY"] = $filter_currency;
if (IntVal($filter_price_all_from) > 0)
	$arFilter[">=PRICE_ALL"] = IntVal($filter_price_all_from);
if (IntVal($filter_price_all_to) > 0)
	$arFilter["<PRICE_ALL"] = IntVal($filter_price_all_to);
if (IntVal($filter_quantity_all_from) > 0)
	$arFilter[">=QUANTITY_ALL"] = IntVal($filter_quantity_all_from);
if (IntVal($filter_quantity_all_to) > 0)
	$arFilter["<QUANTITY_ALL"] = IntVal($filter_quantity_all_to);
if (IntVal($filter_cnt_from) > 0)
	$arFilter[">=PR_COUNT"] = IntVal($filter_cnt_from);
if (IntVal($filter_cnt_to) > 0)
	$arFilter["<PR_COUNT"] = IntVal($filter_cnt_to);
if (isset($filter_universal) && strlen($filter_universal) > 0)
	$arFilter["%NAME_SEARCH"] = trim($filter_universal);

if(strlen($filter_basket_type) > 0)
{
	if($filter_basket_type == "CAN_BUY")
		$arFilter["CAN_BUY"] = "Y";
	if($filter_basket_type == "DELAY")
		$arFilter["DELAY"] = "Y";
	if($filter_basket_type == "SUBSCRIBE")
		$arFilter["SUBSCRIBE"] = "Y";
}
if (strlen($filter_date_insert_from)>0) $arFilter[">=DATE_INSERT"] = Trim($filter_date_insert_from);
if (strlen($filter_date_insert_to)>0)
{
	if ($arDate = ParseDateTime($filter_date_insert_to, CSite::GetDateFormat("FULL", SITE_ID)))
	{
		if (StrLen($filter_date_insert_to) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filter_date_insert_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$arFilter["<=DATE_INSERT"] = $filter_date_insert_to;
	}
	else
	{
		$filter_date_insert_to = "";
	}
}
if (strlen($filter_date_update_from)>0) $arFilter[">=DATE_UPDATE"] = Trim($filter_date_update_from);
if (strlen($filter_date_update_to)>0)
{
	if ($arDate = ParseDateTime($filter_date_update_to, CSite::GetDateFormat("FULL", SITE_ID)))
	{
		if (StrLen($filter_date_update_to) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filter_date_update_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$arFilter["<=DATE_UPDATE"] = $filter_date_update_to;
	}
	else
	{
		$filter_date_update_to = "";
	}
}
if (strlen($filter_product_id) > 0)
	$arFilter["PRODUCT_ID"] = $filter_product_id;

if (is_array($filter_lang) && count($filter_lang) > 0)
{
	foreach($filter_lang as $v)
	{
		if(strlen($v) > 0 && (empty($arAccessibleSites) || in_array($v, $arAccessibleSites)))
			$arFilter["LID"][] = $v;
	}
}
if (is_array($filter_group_id) && count($filter_group_id) > 0)
{
	foreach($filter_group_id as $v)
	{
		if(IntVal($v) > 0)
			$arFilter["USER_GROUP_ID"][] = $v;
	}
}

if(!$USER->IsAdmin() && !empty($arAccessibleSites) && count($arAccessibleSites) != count($siteName))
{
	if(empty($arFilter["LID"]))
		$arFilter["LID"] = $arAccessibleSites;
}

if (isset($_REQUEST['action']))
{
	if($_REQUEST['action'] == "order_basket")
	{
		$fuserID = IntVal($_REQUEST["FUSER_ID"]);
		if($fuserID > 0)
		{
			$userID = IntVal($_REQUEST["USER_ID"]);
			$siteID = $_REQUEST["SITE_ID"];
			$url = "/bitrix/admin/sale_order_create.php?lang=".LANG."&SITE_ID=".$siteID."&USER_ID=".$userID."&FUSER_ID=".$fuserID."&ABANDONED=Y";

			$dbBasketList = CSaleBasket::GetList(
				array("ID" => "ASC"),
				array(
					"FUSER_ID" => $fuserID,
					"LID" => $siteID,
					// "CAN_BUY" => "Y",
					// "DELAY" => "N",
					"ORDER_ID" => false,
				),
				false,
				false,
				array("ID", "PRODUCT_ID", "CAN_BUY", "DELAY", "SUBSCRIBE", "QUANTITY")
			);
			$arID = Array();
			while($arItems = $dbBasketList->Fetch())
			{
				if($arItems["CAN_BUY"] == "Y" && $arItems["DELAY"] == "N")
				{
					$arID[] = $arItems["ID"];
				}
				elseif($arItems["DELAY"] == "Y")
				{
					$url .= "&productDelay[]=".$arItems["PRODUCT_ID"];
				}
				elseif($arItems["SUBSCRIBE"] == "Y")
				{
					$url .= "&productSub[]=".$arItems["PRODUCT_ID"];
				}
				else
				{
					$url .= "&productNA[]=".$arItems["PRODUCT_ID"];
				}
			}

			if (count($arID) > 0)
			{
				LocalRedirect($url);
				die();
			}
		}
	}
}

$dbResultList = CSaleBasket::GetLeave(
	array($by => $order),
	$arFilter,
	false,
	array("nPageSize"=>CAdminResult::GetNavSize($sTableID))
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SB_NAV")));

$lAdmin->AddHeaders(array(
	array("id" => "DATE_UPDATE_MAX", "content" => GetMessage("SB_DATE_UPDATE"), "sort" => "DATE_UPDATE_MAX", "default" => true),
	array("id" => "USER_ID","content" => GetMessage("SB_USER"), "sort" => "user_id", "default" => true),
	array("id" => "PRICE_ALL", "content" => GetMessage("SB_PRICE_ALL"), "sort" => "PRICE_ALL", "default" => true, "align" => "right"),
	array("id" => "QUANTITY_ALL", "content" => GetMessage('SB_QUANTITY_ALL'), "sort" => "QUANTITY_ALL", "default" => false, "align" => "right"),
	array("id" => "PR_COUNT", "content" => GetMessage("SB_CNT"), "sort" => "PR_COUNT", "default" => true, "align" => "right"),
	array("id" => "LID", "content" => GetMessage("SB_LID"),  "sort" => "LID", "default" => (count($siteName) == 1) ? false : true),
	array("id" => "BASKET", "content" => GetMessage("SB_BASKET"), "sort" => "", "default" => true),
	array("id" => "BASKET_NAME", "content" => GetMessage("SB_BASKET_NAME"), "sort" => "", "default" => false),
	array("id" => "BASKET_QUANTITY", "content" => GetMessage("SB_BASKET_QUANTITY"),  "sort" => "", "default" => false, "align" => "right"),
	array("id" => "BASKET_PRICE", "content" => GetMessage("SB_BASKET_PRICE"), "sort" => "", "default" => false, "align" => "right"),
	array("id" => "DATE_INSERT_MIN", "content" => GetMessage("SB_DATE_INSERT"), "sort" => "DATE_INSERT_MIN", "default" => true),
	array("id" => "FUSER_ID", "content" => GetMessage("SB_FUSER_ID"), "sort" => "FUSER_ID", "default" => false),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arBasket = $dbResultList->Fetch())
{
	$row =& $lAdmin->AddRow($arBasket["ID"], $arBasket);

	$row->AddField("ID", $arBasket["ID"]);

	$fieldValue = GetMessage("SB_NOT_AUTH");
	if((int)$arBasket["USER_ID"] > 0)
	{
		$fieldValue = "[<a href=\"/bitrix/admin/user_edit.php?ID=".$arBasket["USER_ID"]."&lang=".LANG."\" title=\"".GetMessage("SB_USER_INFO")."\">".$arBasket["USER_ID"]."</a>] ";
		$fieldValue .= " (".htmlspecialcharsEx($arBasket["USER_LOGIN"]).") ";
		$fieldValue .= "<a href=\"sale_buyers_profile.php?USER_ID=".$arBasket["USER_ID"]."&lang=".LANG."\" title=\"".GetMessage("SB_FUSER_INFO")."\">".htmlspecialcharsEx($arBasket["USER_NAME"].((strlen($arBasket["USER_NAME"])<=0 || strlen($arBasket["USER_LAST_NAME"])<=0) ? "" : " ").$arBasket["USER_LAST_NAME"])."</a><br />";
		$fieldValue .= "<a href=\"mailto:".htmlspecialcharsEx($arBasket["USER_EMAIL"])."\" title=\"".GetMessage("SB_MAILTO")."\">".htmlspecialcharsEx($arBasket["USER_EMAIL"])."</a>";
	}
	$row->AddField("USER_ID", $fieldValue);
	$row->AddField("LID", "[".htmlspecialcharsbx($arBasket["LID"])."] ".htmlspecialcharsbx($siteName[$arBasket["LID"]]));

	$row->AddField("PRICE_ALL", SaleFormatCurrency($arBasket["PRICE_ALL"], $arBasket["CURRENCY"]));


	$fieldValue = "";
	$productId = "";
	$arFilterBasket = Array("ORDER_ID" => false, "FUSER_ID" => $arBasket["FUSER_ID"], "LID" => $arBasket["LID"]);
	if(isset($arFilter["CAN_BUY"]))
		$arFilterBasket["CAN_BUY"] = $arFilter["CAN_BUY"];
	if(isset($arFilter["DELAY"]))
		$arFilterBasket["DELAY"] = $arFilter["DELAY"];
	if(isset($arFilter["SUBCRIBE"]))
		$arFilterBasket["SUBCRIBE"] = $arFilter["SUBCRIBE"];

	$bNeedLine = false;
	$basket = "";
	$basketName = "";
	$basketPrice = "";
	$basketQuantity = "";
	$basketAvaible = "";
	$arBasketItems = array();

	$dbB = CSaleBasket::GetList(
		array("ID" => "ASC"),
		$arFilterBasket,
		false,
		false,
		array("ID", "PRODUCT_ID", "NAME", "QUANTITY", "PRICE", "CURRENCY", "DETAIL_PAGE_URL", "LID", "SET_PARENT_ID", "TYPE")
	);
	while($arB = $dbB->Fetch())
	{
		$arBasketItems[] = $arB;
	}

	$arBasketItems = getMeasures($arBasketItems);
	foreach ($arBasketItems as $arB)
	{
		if (CSaleBasketHelper::isSetItem($arB))
			continue;
		
		$productId .= "&product[]=".$arB["PRODUCT_ID"];
		if ($bNeedLine)
		{
			$basketName .= "<br />";
			$basketPrice .= "<br />";
			$basketQuantity .= "<br />";
			$basketAvaible .= "<br />";
		}
		$bNeedLine = true;

		if(strlen($arB["DETAIL_PAGE_URL"]) > 0)
		{
			if(strpos($arB["DETAIL_PAGE_URL"], "http") === false)
				$url = $usedProtocol.$serverName[$arB["LID"]].$arB["DETAIL_PAGE_URL"];
			else
				$url = $arB["DETAIL_PAGE_URL"];
			$basketName .= "<nobr><a href=\"".$url."\">";
			$basket .= "<nobr><a href=\"".$url."\">";
		}
		$basket .= htmlspecialcharsbx($arB["NAME"]);
		$basketName .= htmlspecialcharsbx($arB["NAME"]);
		if(strlen($arB["DETAIL_PAGE_URL"]) > 0)
		{
			$basketName .= "</a></nobr>";
			$basket .= "</a></nobr>";
		}

		$measure = (isset($arB["MEASURE_TEXT"])) ? htmlspecialcharsbx($arB["MEASURE_TEXT"]) : GetMessage("SB_SHT");

		$basket .= " (".$arB["QUANTITY"]." ".$measure.") - "."<nobr>".SaleFormatCurrency($arB["PRICE"], $arB["CURRENCY"])."</nobr><br>";
		$dbProp = CSaleBasket::GetPropsList(Array("SORT" => "ASC", "ID" => "ASC"), Array("BASKET_ID" => $arB["ID"], "!CODE" => array("CATALOG.XML_ID", "PRODUCT.XML_ID")));
		while($arProp = $dbProp -> GetNext())
		{
			$basket .= "<div><small>".$arProp["NAME"].": ".$arProp["VALUE"]."</small></div>";
		}

		$basketPrice .= "<nobr>".SaleFormatCurrency($arB["PRICE"], $arB["CURRENCY"])."</nobr>";
		$basketQuantity .= $arB["QUANTITY"];

	}
	$row->AddField("BASKET", $basket);
	$row->AddField("BASKET_NAME", $basketName);
	$row->AddField("BASKET_PRICE", $basketPrice);
	$row->AddField("BASKET_QUANTITY", $basketQuantity);

	$arActions = Array();
	$arActions[] = array("ICON"=>"", "TEXT"=>GetMessage("SB_CREATE_ORDER"), "ACTION"=>$lAdmin->ActionRedirect("sale_basket.php?FUSER_ID=".$arBasket["FUSER_ID"]."&SITE_ID=".$arBasket["LID"]."&USER_ID=".$arBasket["USER_ID"]."&action=order_basket&lang=".LANG), "DEFAULT" => true);

	if((int)$arBasket["USER_ID"] > 0)
	{
		$arActions[] = array("ICON"=>"", "TEXT"=>GetMessage("SB_FUSER_INFO"), "ACTION"=>$lAdmin->ActionRedirect("sale_buyers_profile.php?USER_ID=".$arBasket["USER_ID"]."&lang=".LANG));
	}

	$row->AddActions($arActions);
}


$arFooterArray = array(
	array(
		"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
		"value" => $dbResultList->SelectedRowsCount()
	),
);

$lAdmin->AddFooter($arFooterArray);

$aContext = Array();
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();


require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(GetMessage("SB_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		"find_universal" => GetMessage("SB_UNIVERSAL"),
		"find_user" => GetMessage("SB_USER_ID"),
		"find_fuser" => GetMessage("SB_FUSER_ID"),
		"find_user_login" => GetMessage("SB_USER_LOGIN"),
		"find_price" => GetMessage("SB_PRICE_ALL"),
		"find_quantity" => GetMessage("SB_QUANTITY_ALL"),
		"find_cnt" => GetMessage("SB_CNT"),
		"find_bt" => GetMessage("SB_BASKET_TYPE"),
		"find_di" => GetMessage("SB_DATE_INSERT"),
		"find_du" => GetMessage("SB_DATE_UPDATE"),
		"find_pi" => GetMessage("SB_PRODUCT_ID"),
		"find_cur" => GetMessage("SB_CURRENCY"),
		"find_ug" => GetMessage("SB_USER_GROUP_ID"),
		"find_lid" => GetMessage("SB_LID"),
	)
);

$oFilter->SetDefaultRows(Array("find_universal", "find_price", "find_ug", "find_quantity"));
$oFilter->AddPreset(array(
		"ID" => "find_1",
		"NAME" => GetMessage("SB_FILTER_WEEK"),
		"FIELDS" => array(
			"filter_date_update_from_FILTER_PERIOD" => "week",
			"filter_date_update_from_FILTER_DIRECTION" => "previous",
			"filter_date_update_from" => ConvertTimeStamp(AddToTimeStamp(Array("DD" => -7))),
			),
		"SORT_FIELD" => Array("PRICE_ALL" => "DESC"),
	));
$oFilter->AddPreset(array(
		"ID" => "find_2",
		"NAME" => GetMessage("SB_FILTER_ALL"),
		"FIELDS" => array("find_user" => ""),
		"SORT_FIELD" => Array("PRICE_ALL" => "DESC"),
	));
$oFilter->AddPreset(array(
		"ID" => "find_3",
		"NAME" => GetMessage("SB_FILTER_PRD"),
		"FIELDS" => array("find_user" => ""),
		"SORT_FIELD" => Array("PR_COUNT" => "DESC"),
	));

$oFilter->Begin();
?>
	<tr>
		<td><?echo GetMessage("SB_UNIVERSAL")?>:</td>
		<td>
			<input type="text" name="filter_universal" value="<?echo htmlspecialcharsbx($filter_universal)?>" size="40">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SB_USER_ID")?>:</td>
		<td>
			<?echo FindUserID("filter_user_id", $filter_user_id, "", "find_form");?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SB_FUSER_ID")?>:</td>
		<td>
			<input type="text" name="filter_fuser_id" size="50" value="<?=((intval($filter_fuser_id) > 0) ? intval($filter_fuser_id):"")?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SB_USER_LOGIN")?>:</td>
		<td>
			<input type="text" name="filter_login" size="50" value="<?= htmlspecialcharsbx($filter_login) ?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SB_PRICE_ALL");?>:</td>
		<td>
			<?echo GetMessage("SB_F_FROM");?>
			<input type="text" name="filter_price_all_from" id="filter_price_all_from" value="<?echo (IntVal($filter_price_all_from) > 0) ? IntVal($filter_price_all_from):""?>" size="10">
			<?echo GetMessage("SB_F_TO");?>
			<input type="text" name="filter_price_all_to" id="filter_price_all_to" value="<?echo (IntVal($filter_price_all_to)>0)?IntVal($filter_price_all_to):""?>" size="10">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SB_QUANTITY_ALL");?>:</td>
		<td>
			<?echo GetMessage("SB_F_FROM");?>
			<input type="text" name="filter_quantity_all_from" value="<?echo (IntVal($filter_quantity_all_from) > 0) ? IntVal($filter_quantity_all_from):""?>" size="10">
			<?echo GetMessage("SB_F_TO");?>
			<input type="text" name="filter_quantity_all_to" value="<?echo (IntVal($filter_quantity_all_to)>0)?IntVal($filter_quantity_all_to):""?>" size="10">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SB_CNT");?>:</td>
		<td>
			<?echo GetMessage("SB_F_FROM");?>
			<input type="text" name="filter_cnt_from" value="<?echo (IntVal($filter_price_all_from) > 0) ? IntVal($filter_cnt_from):""?>" size="10">
			<?echo GetMessage("SB_F_TO");?>
			<input type="text" name="filter_cnt_to" value="<?echo (IntVal($filter_cnt_to)>0)?IntVal($filter_cnt_to):""?>" size="10">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SB_BASKET_TYPE")?>:</td>
		<td>
			<select name="filter_basket_type">
				<option value=""<?if(empty($filter_basket_type)) echo " selected"?>><?echo GetMessage("SB_ALL")?></option>
				<option value="CAN_BUY"<?if($filter_basket == "CAN_BUY") echo " selected"?>><?=GetMessage("SB_TYPE_CAN_BUY")?></option>
				<option value="DELAY"<?if($filter_basket_type == "DELAY") echo " selected"?>><?=GetMessage("SB_TYPE_DELAY")?></option>
				<option value="SUBSCRIBE"<?if($filter_basket_type == "SUBSCRIBE") echo " selected"?>><?=GetMessage("SB_TYPE_SUBCRIBE")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SB_DATE_INSERT");?>:</td>
		<td>
			<?echo CalendarPeriod("filter_date_insert_from", $filter_date_insert_from, "filter_date_insert_to", $filter_date_insert_to, "find_form", "Y")?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SB_DATE_UPDATE");?>:</td>
		<td>
			<?echo CalendarPeriod("filter_date_update_from", $filter_date_update_from, "filter_date_update_to", $filter_date_update_to, "find_form", "Y")?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SB_PRODUCT_ID")?></td>
		<td>
			<script language="JavaScript">
			<!--
			function getProductData(arParams)
			{
				var productId = arParams['id'],
					dateURL = '<?=bitrix_sessid_get()?>&ORDER_AJAX=Y&get_product_params=Y&LID=<?=CUtil::JSEscape($LID)?>&productId=' + productId;

				BX.showWait();

				BX.ajax.post(
					'/bitrix/admin/sale_order_new.php',
					dateURL,
					getProductDataResult
				);
			}

			function getProductDataResult(result)
			{
				BX.closeWait();
				var res = eval( '('+result+')' ),
					params = res['params'];

				if (params["id"])
					BX('filter_product_id').value = params["id"];

				if (params["name"])
				{
					el = BX("product_name_alt");
					if(el)
						el.innerHTML = params["name"];
				}
			}

			function showProductSearchDialog()
			{
				var popup = makeProductSearchDialog({
					caller: 'basket_admin',
					lang: '<?=LANGUAGE_ID?>',
					callback: 'getProductData'
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
					content_url: '/bitrix/tools/sale/product_search_dialog.php?lang='+lang+'&LID='+site_id+'&caller=' + caller + '&func_name='+callback+'&STORE_FROM_ID='+store_id,
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


			//-->
			</script>
			<input name="filter_product_id" id="filter_product_id" value="<?= htmlspecialcharsbx($filter_product_id) ?>" size="5" type="text">&nbsp;<input type="button" value="..." id="cat_prod_button" onClick="showProductSearchDialog()"><span id="product_name_alt" class="adm-filter-text-search"></span>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SB_CURRENCY")?>:</td>
		<td align="left">
			<?= CCurrency::SelectBox("filter_currency", $filter_currency, GetMessage("SB_ALL"), True, "", ""); ?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SB_USER_GROUP_ID")?>:</td>
		<td>
			<?
			$z = CGroup::GetDropDownList("AND ID!=2");
			echo SelectBoxM("filter_group_id[]", $z, $filter_group_id, "", false, 5);
			?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SB_LID");?>:</td>
		<td>
			<select name="filter_lang[]" multiple size="5">
				<option value=""<?if(empty($filter_lang)) echo " selected"?>><?=GetMessage("SB_ALL")?></option>
				<?
				foreach($siteName as $id => $val)
				{
					if (!in_array($id, $arAccessibleSites)
						&& $saleModulePermissions < "W")
						continue;

					?><option value="<?= htmlspecialcharsbx($id)?>"<?if(is_array($filter_lang) && in_array($id, $filter_lang)) echo " selected";?>>[<?= htmlspecialcharsbx($id) ?>]&nbsp;<?= htmlspecialcharsbx($val) ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
<?
$oFilter->Buttons(
	array(
		"table_id" => $sTableID,
		"url" => $APPLICATION->GetCurPage(),
		"form" => "find_form"
	)
);
$oFilter->End();
?>
</form>
<?

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>