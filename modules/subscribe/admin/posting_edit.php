<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/prolog.php");
define("HELP_FILE", "add_issue.php");

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("subscribe");
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("post_posting_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("post_posting_tab_title")),
	array("DIV" => "edit2", "TAB" => GetMessage("post_subscr_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("post_subscr_tab_title")),
	array("DIV" => "edit3", "TAB" => GetMessage("post_attachments"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("post_attachments_title")),
	array("DIV" => "edit4", "TAB" => GetMessage("post_params_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("post_params_tab_title")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

CModule::IncludeModule("fileman");
$ID = intval($ID);		// Id of the edited record
$bCopy = ($action == "copy");
$message = null;
$bVarsFromForm = false;

if($REQUEST_METHOD == "POST" && ($save.$apply.$Send.$Resend.$Continue!="") && $POST_RIGHT=="W" && check_bitrix_sessid())
{
	$posting = new CPosting();
	$arFields = Array(
		"FROM_FIELD"	=> $_REQUEST["FROM_FIELD"],
		"TO_FIELD"	=> $_REQUEST["TO_FIELD"],
		"BCC_FIELD"	=> $_REQUEST["BCC_FIELD"],
		"EMAIL_FILTER"	=> $_REQUEST["EMAIL_FILTER"],
		"SUBJECT"	=> $_REQUEST["SUBJECT"],
		"BODY_TYPE"	=> ($_REQUEST["BODY_TYPE"] <> "html"? "text":"html"),
		"BODY"		=> $_REQUEST["BODY"],
		"DIRECT_SEND"	=> ($_REQUEST["DIRECT_SEND"] <> "Y"? "N":"Y"),
		"CHARSET"	=> $_REQUEST["CHARSET"],
		"SUBSCR_FORMAT"	=> ($_REQUEST["SUBSCR_FORMAT"]<>"html" && $_REQUEST["SUBSCR_FORMAT"]<>"text"? false: $_REQUEST["SUBSCR_FORMAT"]),
		"RUB_ID"	=> $_REQUEST["RUB_ID"],
		"GROUP_ID"	=> $_REQUEST["GROUP_ID"],
		"AUTO_SEND_TIME"=> ($_REQUEST["AUTO_SEND_FLAG"]<>"Y"? false: $_REQUEST["AUTO_SEND_TIME"]),
	);

	if($STATUS <> "")
	{
		if($STATUS<>"S" && $STATUS<>"E" && $STATUS<>"P" && $STATUS<>"W")
			$STATUS = "D";
	}

	if($ID>0)
	{
		$res = $posting->Update($ID, $arFields);
		if($Resend <> '')
			$STATUS="W";
		if($res && $STATUS<>"")
			$res = $posting->ChangeStatus($ID, $STATUS);
	}
	else
	{
		$arFields["STATUS"] = "D";
		$ID = $posting->Add($arFields);
		$res = ($ID>0);
	}

	if($res)
	{
		//Delete checked
		if(is_array($FILE_ID))
			foreach($FILE_ID as $file)
				CPosting::DeleteFile($ID, $file);

		//New files
		$arFiles = array();

		//Brandnew
		if(is_array($_FILES["NEW_FILE"]))
		{
			foreach($_FILES["NEW_FILE"] as $attribute=>$files)
			{
				if(is_array($files))
					foreach($files as $index=>$value)
						$arFiles[$index][$attribute]=$value;
			}

			foreach($arFiles as $index => $file)
			{
				if(!is_uploaded_file($file["tmp_name"]))
					unset($arFiles[$index]);
			}
		}

		//Copy
		if(array_key_exists("FILES", $_POST) && is_array($_POST["FILES"]))
		{
			if(intval($COPY_ID) > 0)
			{
				//Files from posting_edit.php
				foreach(array_reverse($_POST["FILES"], true) as $key => $file_id)
				{
					//skip "deleted"
					if(is_array($FILE_ID) && array_key_exists($key, $FILE_ID))
						continue;
					//clone file
					if(intval($file_id) > 0)
					{
						$rsFile = CPosting::GetFileList($COPY_ID, $file_id);
						if($ar = $rsFile->Fetch())
						{
							array_unshift($arFiles, CFile::MakeFileArray($ar["ID"]));
						}
					}
				}
			}
			else
			{
				//Files from template_test.php
				foreach(array_reverse($_POST["FILES"], true) as $file)
				{
					if(
						is_array($file)
						&& $file["tmp_name"] <> ''
						&& $APPLICATION->GetFileAccessPermission($file["tmp_name"]) >= "W"
					)
					{
						array_unshift($arFiles, $file);
					}
				}
			}
		}

		foreach($arFiles as $file)
		{
			if($file["name"] <> '' and intval($file["size"])>0)
			{
				if (!$posting->SaveFile($ID, $file))
				{
					$_SESSION["SESS_ADMIN"]["POSTING_EDIT_MESSAGE"] = array(
						"MESSAGE" => $posting->LAST_ERROR,
						"TYPE" => "ERROR",
					);
					LocalRedirect("posting_edit.php?ID=".$ID."&lang=".LANG."&".$tabControl->ActiveTabParam());
				}
			}
		}
	}

	if($res)
	{
		if($Send!="" || $Resend!="" || $Continue!="")
		{
			LocalRedirect("posting_admin.php?ID=".$ID."&action=send&lang=".LANG."&".bitrix_sessid_get());
		}

		if($apply!="")
		{
			$_SESSION["SESS_ADMIN"]["POSTING_EDIT_MESSAGE"] = array(
				"MESSAGE" => GetMessage("post_save_ok"),
				"TYPE" => "OK",
			);
			LocalRedirect("posting_edit.php?ID=".$ID."&lang=".LANG."&".$tabControl->ActiveTabParam());
		}
		else
		{
			LocalRedirect("posting_admin.php?lang=".LANG);
		}
	}
	else
	{
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("post_save_error"), $e);
		$bVarsFromForm = true;
	}
}

ClearVars();
$str_STATUS = "D";
$str_DIRECT_SEND = "Y";
$str_BODY_TYPE = "text";
$str_FROM_FIELD = COption::GetOptionString("subscribe", "default_from");
$str_TO_FIELD = COption::GetOptionString("subscribe", "default_to");
$str_AUTO_SEND_FLAG = "N";
$str_AUTO_SEND_TIME = ConvertTimeStamp(time()+CTimeZone::GetOffset(), "FULL");

if($ID>0)
{
	$post = CPosting::GetByID($ID);
	if(!($post_arr = $post->ExtractFields("str_")))
		$ID=0;
}

if($bVarsFromForm)
{
	if(!array_key_exists("DIRECT_SEND", $_REQUEST))
		$DIRECT_SEND = "N";
	$DB->InitTableVarsForEdit("b_posting", "", "str_");
	if(array_key_exists("AUTO_SEND_FLAG", $_REQUEST))
		$str_AUTO_SEND_FLAG = "Y";
	else
		$str_AUTO_SEND_FLAG = "N";
}
elseif($ID > 0)
{
	if($str_AUTO_SEND_TIME <> '')
	{
		$str_AUTO_SEND_FLAG = "Y";
	}
	else
	{
		$str_AUTO_SEND_FLAG = "N";
	}
}

$APPLICATION->SetTitle(($ID>0 && !$bCopy? GetMessage("post_title_edit").$ID : GetMessage("post_title_add")));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"=>GetMessage("post_mnu_list"),
		"TITLE"=>GetMessage("post_mnu_list_title"),
		"LINK"=>"posting_admin.php?lang=".LANG,
		"ICON"=>"btn_list",
	)
);
if($ID>0 && !$bCopy)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT"=>GetMessage("post_mnu_add"),
		"TITLE"=>GetMessage("post_mnu_add_title"),
		"LINK"=>"posting_edit.php?lang=".LANG,
		"ICON"=>"btn_new",
	);
	$aMenu[] = array(
		"TEXT"=>GetMessage("post_mnu_copy"),
		"TITLE"=>GetMessage("post_mnu_copy_title"),
		"LINK"=>"posting_edit.php?ID=".$ID."&amp;action=copy&amp;lang=".LANG,
		"ICON"=>"btn_copy",
	);
	$aMenu[] = array(
		"TEXT"=>GetMessage("post_mnu_del"),
		"TITLE"=>GetMessage("post_mnu_del_title"),
		"LINK"=>"javascript:if(confirm('".GetMessage("post_mnu_confirm")."'))window.location='posting_admin.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
		"ICON"=>"btn_delete",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if(is_array($_SESSION["SESS_ADMIN"]["POSTING_EDIT_MESSAGE"]))
{
	CAdminMessage::ShowMessage($_SESSION["SESS_ADMIN"]["POSTING_EDIT_MESSAGE"]);
	$_SESSION["SESS_ADMIN"]["POSTING_EDIT_MESSAGE"]=false;
}

if($message)
	echo $message->Show();
elseif($posting->LAST_ERROR!="")
	CAdminMessage::ShowMessage($posting->LAST_ERROR);
?>

<form method="POST" Action="<?echo $APPLICATION->GetCurPage()?>"  ENCTYPE="multipart/form-data" name="post_form">
<?
$tabControl->Begin();
?>
<?
//********************
//Posting issue
//********************
$tabControl->BeginNextTab();
?>
	<tr class="heading">
		<td colspan="2"><?=GetMessage("post_info")?></td>
	</tr>
<?if($ID>0 && !$bCopy):?>
	<tr>
		<td><?echo GetMessage("post_date_upd")?></td>
		<td><?echo $str_TIMESTAMP_X;?></td>
	</tr>
	<?if($str_DATE_SENT <> ''):?>
	<tr>
		<td><?echo GetMessage("post_date_sent")?></td>
		<td><?echo $str_DATE_SENT;?></td>
	</tr>
	<?endif;?>
	<?
	$arEmailStatuses = CPosting::GetEmailStatuses($ID);
	if(array_key_exists("Y", $arEmailStatuses) || array_key_exists("E", $arEmailStatuses)):?>
	<tr>
		<td><?echo GetMessage("POST_TO")?></td>
		<td>[&nbsp;<a class="tablebodylink" href="javascript:void(0)" OnClick="jsUtils.OpenWindow('posting_bcc.php?ID=<?echo $str_ID?>&lang=<?echo LANG?>&find_status_id=E&set_filter=Y', 600, 500);"><?echo GetMessage("POST_SHOW_LIST")?></a>&nbsp;]</td>
	</tr>
	<?endif;?>
<?endif; //ID?>
	<tr>
		<td width="40%"><?echo GetMessage("post_stat")?></td>
		<td width="60%">
<?
if($ID>0 && !$bCopy)
{
	if($str_STATUS=="D") echo GetMessage("POST_STATUS_DRAFT");
	if($str_STATUS=="S") echo GetMessage("POST_STATUS_SENT");
	if($str_STATUS=="P") echo GetMessage("POST_STATUS_PART");
	if($str_STATUS=="E") echo GetMessage("POST_STATUS_ERROR");
	if($str_STATUS=="W") echo GetMessage("POST_STATUS_WAIT");
}
else
	echo GetMessage("POST_STATUS_DRAFT");
?>
		</td>
	</tr>
<?if($ID>0 && !$bCopy && $str_STATUS!="D"):?>
	<tr>
		<td><?echo GetMessage("post_status_change")?></td>
		<td>
		<select class="typeselect" name="STATUS">
			<option value=""><?echo GetMessage("post_status_not_change")?></option>
			<?if($str_STATUS <> "D" && $str_STATUS <> "P"):?>
			<option value="D"><?echo GetMessage("POST_STATUS_DRAFT")?></option>
			<?endif;?>
			<?if($str_STATUS == "P"):?>
			<option value="W"><?echo GetMessage("POST_STATUS_WAIT")?></option>
			<?endif;?>
		</select>
		</td>
	</tr>
<?endif;?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("post_fields")?></td>
	</tr>
	<tr class="">
		<td><?echo GetMessage("post_fields_from")?></td>
		<td><input type="text" name="FROM_FIELD" value="<?echo $str_FROM_FIELD;?>" size="30" maxlength="255"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("post_fields_to")?></td>
		<td><input type="text" name="TO_FIELD" value="<?echo $str_TO_FIELD;?>" size="30" maxlength="255"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("post_fields_subj")?></td>
		<td><input type="text" name="SUBJECT" value="<?echo $str_SUBJECT;?>" size="30" maxlength="255"></td>
	</tr>
	<tr class="heading adm-detail-required-field">
		<td colspan="2"><?echo GetMessage("post_fields_text")?><span class="required"><sup>1</sup></span></td>
	</tr>
	<tr>
		<td colspan="2">
		<?
		CFileMan::AddHTMLEditorFrame("BODY", $str_BODY, "BODY_TYPE", $str_BODY_TYPE, array('height' => '400', 'width' => '100%'), "N", 0, "", "", SITE_ID);
		?>
		</td>
	</tr>
<?
//********************
//Receipients
//********************
$tabControl->BeginNextTab();
?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("post_subscr")?></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("post_rub")?></td>
		<td>
			<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" id="RUB_ID_ALL" name="RUB_ID_ALL" value="Y" OnClick="CheckAll('RUB_ID', true)"></div>
					<div class="adm-list-label"><label for="RUB_ID_ALL"><?echo GetMessage("MAIN_ALL")?></label></div>
				</div>
			<?
			$aPostRub = array();
			if($ID>0)
			{
				$post_rub = CPosting::GetRubricList($ID);
				while($ar = $post_rub->Fetch())
					$aPostRub[] = $ar["ID"];
			}
			if(!is_array($RUB_ID))
				$RUB_ID = array();
			$rub = CRubric::GetList(array("LID"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), array("ACTIVE"=>"Y"));
			while($ar = $rub->GetNext()):
			?>
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" id="RUB_ID_<?echo $ar["ID"]?>" name="RUB_ID[]" value="<?echo $ar["ID"]?>"<?if(in_array($ar["ID"], ($bVarsFromForm? $RUB_ID:$aPostRub))) echo " checked"?> OnClick="CheckAll('RUB_ID')"></div>
					<div class="adm-list-label"><label for="RUB_ID_<?echo $ar["ID"]?>"><?echo "[".$ar["LID"]."] ".$ar["NAME"]?></label></div>
				</div>
			<?endwhile;?>
			</div>
		</td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("post_format")?></td>
		<td width="60%">
		<select class="typeselect" name="SUBSCR_FORMAT" id="SUBSCR_FORMAT">
			<option value=""<?if($str_SUBSCR_FORMAT=="") echo" selected"?>><?echo GetMessage("post_format_any")?></option>
			<option value="text"<?if($str_SUBSCR_FORMAT=="text") echo" selected"?>><?echo GetMessage("post_format_text")?></option>
			<option value="html"<?if($str_SUBSCR_FORMAT=="html") echo" selected"?>>HTML</option>
		</select>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("post_users")?></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("post_groups")?></td>
		<td>
			<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" id="GROUP_ID_ALL" name="GROUP_ID_ALL" value="Y" OnClick="CheckAll('GROUP_ID', true)"></div>
					<div class="adm-list-label"><label for="GROUP_ID_ALL"><?echo GetMessage("MAIN_ALL")?></label></div>
				</div>

		<?
			$aPostGrp = array();
			if($ID>0)
			{
				$post_grp = CPosting::GetGroupList($ID);
				while($post_grp_arr = $post_grp->Fetch())
					$aPostGrp[] = $post_grp_arr["ID"];
			}
			if(!is_array($GROUP_ID))
				$GROUP_ID = array();
			$group = CGroup::GetList();
			while($ar = $group->GetNext()):
			?>
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" id="GROUP_ID_<?echo $ar["ID"]?>" name="GROUP_ID[]" value="<?echo $ar["ID"]?>"<?if(in_array($ar["ID"], ($bVarsFromForm? $GROUP_ID: $aPostGrp))) echo " checked"?> OnClick="CheckAll('GROUP_ID')"></div>
					<div class="adm-list-label"><label for="GROUP_ID_<?echo $ar["ID"]?>"><?echo $ar["NAME"]?>&nbsp;[<a href="/bitrix/admin/group_edit.php?ID=<?echo $ar["ID"]?>&amp;lang=<?echo LANGUAGE_ID?>"><?echo $ar["ID"]?></a>]</label></div>
				</div>
			<?
				$n++;
			endwhile;
		?>
			</div>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("post_filter_title")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("post_filter")?></td>
		<td><input type="text" name="EMAIL_FILTER" id="EMAIL_FILTER" value="<?echo $str_EMAIL_FILTER?>" size="30" maxlength="255"></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
		<script language="JavaScript">
		<!--
		function ShowEMails()
		{
			var strParam = 'EMAIL_FILTER='+escape(document.post_form.EMAIL_FILTER.value);
			var aCheckBox;
			try
			{
				if('['+document.post_form.elements['RUB_ID[]'].type+']'=='[undefined]')
					aCheckBox = document.post_form.elements['RUB_ID[]'];
				else
					aCheckBox = new Array(document.post_form.elements['RUB_ID[]']);

				for(i=0; i<aCheckBox.length; i++)
					if(aCheckBox[i].checked)
						strParam += ('&RUB_ID[]='+aCheckBox[i].value);
			}
			catch (e)
			{
				//there is no rubrics so we can safely ignore
			}
			if('['+document.post_form.elements['GROUP_ID[]'].type+']'=='[undefined]')
				aCheckBox = document.post_form.elements['GROUP_ID[]'];
			else
				aCheckBox = new Array(document.post_form.elements['GROUP_ID[]']);

			for(i=0; i<aCheckBox.length; i++)
				if(aCheckBox[i].checked)
					strParam += ('&GROUP_ID[]='+aCheckBox[i].value);

			strParam += ('&SUBSCR_FORMAT='+document.post_form.SUBSCR_FORMAT[document.post_form.SUBSCR_FORMAT.selectedIndex].value);

			jsUtils.OpenWindow('posting_search.php?'+strParam+'&lang=<?echo LANG?>', 600, 500);
		}
		function CheckAll(prefix, act)
		{
			var bCheck = document.getElementById(prefix+'_ALL').checked;
			var bAll = true;
			var aCheckBox;
			try
			{
				if('['+document.post_form.elements[prefix+'[]'].type+']'=='[undefined]')
					aCheckBox = document.post_form.elements[prefix+'[]'];
				else
					aCheckBox = new Array(document.post_form.elements[prefix+'[]']);

				for(i=0; i<aCheckBox.length; i++)
				{
					if(act)
					{
						if(bCheck)
							aCheckBox[i].checked = true;
						else
							aCheckBox[i].checked = false;
					}
					else
						bAll = bAll && aCheckBox[i].checked;
				}
			}
			catch (e)
			{
				//there is no rubrics so we can safely ignore
			}
			if(!act)
				document.getElementById(prefix+'_ALL').checked = bAll;
		}
		CheckAll('RUB_ID');
		CheckAll('GROUP_ID');
		//-->
		</script>[ <a class="tablebodylink" title="<?echo GetMessage("post_list_title")?>" href="javascript:ShowEMails()"><?echo GetMessage("post_filter_list")?></a> ]</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("post_additional")?></td>
	</tr>
	<tr>
		<td align="center" colspan="2"><textarea name="BCC_FIELD" cols="50" rows="7" style="width:100%"><?echo $str_BCC_FIELD?></textarea></td>
	</tr>
<?
//********************
//Attachments
//********************
$tabControl->BeginNextTab();
?>
<?
if(COption::GetOptionString("subscribe", "attach_images")=="Y" && $str_BODY<>"" && $str_BODY_TYPE=="html"):
	$tools = new CMailTools;
	$tools->ReplaceImages($post_arr["BODY"]);
	if(count($tools->aMatches)>0):
?>
	<tr>
		<td width="40%" class="adm-detail-valign-top"><?=GetMessage("post_images_list")?>:</td>
		<td width="60%">
			<table border="0" cellspacing="0" cellpadding="0" class="internal">
			<tr class="heading">
				<td align="center"><?echo GetMessage("post_file")?></td>
				<td align="center"><?echo GetMessage("post_size")?></td>
			</tr>
			<?
			foreach($tools->aMatches as $attachment):
				if(CFile::GetImageSize($attachment["PATH"], true) === false)
					continue;
			?>
			<tr>
				<td><a href="<?echo $attachment["SRC"]?>" target=_blank><?echo $attachment["DEST"]?></a></td>
				<td align="right"><?echo filesize($attachment["PATH"])?></td>
			</tr>
			<?endforeach;?>
			</table>
		</td>
	</tr>
<?
	endif;
endif;
?>
	<?if($ID > 0 && ($rsFiles = CPosting::GetFileList($ID)) && ($arFile = $rsFiles->GetNext())):?>
	<tr>
		<td class="adm-detail-valign-top"><?=GetMessage("post_attachments_list")?>:</td>
		<td>
		<table border="0" cellpadding="0" cellspacing="0" class="internal">
		<tr class="heading">
			<td align="center"><?echo GetMessage("post_att_file")?></td>
			<td align="center"><?echo GetMessage("post_size")?></td>
			<td align="center"><?echo GetMessage("post_att_delete")?></td>
		</tr>
<?
		do
		{
?>
			<tr>
				<td><a href="posting_attachment.php?POSTING_ID=<?echo $ID?>&amp;FILE_ID=<?echo $arFile["ID"]?>"><?echo $arFile["ORIGINAL_NAME"]?><a></td>
				<td align="right"><?echo $arFile["FILE_SIZE"]?></td>
				<td align="center">
					<input type="checkbox" name="FILE_ID[<?echo $arFile["ID"]?>]" value="<?echo $arFile["ID"]?>">
					<?if($bCopy):?>
					<input type="hidden" name="FILES[<?echo $arFile["ID"]?>]" value="<?echo $arFile["ID"]?>">
					<?endif?>
				</td>
			</tr>
<?
		} while($arFile = $rsFiles->GetNext());
?>
		</table></td>
	</tr>
	<?endif;?>
	<tr>
		<td class="adm-detail-valign-top"><?=GetMessage("post_attachments_load")?>:</td>
		<td>
			<table border="0" cellpadding="0" cellspacing="0">
			<tr><td><?echo CFile::InputFile("NEW_FILE[n0]", 40, 0)?><br><br></td></tr>
			<tr><td><?echo CFile::InputFile("NEW_FILE[n1]", 40, 0)?><br><br></td></tr>
			<tr><td><?echo CFile::InputFile("NEW_FILE[n2]", 40, 0)?><br><br></td></tr>
			</table>
		</td>
	</tr>
<?
//********************
//Parameters
//********************
$tabControl->BeginNextTab();
?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("post_params")?></td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("post_enc")?></td>
		<td width="60%">
		<select class="typeselect" name="CHARSET">
		<?
		$aCharset = explode(",", COption::GetOptionString("subscribe", "posting_charset"));
		foreach($aCharset as $strCharset):
			?><option value="<?echo htmlspecialcharsbx($strCharset)?>"<?
			if($ID > 0 && ToLower($post_arr["CHARSET"]) == ToLower($strCharset)) echo " selected"
			?>><?echo htmlspecialcharsex($strCharset)?></option><?
		endforeach;
		?>
		</select>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("post_send_params")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("post_direct")?></td>
		<td>
			<input type="checkbox" name="DIRECT_SEND" value="Y"<?if($str_DIRECT_SEND <> "N") echo " checked"?>>
		</td>
	</tr>
	<?if($str_STATUS=="D" || $str_STATUS=="W"):?>
	<tr>
		<td><?echo GetMessage("post_send_flag")?></td>
		<td>
			<input type="checkbox" name="AUTO_SEND_FLAG" value="Y"<?if($str_AUTO_SEND_FLAG == "Y") echo " checked"?> OnClick="EnableAutoSend()">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("post_send_time"). ":"?><span class="required"><sup>2</sup></span></td>
		<td><?echo CalendarDate("AUTO_SEND_TIME", $str_AUTO_SEND_TIME, "post_form", "20")?></td>
	</tr>
<script language="JavaScript">
<!--
function EnableAutoSend()
{
	document.post_form.AUTO_SEND_TIME.disabled = !document.post_form.AUTO_SEND_FLAG.checked;
}
EnableAutoSend();
//-->
</script>
	<?else:
	$str_AUTO_SEND_FLAG = $str_AUTO_SEND_TIME <> ''? "Y" : "N";
	?>
	<tr>
		<td><?echo GetMessage("post_send_flag")?></td>
		<td><? echo ($str_AUTO_SEND_FLAG == "Y"?GetMessage("post_yes"):GetMessage("post_no"))?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("post_send_time"). ":"?><span class="required"><sup>2</sup></span>
		<input type="hidden" name="AUTO_SEND_FLAG" value="<?echo $str_AUTO_SEND_FLAG?>">
		<input type="hidden" name="AUTO_SEND_TIME" value="<?echo $str_AUTO_SEND_TIME?>">
		</td>
		<td><?echo $str_AUTO_SEND_TIME?></td>
	</tr>
	<?endif;?>
<?
$tabControl->Buttons(
	array(
		"disabled"=>($POST_RIGHT<"W"),
		"back_url"=>"posting_admin.php?lang=".LANG,

	)
);
?>
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?=LANG?>">
<?if($str_STATUS=="D"):?>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input <?if ($POST_RIGHT<"W") echo "disabled" ?> type="submit" value="<?echo GetMessage("post_butt_send")?>" name="Send" title="<?echo GetMessage("post_hint_send")?>">
<?elseif($str_STATUS=="W"):?>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input <?if ($POST_RIGHT<"W") echo "disabled" ?> type="submit" value="<?echo GetMessage("post_continue")?>" name="Continue" title="<?echo GetMessage("post_continue_conf")?>">
<?elseif($str_STATUS=="E"):?>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input <?if ($POST_RIGHT<"W") echo "disabled" ?> type="submit" value="<?echo GetMessage("post_resend")?>" name="Resend" title="<?echo GetMessage("post_resend_conf")?>">
<?endif?>
<?if($ID > 0):?>
	<?if($bCopy):?>
		<input type="hidden" name="COPY_ID" value="<?=$ID?>">
	<?else:?>
		<input type="hidden" name="ID" value="<?=$ID?>">
	<?endif?>
<?endif;?>
<?
$tabControl->End();
?>
</form>

<?
$tabControl->ShowWarnings("post_form", $message);
?>

<?echo BeginNote();?>
<span class="required"><sup>1</sup></span><?echo GetMessage("post_note")?><br>
<br>
<span class="required"><sup>2</sup></span><?echo GetMessage("post_send_msg")?><br>
<?echo EndNote();?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
