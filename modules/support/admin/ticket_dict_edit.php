<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# https://www.bitrixsoft.com          #
# mailto:admin@bitrix.ru                     #
##############################################
*/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");

ClearVars();

$bDemo = (CTicket::IsDemo()) ? "Y" : "N";
$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";

if($bAdmin!="Y" && $bDemo!="Y") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");
IncludeModuleLangFile(__FILE__);
$err_mess = "File: ".__FILE__."<br>Line: ";
define("HELP_FILE","ticket_dict_list.php");

$message = null;

/***************************************************************************
									Функции
***************************************************************************/

function CheckFields() // проверка на наличие обязательных полей
{
	global $ID, $NAME, $SID, $C_TYPE, $arrSITE, $DB;
	$str = "";

	$arMsg = Array();

	if (trim($NAME) == '')
		//$str .= GetMessage("SUP_FORGOT_NAME")."<br>";
		$arMsg[] = array("id"=>"NAME", "text"=> GetMessage("SUP_FORGOT_NAME"));

	if (preg_match("/[^A-Za-z_0-9]/",$SID))
		//$str .= GetMessage("SUP_INCORRECT_SID")."<br>";
		$arMsg[] = array("id"=>"SID", "text"=> GetMessage("SUP_INCORRECT_SID"));

	elseif ($SID <> '' && is_array($arrSITE) && count($arrSITE)>0)
	{
		$arFilter = array(
			"ID"	=> "~".$ID,
			"TYPE"	=> $C_TYPE,
			"SID"	=> $SID,
			"SITE"	=> $arrSITE
			);

		$z = CTicketDictionary::GetList();
		if ($zr = $z->Fetch())
		{
			$s = str_replace("#TYPE#", CTicketDictionary::GetTypeNameByID($str_C_TYPE), GetMessage("SUP_SID_ALREADY_IN_USE"));
			$s = str_replace("#LANG#", $zr['LID'] <> ''? $zr['LID'] : mb_strtolower($zr['SITE_ID']), $s);
			$s = str_replace("#RECORD_ID#",$zr["ID"],$s);
			//$str .= $s."<br>";
			$arMsg[] = array("id"=>"SID", "text"=> $s);
		}
	}

	if(!empty($arMsg))
	{
		$e = new CAdminException($arMsg);
		$GLOBALS["APPLICATION"]->ThrowException($e);
		return false;
	}

	return true;
}

function symbolsAndNumbers($str)
{
	return preg_replace ("/[^a-z0-9A-Z]/","",$str);
}

/***************************************************************************
							Обработка GET | POST
***************************************************************************/
$ID = intval($_REQUEST['ID']);
$SET_AS_DEFAULT = $_REQUEST['SET_AS_DEFAULT'];
InitBVar($SET_AS_DEFAULT);



