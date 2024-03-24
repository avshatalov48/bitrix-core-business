<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
$err_mess = "File: ".__FILE__."<br>Line: ";
define("HELP_FILE","searcher_list.php");
$strError = "";
$statDB = CDatabase::GetModuleConnection('statistic');
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("STAT_PROP"), "ICON" => "stat_edit", "TITLE" => GetMessage("STAT_PROP_TITLE")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
/***************************************************************************
			Functions
***************************************************************************/
function CheckFields()
{
	global $strError, $str_NAME;
	$str = "";
	if (trim($str_NAME) == '') $str .= GetMessage("STAT_FORGOT_NAME")."<br>";
	$strError .= $str;
	if ($str <> '') return false; else return true;
}

function Set_Params()
{
	global $ID, $PARAM, $err_mess, $statDB;

	foreach ($PARAM as $pid)
	{
		if (intval($pid)<=0) continue;
		$var_PARAM_ID = "PARAM_ID_".$pid;
		$var_DOMAIN = "DOMAIN_".$pid;
		$var_VARIABLE = "VARIABLE_".$pid;
		$var_CHAR_SET = "CHAR_SET_".$pid;
		global $$var_PARAM_ID, $$var_DOMAIN, $$var_VARIABLE, $$var_CHAR_SET;
		$arFields = array(
			"DOMAIN"	=> "'".$statDB->ForSql($$var_DOMAIN)."'",
			"VARIABLE"	=> "'".$statDB->ForSql($$var_VARIABLE)."'",
			"CHAR_SET"	=> "'".$statDB->ForSql($$var_CHAR_SET)."'",
			);
		$strSql = "SELECT 'x' FROM b_stat_searcher_params WHERE ID='".intval($$var_PARAM_ID)."'";
		$b = $statDB->Query($strSql, false, $err_mess.__LINE__);
		if ($br=$b->Fetch())
		{
			if (trim($$var_DOMAIN) == '')
				$statDB->Query("DELETE FROM b_stat_searcher_params WHERE ID='".intval($$var_PARAM_ID)."'", false, $err_mess.__LINE__);
			else
				$statDB->Update("b_stat_searcher_params",$arFields,"WHERE ID='".intval($$var_PARAM_ID)."'",$err_mess.__LINE__);
		}
		elseif (trim($$var_DOMAIN) <> '')
		{
				$arFields["SEARCHER_ID"] = intval($ID);
				$statDB->Insert("b_stat_searcher_params",$arFields, $err_mess.__LINE__);
		}
	}
}

$ID = intval($ID);
InitBVar($ACTIVE);
InitBVar($SAVE_STATISTIC);
InitBVar($DIAGRAM_DEFAULT);
InitBVar($CHECK_ACTIVITY);

