<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
$statDB = CDatabase::GetModuleConnection('statistic');
IncludeModuleLangFile(__FILE__);
$err_mess = "File: ".__FILE__."<br>Line: ";
define("HELP_FILE","adv_list.php");
$strError = "";
/***************************************************************************
				Functions
***************************************************************************/
function CheckFields()
{
	global $strError, $str_REFERER1, $str_REFERER2, $err_mess, $ID, $statDB;

	$str = "";
	if (trim($str_REFERER1) == '' && trim($str_REFERER2) == '')
		$str .= GetMessage("STAT_FORGOT_REFERER")."<br>";
	elseif (intval($ID)<=0)
	{
		$strSql = "
			SELECT
				ID
			FROM
				b_stat_adv
			WHERE
				REFERER1".(($str_REFERER1 <> '') ? "='".$statDB->ForSql($str_REFERER1,255)."'" : " is null")."
			and REFERER2".(($str_REFERER2 <> '') ? "='".$statDB->ForSql($str_REFERER2,255)."'" : " is null")."
			";
		$a = $statDB->Query($strSql, false, $err_mess.__LINE__);
		if ($a->Fetch()) $str .= GetMessage("STAT_WRONG_REFERER")."<br>";
	}
	$strError .= $str;
	if ($str <> '') return false; else return true;
}

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("STAT_PROP"), "ICON" => "stat_adv_edit", "TITLE" => GetMessage("STAT_PROP_TITLE")),
	array("DIV" => "edit2", "TAB" => GetMessage("STAT_MORE"), "ICON" => "stat_adv_edit", "TITLE" => GetMessage("STAT_MORE_TITLE")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
/***************************************************************************
			GET | POST Handlers
***************************************************************************/
$ID = intval($ID);

$base_currency = GetStatisticBaseCurrency();
if ($base_currency <> '')
{
	if (CModule::IncludeModule("currency"))
	{
		$currency_module = "Y";
		$arrRefID = array();
		$arrRef = array();
		$rsCur = CCurrency::GetList("sort", "asc");
		$strJavaCurArray = "
			var arrCur = new Array();
			";
		$i = 0;
			$strJavaCurArray .= "
				arrCur[0] = ' ';";
		while ($arCur = $rsCur->Fetch())
		{
			$str = $arCur["CURRENCY"]." (".$arCur["FULL_NAME"].")";
			if ($base_currency==$arCur["CURRENCY"]) $str .= " [".GetMessage("STAT_BASE")."]";
			$arrRef[] = $str;
			$arrRefID[] = $arCur["CURRENCY"];
			$i++;
			$strJavaCurArray .= "
				arrCur[".$i."] = '".$arCur["CURRENCY"]."';";
		}
		$strJavaCurArray .= "\n\n";
		$arrCurrency = array("REFERENCE" => $arrRef, "REFERENCE_ID" => $arrRefID);
	}
}

if (($save <> '' || $apply <> '') && $REQUEST_METHOD=="POST" && $STAT_RIGHT>="W" && check_bitrix_sessid())
{
	if($EVENTS_VIEW=="NOT_REF")
		$EVENTS_VIEW="";
	$statDB->PrepareFields("b_stat_adv");
	$str_REFERER1 = trim($str_REFERER1);
	$str_REFERER2 = trim($str_REFERER2);
	$arFields = array(
		"REFERER1" => ($str_REFERER1 == '')? "null": "'".$str_REFERER1."'",
		"REFERER2" => ($str_REFERER1 == '')? "null": "'".$str_REFERER2."'",
		"EVENTS_VIEW" => "'".$str_EVENTS_VIEW."'",
		"PRIORITY" => intval($str_PRIORITY),
		"DESCRIPTION" => "'".$str_DESCRIPTION."'",
	);
	if (CheckFields())
	{
		$rate_cost = 1;
		$rate_revenue = 1;
		if ($currency_module=="Y")
		{
			if ($CURRENCY_COST!=$base_currency && $CURRENCY_COST <> '')
			{
				$rate_cost = CCurrencyRates::GetConvertFactor($CURRENCY_COST, $base_currency);
			}
			if ($CURRENCY_REVENUE!=$base_currency && $CURRENCY_REVENUE <> '')
			{
				$rate_revenue = CCurrencyRates::GetConvertFactor($CURRENCY_REVENUE, $base_currency);
			}
		}
		$arFields["COST"] = round(doubleval($COST)*$rate_cost,2);
		$arFields["REVENUE"] = round(doubleval($REVENUE)*$rate_revenue,2);

		if($ID > 0)
			$statDB->Update("b_stat_adv",$arFields,"WHERE ID = ".$ID, $err_mess.__LINE__);
		else
			$ID = $statDB->Insert("b_stat_adv", $arFields, $err_mess.__LINE__);

		if ($strError == '')
		{
			$statDB->Query("DELETE FROM b_stat_adv_searcher WHERE ADV_ID = ".$ID, false, $err_mess.__LINE__);
			if (is_array($arSEARCHERS))
			{
				foreach ($arSEARCHERS as $searcher_id)
				{
					$arFields = array(
						"ADV_ID" => "'".intval($ID)."'",
						"SEARCHER_ID" => "'".intval($searcher_id)."'",
					);
					$statDB->Insert("b_stat_adv_searcher",$arFields, $err_mess.__LINE__);
				}
			}

			$statDB->Query("DELETE FROM b_stat_adv_page WHERE ADV_ID = ".$ID, false, $err_mess.__LINE__);

			$arPAGES_TO = preg_split("/[\n\r]+/", $PAGES_TO);
			if (is_array($arPAGES_TO))
			{
				$arPAGES_TO = array_unique($arPAGES_TO);
				TrimArr($arPAGES_TO);
				if (count($arPAGES_TO)>0)
				{
					foreach ($arPAGES_TO as $page_to)
					{
						$arFields = array(
							"ADV_ID" => $ID,
							"PAGE" => "'".$statDB->ForSql($page_to, 2000)."'",
							"C_TYPE" => "'TO'",
							);
						$statDB->Insert("b_stat_adv_page",$arFields, $err_mess.__LINE__);
					}
				}
			}

			$arPAGES_FROM = preg_split("/[\n\r]+/", $PAGES_FROM);
			if (is_array($arPAGES_FROM))
			{
				$arPAGES_FROM = array_unique($arPAGES_FROM);
				TrimArr($arPAGES_FROM);
				if (count($arPAGES_FROM)>0)
				{
					foreach ($arPAGES_FROM as $page_from)
					{
						$arFields = array(
							"ADV_ID" => $ID,
							"PAGE" => "'".$statDB->ForSql($page_from, 2000)."'",
							"C_TYPE" => "'FROM'",
							);
						$statDB->Insert("b_stat_adv_page",$arFields, $err_mess.__LINE__);
					}
				}
			}

			if ($strError == '')
			{
				if ($save <> '') LocalRedirect("adv_list.php?lang=".LANG);
				else LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANG."&ID=".$ID."&".$tabControl->ActiveTabParam());
			}
		}
	}
}