// если была нажата кнопка "save" на текущей странице
if (($save <> '' || $apply <> '') && $_SERVER['REQUEST_METHOD']=="POST" && $bAdmin=="Y" && check_bitrix_sessid())
{
	$arFields = array(
		'C_TYPE'				=> symbolsAndNumbers($_REQUEST['C_TYPE']),
		'SID'					=> $_REQUEST['SID'],
		'SET_AS_DEFAULT'		=> $SET_AS_DEFAULT,
		'C_SORT'				=> $_REQUEST['C_SORT'],
		'NAME'					=> $_REQUEST['NAME'],
		'DESCR'					=> $_REQUEST['DESCR'],
		'RESPONSIBLE_USER_ID'	=> $_REQUEST['RESPONSIBLE_USER_ID'],
		'arrSITE'				=> $_REQUEST['arrSITE'],
	);
	if (is_array($_REQUEST['arrSITE']) && count($_REQUEST['arrSITE']) > 0)
	{
		$arFields['FIRST_SITE_ID'] = reset($_REQUEST['arrSITE']);
		$_SESSION['SESS_TICKET_DIC_SITE'] = $_REQUEST['arrSITE'];
	}

	if (CModule::IncludeModule('statistic') && $_REQUEST['C_TYPE']=='C')
	{
		$arFields['EVENT1'] = $_REQUEST['EVENT1'];
		$arFields['EVENT2'] = $_REQUEST['EVENT2'];
		$arFields['EVENT3'] = $_REQUEST['EVENT3'];
	}

	$bOK = false;
	$new = false;
	if ($ID > 0)
	{
		$bOK = CTicketDictionary::Update($ID, $arFields);
	}
	else
	{
		if ($ID = CTicketDictionary::Add($arFields))
		{
			$new = true;
			$bOK = true;
		}
	}

	if ($bOK)
	{
		if ($save <> '') LocalRedirect("/bitrix/admin/ticket_dict_list.php?lang=".LANGUAGE_ID. "&find_type=".symbolsAndNumbers($_REQUEST['C_TYPE']));
		elseif ($new) LocalRedirect("/bitrix/admin/ticket_dict_edit.php?ID=".$ID. "&lang=".LANGUAGE_ID."&find_type=".symbolsAndNumbers($_REQUEST['C_TYPE'])."&tabControl_active_tab=".urlencode($tabControl_active_tab));
	}
	else
	{
		if ($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("SUP_ERROR"), $e);
	}

	/*
	if (CheckFields())
	{
		$DB->PrepareFields("b_ticket_dictionary");
		if (is_array($arrSite)>0)	$_SESSION["SESS_TICKET_DIC_SITE"] = $arrSite;
		if ($SET_AS_DEFAULT=="Y")
		{
			$arFilter = array(
				"TYPE"	=> $C_TYPE,
				"SITE"	=> $arrSITE
				);
			$z = CTicketDictionary::GetList($v1, $v2, $arFilter, $v3);
			while ($zr = $z->Fetch())
			{
				$arFields = array("SET_AS_DEFAULT" => "'N'");
				$DB->Update("b_ticket_dictionary",$arFields,"WHERE ID='".$zr["ID"]."'",$err_mess.__LINE__);
			}
		}
		if (is_array($arrSITE))
		{
			reset($arrSITE);
			list($k, $FIRST_SITE_ID) = each($arrSITE);
		}
		$arFields = array(
			"FIRST_SITE_ID"			=> "'".$DB->ForSql($FIRST_SITE_ID,2)."'",
			"C_TYPE"				=> "'".$str_C_TYPE."'",
			"SID"					=> "'".$str_SID."'",
			"SET_AS_DEFAULT"		=> "'".$str_SET_AS_DEFAULT."'",
			"C_SORT"				=> "'".$str_C_SORT."'",
			"NAME"					=> "'".$str_NAME."'",
			"DESCR"					=> "'".$str_DESCR."'",
			"RESPONSIBLE_USER_ID"	=> "'".$str_RESPONSIBLE_USER_ID."'"
			);
		if (CModule::IncludeModule("statistic") && $str_C_TYPE=="C")
		{
			$arFields["EVENT1"] = "'".$str_EVENT1."'";
			$arFields["EVENT2"] = "'".$str_EVENT2."'";
			$arFields["EVENT3"] = "'".$str_EVENT3."'";
		}
		if ($ID>0) $DB->Update("b_ticket_dictionary",$arFields,"WHERE ID='".$ID."'",$err_mess.__LINE__);
		else
		{
			$ID = $DB->Insert("b_ticket_dictionary",$arFields, $err_mess.__LINE__);
			$new = "Y";
		}
		if (!$message)
		{
			// сайты
			$DB->Query("DELETE FROM b_ticket_dictionary_2_site WHERE DICTIONARY_ID='".$ID."'", false, $err_mess.__LINE__);
			if (is_array($arrSITE))
			{
				reset($arrSITE);
				foreach($arrSITE as $sid)
				{
					$strSql = "INSERT INTO b_ticket_dictionary_2_site (DICTIONARY_ID, SITE_ID) VALUES ($ID, '".$DB->ForSql($sid,2)."')";
					$DB->Query($strSql, false, $err_mess.__LINE__);
				}
			}
			if (strlen($save)>0) LocalRedirect("/bitrix/admin/ticket_dict_list.php?lang=".LANGUAGE_ID. "&find_type=".$str_C_TYPE."&set_filter=Y");
			elseif ($new=="Y") LocalRedirect("/bitrix/admin/ticket_dict_edit.php?ID=".$ID. "&lang=".LANGUAGE_ID."&find_type=".$str_C_TYPE."&set_filter=Y&tabControl_active_tab=".urlencode($tabControl_active_tab));
		}
	}
	else
	{
		if ($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("SUP_ERROR"), $e);
	}
	//*/
}

$arrSites = array();
$rs = CSite::GetList();
while ($ar = $rs->Fetch())
	$arrSites[$ar["ID"]] = $ar;

$tdic = CTicketDictionary::GetByID($ID);
if (!($tdic && $tdic->ExtractFields()))
{
	$ID=0;
	$str_C_SORT="100";
	$arrSite = $_SESSION["SESS_TICKET_DIC_SITE"];
	$str_C_TYPE = symbolsAndNumbers($find_type);
	if ($str_C_TYPE <> '') $str_C_SORT = CTicketDictionary::GetNextSort($TYPE_ID);
	//$str_EVENT1 = "ticket";
	$str_EVENT1 = "";
}
else
{
	$arrSITE =  CTicketDictionary::GetSiteArray($ID);
	if ($str_C_TYPE!="C")
	{
		$str_EVENT1 = "";
		$str_EVENT2 = "";
		$str_EVENT3 = "";
	}
}
if ($message) $DB->InitTableVarsForEdit("b_ticket_dictionary", "", "str_");