if (($save <> '' || $apply <> '') && $REQUEST_METHOD=="POST" && $STAT_RIGHT>="W" && check_bitrix_sessid())
{
	$strSql = "SELECT HIT_KEEP_DAYS FROM b_stat_searcher WHERE ID = $ID";
	$rsSearcher = $statDB->Query($strSql, false, $err_mess.__LINE__);
	$arSearcher = $rsSearcher->Fetch();

	$statDB->PrepareFields("b_stat_searcher");
	$sql_HIT_KEEP_DAYS = ($HIT_KEEP_DAYS == '') ? "null" : intval($HIT_KEEP_DAYS);
	$arFields = array(
		"ACTIVE"			=> "'".$str_ACTIVE."'",
		"SAVE_STATISTIC"	=> "'".$str_SAVE_STATISTIC."'",
		"DIAGRAM_DEFAULT"	=> "'".$str_DIAGRAM_DEFAULT."'",
		"NAME"				=> "'".$str_NAME."'",
		"USER_AGENT"		=> ($USER_AGENT == '') ? "null" : "'".$str_USER_AGENT."'",
		"HIT_KEEP_DAYS"		=> $sql_HIT_KEEP_DAYS,
		"DYNAMIC_KEEP_DAYS"	=> ($DYNAMIC_KEEP_DAYS == '') ? "null" : intval($DYNAMIC_KEEP_DAYS),
		"CHECK_ACTIVITY"	=> "'".$str_CHECK_ACTIVITY."'"
		);
	if (CheckFields())
	{
		$statDB->StartTransaction();
		if ($ID>0)
		{
			$statDB->Update("b_stat_searcher",$arFields,"WHERE ID='".$ID."'",$err_mess.__LINE__);
			Set_Params();
			if (intval($HIT_KEEP_DAYS)!=$arSearcher["HIT_KEEP_DAYS"])
			{
				$arFields = array("HIT_KEEP_DAYS" => $sql_HIT_KEEP_DAYS);
				$statDB->Update("b_stat_searcher_hit",$arFields,"WHERE SEARCHER_ID=$ID",$err_mess.__LINE__);
			}
		}
		else
		{
			$ID = $statDB->Insert("b_stat_searcher",$arFields, $err_mess.__LINE__);
			if (intval($ID)>0) Set_Params();
			$new = "Y";
		}
		if ($strError == '')
		{
			$statDB->Commit();
			if ($save <> '') LocalRedirect("searcher_list.php?lang=".LANG);
			elseif ($new=="Y") LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANG."&ID=".$ID);
		}
		else
		{
			$statDB->Rollback();
		}
	}
}

ClearVars();
$searcher = CSearcher::GetByID($ID);
if (!($searcher->ExtractFields()))
{
	$ID=0;
	$str_ACTIVE="Y";
	$str_SAVE_STATISTIC="Y";
	$str_USER_AGENT = htmlspecialcharsbx($ua);
	$str_NAME = htmlspecialcharsbx($nm);
	$str_CHECK_ACTIVITY="Y";
}
if ($strError <> '') $statDB->InitTableVarsForEdit("b_stat_searcher", "", "str_");

if ($ID>0) $sDocTitle = GetMessage("STAT_EDIT_RECORD", array("#ID#" => $ID));
else $sDocTitle = GetMessage("STAT_NEW_RECORD");

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
		"LINK"	=> "searcher_list.php?lang=".LANGUAGE_ID
	)
);