ClearVars();
$arSEARCHERS = array();
$arPAGES_TO = array();
$arPAGES_FROM = array();

$adv = CAdv::GetByID($ID);
if (!($adv->ExtractFields()))
{
	$ID=0;
	$str_PRIORITY = "100";
}
else
{
	$strSql = "SELECT SEARCHER_ID FROM b_stat_adv_searcher WHERE ADV_ID = ".$ID;
	$z = $statDB->Query($strSql, false, $err_mess.__LINE__);
	while ($zr=$z->Fetch())
		$arSEARCHERS[] = $zr["SEARCHER_ID"];

	$strSql = "SELECT PAGE FROM b_stat_adv_page WHERE ADV_ID = ".$ID." and C_TYPE='TO'";
	$z = $statDB->Query($strSql, false, $err_mess.__LINE__);
	while($zr = $z->Fetch())
		$arPAGES_TO[] = htmlspecialcharsbx($zr["PAGE"]);

	$strSql = "SELECT PAGE FROM b_stat_adv_page WHERE ADV_ID = ".$ID." and C_TYPE='FROM'";
	$z = $statDB->Query($strSql, false, $err_mess.__LINE__);
	while($zr = $z->Fetch())
		$arPAGES_FROM[] = htmlspecialcharsbx($zr["PAGE"]);
}

