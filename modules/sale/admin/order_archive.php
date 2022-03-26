<?/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global string $DBType */
/** @global CDatabase $DB */
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Sale\Internals\StatusTable;
use Bitrix\Sale;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
Loader::includeModule('sale');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

// include functions
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/admin_tool.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$LOCAL_SITE_LIST_CACHE = array();
$LOCAL_PERSON_TYPE_CACHE = array();
$LOCAL_PAYED_USER_CACHE = array();
$LOCAL_STATUS_CACHE = array();

Loc::loadMessages(__FILE__);

$arUserGroups = $USER->GetUserGroupArray();
$intUserID = (int)$USER->GetID();

$arAccessibleSites = array();
$dbAccessibleSites = CSaleGroupAccessToSite::GetList(
		array(),
		array("GROUP_ID" => $arUserGroups),
		false,
		false,
		array("SITE_ID")
	);
while ($arAccessibleSite = $dbAccessibleSites->Fetch())
{
	if (!in_array($arAccessibleSite["SITE_ID"], $arAccessibleSites))
		$arAccessibleSites[] = $arAccessibleSite["SITE_ID"];
}

$exportMode = (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'excel');

$sTableID = "tbl_sale_order_archive";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminSaleList($sTableID, $oSort);
$runtimeFields = array();

/** Filter*/
//----------------------------------------------------------------------------------------------------------------------
$arFilterFields = array(
	"filter_universal",
	"filter_order_id_from",
	"filter_id_from",
	"filter_order_id_to",
	"filter_id_to",
	"filter_account_number",
	"filter_date_from",
	"filter_date_to",
	"filter_date_archived_from",
	"filter_date_archived_to",
	"filter_lang",
	"filter_date_order_from",
	"filter_date_order_to",
	"filter_price_from",
	"filter_price_to",
	"filter_buyer",
	"filter_person_type",
	"filter_user_id",
	"filter_user_login",
	"filter_user_email",
	"filter_status"
);

$lAdmin->InitFilter($arFilterFields);

$filter_lang = trim($filter_lang);
if ($filter_lang <> '')
{
	if (!in_array($filter_lang, $arAccessibleSites) && $saleModulePermissions < "W")
		$filter_lang = "";
}


if ($filter_lang <> '' && $filter_lang!="NOT_REF")
	$arFilter["=LID"] = trim($filter_lang);

if($saleModulePermissions < "W")
{
	if($filter_lang == '' && count($arAccessibleSites) > 0)
		$arFilter["=LID"] = $arAccessibleSites;
}

$arFilter = array();

if($saleModulePermissions == "P")
{
	$userCompanyList = Sale\Services\Company\Manager::getUserCompanyList($USER->GetID());

	$arFilter[] = array(
		"LOGIC" => "OR",
		'=RESPONSIBLE_ID' => $USER->GetID(),
		'=COMPANY_ID' => $userCompanyList,
	);

	$arSelectFields[] = 'RESPONSIBLE_ID';
	$arSelectFields[] = 'COMPANY_ID';

}

if ((int)($filter_id_from)>0)
	$arFilter[">=ID"] = (int)($filter_id_from);
if ((int)($filter_id_to)>0)
	$arFilter["<=ID"] = (int)($filter_id_to);
if ((int)($filter_order_id_from)>0)
	$arFilter[">=ORDER_ID"] = (int)($filter_order_id_from);
if ((int)($filter_order_id_to)>0)
	$arFilter["<=ORDER_ID"] = (int)($filter_order_id_to);
if ($filter_date_from <> '')
	$arFilter[">=DATE_INSERT"] = trim($filter_date_from);
