<?php

use Bitrix\Catalog\Access\ActionDictionary;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");
CModule::IncludeModule('catalog');
global $APPLICATION, $USER, $DB;

if (!\Bitrix\Catalog\Access\AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

IncludeModuleLangFile(__FILE__);

$boolMultiSelect = (isset($_REQUEST['m']) && 'y' == $_REQUEST['m']);
$n = preg_replace("/[^a-zA-Z0-9_:\\[\\]]/", "", $_GET["n"]);
$k = preg_replace("/[^a-zA-Z0-9_:]/", "", $_GET["k"]);
$lookup = preg_replace("/[^a-zA-Z0-9_:]/", "", $_GET["lookup"]);
$boolDiscount = (array_key_exists('discount', $_REQUEST) && 'Y' == $_REQUEST['discount']);

$sTableID = ($boolDiscount ? "tbl_cat_iblock_search".md5('discount') : "tbl_cat_iblock_search".md5($n));
$lAdmin = new CAdminList($sTableID);

$lAdmin->InitFilter(array("filter_iblock_type_id"));
$IBLOCK_TYPE_ID = '';
if (isset($filter_iblock_type_id) && !is_array($filter_iblock_type_id))
{
	$filter_iblock_type_id = strval($filter_iblock_type_id);
	if ('' != $filter_iblock_type_id)
	{
		$IBLOCK_TYPE_ID = $filter_iblock_type_id;
	}
}
if ('' == $IBLOCK_TYPE_ID && isset($_REQUEST['IBLOCK_TYPE_ID']) && !is_array($_REQUEST['IBLOCK_TYPE_ID']))
{
	$strTempo = strval($_REQUEST['IBLOCK_TYPE_ID']);
	if ('' != $strTempo)
	{
		$IBLOCK_TYPE_ID = $strTempo;
	}
	unset($strTempo);
}

if ('' !== $IBLOCK_TYPE_ID)
{
	$arIBlockType = CIBlockType::GetByIDLang($IBLOCK_TYPE_ID, LANGUAGE_ID);
	if(!$arIBlockType)
	{
		$IBLOCK_TYPE_ID = '';
		$APPLICATION->AuthForm(GetMessage("BX_MOD_CATALOG_ADMIN_CIS_BAD_IBLOCK_TYPE_ID"));
	}
}

$APPLICATION->SetTitle(GetMessage("BX_MOD_CATALOG_ADMIN_CIS_TITLE"));

$arFilterFields = array(
	'filter_iblock_type_id'
);

$oSort = new CAdminSorting($sTableID, "NAME", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());

$lAdmin->InitFilter($arFilterFields);

$arFilter = array(
	"IBLOCK_TYPE_ID" => $IBLOCK_TYPE_ID,
	"CHECK_PERMISSIONS" => "Y",
	"MIN_PERMISSION" => "R",
);

$arHeader = array();
$arHeader[] = array("id" => "ID", "content" => GetMessage("BX_MOD_CATALOG_ADMIN_CIS_HEAD_ID"), "sort" => "ID", "align" => "right", "default" => true);
$arHeader[] = array("id" => "IBLOCK_TYPE_ID", "content" => GetMessage("BX_MOD_CATALOG_ADMIN_CIS_HEAD_IBLOCK_TYPE_ID"), "sort" => "IBLOCK_TYPE_ID", "default" => true);
$arHeader[] = array("id" => "NAME", "content" => GetMessage("BX_MOD_CATALOG_ADMIN_CIS_HEAD_NAME"), "sort" => "NAME", "default" => true);
$arHeader[] = array("id" => "ACTIVE", "content" => GetMessage("BX_MOD_CATALOG_ADMIN_CIS_HEAD_ACTIVE"), "sort" => "ACTIVE");
$arHeader[] = array("id" => "XML_ID", "content" => GetMessage("BX_MOD_CATALOG_ADMIN_CIS_HEAD_XML_ID"), "sort" => "XML_ID");
$arHeader[] = array("id" => "CODE", "content" => GetMessage("BX_MOD_CATALOG_ADMIN_CIS_HEAD_CODE"), "sort" => "CODE");

$lAdmin->AddHeaders($arHeader);

$rsIBlocks = CIBlock::GetList(array($by=>$order), $arFilter);
$rsIBlocks = new CAdminResult($rsIBlocks, $sTableID);
$rsIBlocks->NavStart();

$lAdmin->NavText($rsIBlocks->GetNavPrint(GetMessage("BX_MOD_CATALOG_ADMIN_CIS_NAV")));

while ($arRes = $rsIBlocks->GetNext())
{
	$row = &$lAdmin->AddRow($arRes["ID"], $arRes);

	$row->AddViewField(
		'NAME',
		$arRes['NAME'].'<input type="hidden" name="n'.$arRes['ID'].'" id="name_'.$arRes['ID'].'" value="'.htmlspecialcharsbx($arRes['NAME']).'">'
	);
	$row->AddViewField("IBLOCK_TYPE_ID", $arRes["IBLOCK_TYPE_ID"]);
	$row->AddCheckField("ACTIVE", false);
	$row->AddViewField("XML_ID", $arRes["XML_ID"]);
	$row->AddViewField("CODE", $arRes["CODE"]);

	$row->AddActions(array(
		array(
			"DEFAULT" => "Y",
			"TEXT" => GetMessage("BX_MOD_CATALOG_ADMIN_CIS_SELECT"),
			"ACTION"=>"javascript:SelEl('".CUtil::JSEscape($arRes["ID"])."', '".CUtil::JSEscape($arRes["NAME"])."')",
		),
	));
}

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("BX_MOD_CATALOG_ADMIN_CIS_MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsIBlocks->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("BX_MOD_CATALOG_ADMIN_CIS_MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

if ($boolMultiSelect)
{
	$lAdmin->AddGroupActionTable(array(
		array(
			"action" => "SelAll()",
			"value" => "select",
			"type" => "button",
			"name" => GetMessage("BX_MOD_CATALOG_ADMIN_CIS_SELECT"),
			)
	), array("disable_action_target"=>true));
}

$lAdmin->AddAdminContextMenu(array(), false);

$lAdmin->CheckListMode();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");

?><script type="text/javascript">
function SelEl(id, name)
{
<?
if ('' != $lookup)
{
	if ('' != $boolMultiSelect)
	{
		?>window.opener.<? echo $lookup; ?>.AddValue(id);<?
	}
	else
	{
		?>window.opener.<? echo $lookup; ?>.AddValue(id); window.close();<?
	}
}
else
{
	if($boolMultiSelect)
	{
		?>window.opener.InS<? echo md5($n); ?>(id, name);<?
	}
	else
	{
		?>el = window.opener.document.getElementById('<? echo $n?>[<? echo $k?>]');
	if(!el)
		el = window.opener.document.getElementById('<? echo $n?>');
	if(el)
	{
		el.value = id;
	}
	el = window.opener.document.getElementById('<? echo md5($n)?>_<? echo $k?>_link');
	if(!el)
		el = window.opener.document.getElementById('<?echo $n?>_link');
	if(el)
		el.innerHTML = name;
	window.close();<?
	}
}
?>
}

function SelAll()
{
	var frm = document.getElementById('form_<? echo $sTableID?>');
	if (frm)
	{
		var e = frm.elements['ID[]'];
		if (e && e.nodeName)
		{
			var v = e.value;
			var n = document.getElementById('name_'+v).value;
			SelEl(v, n);
		}
		else if (e)
		{
			var l = e.length;
			for(i=0;i<l;i++)
			{
				var a = e[i].checked;
				if (a == true)
				{
					var v = e[i].value;
					var n = document.getElementById('name_'+v).value;
					SelEl(v, n);
				}
			}
		}
		window.close();
	}
}
</script><?

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");