if ($strError <> '')
{
	$statDB->InitTableVarsForEdit("b_stat_adv", "", "str_");

	$ar = preg_split("/[\n\r]+/", $PAGES_TO);
	if (is_array($ar))
	{
		foreach ($ar as $page_to)
			$arPAGES_TO[] = htmlspecialcharsbx($page_to);
	}

	$ar = preg_split("/[\n\r]+/", $PAGES_FROM);
	if (is_array($ar))
	{
		foreach ($ar as $page_to)
			$arPAGES_FROM[] = htmlspecialcharsbx($page_to);
	}
}
else
{
	$CURRENCY_COST = $CURRENCY_REVENUE = $base_currency;
}

$sDocTitle = ($ID>0) ? GetMessage("STAT_EDIT_RECORD", array("#ID#" => $ID)) : GetMessage("STAT_NEW_RECORD");
$APPLICATION->SetTitle($sDocTitle);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/***************************************************************************
				HTML form
****************************************************************************/

$aMenu = array(
	array(
		"ICON"	=> "btn_list",
		"TEXT"	=> GetMessage("STAT_LIST"),
		"TITLE"	=> GetMessage("STAT_RECORDS_LIST"),
		"LINK"	=> "adv_list.php?lang=".LANGUAGE_ID
	)
);

