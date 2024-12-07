<? 
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");

ClearVars();

$bDemo = (CTicket::IsDemo()) ? "Y" : "N";
$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
$strError = null;

if($bAdmin!="Y" && $bDemo!="Y") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/admin/ticket_message_edit.php");
$err_mess = "File: ".__FILE__."<br>Line: ";
define("HELP_FILE","ticket_list.php");

/***************************************************************************
									Функции
***************************************************************************/

function CheckFields() // проверка на наличие обязательных полей
{
	global $arrFILES, $bAdmin, $bSupportTeam;

	$arMsg = Array();
	if ($bSupportTeam!="Y" && $bAdmin!="Y")
	{
		$max_size = COption::GetOptionString("support", "SUPPORT_MAX_FILESIZE");
		$max_size = intval($max_size)*1024;
	}

	if ($max_size>0 && is_array($arrFILES) && count($arrFILES)>0)
	{
		$i = 0;
		foreach ($arrFILES as $key => $arFILE)
		{
			$i++;
			if (intval($arFILE["size"])>$max_size) 
			{
				$arMsg[] = array("id"=>"FILE_".$i, "text"=> str_replace("#FILE_NAME#", $arFILE["name"], GetMessage("SUP_MAX_FILE_SIZE_EXCEEDING")));
				//$str .= str_replace("#FILE_NAME#", $arFILE["name"], GetMessage("SUP_MAX_FILE_SIZE_EXCEEDING"))."<br>";
			}
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

/***************************************************************************
							Обработка GET | POST
***************************************************************************/
$TICKET_LIST_URL = $TICKET_LIST_URL <> ''? CUtil::AddSlashes(htmlspecialcharsbx((mb_substr($TICKET_LIST_URL, 0, 4) == 'http'?'':'/').$TICKET_LIST_URL)) : "ticket_list.php";
$TICKET_EDIT_URL = $TICKET_EDIT_URL <> ''? CUtil::AddSlashes(htmlspecialcharsbx((mb_substr($TICKET_EDIT_URL, 0, 4) == 'http'?'':'/').$TICKET_EDIT_URL)) : "ticket_edit.php";
$TICKET_MESSAGE_EDIT_URL = $TICKET_MESSAGE_EDIT_URL <> ''? CUtil::AddSlashes(htmlspecialcharsbx((mb_substr($TICKET_MESSAGE_EDIT_URL, 0, 4) == 'http'?'':'/').$TICKET_MESSAGE_EDIT_URL)) : "ticket_message_edit.php";

$arFiles = array();
$ID = intval($ID);
$TICKET_ID = intval($TICKET_ID);
$rsTicket = CTicket::GetByID($TICKET_ID, LANGUAGE_ID, "Y", "N", "N");
if ($arTicket = $rsTicket->Fetch())
{
	CTicket::UpdateOnline($ID, $USER->GetID());

	if ($rsFiles = CTicket::GetFileList("s_id", "asc", array("MESSAGE_ID" => $ID))) :
		while ($arFile = $rsFiles->Fetch()) :
			$name = $arFile["ORIGINAL_NAME"];
			if ($arFile["EXTENSION_SUFFIX"] <> '') :
				$suffix_length = mb_strlen($arFile["EXTENSION_SUFFIX"]);
				$name = mb_substr($name, 0, mb_strlen($name) - $suffix_length);
			endif;
			$arFile["NAME"] = $name;
			$arFiles[] = $arFile;
		endwhile;
	endif;

	// если была нажата кнопка "save" на текущей странице
	if (($save <> '' || $apply <> '') && $_SERVER['REQUEST_METHOD']=="POST" && $bAdmin=="Y" && $ID>0 && $TICKET_ID>0 && check_bitrix_sessid())
	{
		$DB->PrepareFields("b_ticket_message");
		$arrFILES = array();
		if (is_array($arFiles) && count($arFiles)>0)
		{
			reset ($arFiles);
			foreach($arFiles as $arFile)
			{
				$key = "EXIST_FILE_".$arFile["ID"];
				$arF = $_FILES[$key];
				$arF["MODULE_ID"] = "support";
				$arF["old_file"] = $arFile["ID"];
				$arF["del"] = ${$key."_del"};
				if ($arF["del"]=="Y" || $arF["name"] <> '') $arrFILES[] = $arF;
			}
		}

		if (is_array($_FILES) && count($_FILES)>0)
		{
			foreach ($_FILES as $key => $arF)
			{
				if ($arF["name"] <> '')
				{
					$arF["MODULE_ID"] = "support";
					if (mb_strpos($key, "NEW_FILE") !== false)
					{
						$arrFILES[] = $arF;
					}
				}
			}
		}
		if (CheckFields())
		{
			$IS_SPAM = ($str_IS_SPAM=="Y" || $str_IS_SPAM=="N") ? $str_IS_SPAM : "";
			$IS_HIDDEN = ($str_IS_HIDDEN=="Y" || $str_IS_HIDDEN=="N") ? $str_IS_HIDDEN : "";
			$IS_LOG = ($str_IS_LOG=="Y" || $str_IS_LOG=="N") ? $str_IS_LOG : "";
			$IS_OVERDUE = ($str_IS_OVERDUE=="Y" || $str_IS_OVERDUE=="N") ? $str_IS_OVERDUE : "";

			$arFields = array(
				"C_NUMBER"			=> $C_NUMBER,
				"MESSAGE"			=> $MESSAGE,
				"SOURCE_ID"			=> $SOURCE_ID,
				"OWNER_SID"			=> $OWNER_SID,
				"OWNER_USER_ID"		=> $OWNER_USER_ID,
				"EXTERNAL_ID"		=> $EXTERNAL_ID,
				"EXTERNAL_FIELD_1"	=> $EXTERNAL_FIELD_1,
				"IS_SPAM"			=> $IS_SPAM,
				"TASK_TIME" => $TASK_TIME,
				"IS_HIDDEN"			=> $IS_HIDDEN,
				"IS_LOG"			=> $IS_LOG,
				"IS_OVERDUE"		=> $IS_OVERDUE,
				"NOT_CHANGE_STATUS" => $NOT_CHANGE_STATUS,
				"FILES"				=> $arrFILES
				);

			if ($IS_LOG == "Y") unset($arFields["TASK_TIME"]);

			CTicket::UpdateMessage($ID, $arFields);
			//CTicket::UpdateLastParams2($TICKET_ID);
			CTicket::UpdateLastParamsN($TICKET_ID, array("EVENT"=>array(CTicket::UPDATE)), true, true);
			
			if (!$strError) 
			{
				if ($save <> '') LocalRedirect($TICKET_EDIT_URL."?lang=".LANGUAGE_ID."&ID=".$TICKET_ID); 
				elseif ($apply <> '') LocalRedirect($TICKET_MESSAGE_EDIT_URL."?ID=".$ID. "&TICKET_ID=".$TICKET_ID."&lang=".LANGUAGE_ID);
			}
		}
		else
		{
			if ($e = $APPLICATION->GetException())
				$strError = new CAdminMessage(GetMessage("SUP_ERROR"), $e);
		}
	}

	$message = CTicket::GetMessageByID($ID);
	if (!($message->ExtractFields()))
	{
		//$strError .= GetMessage("SUP_MESSAGE_NOT_FOUND")."<br>";
		$e = $APPLICATION->GetException();
		$strError = new CAdminMessage(GetMessage("SUP_MESSAGE_NOT_FOUND"),$e);
	}
	else
	{
		$arFiles = array();
		if ($rsFiles = CTicket::GetFileList("s_id", "asc", array("MESSAGE_ID" => $ID))) :
			while ($arFile = $rsFiles->Fetch()) :
				$name = $arFile["ORIGINAL_NAME"];
				if ($arFile["EXTENSION_SUFFIX"] <> '') :
					$suffix_length = mb_strlen($arFile["EXTENSION_SUFFIX"]);
					$name = mb_substr($name, 0, mb_strlen($name) - $suffix_length);
				endif;
				$arFile["NAME"] = $name;
				$arFiles[] = $arFile;
			endwhile;
		endif;
	}

	if ($strError)
		$DB->InitTableVarsForEdit("b_ticket_message", "", "str_");

	$str_OWNER_USER_ID = intval($str_OWNER_USER_ID)>0 ? intval($str_OWNER_USER_ID) : "";

	$sDocTitle = GetMessage("SUP_EDIT_RECORD", array("#ID#" => $ID, "#TID#" => $TICKET_ID));

	$APPLICATION->SetTitle($sDocTitle);
}
if ($ADD_PUBLIC_CHAIN=="Y" || !isset($ADD_PUBLIC_CHAIN))
{
	$APPLICATION->AddChainItem(GetMessage("SUP_TICKETS_LIST"), $TICKET_LIST_URL);
	$APPLICATION->AddChainItem(str_replace("#TID#",$TICKET_ID,GetMessage("SUP_TICKET_EDIT")), $TICKET_EDIT_URL."?ID=".$TICKET_ID);
}
$is_ie = IsIE();
$is_ie = true;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");?>

<? 
$aMenu = array();
$aMenu[] = array(
	"TEXT"	=> str_replace("#TID#",$TICKET_ID,GetMessage("SUP_TICKET_EDIT")), 
	"LINK"	=> "/bitrix/admin/ticket_edit.php?lang=".LANGUAGE_ID."&ID=".$TICKET_ID
	);

if (intval($arTicket["MESSAGES"])>1)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT"	=> GetMessage("SUP_DELETE_MESSAGE"),
		"LINK"	=> "javascript:if(confirm('".GetMessage("SUP_DELETE_MESSAGE_CONFIRM")."')) window.location='/bitrix/admin/ticket_edit.php?ID=".$TICKET_ID."&mdel_id=".$ID."&lang=".LANGUAGE_ID."&".bitrix_sessid_get(). "&set_default=Y';",
		"WARNING"=>"Y"
		);
}

//echo ShowSubMenu($aMenu);


$context = new CAdminContextMenu($aMenu);
$context->Show();

//ShowError($strError);
//ShowNote($strNote);
if ($strError)
	echo $strError->Show();
/***************************************************************************
								HTML форма
****************************************************************************/
?>
<form name="form1" method="POST" action="<?=$APPLICATION->GetCurPage()?>?ID=<?=$ID?>&TICKET_ID=<?=$TICKET_ID?>&lang=<?=LANG?>" enctype="multipart/form-data">
<?=bitrix_sessid_post()?>
<input type="hidden" name="set_default" value="Y">
<input type="hidden" name="ID" value=<?=$ID?>>
<input type="hidden" name="TICKET_ID" value=<?=$TICKET_ID?>>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?
	$aTabs = array();
	$aTabs[] = array("DIV" => "edit1", "TAB" => GetMessage("SUP_EDIT_ALT"), "ICON"=>"ticket_edit",
	"TITLE"=> $APPLICATION->GetTitle()
	);
	$tabControl = new CAdminTabControl("tabControl", $aTabs);
	$tabControl->Begin();
	$tabControl->BeginNextTab();
?>
	<SCRIPT>
	<!--
	function SelectSource()
	{
		var objSourceSelect, strSourceValue;
		objSourceSelect = document.form1.SOURCE_ID;
		strSourceValue = objSourceSelect[objSourceSelect.selectedIndex].value;
		document.getElementById("OWNER_SID").style.display = "none";
		document.getElementById("OWNER_SID").disabled = true;
		if (strSourceValue!="NOT_REF")
		{
			document.getElementById("OWNER_SID").disabled = false;
			document.getElementById("OWNER_SID").style.display = "inline";
		}
	}
	//-->
	</SCRIPT>

	<tr valign="middle">
		<td width="20%" nowrap><?=GetMessage("SUP_SOURCE")." / ".GetMessage("SUP_FROM")?></td>
		<td width="80%" nowrap><?
			echo SelectBox("SOURCE_ID", CTicket::GetRefBookValues("SR", $arTicket["LID"]), "< web >", $str_SOURCE_ID, "OnChange=SelectSource() ");
			?>&nbsp;<input type="text" size="12" name="OWNER_SID" id="OWNER_SID" value="<?=$str_OWNER_SID?>"><?echo FindUserID("OWNER_USER_ID", $str_OWNER_USER_ID)?></td>
	</tr>
	<SCRIPT>
	<!--
	SelectSource();
	//-->
	</SCRIPT>
	<tr valign="top">
		<td width="20%"><?=GetMessage("SUP_CREATE")?></td>
		<td align="left" width="80%"><?=$str_DATE_CREATE?>&nbsp;&nbsp;&nbsp;<?
		if ($str_CREATED_MODULE_NAME == '' || $str_CREATED_MODULE_NAME=="support") :
			?>[<a title="<?=GetMessage("SUP_USER_PROFILE")?>" href="/bitrix/admin/user_edit.php?lang=<?=LANG?>&ID=<?=$str_CREATED_USER_ID?>"><?echo $str_CREATED_USER_ID?></a>] (<?=$str_CREATED_LOGIN?>) <?=$str_CREATED_NAME?><?
			if (intval($str_CREATED_GUEST_ID)>0 && CModule::IncludeModule("statistic")) :
				echo " [<a title='".GetMessage("SUP_GUEST_ID")."'  href='/bitrix/admin/guest_list.php?lang=".LANG."&find_id=". $str_CREATED_GUEST_ID."&find_id_exact_match=Y&set_filter=Y' >".$str_CREATED_GUEST_ID."</a>]";
			endif;
		else :
			echo $str_CREATED_MODULE_NAME;
		endif;
		?></td></td>
	</tr>
	<?if ($str_DATE_CREATE!=$str_TIMESTAMP_X):?>
	<tr valign="middle">
		<td><?=GetMessage("SUP_TIMESTAMP")?></td>
		<td align="left"><?=$str_TIMESTAMP_X?>&nbsp;&nbsp;&nbsp;<?
			?>[<a title="<?=GetMessage("SUP_USER_PROFILE")?>" href="/bitrix/admin/user_edit.php?lang=<?=LANG?>&ID=<?echo $str_MODIFIED_USER_ID?>"><?=$str_MODIFIED_USER_ID?></a>] (<?=$str_MODIFIED_LOGIN?>) <?=$str_MODIFIED_NAME?><?
			if (intval($str_MODIFIED_GUEST_ID)>0 && CModule::IncludeModule("statistic")) :
				echo " [<a title='".GetMessage("SUP_GUEST_ID")."'  href='/bitrix/admin/guest_list.php?lang=".LANG."&find_id=".$str_MODIFIED_GUEST_ID."&find_id_exact_match=Y&set_filter=Y'>".$str_MODIFIED_GUEST_ID."</a>]";
			endif;
		?></td>
	</tr>
	<?endif;?>
	<tr>
		<td><?=GetMessage("SUP_NUMBER")?></td>
		<td><input type="text" name="C_NUMBER" value="<?=$str_C_NUMBER?>" size="5"></td>
	</tr>
	<tr>
		<td><?=GetMessage("SUP_SPAM_MARK")?></td>
		<td><?
			$arr = array(
				"reference" => array(
					GetMessage("SUP_POSSIBLE_SPAM"),
					GetMessage("SUP_CERTAIN_SPAM")
					), 
				"reference_id" => array(
					"N", 
					"Y")
				);
		echo SelectBoxFromArray("IS_SPAM", $arr, htmlspecialcharsbx($str_IS_SPAM), " ");
		?></td>
	</tr>

	<tr valign="top"> 
		<td valign="top"><?=GetMessage("CHANGE_STATUS")?>:</td>
		<td valign="top"><?echo InputType("checkbox", "NOT_CHANGE_STATUS", "Y", $str_NOT_CHANGE_STATUS, false, "", "id=\"NOT_CHANGE_STATUS\"")?></td>
	</tr>

	<tr valign="top">
		<td><?=GetMessage("SUP_IS_HIDDEN")?></td>
		<td><?echo InputType("checkbox","IS_HIDDEN","Y",$str_IS_HIDDEN, false, "")?></td>
	</tr>
	<tr valign="top">
		<td><?=GetMessage("SUP_IS_LOG")?></td>
		<td><?echo InputType("checkbox","IS_LOG","Y",$str_IS_LOG, false, "")?></td>
	</tr>
	<tr valign="top">
		<td><?=GetMessage("SUP_IS_OVERDUE")?></td>
		<td><?echo InputType("checkbox","IS_OVERDUE","Y",$str_IS_OVERDUE, false, "")?></td>
	</tr>
	<tr valign="top"> 
		<td><?=GetMessage("SUP_MESSAGE")?></td>
		<td><textarea name="MESSAGE" style="width:100%; height:300px;" wrap="virtual"><?
		$str_MESSAGE = str_replace("&#", "&amp;#", $str_MESSAGE);
		echo $str_MESSAGE;
		?></textarea></td>
	</tr>
	<?if (is_array($arFiles) && count($arFiles)>0) :?>
	<tr>
		<td valign="top"><?=GetMessage("SUP_ATTACHED_FILES")?></td>
		<td><table cellspacing=0 cellpadding=0 border=0>
				<?
				reset ($arFiles);
				foreach($arFiles as $arFile) :
				?>
				<tr>
					<td><? 
						?><a title="<?=GetMessage("SUP_VIEW_ALT")?>" target="_blank" href="/bitrix/tools/ticket_show_file.php?hash=<?echo $arFile["HASH"]?>&lang=<?=LANG?>"><?echo htmlspecialcharsbx($arFile["NAME"])?></a> (<?
						/*$a = array("b", "kb", "mb", "gb");
						$pos = 0;
						$size = $arFile["FILE_SIZE"];
						while($size >= 1024) {$size /= 1024; $pos++;}
						echo round($size,2)." ".$a[$pos];*/
						echo CFile::FormatSize($arFile["FILE_SIZE"]);
						?>)&nbsp;&nbsp;[&nbsp;<a href="/bitrix/tools/ticket_show_file.php?hash=<?echo $arFile["HASH"]?>&lang=<?=LANG?>&action=download"><?echo GetMessage("SUP_DOWNLOAD")?></a>&nbsp;]&nbsp;&nbsp;<?=GetMessage("SUP_DELETE")?>:<input type="checkbox" name="EXIST_FILE_<?=$arFile["ID"]?>_del" value="Y"></td>
				</tr>
				<tr>
					<td><input name="EXIST_FILE_<?=$arFile["ID"]?>" size="30" type="file"></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<?
				endforeach;
				?>
			</table>
		</td>		
	</tr>
	<?endif;?>

	<script>
	<!--
	function AddFileInput()
	{
		var counter = document.form1.files_counter.value;
		counter++;
		var tb = document.getElementById("files_table");
		var oRow = tb.insertRow(0);
		var oCell = oRow.insertCell(0);
		oCell.innerHTML = '<input name="NEW_FILE_'+counter+'" id="NEW_FILE_'+counter+'" size="45" type="file">';
		document.form1.files_counter.value = counter;
	}
	//-->
	</script>
	<tr valign="top">
		<td valign="top"><?=GetMessage("SUP_ATTACH_NEW_FILES")?></td>
		<td><table cellspacing=0 cellpadding=0 border=0 id="files_table">
				<? for ($i=0; $i<=2; $i++) : ?>
				<tr>
					<td><input name="NEW_FILE_<?=$i?>" class="typefile" size="45" type="file"></td>
				</tr><?
				endfor;
				?>
			<input type="hidden" name="files_counter" value="<?=$i-1?>">
			</table>
				<input type="button" value="<?=GetMessage("SUP_MORE")?>" OnClick="AddFileInput()"></td>
	</tr>
	<tr>
		<td><?=GetMessage("SUP_EXTERNAL_ID")?></td>
		<td><input type="text" name="EXTERNAL_ID" value="<?=$str_EXTERNAL_ID?>" size="5"></td>
	</tr>
	<tr>
		<td><?=GetMessage("SUP_TASK_TIME")?></td>
		<td><input type="text" name="TASK_TIME" id="TASK_TIME" size="7" maxlength="10" value="<?=$str_TASK_TIME?>"></td>
	</tr>
	<tr valign="top">
		<td><?=GetMessage("SUP_EXTERNAL_FIELD_1")?></td>
		<td><textarea name="EXTERNAL_FIELD_1" style="width:100%;height:150px;" wrap="virtual"><?=$str_EXTERNAL_FIELD_1?></textarea></td>
	</tr>
<?
$tabControl->Buttons(array("back_url"=>$TICKET_EDIT_URL."?lang=".LANGUAGE_ID."&ID=".$TICKET_ID));
$tabControl->End();
?>
</form>
<?$tabControl->ShowWarnings("form1", $strError);?>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>