<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "settings/mail_events/message_edit.php");

if(!$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

CModule::IncludeModule("fileman");

$isAdmin = $USER->CanDoOperation('lpa_template_edit');
$isUserHavePhpAccess = $USER->CanDoOperation('edit_php');

ClearVars();

IncludeModuleLangFile(__FILE__);

$strError="";
$bVarsFromForm = false;
$ID=intval($ID);
$COPY_ID=intval($COPY_ID);
$message=null;
if($COPY_ID>0)
	$ID = $COPY_ID;
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB"), "ICON" => "message_edit", "TITLE" => GetMessage("MAIN_TAB_TITLE")),
	array("DIV" => "edit2", "TAB" => GetMessage("ATTACHMENT_TAB"), "ICON" => "message_edit", "TITLE" => GetMessage("ATTACHMENT_TAB_TITLE")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($REQUEST_METHOD=="POST" && (strlen($save)>0 || strlen($apply)>0)&& $isAdmin && check_bitrix_sessid())
{
	if(!$isUserHavePhpAccess)
	{
		$MESSAGE_OLD = false;
		if($ID>0)
		{
			$emOldDb = CEventMessage::GetByID($ID);
			if($emOld = $emOldDb->Fetch())
			{
				$MESSAGE_OLD = $emOld['MESSAGE'];
			}
		}

		$MESSAGE = LPA::Process($MESSAGE, $MESSAGE_OLD);
	}

	if(is_array($ADDITIONAL_FIELD))
	{
		$ADDITIONAL_FIELD_tmp = array();
		foreach($ADDITIONAL_FIELD['NAME'] as $AddFieldNum => $addFieldName)
		{
			if(strlen($addFieldName)>0)
			{
				if(isset($ADDITIONAL_FIELD['VALUE'][$AddFieldNum]))
					$addFieldValue = $ADDITIONAL_FIELD['VALUE'][$AddFieldNum];
				else
					$addFieldValue = '';

				$ADDITIONAL_FIELD_tmp[] = array('NAME'=> $addFieldName, 'VALUE'=> $addFieldValue);
			}
		}

		$ADDITIONAL_FIELD = $ADDITIONAL_FIELD_tmp;
	}


	$em = new CEventMessage;
	$arFields = array(
		"ACTIVE"		    => $ACTIVE,
		"EVENT_NAME"	    => $EVENT_NAME,
		"LID"			    => $LID,
		"EMAIL_FROM"	    => $EMAIL_FROM,
		"EMAIL_TO"		    => $EMAIL_TO,
		"BCC"			    => $BCC,
		"CC"			    => $CC,
		"REPLY_TO"		    => $REPLY_TO,
		"IN_REPLY_TO"	    => $IN_REPLY_TO,
		"PRIORITY"		    => $PRIORITY,
		"FIELD1_NAME"	    => $FIELD1_NAME,
		"FIELD1_VALUE"	    => $FIELD1_VALUE,
		"FIELD2_NAME"	    => $FIELD2_NAME,
		"FIELD2_VALUE"	    => $FIELD2_VALUE,
		"SUBJECT"		    => $SUBJECT,
		"MESSAGE"		    => $MESSAGE,
		"BODY_TYPE"		    => $BODY_TYPE,
		"SITE_TEMPLATE_ID"	=> $SITE_TEMPLATE_ID,
		"ADDITIONAL_FIELD" => $ADDITIONAL_FIELD,
		"LANGUAGE_ID" => $_POST["LANGUAGE_ID"],
	);

	if($ID>0 && $COPY_ID<=0)
		$res = $em->Update($ID, $arFields);
	else
	{
		$ID = $em->Add($arFields);
		$res = ($ID>0);
		$new="Y";
	}

	if(!$res)
	{
		$bVarsFromForm = true;
	}
	else
	{
		//Delete checked
		if(is_array($FILES_del))
		{
			$FILE_ID_tmp = array();
			foreach($FILES_del as $file=>$fileMarkDel)
			{
				$file = intval($file);
				if($file>0)
					$FILE_ID_tmp[] = $file;
			}

			if(count($FILE_ID_tmp)>0)
			{
				$deleteFileDb = \Bitrix\Main\Mail\Internal\EventMessageAttachmentTable::getList(array(
					'select' => array('FILE_ID'),
					'filter' => array('EVENT_MESSAGE_ID' => $ID, 'FILE_ID' => $FILE_ID_tmp),
				));
				while($arDeleteFile = $deleteFileDb->fetch())
				{
					CFile::Delete($arDeleteFile["FILE_ID"]);
					\Bitrix\Main\Mail\Internal\EventMessageAttachmentTable::delete($ID);
				}
			}
		}


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

		//New from media library and file structure
		if(array_key_exists("NEW_FILE", $_POST) && is_array($_POST["NEW_FILE"]))
		{
			foreach($_POST["NEW_FILE"] as $index=>$value)
				$arFiles[$index] = CFile::MakeFileArray($value);
		}

		//Copy
		if(array_key_exists("FILES", $_POST) && is_array($_POST["FILES"]))
		{
			if(intval($COPY_ID) > 0)
			{
				$arFileCopy_tmp = array();
				foreach(array_reverse($_POST["FILES"], true) as $key => $file_id)
				{
					//skip "deleted"
					if(is_array($FILES_del) && array_key_exists($key, $FILES_del))
						continue;
					//clone file
					if(intval($file_id) > 0)
					{
						$arFileCopy_tmp[] = $file_id;
					}
				}

				$deleteFileDb = \Bitrix\Main\Mail\Internal\EventMessageAttachmentTable::getList(array(
					'select' => array('FILE_ID'),
					'filter' => array('EVENT_MESSAGE_ID' => $COPY_ID, 'FILE_ID' => $arFileCopy_tmp),
				));
				while($arExistingFile = $deleteFileDb->fetch())
				{
					array_unshift($arFiles, CFile::MakeFileArray($arExistingFile["FILE_ID"]));
				}
			}
			else
			{
				//Files from template_test.php
				foreach(array_reverse($_POST["FILES"], true) as $file)
				{
					if(
						is_array($file)
						&& strlen($file["tmp_name"]) > 0
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
			if(strlen($file["name"])>0 and intval($file["size"])>0)
			{
				$resultInsertAttachFile = false;
				$file["MODULE_ID"] = "main";
				$fid = intval(CFile::SaveFile($file, "main", true));
				if($fid > 0)
				{
					$resultAddAttachFile = \Bitrix\Main\Mail\Internal\EventMessageAttachmentTable::add(array(
						'EVENT_MESSAGE_ID' => $ID,
						'FILE_ID' => $fid
					));
					$resultInsertAttachFile = $resultAddAttachFile->isSuccess();
				}

				if(!$resultInsertAttachFile)
					break;
			}
		}
	
		if (strlen($save)>0)
		{
			if (!empty($_REQUEST["type"]))
				LocalRedirect(BX_ROOT."/admin/type_edit.php?EVENT_NAME=".$EVENT_NAME."&lang=".LANGUAGE_ID);
			else
				LocalRedirect(BX_ROOT."/admin/message_admin.php?lang=".LANGUAGE_ID);
		}
		else
			LocalRedirect(BX_ROOT."/admin/message_edit.php?lang=".LANGUAGE_ID."&ID=".$ID."&type=".$_REQUEST["type"]."&".$tabControl->ActiveTabParam());
	}
}

$arEventMessageFile = array();
$str_ACTIVE="Y";
$str_EVENT_NAME=$EVENT_NAME;
$em = CEventMessage::GetByID($ID);
if(!$em->ExtractEditFields("str_"))
{
	$ID=0;
}
else
{
	$str_LID = Array();
	$db_LID = CEventMessage::GetLang($ID);
	while($ar_LID = $db_LID->Fetch())
		$str_LID[] = $ar_LID["LID"];

	$attachmentFileDb = \Bitrix\Main\Mail\Internal\EventMessageAttachmentTable::getList(array(
		'select' => array('FILE_ID'),
		'filter' => array('EVENT_MESSAGE_ID' => $ID),
	));
	while($ar = $attachmentFileDb->fetch())
	{
		if($arFileFetch = CFile::GetFileArray($ar['FILE_ID']))
			$arEventMessageFile[] = $arFileFetch;
	}
}

if($bVarsFromForm)
{
	$str_LID = $LID;
	$DB->InitTableVarsForEdit("b_event_message", "", "str_");
	$str_ADDITIONAL_FIELD = $ADDITIONAL_FIELD;
}

$arMailSiteTemplate = array();
$mailSiteTemplateDb = CSiteTemplate::GetList(null, array('TYPE' => 'mail'));
while($mailSiteTemplate = $mailSiteTemplateDb->GetNext())
	$arMailSiteTemplate[] = $mailSiteTemplate;


if(!$isUserHavePhpAccess)
{
	$str_MESSAGE = htmlspecialcharsbx(LPA::PrepareContent(htmlspecialcharsback($str_MESSAGE)));
}

if($ID>0 && $COPY_ID<=0)
	$APPLICATION->SetTitle(str_replace("#ID#", "$ID", GetMessage("EDIT_MESSAGE_TITLE")));
else
	$APPLICATION->SetTitle(GetMessage("NEW_MESSAGE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>" name="form1" enctype="multipart/form-data">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?echo LANG?>" />
<input type="hidden" name="ID" value="<?echo $ID?>" />
<input type="hidden" name="COPY_ID" value="<?echo $COPY_ID?>" />
<input type="hidden" name="type" value="<?echo htmlspecialcharsbx($_REQUEST["type"])?>" />
<script type="text/javascript" language="JavaScript">
<!--
var t=null;
function PutString(str, field)
{
	var bMessageHtmlEditorVisible = false;
	var messageHtmlEditor = window.BXHtmlEditor.Get('MESSAGE');
	if(messageHtmlEditor) bMessageHtmlEditorVisible = messageHtmlEditor.IsShown();


	if(!t && !bMessageHtmlEditorVisible) return;

	if(bMessageHtmlEditorVisible)
	{
		messageHtmlEditor.InsertHtml(str);
	}
	else if(t.name=="MESSAGE" || t.name=="EMAIL_FROM" || t.name=="EMAIL_TO" || t.name=="SUBJECT" || t.name=="BCC")
	{
		t.value+=str;
		BX.fireEvent(t, 'change');
	}
}


function PutAttachString(str)
{
	var bMessageHtmlEditorVisible = false;
	var messageHtmlEditor = window.BXHtmlEditor.Get('MESSAGE');
	if(messageHtmlEditor) bMessageHtmlEditorVisible = messageHtmlEditor.IsShown();


	if(!t && !bMessageHtmlEditorVisible) return;

	if(bMessageHtmlEditorVisible)
	{
		messageHtmlEditor.InsertHtml(str);
	}
	else if(t.name=="MESSAGE")
	{
		t.value+=str;
		BX.fireEvent(t, 'change');
	}
}
//-->
</script>
<?
$aMenu = array(
	array(
		"TEXT"	=> GetMessage("RECORD_LIST"),
		"LINK"	=> "/bitrix/admin/message_admin.php?lang=".LANGUAGE_ID."&set_default=Y",
		"TITLE"	=> GetMessage("RECORD_LIST_TITLE"),
		"ICON"	=> "btn_list"
	)
);

if (intval($ID)>0 && $COPY_ID<=0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");

	$aMenu[] = array(
		"TEXT"	=> GetMessage("MAIN_NEW_RECORD"),
		"LINK"	=> "/bitrix/admin/message_edit.php?lang=".LANGUAGE_ID,
		"TITLE"	=> GetMessage("MAIN_NEW_RECORD_TITLE"),
		"ICON"	=> "btn_new"
		);

	$aMenu[] = array(
		"TEXT"	=> GetMessage("MAIN_COPY_RECORD"),
		"LINK"	=> "/bitrix/admin/message_edit.php?lang=".LANGUAGE_ID.htmlspecialcharsbx("&COPY_ID=").$ID,
		"TITLE"	=> GetMessage("MAIN_COPY_RECORD_TITLE"),
		"ICON"	=> "btn_copy"
		);

	$aMenu[] = array(
		"TEXT"	=> GetMessage("MAIN_DELETE_RECORD"),
		"LINK"	=> "javascript:if(confirm('".GetMessage("MAIN_DELETE_RECORD_CONF")."')) window.location='/bitrix/admin/message_admin.php?ID=".$ID."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."&action=delete';",
		"TITLE"	=> GetMessage("MAIN_DELETE_RECORD_TITLE"),
		"ICON"	=> "btn_delete"
		);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("MAIN_ERROR_SAVING"), $e);
if($message)
	echo $message->Show();

if(strlen($strError)>0)
	CAdminMessage::ShowMessage(Array("MESSAGE"=>$strError, "HTML"=>true, "TYPE"=>"ERROR"));

$tabControl->Begin();

$tabControl->BeginNextTab();
?>
	<tr>
		<td><?echo GetMessage("EVENT_NAME")?></td>
		<td><?
			$event_type_ref = array();
			$rsType = CEventType::GetList(
				array(
					"LID"=>LANGUAGE_ID,
					"EVENT_TYPE" => \Bitrix\Main\Mail\Internal\EventTypeTable::TYPE_EMAIL),
				array("name"=>"asc")
			);
			while ($arType = $rsType->Fetch())
			{
				$arType["NAME_WITHOUT_EVENT_NAME"] = $arType["NAME"];
				$arType["NAME"] = $arType["NAME"]." [".$arType["EVENT_NAME"]."]";
				$event_type_ref[$arType["EVENT_NAME"]] = $arType;
			}

			if($ID>0 && $COPY_ID<=0)
			{
				$arType = $event_type_ref[$str_EVENT_NAME];
				$type_DESCRIPTION = htmlspecialcharsbx($arType["DESCRIPTION"]);
				$type_NAME = htmlspecialcharsbx($arType["NAME_WITHOUT_EVENT_NAME"]);
				?><input type="hidden" name="EVENT_NAME" value="<? echo $str_EVENT_NAME?>"><a href="type_edit.php?EVENT_NAME=<? echo $str_EVENT_NAME?>"><?echo $type_NAME?></a> [<? echo $str_EVENT_NAME?>]<?
			}
			else
			{
				$id_1st = false;
				?>
				<select name="EVENT_NAME" style="width:370px" onchange="window.location='message_edit.php?lang=<?=LANGUAGE_ID?>&EVENT_NAME='+this[this.selectedIndex].value">
				<?
				foreach($event_type_ref as $ev_name=>$arType):
					if($id_1st===false)
						$id_1st = $ev_name;
				?>
					<option value="<?=htmlspecialcharsbx($arType["EVENT_NAME"])?>"<?
					if($str_EVENT_NAME==$arType["EVENT_NAME"])
					{
						echo " selected";
						$id_1st = $ev_name;
										}
					?>><?=htmlspecialcharsbx($arType["NAME"])?></option>
				<?
				endforeach;
				?>
				</select>
				<?
				$type_DESCRIPTION = htmlspecialcharsbx($event_type_ref[$id_1st]["DESCRIPTION"]);
			}
		?></td>
	</tr>
	<?if($ID>0 && $COPY_ID<=0):?>
	<tr>
		<td width="40%"><?echo GetMessage('LAST_UPDATE')?></td>
		<td width="60%"><?echo $str_TIMESTAMP_X?></td>
	</tr>
	<? endif; ?>
	<tr>
		<td><label for="active"><?echo GetMessage('ACTIVE')?></label></td>
		<td><input type="checkbox" name="ACTIVE" id="active" value="Y"<?if($str_ACTIVE=="Y")echo " checked"?>></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td class="adm-detail-valign-top"><?echo GetMessage('LID')?></td>
		<td><?=CLang::SelectBoxMulti("LID", $str_LID);?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("main_mess_edit_lang")?></td>
		<td>
			<select name="LANGUAGE_ID">
				<option value=""><?echo GetMessage("main_mess_edit_lang_not_set")?></option>
				<?
				$languages = \Bitrix\Main\Localization\LanguageTable::getList(array(
					"filter" => array("=ACTIVE" => "Y"),
					"order" => array("SORT" => "ASC", "NAME" => "ASC")
				));
				?>
				<? while($language = $languages->fetch()): ?>
					<option value="<?=$language["LID"]?>"<? if($str_LANGUAGE_ID == $language["LID"]) echo " selected" ?>>
						<?=\Bitrix\Main\Text\HtmlFilter::encode($language["NAME"])?>
					</option>
				<? endwhile ?>
			</select>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("main_mess_edit_fields")?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><? echo GetMessage('MSG_EMAIL_FROM')?></td>
		<td><input type="text" name="EMAIL_FROM" size="50" maxlength="255" value="<?echo $str_EMAIL_FROM?>" onfocus="t=this">
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage('MSG_EMAIL_TO')?></td>
		<td><input type="text" name="EMAIL_TO" size="50" maxlength="255" value="<?echo $str_EMAIL_TO?>" onfocus="t=this"></td>
	</tr>

	<?
	$str_show_ext = '';
	$show_ext = ($str_CC!='' || $str_BCC!='' || $str_REPLY_TO!='' || $str_IN_REPLY_TO!='' || $str_PRIORITY!='' || !empty($str_ADDITIONAL_FIELD));
	if(!$show_ext):
		$str_show_ext = 'style="display:none;"';
		?>
		<script>
		function ShowExtH()
		{
			for(var i=1; i<12; i++)
			{
				if(!document.getElementById('msg_ext'+i)) break;

				try
				{
					document.getElementById('msg_ext'+i).style.display = 'table-row';
				}
				catch(e)
				{
					document.getElementById('msg_ext'+i).style.display = 'block';
				}
			}
			document.getElementById('msg_ext0').style.display = 'none';
		}
		</script>
		<tr id="msg_ext0">
			<td></td>
			<td><a href="javascript:void(0)" onclick="return ShowExtH()"><?echo GetMessage("MSG_EXT")?></a></td>
		</tr>
		<?
	endif;
	?>

	<tr id="msg_ext1" <?=$str_show_ext?>>
		<td><?echo GetMessage("MSG_CC")?></td>
		<td><input type="text" name="CC" size="50" maxlength="255" value="<?echo $str_CC?>" onfocus="t=this">
		</td>
	</tr>

	<tr id="msg_ext2" <?=$str_show_ext?>>
		<td><?echo GetMessage("MSG_BCC")?></td>
		<td><input type="text" name="BCC" size="50" value="<?echo $str_BCC?>" onfocus="t=this">
		</td>
	</tr>

	<tr id="msg_ext3" <?=$str_show_ext?>>
		<td><?echo GetMessage("MSG_REPLY_TO")?></td>
		<td><input type="text" name="REPLY_TO" size="50" maxlength="255" value="<?echo $str_REPLY_TO?>" onfocus="t=this">
		</td>
	</tr>

	<tr id="msg_ext4" <?=$str_show_ext?>>
		<td><?echo GetMessage("MSG_IN_REPLY_TO")?></td>
		<td><input type="text" name="IN_REPLY_TO" size="50" maxlength="255" value="<?echo $str_IN_REPLY_TO?>" onfocus="t=this">
		</td>
	</tr>

	<tr id="msg_ext5" <?=$str_show_ext?>>
		<td><?echo GetMessage("MSG_PRIORITY")?></td>
		<td>
		<input type="text" name="PRIORITY" id="MSG_PRIORITY" size="10" maxlength="255" value="<?echo $str_PRIORITY?>" onfocus="t=this">
		<select onchange="document.getElementById('MSG_PRIORITY').value=this.value">
			<option value=""></option>
			<option value="1 (Highest)"<?if($str_PRIORITY=='1 (Highest)')echo ' selected'?>><?echo GetMessage("MSG_PRIORITY_1")?></option>
			<option value="3 (Normal)"<?if($str_PRIORITY=='3 (Normal)')echo ' selected'?>><?echo GetMessage("MSG_PRIORITY_3")?></option>
			<option value="5 (Lowest)"<?if($str_PRIORITY=='5 (Lowest)')echo ' selected'?>><?echo GetMessage("MSG_PRIORITY_5")?></option>
		</select>
		</td>
	</tr>

	<?
		$msg_ext = 5;
		$arADDITIONAL_FIELD = array(
			array('NAME' => '', 'VALUE' => ''),
			array('NAME' => '', 'VALUE' => ''),
		);
		if(is_array($str_ADDITIONAL_FIELD))
			$arADDITIONAL_FIELD = array_merge($str_ADDITIONAL_FIELD, $arADDITIONAL_FIELD);

	?>
	<?foreach($arADDITIONAL_FIELD as $additionalField):?>
		<tr id="msg_ext<?=++$msg_ext?>" <?=$str_show_ext?>>
			<td>
				<input type="text" name="ADDITIONAL_FIELD[NAME][]" size="20" maxlength="255" value="<?echo htmlspecialcharsbx($additionalField['NAME'])?>" onfocus="t=this">:
			</td>
			<td>
				<input type="text" name="ADDITIONAL_FIELD[VALUE][]" size="55" maxlength="255" value="<?echo htmlspecialcharsbx($additionalField['VALUE'])?>" onfocus="t=this">
			</td>
		</tr>
	<?endforeach;?>


	<tr>
		<td><?echo GetMessage("SUBJECT")?></td>
		<td><input type="text" name="SUBJECT" size="50" maxlength="255" value="<?echo $str_SUBJECT?>" onfocus="t=this"></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?=GetMessage("MSG_BODY")?></td>
	</tr>
	<tr>
		<td><? echo GetMessage('MSG_SITE_TEMPLATE_ID')?></td>
		<td>
			<select name="SITE_TEMPLATE_ID">
				<option value=""></option>
				<?foreach($arMailSiteTemplate as $mailTemplate):?>
					<option value="<?=$mailTemplate['ID']?>" <?=($mailTemplate['ID']==$str_SITE_TEMPLATE_ID ? 'selected' : '')?>>[<?=$mailTemplate['ID']?>] <?=$mailTemplate['NAME']?></option>
				<?endforeach;?>
			</select>
		</td>
	</tr>

	<tr>
		<td colspan="2" align="center">
			<?CFileMan::AddHTMLEditorFrame(
				"MESSAGE",
				$str_MESSAGE,
				"BODY_TYPE",
				$str_BODY_TYPE,
				array(
					'height' => 450,
					'width' => '100%'
				),
				"N",
				0,
				"",
				"onfocus=\"t=this\"",
				false,
				!$isUserHavePhpAccess,
				false,
				array(
					//'saveEditorKey' => $IBLOCK_ID,
					//'site_template_type' => 'mail',
					'templateID' => $str_SITE_TEMPLATE_ID,
					'componentFilter' => array('TYPE' => 'mail'),
					'limit_php_access' => !$isUserHavePhpAccess
				)
			);?>
			<script type="text/javascript" language="JavaScript">
				BX.addCustomEvent('OnEditorInitedAfter', function(editor){editor.components.SetComponentIcludeMethod('EventMessageThemeCompiler::includeComponent'); });
			</script>
		</td>
	</tr>

	<?
	$arAttachedImagePlaceHolders = array();
	foreach($arEventMessageFile as $arFile)
	{
		if(substr($arFile['CONTENT_TYPE'], 0, 5) == 'image')
		{
			$arAttachedImagePlaceHolders[] = $arFile;
		}
	}
	?>
	<?if(count($arAttachedImagePlaceHolders)>0):?>
	<tr>
		<td align="left" colspan="2"><br><b><?=GetMessage("AVAILABLE_FIELDS_ATTACHMENT")?></b><br><br>
			<?foreach($arAttachedImagePlaceHolders as $arFile):?>
				<a title="<?=GetMessage("MAIN_INSERT")?>" href="javascript:PutAttachString('<img bxmailattachcid=\'<?=$arFile["ID"]?>\' src=\'<?=$arFile['SRC']?>\' width=\'<?=$arFile['WIDTH']?>\' height=\'<?=$arFile['HEIGHT']?>\'>');"><?=htmlspecialcharsbx($arFile['ORIGINAL_NAME'])?></a><br>
			<?endforeach;?>
		</td>
	</tr>
	<?endif;?>
	<?
	$str_def =
	"#DEFAULT_EMAIL_FROM# - ".GetMessage("MAIN_MESS_ED_DEF_EMAIL")."
	#SITE_NAME# - ".GetMessage("MAIN_MESS_ED_SITENAME")."
	#SERVER_NAME# - ".GetMessage("MAIN_MESS_ED_SERVERNAME")."
	";
	function ReplaceVars($str)
	{
		return preg_replace("/(#.+?#)/", "<a title='".GetMessage("MAIN_INSERT")."' href=\"javascript:PutString('\\1')\">\\1</a>", $str);
	}
	?>
	<tr>
		<td align="left" colspan="2"><br><b><?=GetMessage("AVAILABLE_FIELDS")?></b><br><br>
			<?echo ReplaceVars(nl2br(trim($type_DESCRIPTION)."\r\n".$str_def));?></td>
	</tr>
	
	<?
	//********************
	//Attachments
	//********************
	$tabControl->BeginNextTab();
	?>
	<tr class="heading">
		<td colspan="2"><?=GetMessage("ATTACHMENT_PRESET")?></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?=GetMessage("ATTACHMENT_PRESET_LOAD")?>:</td>
		<td>
			<?
			$arInputControlValues = array();
			foreach($arEventMessageFile as $arFile) $arInputControlValues["FILES[".$arFile["ID"]."]"] = $arFile["ID"];
			\Bitrix\Main\Loader::includeModule("fileman");
			echo CFileInput::ShowMultiple($arInputControlValues, "NEW_FILE[n#IND#]",
				array(
					"IMAGE" => "Y",
					"PATH" => "Y",
					"FILE_SIZE" => "Y",
					"DIMENSIONS" => "Y",
					"IMAGE_POPUP" => "Y",
				),
				false,
				array(
					'upload' => true,
					'medialib' => true,
					'file_dialog' => true,
					'cloud' => true,
					'del' => true,
					'description' => false,
				)
			);
			?>
		</td>
	</tr>


<?$tabControl->Buttons(array("disabled" => !$isAdmin, "back_url"=>"message_admin.php?lang=".LANGUAGE_ID));
$tabControl->End();
$tabControl->ShowWarnings("form1", $message);
?>
</form>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>