if ($filter_date_to <> '')
{
	if ($arDate = ParseDateTime($filter_date_to, CSite::GetDateFormat("FULL", SITE_ID)))
	{
		if (mb_strlen($filter_date_to) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filter_date_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$arFilter["<=DATE_INSERT"] = $filter_date_to;
	}
	else
	{
		$filter_date_to = "";
	}
}

if ($filter_date_archived_from <> '')
	$arFilter[">=DATE_ARCHIVED"] = trim($filter_date_archived_from);
if ($filter_date_archived_to <> '')
{
	if ($arDate = ParseDateTime($filter_date_archived_to, CSite::GetDateFormat("FULL", SITE_ID)))
	{
		if (mb_strlen($filter_date_archived_to) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filter_date_archived_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$arFilter["<=DATE_ARCHIVED"] = $filter_date_archived_to;
	}
	else
	{
		$filter_date_archived_to = "";
	}
}

if ((int)($filter_user_id)>0)
	$arFilter["=USER_ID"] = (int)($filter_user_id);
if (is_array($filter_group_id) && count($filter_group_id) > 0)
{
	foreach($filter_group_id as $v)
	{
		if((int)($v) > 0)
			$arFilter["USER_GROUP.GROUP_ID"][] = $v;
	}
}

if (isset($filter_person_type) && is_array($filter_person_type) && count($filter_person_type) > 0)
{
	$countFilterPerson = count($filter_person_type);
	for ($i = 0; $i < $countFilterPerson; $i++)
	{
		if ((int)($filter_person_type[$i]) > 0)
			$arFilter["=PERSON_TYPE_ID"][] = $filter_person_type[$i];
	}
}

if ((float)($filter_price_from)>0)
	$arFilter[">=PRICE"] = (float)($filter_price_from);
if ((float)($filter_price_to)>0)
	$arFilter["<PRICE"] = (float)($filter_price_to);
if ($filter_account_number <> '')
	$arFilter["ACCOUNT_NUMBER"] = trim($filter_account_number);

if (!empty($_REQUEST['OID']) && is_array($_REQUEST['OID']))
{
	foreach ($_REQUEST['OID'] as $orderId)
	{
		if ((int)($orderId) > 0)
		{
			$arFilter['=ID'][] = (int)($orderId);
		}
	}
}

if (!empty($filter_product_id))
{
	if ((int)($filter_product_id) > 0)
	{
		$runtimeFields["ARCHIVED_BASKET_ITEMS"] = array(
			'select' => "PRODUCT_ID",
			'data_type' => '\Bitrix\Sale\Internals\BasketArchiveTable',
			'reference' => array(
				'=this.ID' => 'ref.ARCHIVE_ID'
			)
		);

		$arFilter["=ARCHIVED_BASKET_ITEMS.PRODUCT_ID"] = (int)($filter_product_id);
	}
}

if (isset($filter_status) && !is_array($filter_status) && $filter_status <> '')
	$filter_status = array($filter_status);
if (isset($filter_status) && is_array($filter_status) && count($filter_status) > 0)
{
	$countFilter = count($filter_status);
	for ($i = 0; $i < $countFilter; $i++)
	{
		$filter_status[$i] = trim($filter_status[$i]);
		if ($filter_status[$i] <> '')
			$arFilter["=STATUS_ID"][] = $filter_status[$i];
	}
}

$allowedStatusesView = \Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('view'));

if($saleModulePermissions < "W")
{
	if(!$arFilter["=STATUS_ID"])
		$arFilter["=STATUS_ID"] = array();

	$intersected = array_intersect($arFilter["=STATUS_ID"], $allowedStatusesView);

	if(!empty($arFilter["=STATUS_ID"]))
	{
		if(empty($intersected))
		{
			$arFilter[]["=STATUS_ID"] = $arFilter["=STATUS_ID"];
			$arFilter[]["=STATUS_ID"] = $allowedStatusesView;
			unset($arFilter["=STATUS_ID"], $arFilter["=STATUS_ID"]);
		}
		else
		{
			$arFilter["=STATUS_ID"] = $intersected;
		}
	}
	else
	{
		$arFilter["=STATUS_ID"] = $allowedStatusesView;
	}
}

if ($filter_payed <> '') $arFilter["=PAYED"] = trim($filter_payed);
if ($filter_canceled <> '') $arFilter["=CANCELED"] = trim($filter_canceled);
if ($filter_deducted <> '') $arFilter["=DEDUCTED"] = trim($filter_deducted);
if ((int)($filter_user_id)>0) $arFilter["=USER_ID"] = (int)($filter_user_id);
if ($filter_user_login <> '') $arFilter["USER.LOGIN"] = trim($filter_user_login);
if ($filter_user_email <> '') $arFilter["USER.EMAIL"] = trim($filter_user_email);
if ($filter_xml_id <> '') $arFilter["%XML_ID"] = trim($filter_xml_id);

if (isset($filter_person_type) && is_array($filter_person_type) && count($filter_person_type) > 0)
{
	$countFilterPerson = count($filter_person_type);
	for ($i = 0; $i < $countFilterPerson; $i++)
	{
		if ((int)($filter_person_type[$i]) > 0)
			$arFilter["=PERSON_TYPE_ID"][] = $filter_person_type[$i];
	}
}

$arFilterTmp = $arFilter;

$idLists = array();

if ($_REQUEST['action_target'] == 'selected')
{
	$archiveData = Sale\Archive\Manager::getList(
		array(
			"filter" => $arFilterTmp,
			"select" => array("ID")
		)
	);
	while ($archive = $archiveData->fetch())
	{
		$idLists[] = $archive['ID'];
	}
}
else
{
	$idLists = $lAdmin->GroupAction();
}

/** Block of group operations*/
//----------------------------------------------------------------------------------------------------------------------
if ($idLists && $saleModulePermissions == "W")
{
	switch ($_REQUEST['action_button'])
	{
		case "delete":
			foreach ($idLists as $id)
			{
				if ((int)$id)
					Sale\Archive\Manager::delete($id);
			}
			break;
	}
}

/** Headers of table */
//----------------------------------------------------------------------------------------------------------------------
$arColumn2Field = array(
		"ORDER_ID" => array("ID"),
		"ACCOUNT_NUMBER" => array("ACCOUNT_NUMBER"),
		"PRICE" => array("PRICE", "CURRENCY"),
		"DATE_INSERT" => array("DATE_INSERT"),
		"DATE_ARCHIVED" => array("DATE_ARCHIVED"),

	);

$arHeaders = array(

	array("id"=>"ORDER_ID","content"=>Loc::getMessage("SALE_F_ORDER_ID"), "sort"=>"ORDER_ID", "default"=>true),
	array("id"=>"ACCOUNT_NUMBER","content"=>Loc::getMessage("SOA_ACCOUNT_NUMBER"), "sort"=>"ACCOUNT_NUMBER", "default"=>true),
	array("id"=>"DATE_INSERT","content"=>Loc::getMessage("SI_DATE_INSERT_ORDER"), "sort"=>"DATE_INSERT", "default"=>true),
	array("id"=>"USER","content"=>Loc::getMessage("SI_BUYER"), "sort"=>"USER_ID", "default"=>true),
	array("id"=>"PRICE","content"=>Loc::getMessage("SI_SUM"), "sort"=>"PRICE", "default"=>true),
	array("id"=>"STATUS_ID","content"=>Loc::getMessage("SI_STATUS"), "sort"=>"STATUS_ID", "default"=>true, "title" => Loc::getMessage("SO_S_DATE_STATUS")),
	array("id"=>"PAYED","content"=>Loc::getMessage("SI_PAID"), "sort"=>"PAYED", "default"=>true, "title" => Loc::getMessage("SO_S_DATE_PAYED")),
	array("id"=>"CANCELED","content"=>Loc::getMessage("SI_CANCELED"), "sort"=>"CANCELED", "default"=>true),
	array("id"=>"DEDUCTED","content"=>Loc::getMessage("SI_DEDUCTED"), "sort"=>"DEDUCTED", "default"=>true),
	array("id"=>"BASKET","content"=>Loc::getMessage("SI_ITEMS"), "sort"=>"", "default"=>true),
	array("id"=>"DATE_ARCHIVED","content"=>Loc::getMessage("SI_DATE_ARCHIVED"), "sort"=>"DATE_ARCHIVED", "default"=>true),
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID"),
	array("id"=>"LID","content"=>Loc::getMessage("SI_SITE"), "sort"=>"LID"),
	array("id"=>"USER_EMAIL","content"=>Loc::getMessage("SALE_F_USER_EMAIL"), "sort"=>"USER_EMAIL", "default"=>false),
	array("id"=>"PERSON_TYPE","content"=>Loc::getMessage("SI_PAYER_TYPE"), "sort"=>"PERSON_TYPE_ID"),
	array("id"=>"BASKET_NAME","content"=>Loc::getMessage("SOA_BASKET_NAME"), "sort"=>""),
	array("id"=>"BASKET_PRODUCT_ID","content"=>Loc::getMessage("SOA_BASKET_PRODUCT_ID"), "sort"=>""),
	array("id"=>"BASKET_PRICE","content"=>Loc::getMessage("SOA_BASKET_PRICE"), "sort"=>""),
	array("id"=>"BASKET_QUANTITY","content"=>Loc::getMessage("SOA_BASKET_QUANTITY"), "sort"=>""),
	array("id"=>"BASKET_WEIGHT","content"=>Loc::getMessage("SOA_BASKET_WEIGHT"), "sort"=>""),
	array("id"=>"BASKET_PRODUCT_XML_ID","content"=>Loc::getMessage("SOA_BASKET_PRODUCT_XML_ID"), "sort"=>""),
	array("id"=>"XML_ID","content"=>Loc::getMessage("SO_XML_ID"), "sort"=>"XML_ID", "default"=>false)
);

$lAdmin->AddHeaders($arHeaders);

$arSelectFields = array(
	"ORDER_ID", "ACCOUNT_NUMBER","USER_ID", "PRICE", "DATE_ARCHIVED", "DATE_INSERT", "STATUS_ID",
	"PAYED", "DEDUCTED", "CANCELED", "PERSON_TYPE_ID", "XML_ID", "ID_1C", "ID", "LID", "USER_EMAIL"
);

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();
$bNeedProps = false;
$bNeedBasket = false;
foreach ($arVisibleColumns as $visibleColumn)
{
	if (!$bNeedProps && mb_substr($visibleColumn, 0, mb_strlen("PROP_")) == "PROP_")
		$bNeedProps = true;
	if (
		!$bNeedBasket
		&& $visibleColumn != 'BASKET_DISCOUNT_COUPON'
		&& $visibleColumn != 'BASKET_DISCOUNT_NAME'
		&& mb_strpos($visibleColumn, "BASKET") !== false
	)
		$bNeedBasket = true;

	if(array_key_exists($visibleColumn, $arColumn2Field))
	{
		if(is_array($arColumn2Field[$visibleColumn]) && count($arColumn2Field[$visibleColumn]) > 0)
		{
			$countArColumn = count($arColumn2Field[$visibleColumn]);
			for ($i = 0; $i < $countArColumn; $i++)
			{
				if(!in_array($arColumn2Field[$visibleColumn][$i], $arSelectFields))
					$arSelectFields[] = $arColumn2Field[$visibleColumn][$i];
			}
		}
	}
}

if (in_array('USER_EMAIL', $arSelectFields))
{
	$arSelectFields["USER_EMAIL"] = 'USER.EMAIL';

	if ($searchIndex = array_search('USER_EMAIL', $arSelectFields))
		unset($arSelectFields[$searchIndex]);
}

$filterOrderSelection = array();

if (!empty($by) && in_array($by, $arSelectFields))
{
	if (!isset($order))
		$order = "DESC";
	$filterOrderSelection[mb_strtoupper($by)] = $order;
}

$ordersIds = array();
$shipmentStatuses = array();
$rowsList = array();
$basketSeparator = '<hr size="1" width="90%">';
$basketSetSeparator = '<br>&nbsp;-&nbsp;';

if ($exportMode)
{
	$basketSeparator = "<br>";
	$basketSetSeparator = "<br>";
}

$orderList = array();
$archivedOrdersId = array();
$basketList = array();

$users = array();
$formattedUserNames = array();

$nav = new \Bitrix\Main\UI\AdminPageNavigation("nav-archive");

$orderIteratorParams = [
	'filter' => $arFilterTmp,
	'select' => $arSelectFields,
	'runtime' => $runtimeFields,
	'order' => $filterOrderSelection,
	'count_total' => true,
];

if (!$exportMode)
{
	$orderIteratorParams['offset'] = $nav->getOffset();
	$orderIteratorParams['limit'] = $nav->getLimit();
}

$orderIterator = \Bitrix\Sale\Internals\OrderArchiveTable::getList($orderIteratorParams);

$nav->setRecordCount($orderIterator->getCount());

$lAdmin->setNavigation($nav, Loc::getMessage("SALE_PRLIST"));

while($order = $orderIterator->fetch())
{
	$orderList[$order['ID']] = $order;
}

if (!empty($orderList) && is_array($orderList))
{
	if(\Bitrix\Main\Analytics\Catalog::isOn() || $bNeedBasket)
	{
		$dbItemsList = \Bitrix\Sale\Internals\BasketArchiveTable::getList(array(
			'order' => array('ID' => 'ASC'),
			'filter' => array('=ARCHIVE_ID' => array_keys($orderList))
		));

		while ($item = $dbItemsList->fetch())
		{
			$basketList[$item['ARCHIVE_ID']][$item['ID']] = $item;
		}
	}

	foreach ($orderList as $id => $arOrder)
	{
		$formattedUserNames = GetFormatedUserName(array_values($users), false);

		$basketItems = array();
		if(\Bitrix\Main\Analytics\Catalog::isOn() || $bNeedBasket)
		{
			$basketItems = $basketList[$arOrder["ID"]];
		}

		/**
		 * build row
		 */
		$rowsList[$arOrder['ID']] = $row =& $lAdmin->AddRow($arOrder['ID'], $arOrder);


		$rowTmp = '<table>
						<tr>
							<td valign="top"></td>
							<td><b>##ID##</b></td>
						</tr>
					</table>';
		$row->AddField("ORDER_ID", str_replace('##ID##', Loc::getMessage("SO_ORDER_ID_PREF").$arOrder["ORDER_ID"], $rowTmp));

		//ACCOUNT_NUMBER
		$fieldValue = "";
		if(in_array("ACCOUNT_NUMBER", $arVisibleColumns))
		{
			$fieldValue = str_replace('##ID##', Loc::getMessage("SO_ORDER_ID_PREF").htmlspecialcharsbx($arOrder["ACCOUNT_NUMBER"]), $rowTmp);
		}
		$row->AddField("ACCOUNT_NUMBER", $fieldValue);

		$row->AddField("DATE_INSERT", $arOrder["DATE_INSERT"]);

		//XML_ID
		$fieldValue = "";
		if(in_array("XML_ID", $arVisibleColumns))
		{
			$fieldValue = $arOrder["XML_ID"];
		}
		$row->AddField("XML_ID", $fieldValue);

		//LID
		$fieldValue = "";
		if(in_array("LID", $arVisibleColumns))
		{
			if(!isset($LOCAL_SITE_LIST_CACHE[$arOrder["LID"]])
				|| empty($LOCAL_SITE_LIST_CACHE[$arOrder["LID"]]))
			{
				$dbSite = CSite::GetByID($arOrder["LID"]);
				if($arSite = $dbSite->Fetch())
					$LOCAL_SITE_LIST_CACHE[$arOrder["LID"]] = htmlspecialcharsbx($arSite["NAME"]);
			}
			$fieldValue = "[".$arOrder["LID"]."] ".$LOCAL_SITE_LIST_CACHE[$arOrder["LID"]];
		}
		$row->AddField("LID", $fieldValue);

		//PERSON_TYPE
		$fieldValue = "";
		if(in_array("PERSON_TYPE", $arVisibleColumns))
		{
			if(!isset($LOCAL_PERSON_TYPE_CACHE[$arOrder["PERSON_TYPE_ID"]])
				|| empty($LOCAL_PERSON_TYPE_CACHE[$arOrder["PERSON_TYPE_ID"]]))
			{
				$personTypeList = Sale\PersonType::load($arOrder["LID"]);

				if(count($personTypeList))
					$LOCAL_PERSON_TYPE_CACHE[$arOrder["PERSON_TYPE_ID"]] = htmlspecialcharsbx($personTypeList[$arOrder["PERSON_TYPE_ID"]]["NAME"]);
			}
			$fieldValue = "[";
			if($saleModulePermissions >= "W")
				$fieldValue .= '<a href="/bitrix/admin/sale_person_type.php?lang='.LANGUAGE_ID.'">';
			$fieldValue .= $arOrder["PERSON_TYPE_ID"];
			if($saleModulePermissions >= "W")
				$fieldValue .= "</a>";
			$fieldValue .= "] ".$LOCAL_PERSON_TYPE_CACHE[$arOrder["PERSON_TYPE_ID"]];
		}
		$row->AddField("PERSON_TYPE", $fieldValue);

		//PAYED
		$fieldValue = "";
		if(in_array("PAYED", $arVisibleColumns))
		{
			$fieldValue .= '<span id="payed_'.$arOrder['ID'].'">'.(($arOrder["PAYED"] == "Y") ? Loc::getMessage("SO_YES") : Loc::getMessage("SO_NO"))."</span>";
		}
		$row->AddField("PAYED", $fieldValue);

		//CANCELED
		if($row->bEditMode != true
			|| $row->bEditMode == true && !CSaleOrder::CanUserCancelOrder($orderId, $arUserGroups, $intUserID))
		{
			$fieldValue = "";
			if(in_array("CANCELED", $arVisibleColumns))
			{
				$fieldValue .= '<span id="cancel_'.$arOrder['ID'].'">'.(($arOrder["CANCELED"] == "Y") ? Loc::getMessage("SO_YES") : Loc::getMessage("SO_NO"))."</span>";
			}
			$row->AddField("CANCELED", $fieldValue);
		}
		else
		{
			$row->AddCheckField("CANCELED");
		}

		//DEDUCTED
		$fieldValue = "";
		if(in_array("PAYED", $arVisibleColumns))
		{
			$fieldValue .= '<span id="payed_'.$arOrder['ID'].'">'.(($arOrder["DEDUCTED"] == "Y") ? Loc::getMessage("SO_YES") : Loc::getMessage("SO_NO"))."</span>";
		}
		$row->AddField("DEDUCTED", $fieldValue);

		//STATUS_ID
		if(in_array("STATUS_ID", $arVisibleColumns))
		{
			$arStatusList = false;
			$fieldValue = "";
			$fieldValueTmp = "";
			if(in_array("STATUS_ID", $arVisibleColumns))
			{
				if(!isset($LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]])
					|| empty($LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]]))
				{
					$arStatus =  StatusTable::getList(array(
						'select' => array(
							'NAME' => 'Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME',
							'COLOR'
						),
						'filter' => array(
							'=ID' => $arOrder["STATUS_ID"],
							'=Bitrix\Sale\Internals\StatusLangTable:STATUS.LID' => LANGUAGE_ID,
							'=TYPE' => 'O'
						),
						'limit'  => 1,
					))->fetch();

					if($arStatus)
					{
						$LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]]['NAME'] = htmlspecialcharsEx($arStatus["NAME"]);
						$LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]]['COLOR'] = $arStatus["COLOR"];
					}
				}

				$fieldValueTmp .= "[";

				if($saleModulePermissions >= "W")
					$fieldValueTmp .= '<a href="/bitrix/admin/sale_status_edit.php?ID='.$arOrder["STATUS_ID"].'&lang='.LANGUAGE_ID.'">';

				$fieldValueTmp .= $arOrder["STATUS_ID"];

				if($saleModulePermissions >= "W")
					$fieldValueTmp .= "</a>";

				$fieldValueTmp .= "] ".$LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]]['NAME'];
				$fieldValue .= '<span id="status_order_'.$arOrder["ID"].'">'.$LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]]['NAME'].'</span>';
				$colorRGB = array();
				$colorRGB = sscanf($LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]]['COLOR'], "#%02x%02x%02x");
				if (is_array($colorRGB) && !empty($colorRGB))
				{
					$color = "background:rgba(".$colorRGB[0].",".$colorRGB[1].",".$colorRGB[2].",0.6);";
					$fieldValue = '<div style=	"'.$color.'
									margin: -11px 0 -10px -16px;
									padding: 11px 10px 10px 16px;
									height: 100%;
								">'.$fieldValue."</div>";
				}
				$fieldValueTmp .= "<br />".$arOrder["DATE_STATUS"];

				if((int)($arOrder["EMP_STATUS_ID"]) > 0)
					$fieldValueTmp .= '<br />'.$formattedUserNames[$arOrder["EMP_STATUS_ID"]];

				$sScript .= "
					new top.BX.CHint({
						parent: top.BX('status_order_".$arOrder["ID"]."'),
						show_timeout: 10,
						hide_timeout: 100,
						dx: 2,
						preventHide: true,
						min_width: 250,
						hint: '".CUtil::JSEscape($fieldValueTmp)."'
					});
				";
			}
			$row->AddField("STATUS_ID", $fieldValue);
		}

		$row->AddField("PRICE", '<span style="white-space:nowrap;">'.SaleFormatCurrency($arOrder["PRICE"], $arOrder["CURRENCY"]).'</span>');

		$fieldValue = "";

		//BASKET POSITIONS
		$fieldValue = "";
		$fieldName = "";
		$fieldQuantity = "";
		$fieldProductID = "";
		$fieldPrice = "";
		$fieldWeight = "";
		$fieldNotes = "";
		$fieldDiscountPrice = "";
		$fieldCatalogXML = "";
		$fieldProductXML = "";
		$fieldDiscountName  = "";
		$fieldDiscountValue  = "";
		$fieldVatRate  = "";
		$setParentId = null;

		if($bNeedBasket && is_array($basketItems))
		{
			$bNeedLine = false;
			$arElementId = array();

			$parentItemFound = false;

			foreach ($basketItems as $arItem)
			{
				$arElementId[] = $arItem["PRODUCT_ID"];

				if(CSaleBasketHelper::isSetParent($arItem) || CSaleBasketHelper::isSetItem($arItem))
					$parentItemFound = true;
			}

			$basketItems = getMeasures($basketItems);

			foreach ($basketItems as $arItem)
			{
				$measure = (isset($arItem["MEASURE_TEXT"])) ? $arItem["MEASURE_TEXT"] : Loc::getMessage("SO_SHT");

				if($bNeedLine)
				{
					if(!CSaleBasketHelper::isSetItem($arItem))
						$separator = $basketSeparator;
					else
						$separator = $basketSetSeparator;
				}

				$fieldName .= $separator;
				$fieldQuantity .= $separator;
				$fieldProductID .= $separator;
				$fieldPrice .= $separator;
				$fieldWeight .= $separator;
				$fieldNotes .= $separator;
				$fieldDiscountPrice .= $separator;
				$fieldCatalogXML .= $separator;
				$fieldProductXML .= $separator;
				$fieldDiscountValue  .= $separator;
				$fieldVatRate  .= $separator;

				$bNeedLine = true;

				$hidden = "";
				$setItemClass = "";
				$linkClass = "";
				if(CSaleBasketHelper::isSetItem($arItem))
				{
					$hidden = 'style="display:none"';
					$setItemClass = 'class="set_item_'.$setParentId.'"';
					$linkClass = "set-item-link-name";
				}

				$fieldValue .= "<div ".$hidden. " ".$setItemClass.">";

				if($arItem['RECOMMENDATION'])
					$fieldValue .= '<div class="bx-adm-bigdata-icon-medium-inner"></div>';

				$fieldValue .= "[".$arItem["PRODUCT_ID"]."] ";

				$fieldValue .= htmlspecialcharsbx($arItem["NAME"]);
				if($arItem["DETAIL_PAGE_URL"] <> '')
					$fieldValue .= "</a>";

				$fieldValue .= " <nobr>(".Sale\BasketItem::formatQuantity($arItem["QUANTITY"])." ".$measure.")</nobr>";

				if(CSaleBasketHelper::isSetParent($arItem))
				{
					$setParentId = $arItem["ID"];
					$fieldValue .= '<div class="set-link-block">';
					$fieldValue	.= '<a class="dashed-link show-set-link" href="javascript:void(0);" id="set_toggle_link_'.$arItem["ID"].'" onclick="fToggleSetItems('.$arItem["ID"].')">'.Loc::getMessage("SOA_SHOW_SET")."</a>";
					$fieldValue .= "</div>";
				}

				if($bNeedLine)
					$fieldValue .= $basketSeparator;

				$fieldValue .= "</div>";

				if($arItem["NAME"] <> '')
				{
					$fieldName .= "<nobr>";
					if($arItem["DETAIL_PAGE_URL"] <> '')
						$fieldName .= '<a href="'.$url.'">';
					$fieldName .= htmlspecialcharsbx($arItem["NAME"]);
					if($arItem["DETAIL_PAGE_URL"] <> '')
						$fieldName .= "</a>";
					$fieldName .= "</nobr>";
				}
				else
					$fieldName .= "<br />";

				if($arItem["QUANTITY"] <> '')
					$fieldQuantity .= htmlspecialcharsbx(Sale\BasketItem::formatQuantity($arItem["QUANTITY"]))." ".$measure;
				else
					$fieldQuantity .= "<br />";
				if($arItem["PRODUCT_ID"] <> '')
					$fieldProductID .= htmlspecialcharsbx($arItem["PRODUCT_ID"]);
				else
					$fieldProductID .= "<br />";
				if($arItem["PRICE"] <> '')
					$fieldPrice .= "<nobr>".SaleFormatCurrency($arItem["PRICE"], $arItem["CURRENCY"])."</nobr>";
				else
					$fieldPrice .= "<br />";
				if($arItem["WEIGHT"] <> '')
				{
					if((float)$WEIGHT_KOEF[$arOrder["LID"]] > 0)
						$fieldWeightCalc = (float)($arItem["WEIGHT"]/$WEIGHT_KOEF[$arOrder["LID"]]);
					else
						$fieldWeightCalc = (float)$arItem["WEIGHT"];
					if(!empty($arItem["QUANTITY"]))
					{
						$fieldWeightCalc *= $arItem["QUANTITY"];
					}
					$fieldWeight .= htmlspecialcharsbx(roundEx($fieldWeightCalc, SALE_WEIGHT_PRECISION).' '.$WEIGHT_UNIT[$arOrder["LID"]]);
				}
				else
					$fieldWeight .= "<br />";

				if($arItem["PRODUCT_XML_ID"] <> '')
					$fieldProductXML .= $arItem["PRODUCT_XML_ID"];
				else
					$fieldProductXML .= "<br />";
			}
			unset($arItem);
		}
		$row->AddField("BASKET", $fieldValue);
		$row->AddField("BASKET_NAME", $fieldName);
		$row->AddField("BASKET_QUANTITY", $fieldQuantity);
		$row->AddField("BASKET_PRODUCT_ID", $fieldProductID);
		$row->AddField("BASKET_PRICE", $fieldPrice);
		$row->AddField("BASKET_WEIGHT", $fieldWeight);
		//ID
		$idTmp = '<table>
						<tr>
							<td valign="top"></td>
							<td><a href="/bitrix/admin/sale_order_archive_view.php?ID=##ID##"><b>'.Loc::getMessage("SO_ORDER_ID_PREF").'##ID##</b></a></td>
						</tr>
					</table>';
		$row->AddField("ID", str_replace('##ID##', $arOrder["ID"], $idTmp));

		$row->AddField("BASKET_PRODUCT_XML_ID", $fieldProductXML);


		if(in_array("USER", $arVisibleColumns))
			$fieldValue = GetFormatedUserName($arOrder["USER_ID"], false, false);

		$row->AddField("USER", $fieldValue);

		$allowedStatusesUpdate = \Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));

		if (in_array($arOrder["STATUS_ID"], $allowedStatusesUpdate))
		{
			$arActions[] = array("ICON"=>"copy", "TEXT"=>Loc::getMessage("SOA_RESTORE_ORDER"), "ACTION"=>$lAdmin->ActionRedirect("sale_order_create.php?restoreID=".$arOrder['ID']."&lang=".LANGUAGE_ID."&".'SITE_ID='.$arOrder['LID'].'&'.bitrix_sessid_get().GetFilterParams("filter_")));
		}

		$arActions[] = array("ICON"=>"view", "TEXT"=>Loc::getMessage("SALE_DETAIL_DESCR"), "ACTION"=>$lAdmin->ActionRedirect("sale_order_archive_view.php?ID=".$arOrder['ID']."&lang=".LANGUAGE_ID."&".'SITE_ID='.$arOrder['LID'].'&'.bitrix_sessid_get()), "DEFAULT"=>true);
		if ($saleModulePermissions == "W")
		{
			$arActions[] = array("ICON"=>"delete", "TEXT"=>Loc::getMessage("SALE_DELETE_DESCR"), "ACTION"=>$lAdmin->ActionDoGroup($arOrder['ID'], 'delete'));
		}

		$row->AddActions($arActions);
		unset($arActions);
	}
}