if ($ID>0) $sDocTitle = GetMessage("SUP_EDIT_RECORD", array("#ID#" => $ID));
else $sDocTitle = GetMessage("SUP_NEW_RECORD");

$APPLICATION->SetTitle($sDocTitle);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");


/***************************************************************************
									HTML форма
****************************************************************************/
?>
<script>
<!--
function C_TYPE_Change()
{
	var cur = document.forms['form1'].elements['C_TYPE'][document.forms['form1'].elements['C_TYPE'].selectedIndex].value;
	var tr_responsible = document.getElementById('tr_responsible');
	var tr_default = document.getElementById('tr_default');
	var events = document.getElementById('events');
	var display_status, disable_status;

	display_status = (cur=="C" || cur=="K" || cur=="SR") ? "block" : "none";
	disable_status = (display_status=="block") ? false : true;

	document.forms['form1'].elements['RESPONSIBLE_USER_ID'].disabled = document.forms['form1'].elements['SET_AS_DEFAULT'].disabled = disable_status;
	tr_responsible.style.display = tr_default.style.display = display_status;

	<? if (CModule::IncludeModule("statistic")) : ?>

	display_status = (cur=="C") ? "block" : "none";

	disable_status = (display_status=="block") ? false : true;

	if (disable_status)
		tabControl.DisableTab('edit2');
	else
		tabControl.EnableTab('edit2');

	events.style.display = display_status;

	document.forms['form1'].elements['EVENT1'].disabled = document.forms['form1'].elements['EVENT2'].disabled = document.forms['form1'].elements['EVENT3'].disabled = disable_status;
	<? endif; ?>
}
//-->
</SCRIPT>
<?
$aMenu = array(
	array(
		"ICON"	=> "btn_list",
		"TEXT"	=> GetMessage("SUP_RECORDS_LIST"),
		"LINK"	=> "/bitrix/admin/ticket_dict_list.php?lang=".LANGUAGE_ID."&find_type=".symbolsAndNumbers($find_type)
	)
);