if(intval($ID)>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");

	$aMenu[] = array(
		"ICON"	=> "btn_new",
		"TEXT"	=> GetMessage("STAT_ADD"),
		"TITLE"	=> GetMessage("STAT_NEW_ADV"),
		"LINK"	=> "adv_edit.php?lang=".LANGUAGE_ID
	);

	$aMenu[] = array(
		"TEXT"	=> GetMessage("STAT_RESET"),
		"TITLE"	=> GetMessage("STAT_RESET_ADV"),
		"LINK"	=> "javascript:if(confirm('".GetMessageJS("STAT_RESET_ADV_CONFIRM")."'))window.location='adv_list.php?clear_id=".$ID."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';",
	);

	$aMenu[] = array(
		"ICON"	=> "btn_delete",
		"TEXT"	=> GetMessage("STAT_DELETE"),
		"TITLE"	=> GetMessage("STAT_DELETE_ADV"),
		"LINK"	=> "javascript:if(confirm('".GetMessageJS("STAT_DELETE_ADV_CONFIRM")."'))window.location='adv_list.php?ID=".$ID."&action=delete&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';",
	);
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

if($strError)
{
	$aMsg=array();
	$arrErr = explode("<br>",$strError);
	foreach ($arrErr as $err)
	{
		$aMsg[]['text'] = $err;
	}

	$e = new CAdminException($aMsg);
	$GLOBALS["APPLICATION"]->ThrowException($e);
	$message = new CAdminMessage(GetMessage("STAT_ERROR"), $e);
	echo $message->Show();
}
?>
<a name="tb"></a>
<?if ($base_currency == '') : ?>
<p><?=GetMessage("STAT_BASE_CURRENCY_NOT_INSTALLED").$base_currency?>&nbsp;[&nbsp;<a href="/bitrix/admin/settings.php?lang=<?=LANGUAGE_ID?>&mid=statistic"><?=GetMessage("STAT_CHOOSE_CURRENCY")?></a>&nbsp;]</p>
<?endif;?>
<form method="POST" action="<?=$APPLICATION->GetCurPage()?>?ID=<?=$ID?>&lang=<?=LANGUAGE_ID?>">
<?=bitrix_sessid_post()?>
<input type="hidden" name="ID" value=<?=$ID?>>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?
$tabControl->Begin();
?>
<?
//********************
// 1st Tab
//********************
$tabControl->BeginNextTab();
?>
	<tr>
		<td width=40%><?=GetMessage("STAT_PRIORITY")?></td>
		<td width=60%><input type="text" name="PRIORITY" size="5" value="<?=$str_PRIORITY?>">&nbsp;<?=GetMessage("STAT_PRIORITY_ALT")?></td>
	</tr>
	<tr class=heading>
		<td colspan="2"><?=GetMessage("STAT_IDENTIFIERS")?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td>referer1:</td>
		<td><input type="text" name="REFERER1" size="40" maxlength="255" value="<?=$str_REFERER1?>"></td>
	</tr>
	<tr>
		<td>referer2:</td>
		<td><input type="text" name="REFERER2" size="40" maxlength="255" value="<?=$str_REFERER2?>"></td>
	</tr>
	<tr class=heading>
		<td colspan="2"><?echo GetMessage("STAT_ID_ADD");?><span class=required><sup>1</sup></span></td>
	</tr>
	<tr>
		<td valign=top><?echo GetMessage("STAT_PAGES_FROM")?></td>
		<td><textarea name="PAGES_FROM" cols="50" rows="8"><?echo implode("\r\n", $arPAGES_FROM)?></textarea></td>
	</tr>
	<tr valign="top">
		<td valign="top"><?=GetMessage("STAT_SEARCHERS")?><br><IMG SRC="/bitrix/images/statistic/mouse.gif" WIDTH="44" HEIGHT="21" BORDER=0 ALT=""><br><?=GetMessage("STAT_SELECT_WHAT_YOU_NEED")?></td>
		<td><?
			$ref = $ref_id = array();
			$strSql = "SELECT ID, NAME FROM b_stat_searcher WHERE ID>1 ORDER BY NAME";
			$rs = $statDB->Query($strSql, false, $err_mess.__LINE__);
			while($ar = $rs->Fetch())
			{
				$ref[] = $ar["NAME"]." [".$ar["ID"]."]";
				$ref_id[] = $ar["ID"];
			}
			echo SelectBoxMFromArray("arSEARCHERS[]",array("REFERENCE"=>$ref, "REFERENCE_ID"=>$ref_id), $arSEARCHERS,"",false,"10", "class=typeselect");
			?></td>
	</tr>
	<tr>
		<td valign=top><?echo GetMessage("STAT_PAGES_TO")?></td>
		<td><textarea name="PAGES_TO" cols="50" rows="8"><?echo implode("\r\n", $arPAGES_TO);?></textarea></td>
</tr>
<?
//********************
// 2nd Tab
//********************
$tabControl->BeginNextTab();
?>
<?if ($STAT_RIGHT>"M"):?>
<tr class=heading>
	<td colspan="2"><?=GetMessage("STAT_FINANCES")?></td>
</tr>
<tr>
	<td width=40%><?=GetMessage("STAT_COST")?></td>
	<td width=60%><input type="text" name="COST" size="10" value="<?echo ($str_COST!=0) ? $str_COST : ""?>"><?if ($currency_module=="Y") :?>&nbsp;<?echo SelectBoxFromArray("CURRENCY_COST", $arrCurrency, $CURRENCY_COST);?><?endif;?></td>
</tr>
<tr>
	<td><?=GetMessage("STAT_REVENUE")?></td>
	<td><input type="text" name="REVENUE" size="10" value="<?echo ($str_REVENUE!=0) ? $str_REVENUE : ""?>"><?if ($currency_module=="Y") :?>&nbsp;<?echo SelectBoxFromArray("CURRENCY_REVENUE", $arrCurrency, $CURRENCY_REVENUE);?><?endif;?></td>
</tr>
<?endif;?>
<tr class=heading>
	<td colspan="2"><?=GetMessage("STAT_ADDITIONAL")?></td>
</tr>
<tr>
	<td><?=GetMessage("STAT_EVENTS_VIEW")?></td>
	<td><?
		$arr = array("reference"=>array(GetMessage("STAT_SHOW_LINK"), GetMessage("STAT_SHOW_LIST"), GetMessage("STAT_GROUP_BY_EVENT1"), GetMessage("STAT_GROUP_BY_EVENT2")), "reference_id"=>array("link","list", "event1","event2"));
		echo SelectBoxFromArray("EVENTS_VIEW", $arr,$str_EVENTS_VIEW,GetMessage("STAT_DEFAULT"));
		?></td>
</tr>
<tr>
	<td valign=top><?=GetMessage("STAT_DESCRIPTION")?></td>
	<td><textarea name="DESCRIPTION" rows="8" cols="50"><?echo $str_DESCRIPTION?></textarea></td>
</tr>
<?
$tabControl->EndTab();
$tabControl->Buttons(array("disabled"=>($STAT_RIGHT<"W"), "back_url"=>"adv_list.php?lang=".LANGUAGE_ID));
$tabControl->End();
?>
</form>
<?echo BeginNote();?>
<span class="required"><sup>1</sup></span> - <?echo GetMessage("STAT_ID_ADD_NOTE")?>
<?echo EndNote();

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