unset($rowsList);
if ($saleModulePermissions == "W")
{
	$arGroupActionsTmp = array(
		"delete" => Loc::getMessage("MAIN_ADMIN_LIST_DELETE")
	);
}

$lAdmin->AddGroupActionTable($arGroupActionsTmp);
$aContext = array();

$allowedStatusesDelete = \Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations($intUserID, array('delete'));

if($saleModulePermissions >= "W" || !empty($allowedStatusesDelete))
{
	$aContext = array(
		array(
			"TEXT" => Loc::getMessage("SOAN_ARCHIVE_LINK"),
			"LINK" => "sale_archive.php?lang=".LANGUAGE_ID,
			"TITLE" => Loc::getMessage("SOAN_ARCHIVE_LINK_TITLE")
		),
	);
}

$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

\Bitrix\Main\Page\Asset::getInstance()->addString('<style>.adm-filter-item-center, .adm-filter-content {overflow: visible !important;}</style>');

/*********************************************************************/
/********************  PAGE  *****************************************/
/*********************************************************************/

$APPLICATION->SetTitle(Loc::getMessage("SALE_SECTION_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

?>
<script type="text/javascript">
function fToggleSetItems(setParentId)
{
	var elements = document.getElementsByClassName('set_item_' + setParentId);
	var hide = false;

	for (var i = 0; i < elements.length; ++i)
	{
		if(elements[i].style.display == 'none' || elements[i].style.display == '')
		{
			elements[i].style.display = 'table-row';
			hide = true;
		}
		else
			elements[i].style.display = 'none';
	}

	if(hide)
		BX("set_toggle_link_" + setParentId).innerHTML = '<?=Loc::getMessage("SOA_HIDE_SET")?>';
	else
		BX("set_toggle_link_" + setParentId).innerHTML = '<?=Loc::getMessage("SOA_SHOW_SET")?>';
}
</script>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$arFilterFieldsTmp = array(
	"filter_order_id_from" => Loc::getMessage("SALE_F_ORDER_ID"),
	"filter_account_number" => Loc::getMessage("SALE_F_ACCOUNT_NUMBER"),
	"filter_date_insert" => Loc::getMessage("SALE_F_DATE"),
	"filter_price" => Loc::getMessage("SOA_F_PRICE"),
	"filter_person_type" => Loc::getMessage("SALE_F_PERSON_TYPE"),
	"filter_user_id" => Loc::getMessage("SALE_F_USER_ID"),
	"filter_user_login" => Loc::getMessage("SALE_F_USER_LOGIN"),
	"filter_user_email" => Loc::getMessage("SALE_F_USER_EMAIL"),
	"filter_date_archived" => Loc::getMessage("SALE_F_DATE_ARCHIVED"),
	"filter_status" => Loc::getMessage("SALE_F_STATUS"),
	"filter_canceled" => Loc::getMessage("SALE_F_CANCELED"),
	"filter_deducted" => Loc::getMessage("SALE_F_DEDUCTED"),
	"filter_payed" => Loc::getMessage("SALE_F_PAYED"),
	"filter_id_from" => Loc::getMessage("SALE_F_ID"),
	"filter_product_id" => Loc::getMessage("SO_PRODUCT_ID"),
	"filter_xml_id" => Loc::getMessage("SO_XML_ID"),
);

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	$arFilterFieldsTmp
);

$oFilter->SetDefaultRows(array("filter_id_from", "filter_id_to"));

/***********************************************************************************************************************
 * Filter
 ***********************************************************************************************************************/
$oFilter->Begin();
?>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_ORDER_ID");?>:</td>
		<td>
			<script type="text/javascript">
				function filter_id_from_Change()
				{
					if(document.find_form.filter_order_id_to.value.length<=0)
					{
						document.find_form.filter_order_id_to.value = document.find_form.filter_order_id_from.value;
					}
				}
			</script>
			<?echo Loc::getMessage("SALE_F_FROM");?>
			<input type="text" name="filter_order_id_from" OnChange="filter_id_from_Change()" value="<?echo ((int)($filter_order_id_from)>0)?(int)($filter_order_id_from):""?>" size="10">
			<?echo Loc::getMessage("SALE_F_TO");?>
			<input type="text" name="filter_order_id_to" value="<?echo ((int)($filter_order_id_to)>0)?(int)($filter_order_id_to):""?>" size="10">
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_ACCOUNT_NUMBER");?>:</td>
		<td>
			<input type="text" name="filter_account_number" value="<?echo htmlspecialcharsbx($filter_account_number)?>" size="10">
		</td>
	</tr>
	<tr>
		<td><b><?echo Loc::getMessage("SALE_F_DATE");?>:</b></td>
		<td>
			<?echo CalendarPeriod("filter_date_from", $filter_date_from, "filter_date_to", $filter_date_to, "find_form", "Y")?>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage("SOA_F_PRICE");?>:</td>
		<td>
			<?echo Loc::getMessage("SOA_F_PRICE_FROM");?>
			<input type="text" name="filter_price_from" value="<?=((float)($filter_price_from)>0)?(float)($filter_price_from):""?>" size="3">

			<?echo Loc::getMessage("SOA_F_PRICE_TO");?>
			<input type="text" name="filter_price_to" value="<?=((float)($filter_price_to)>0)?(float)($filter_price_to):""?>" size="3">
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_PERSON_TYPE");?>:</td>
		<td>
			<select name="filter_person_type[]" multiple size="3">
				<option value=""><?echo Loc::getMessage("SALE_F_ALL")?></option>
				<?
					$ptRes = Sale\Internals\PersonTypeTable::getList(array(
						'order' => array("SORT"=>"ASC", "NAME"=>"ASC")
					));

					$personTypes = array();
					while ($personType = $ptRes->fetch())
						$personTypes[$personType['ID']] = $personType;
					foreach ($personTypes as $personType):
						?><option value="<?echo htmlspecialcharsbx($personType["ID"])?>"<?if(is_array($filter_person_type) && in_array($personType["ID"], $filter_person_type)) echo " selected"?>>[<?echo htmlspecialcharsbx($personType["ID"]) ?>] <?echo htmlspecialcharsbx($personType["NAME"])?> <?echo "(".htmlspecialcharsbx($personType["LID"]).")";?></option><?
					endforeach;
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_USER_ID");?>:</td>
		<td>
			<?echo FindUserID("filter_user_id", $filter_user_id, "", "find_form");?>
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_USER_LOGIN");?>:</td>
		<td>
			<input type="text" name="filter_user_login" value="<?echo htmlspecialcharsbx($filter_user_login)?>" size="40">
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_USER_EMAIL");?>:</td>
		<td>
			<input type="text" name="filter_user_email" value="<?echo htmlspecialcharsbx($filter_user_email)?>" size="40">
		</td>
	</tr>
	<tr>
		<td><b><?echo Loc::getMessage("SALE_F_DATE_ARCHIVED");?>:</b></td>
		<td>
			<?echo CalendarPeriod("filter_date_archived_from", $filter_date_archived_from, "filter_date_archived_to", $filter_date_archived_to, "find_form", "Y")?>
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo Loc::getMessage("SALE_F_STATUS")?>:</td>
		<td valign="top">
			<select name="filter_status[]" multiple size="3">
				<?
					$statusesList = \Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations(
						$USER->GetID(),
						array('view')
					);

					$allStatusNames = \Bitrix\Sale\OrderStatus::getAllStatusesNames();

					foreach($statusesList as  $statusCode)
					{
						if (!$statusName = $allStatusNames[$statusCode])
							continue;
						?><option value="<?= htmlspecialcharsbx($statusCode) ?>"<?if(is_array($filter_status) && in_array($statusCode, $filter_status)) echo " selected"?>>[<?= htmlspecialcharsbx($statusCode) ?>] <?= htmlspecialcharsbx($statusName) ?></option><?
					}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_CANCELED")?>:</td>
		<td>
			<select name="filter_canceled">
				<option value=""><?echo Loc::getMessage("SALE_F_ALL")?></option>
				<option value="Y"<?if($filter_canceled=="Y") echo " selected"?>><?echo Loc::getMessage("SALE_YES")?></option>
				<option value="N"<?if($filter_canceled=="N") echo " selected"?>><?echo Loc::getMessage("SALE_NO")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_DEDUCTED")?>:</td>
		<td>
			<select name="filter_deducted">
				<option value=""><?echo Loc::getMessage("SALE_F_ALL")?></option>
				<option value="Y"<?if($filter_deducted=="Y") echo " selected"?>><?echo Loc::getMessage("SALE_YES")?></option>
				<option value="N"<?if($filter_deducted=="N") echo " selected"?>><?echo Loc::getMessage("SALE_NO")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_PAYED")?>:</td>
		<td>
			<select name="filter_payed">
				<option value=""><?echo Loc::getMessage("SALE_F_ALL")?></option>
				<option value="Y"<?if($filter_payed=="Y") echo " selected"?>><?echo Loc::getMessage("SALE_YES")?></option>
				<option value="N"<?if($filter_payed=="N") echo " selected"?>><?echo Loc::getMessage("SALE_NO")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_ID");?>:</td>
		<td>
			<script type="text/javascript">
				function filter_id_from_Change()
				{
					if(document.find_form.filter_id_to.value.length<=0)
					{
						document.find_form.filter_id_to.value = document.find_form.filter_id_from.value;
					}
				}
			</script>
			<?echo Loc::getMessage("SALE_F_FROM");?>
			<input type="text" name="filter_id_from" OnChange="filter_id_from_Change()" value="<?echo ((int)($filter_id_from)>0)?(int)($filter_id_from):""?>" size="10">
			<?echo Loc::getMessage("SALE_F_TO");?>
			<input type="text" name="filter_id_to" value="<?echo ((int)($filter_id_to)>0)?(int)($filter_id_to):""?>" size="10">
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SO_PRODUCT_ID")?></td>
		<td>
			<script type="text/javascript">
				function FillProductFields(arParams)
				{
					if(arParams["id"])
						document.find_form.filter_product_id.value = arParams["id"];

					el = document.getElementById("product_name_alt");
					if(el)
						el.innerHTML = arParams["name"] ? arParams["name"] : '';
				}

				function showProductSearchDialog()
				{
					var popup = makeProductSearchDialog({
						caller: 'order',
						lang: '<?=LANGUAGE_ID?>',
						callback: 'FillProductFields'
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
			</script>
			<input name="filter_product_id" value="<?= htmlspecialcharsbx($filter_product_id) ?>" size="5" type="text">&nbsp;<input type="button" value="..." id="cat_prod_button" onClick="showProductSearchDialog()"><span id="product_name_alt" class="adm-filter-text-search"></span>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('SO_XML_ID')?>:</td>
		<td>
			<input type="text" name="filter_xml_id" value="<?echo htmlspecialcharsbx($filter_xml_id)?>" size="40">
		</td>
	</tr>
	<script>
		function exportData(val)
		{
			var oForm = document.form_<?= $sTableID ?>;
			var expType = oForm.action_target.checked;

			var par = "mode=excel";
			if(!expType)
			{
				var num = oForm.elements.length;
				for (var i = 0; i < num; i++)
				{
					if(oForm.elements[i].tagName.toUpperCase() == "INPUT"
						&& oForm.elements[i].type.toUpperCase() == "CHECKBOX"
						&& oForm.elements[i].name.toUpperCase() == "ID[]"
						&& oForm.elements[i].checked == true)
					{
						par += "&OID[]=" + oForm.elements[i].value;
					}
				}
			}

			if(expType)
			{
				par += "<?= CUtil::JSEscape(GetFilterParams("filter_", false)); ?>";
			}

			if(par.length > 0)
			{
				if (val == "excel")
				{
					url = 'sale_order_archive.php';
					window.open(url + "?EXPORT_FORMAT="+val+"&"+par, "vvvvv");
				}
			}
		}
	</script>
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