if(intval($ID)>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");

	$aMenu[] = array(
		"ICON"	=> "btn_new",
		"TEXT"	=> GetMessage("SUP_CREATE_NEW_RECORD"),
		"LINK"	=> "/bitrix/admin/ticket_dict_edit.php?lang=".LANGUAGE_ID."&find_type=".symbolsAndNumbers($find_type)
		);

	if ($bAdmin=="Y")
	{
		$aMenu[] = array(
			"ICON"	=> "btn_delete",
			"TEXT"	=> GetMessage("SUP_DELETE_RECORD"),
			"LINK"	=> "javascript:if(confirm('".GetMessage("SUP_DELETE_RECORD_CONFIRM")."')) window.location='/bitrix/admin/ticket_dict_list.php?action=delete&ID=".$ID."&lang=".LANGUAGE_ID."&find_type=".symbolsAndNumbers($find_type) ."&". bitrix_sessid_get()."';",
			);
	}
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($message)
	echo $message->Show();
?>

<form name="form1" method="POST" action="<?=$APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>&ID=<?=$ID?>">
<?=bitrix_sessid_post()?>
<input type="hidden" name="find_type" value="<?=htmlspecialcharsbx($find_type)?>">
<input type="hidden" name="ID" value=<?=intval($ID)?>>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?
$bTab2 = CModule::IncludeModule("statistic");

$aTabs = array();
$aTabs[] = array("DIV" => "edit1", "TAB" => GetMessage("SUP_RECORD"), "ICON"=>"ticket_dict_edit", "TITLE"=>GetMessage("SUP_RECORD_TITLE"));
if ($bTab2) $aTabs[] = array("DIV" => "edit2", "TAB" => GetMessage("SUP_STAT"), "ICON"=>"ticket_dict_edit", "TITLE"=>GetMessage("SUP_STAT"));

$tabControl = new CAdminTabControl("tabControl", $aTabs);

$tabControl->Begin();?>

<?$tabControl->BeginNextTab();?>

	<tr>
		<td width="40%"><?=GetMessage("SUP_TYPE")?></td>
		<td width="60%"><?
			$arr = CTicketDictionary::GetTypeList();
			echo SelectBoxFromArray("C_TYPE", $arr, htmlspecialcharsbx($str_C_TYPE),"","OnChange='C_TYPE_Change()' ");
			?></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?=GetMessage("SUP_SITE")?></td>
		<td>
			<div class="adm-list">
			<?
			foreach ($arrSites as $sid => $arrS):
			$checked = ((is_array($arrSITE) && in_array($sid, $arrSITE)) || ($ID<=0 && $def_site_id==$sid)) ? "checked" : "";
			/*<?=$disabled?>*/
			?>
			<div class="adm-list-item">
				<div class="adm-list-control"><input type="checkbox" name="arrSITE[]" value="<?=htmlspecialcharsex($sid)?>" id="<?=htmlspecialcharsex($sid)?>" <?=$checked?>></div>
				<div class="adm-list-label"><label for="<?=htmlspecialcharsbx($sid)?>"><?echo '[<a title="'.GetMessage("MAIN_ADMIN_MENU_EDIT").'" href="/bitrix/admin/site_edit.php?LID='.htmlspecialcharsbx($sid).'&lang='.LANGUAGE_ID.'">'.htmlspecialcharsex($sid).'</a>]&nbsp;'.htmlspecialcharsex($arrS["NAME"])?></label></div>
			</div>
			<?
			endforeach;
		?></div></td>
	</tr>
	<tr>
		<td><?=GetMessage("SUP_SORT")?></td>
		<td><input type="text" name="C_SORT" size="5" value="<?=$str_C_SORT?>"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?=GetMessage("SUP_NAME")?></td>
		<td><input type="text" name="NAME" size="60" maxlength="255" value="<?=$str_NAME?>"></td>
	</tr>
	<tr>
		<td><?=GetMessage("SUP_SID")?></td>
		<td><input type="text" name="SID" size="60" maxlength="255" value="<?=$str_SID?>"></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?=GetMessage("SUP_DESCRIPTION")?></td>
	</tr>
	<tr>
		<td colspan="2"><textarea name="DESCR" style="width:100%;height:300px;"><?=$str_DESCR?></textarea></td>
	</tr>

	<tr>
		<td width="100%" colspan=2 align="center">
			<div id="tr_default">
			<table width="100%" cellspacing=0 cellpadding=0>
				<tr valign="top">
					<td align="right" width="40%"><?echo GetMessage("SUP_BY_DEFAULT")?></td>
					<td width="60%" align="left" style="padding-left: 10px;"><?echo InputType("checkbox", "SET_AS_DEFAULT", "Y", $str_SET_AS_DEFAULT, false); ?></td>
				</tr>
			</table>
			</div>
		</td>
	</tr>
	<tr>
		<td width="100%" colspan=2 align="center">
			<div id="tr_responsible">
			<table width="100%" cellspacing=0 cellpadding=0>
				<tr>
					<td align="right" width="40%"><?=GetMessage("SUP_RESPONSIBLE")?></td>
					<td width="60%" align="left" style="padding-left: 10px;"><?echo SelectBox("RESPONSIBLE_USER_ID", CTicket::GetSupportTeamList(), GetMessage("SUP_NO"), $str_RESPONSIBLE_USER_ID);?></td>
				</tr>
			</table>
			</div>
		</td>
	</tr>
	<? if ($bTab2):
		$tabControl->BeginNextTab();?>
	<tr>
		<td width="100%" colspan=2 align="center">
			<div id="events">
			<table width="100%" cellspacing="8" cellpadding="0">
				<tr>
					<td colspan="2" width="100%"><?=GetMessage("SUP_EVENT_PARAMS")?></td>
				</tr>
				<tr>
					<td align="right" width="40%">event1:</td>
					<td width="60%" align="left" style="padding-left: 10px;"><input type="text" name="EVENT1" maxlength="255" size="30" value="<? echo $str_EVENT1;?>"></td>
				</tr>
				<tr>
					<td align="right">event2:</td>
					<td align="left" style="padding-left: 10px;"><input type="text" name="EVENT2" maxlength="255" size="30" value="<? echo $str_EVENT2;?>"><br><?echo GetMessage("SUP_EVENT12")?></td>
				</tr>
				<tr>
					<td align="right">event3:</td>
					<td align="left" style="padding-left: 10px;"><input type="text" name="EVENT3" maxlength="255" size="30" value="<? echo $str_EVENT3;?>"><br><?echo GetMessage("SUP_EVENT3")?></td>
				</tr>
			</table>
			</div>
		</td>
	</tr>
	<? endif; ?>

<?
$tabControl->Buttons(Array("disabled" => $bAdmin!="Y","back_url" =>"/bitrix/admin/ticket_dict_list.php?lang=".LANGUAGE_ID. "&find_type=".$str_C_TYPE));
$tabControl->End();
?>

<SCRIPT>
<!--
C_TYPE_Change();
//-->
</SCRIPT>
</form>
<?$tabControl->ShowWarnings("form1", $message);?>

<? require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>