if(intval($ID)>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");

	$aMenu[] = array(
		"ICON"	=> "btn_new",
		"TEXT"	=> GetMessage("STAT_ADD"),
		"TITLE"	=> GetMessage("STAT_NEW_SEARCHER"),
		"LINK"	=> "searcher_edit.php?lang=".LANGUAGE_ID
	);

	$aMenu[] = array(
		"ICON"	=> "btn_delete",
		"TEXT"	=> GetMessage("STAT_DELETE"),
		"TITLE"	=> GetMessage("STAT_DELETE_SEARCHER"),
		"LINK"	=> "javascript:if(confirm('".GetMessageJS("STAT_DELETE_SEARCHER_CONFIRM")."'))window.location='searcher_list.php?action=delete&ID=".$ID."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';",
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
	$message = new CAdminMessage(GetMessage("STAT_FORM_ERROR_SAVE"), $e);
	echo $message->Show();
}
?>
<a name="tb"></a>
<form method="POST" action="<?=$APPLICATION->GetCurPage()?>">
<?=bitrix_sessid_post()?>
<input type="hidden" name="ID" value=<?echo $ID?>>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?
$tabControl->Begin();
?>
<?
//********************
//General Tab
//********************
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="40%"><?echo GetMessage("STAT_ACTIVE")?></td>
		<td width="60%"><?echo InputType("checkbox","ACTIVE","Y",$str_ACTIVE,false) ?></td>
	</tr>
	<tr>
		<td><? echo GetMessage("STAT_CHECK_ACTIVITY")?></td>
		<td><?echo InputType("checkbox","CHECK_ACTIVITY","Y",$str_CHECK_ACTIVITY,false) ?></td>
	</tr>
	<tr>
		<td><? echo GetMessage("STAT_STATISTICS")?></td>
		<td><?echo InputType("checkbox","SAVE_STATISTIC","Y",$str_SAVE_STATISTIC,false) ?></td>
	</tr>
	<tr>
		<td><? echo GetMessage("STAT_PIE_CHART")?></td>
		<td><?echo InputType("checkbox","DIAGRAM_DEFAULT","Y",$str_DIAGRAM_DEFAULT,false) ?></td>
	</tr>
	<tr>
		<td><? echo GetMessage("STAT_HIT_KEEP_DAYS")?></td>
		<td><input type="text" name="HIT_KEEP_DAYS" size="5" value="<?echo $str_HIT_KEEP_DAYS?>"></td>
	</tr>
	<tr>
		<td><? echo GetMessage("STAT_DYNAMIC_KEEP_DAYS")?></td>
		<td><input type="text" name="DYNAMIC_KEEP_DAYS" size="5" value="<?echo $str_DYNAMIC_KEEP_DAYS?>"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><? echo GetMessage("STAT_NAME")?></td>
		<td><input type="text" name="NAME" size="40" maxlength="50" value="<?echo $str_NAME?>"></td>
	</tr>
	<tr>
		<td><? echo GetMessage("STAT_USER_AGENT")?></td>
		<td><input type="text" name="USER_AGENT" size="40" maxlength="500" value="<?echo $str_USER_AGENT?>"></td>
	</tr>
	<tr>
		<td><?=GetMessage("STAT_SEARCHER_DOMAINS")?></td>
		<td>
			<table border="0" cellspacing="1" cellpadding="0">
				<tr>
					<td><? echo GetMessage("STAT_DOMAIN")?></td>
					<td><? echo GetMessage("STAT_VARIABLE")?></td>
					<td><? echo GetMessage("STAT_CHAR_SET")?></td>
				</tr>
				<?
				$rs = CSearcher::GetDomainList("s_id", "asc", array("SEARCHER_ID" => $ID));
				$i = 1;
				while($arDomain = $rs->GetNext()):
				?>
				<tr>
					<td><input type="hidden" name="PARAM[]" value="<?echo $i?>"><input type="hidden" name="PARAM_ID_<?echo $i?>" value="<?echo $arDomain["ID"]?>"><input type="text" name="DOMAIN_<?echo $i?>" value="<?echo $arDomain["DOMAIN"]?>" size="30"></td>
					<td><input type="text" name="VARIABLE_<?echo $i?>" value="<?echo $arDomain["VARIABLE"]?>" size="10"></td>
					<td><input type="text" name="CHAR_SET_<?echo $i?>" value="<?echo $arDomain["CHAR_SET"]?>" size="20"></td>
				</tr>
				<?
				$i++;
				endwhile;
				$count = $i+5;
				while ($i<=$count) :
				?>
				<tr>
					<td><input type="hidden" name="PARAM[]" value="<?echo $i?>"><input type="hidden" name="PARAM_ID_<?echo $i?>" value="0"><input type="text" name="DOMAIN_<?echo $i?>" size="30" value="<?echo htmlspecialcharsbx(${"DOMAIN_".$i})?>"></td>
					<td><input type="text" name="VARIABLE_<?echo $i?>" size="10" value="<?echo htmlspecialcharsbx(${"VARIABLE_".$i})?>"></td>
					<td><input type="text" name="CHAR_SET_<?echo $i?>" size="20" value="<?echo htmlspecialcharsbx(${"CHAR_SET_".$i})?>"></td>
				</tr>
				<?
				$i++;
				endwhile;
				?>
			</table>
		</td>
	</tr>
<?
$tabControl->EndTab();
$tabControl->Buttons(array("disabled"=>($STAT_RIGHT<"W"), "back_url"=>"searcher_list.php?lang=".LANGUAGE_ID));
$tabControl->End();
?>
</form>
<? require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
