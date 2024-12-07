<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/prolog.php");

$WORKFLOW_RIGHT = $APPLICATION->GetGroupRight("workflow");
if ($WORKFLOW_RIGHT == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/include.php");
IncludeModuleLangFile(__FILE__);
define("HELP_FILE","workflow_list.php");
$fname = $_REQUEST["fname"];
$message = null;

/**************************************************************************
				Helper functions
***************************************************************************/
function CheckFields() // fields check
{
	global $DB, $FILENAME, $APPLICATION, $ID, $BODY, $USER, $SITE_ID, $STATUS_ID, $DOC_ROOT;
	$str = "";
	$arMsg = Array();
	$SCRIPT_FILE_TYPE = GetFileType($FILENAME);
	$FILENAME = trim($FILENAME);
	$FILENAME = "/".ltrim(_normalizePath($FILENAME), "/");
	$io = CBXVirtualIo::GetInstance();

	if ($FILENAME == '')
	{
		$arMsg[] = array(
			"id" => "FILENAME",
			"text" => GetMessage("FLOW_FORGOT_FILENAME"),
		);
	}
	elseif (!$io->ValidatePathString($FILENAME))
	{
		$arMsg[] = array(
			"id" => "FILENAME",
			"text" => GetMessage("FLOW_FILE_NAME_NOT_VALID"),
		);
	}
	elseif ($SCRIPT_FILE_TYPE!="SOURCE")
	{
		$arMsg[] = array(
			"id" => "FILENAME",
			"text" => GetMessage("FLOW_INCORRECT_FILETYPE"),
		);
	}
	else
	{
		$SITE_ID = CWorkflow::__CheckSite($SITE_ID);
		if(!$SITE_ID)
			$SITE_ID = CSite::GetSiteByFullPath($_SERVER['DOCUMENT_ROOT'].$FILENAME);

		if(!$USER->CanDoFileOperation('fm_edit_in_workflow', array($SITE_ID, $FILENAME)))
		{
			$s = str_replace("#FILENAME#","$FILENAME",GetMessage("FLOW_ACCESS_DENIED"));
			$arMsg[] = array(
				"id" => "FILENAME",
				"text" => $s.": ".GetMessage("FLOW_MIN_RIGHTS"),
			);
		}
		elseif(
			$STATUS_ID==1
			&& !(
				$USER->CanDoFileOperation('fm_edit_existent_file', array($SITE_ID, $FILENAME))
				&& $USER->CanDoFileOperation('fm_create_new_file', array($SITE_ID, $FILENAME))
			)
		)
		{
			$arMsg[] = array(
				"id" => "FILENAME",
				"text" => GetMessage("FLOW_ACCESS_DENIED_FOR_FILE_WRITE", array(
					"#FILENAME#" => $FILENAME,
				)),
			);
		}
		else
		{
			$z = CWorkflow::GetByFilename($FILENAME, $SITE_ID);
			if ($zr=$z->Fetch())
			{
				if ($zr["ID"]!=$ID && $zr["STATUS_ID"]!=1)
				{
					$arMsg[] = array(
						"id" => "FILENAME",
						"text" => str_replace("#FILENAME#", $FILENAME, GetMessage("FLOW_FILENAME_EXIST")),
					);
				}
			}
		}
	}

	if (!CWorkflow::IsAdmin())
	{
		$arGroups = $USER->GetUserGroupArray();
		if (!is_array($arGroups))
			$arGroups = array(2);
		$arFilter = array(
			"GROUP_ID" => $arGroups,
			"PERMISSION_TYPE_1" => 1,
			"ID_EXACT_MATCH" => "Y",
			"ID" => $STATUS_ID,
		);
		$rsStatuses = CWorkflowStatus::GetList("s_c_sort", "asc", $arFilter, null, array("ID"));
		if (!$rsStatuses->Fetch())
		{
			$arMsg[] = array(
				"id" => "STATUS_ID",
				"text" => GetMessage("FLOW_ERROR_WRONG_STATUS"),
			);
		}
	}

	$bIsPhp = IsPHP($BODY);

	if ($bIsPhp)
	{
		if ($USER->CanDoFileOperation('fm_lpa', Array($SITE_ID, $FILENAME)) && !$USER->CanDoOperation('edit_php'))
		{
			if (CModule::IncludeModule("fileman"))
			{
				$old_res = CFileman::ParseFileContent($APPLICATION->GetFileContent($DOC_ROOT.$FILENAME), true);
				$old_BODY = $old_res["CONTENT"];
				$BODY = CMain::ProcessLPA($BODY, $old_BODY);
			}
			else
			{
				$arMsg[] = array(
					"id" => "BODY",
					"text" => "Error! Fileman is not included!",
				);
			}
		}
		else if (!$USER->CanDoOperation('edit_php'))
		{
			$arMsg[] = array(
				"id" => "BODY",
				"text" => GetMessage("FLOW_PHP_IS_NOT_AVAILABLE"),
			);
		}
	}

	if(!empty($arMsg))
	{
		$e = new CAdminException($arMsg);
		$APPLICATION->ThrowException($e);
		return false;
	}

	return true;
}

/**************************************************************************
				GET | POST handlers
***************************************************************************/

$ID = intval($ID);
$STATUS_ID = intval($STATUS_ID);
$arExt = GetScriptFileExt();
$arTemplates = GetFileTemplates();
$arUploadedFiles = array();
$BODY_TYPE = ($BODY_TYPE=="text") ? "text" : "html";
$FILENAME = str_replace("\\", "/", $FILENAME);
$arContent = array();

$site = CWorkflow::__CheckSite($site);
$DOC_ROOT = CSite::GetSiteDocRoot($site);
$del_id = intval($del_id); // id of the record being deleted
if ($del_id > 0 && $WORKFLOW_RIGHT > "R" && check_bitrix_sessid())
{
	if (CWorkflow::IsAllowEdit($del_id, $locked_by, $date_lock))
	{
		CWorkflow::Delete($del_id);
		LocalRedirect("/bitrix/admin/workflow_list.php?lang=".LANGUAGE_ID);
	}
	else
	{
		if (intval($locked_by) > 0)
		{
			$str = str_replace("#DID#","$del_id",GetMessage("FLOW_DOCUMENT_LOCKED"));
			$str = str_replace("#ID#","$locked_by",$str);
			$str = str_replace("#DATE#","$date_lock",$str);
			$message = new CAdminMessage(Array("MESSAGE" => GetMessage("FLOW_ERROR"), "DETAILS" => $str, "TYPE"=>"ERROR"));
		}
		else
		{
			$str = str_replace("#ID#",$del_id,GetMessage("FLOW_DOCUMENT_IS_NOT_AVAILABLE"));
			$message = new CAdminMessage(Array("MESSAGE" => GetMessage("FLOW_ERROR"), "DETAILS" => $str, "TYPE"=>"ERROR"));
		}
	}
}

// when ID of the document is given
if($ID > 0)
{
	// check if it is exists in the database
	$z = $DB->Query("SELECT ID FROM b_workflow_document WHERE ID='$ID'");
	if (!($zr=$z->Fetch()))
	{
		if($fname <> '')
		{
			$ID = 0;
		}
		else
		{
			require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

			$aMenu = array(
				array(
					"ICON" => "btn_list",
					"TEXT" => GetMessage("FLOW_RECORDS_LIST"),
					"LINK" => "workflow_list.php?lang=".LANGUAGE_ID, //"&ID=".$ID
					"TITLE" => GetMessage("FLOW_RECORDS_LIST"),
				),
			);
			$context = new CAdminContextMenu($aMenu);
			$context->Show();

			CAdminMessage::ShowMessage(GetMessage("FLOW_DOCUMENT_NOT_FOUND"));

			require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
			die();
		}
	}
}

if ($ID > 0)
{
	// check if document is published
	$z = CWorkflow::GetStatus($ID);
	$zr = $z->Fetch();
	if (intval($zr["ID"])==1)
	{
		$message = new CAdminMessage(Array("MESSAGE" => GetMessage("FLOW_ERROR"), "DETAILS" => GetMessage("FLOW_DOCUMENT_IS_NOT_AVAILABLE"), "TYPE"=>"ERROR"));
	}
	else
	{
		// rights check
		if (!(CWorkflow::IsHaveEditRights($ID)))
		{
			$sDocTitle = str_replace("#ID#","$ID",GetMessage("FLOW_EDIT_RECORD"));
			$APPLICATION->SetTitle($sDocTitle);
			require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

			$aMenu = array(
				array(
					"ICON"	=> "btn_list",
					"TEXT"	=> GetMessage("FLOW_RECORDS_LIST"),
					"LINK"	=> "workflow_list.php?lang=".LANGUAGE_ID,//"&ID=".$ID
					"TITLE"	=> GetMessage("FLOW_RECORDS_LIST"),
				)
			);
			$context = new CAdminContextMenu($aMenu);
			$context->Show();

			CAdminMessage::ShowMessage(str_replace("#ID#","$ID",GetMessage("FLOW_NOT_ENOUGH_RIGHTS")));

			require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
			die();
		}

		// check if locked
		if (!(CWorkflow::IsAllowEdit($ID,$locked_by,$date_lock,"N")))
		{
			if (intval($locked_by)>0)
			{
				$str = str_replace("#ID#","$locked_by",GetMessage("FLOW_DOCUMENT_LOCKED"));
				$str = str_replace("#DATE#","$date_lock",$str);

				$message = new CAdminMessage(Array("MESSAGE" => GetMessage("FLOW_ERROR"), "DETAILS" => $str, "TYPE"=>"ERROR"));
			}
		}
	}
}

$aTabs = array();
if (intval($ID) > 0)
	$aTabs[] = array(
		"DIV" => "edit1",
		"TAB" => GetMessage("FLOW_EDIT_RECORD"),
		"ICON" => "workflow_edit",
		"TITLE" => GetMessage("FLOW_EDIT_RECORD_TIT"),
	);
else
	$aTabs[] = array(
		"DIV" => "edit1",
		"TAB" => GetMessage("FLOW_EDIT_RECORD"),
		"ICON" => "workflow_edit",
		"TITLE" => GetMessage("FLOW_NEW_RECORD"),
	);

$aTabs[] = array(
	"DIV" => "edit2",
	"TAB" => GetMessage("FLOW_UPLOADED_FILES"),
	"ICON" => "workflow_edit",
	"TITLE" => GetMessage("FLOW_UPLOADED_FILES_TITLE"),
);
$aTabs[] = array(
	"DIV" => "edit3",
	"TAB" => GetMessage("FLOW_COMMENTS"),
	"ICON" => "workflow_edit",
	"TITLE" => GetMessage("FLOW_COMMENTS_TITLE"),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);

// Save or Apply was clicked
if (
	($save <> '' || $apply <> '')
	&& $WORKFLOW_RIGHT > "R"
	&& $_SERVER["REQUEST_METHOD"] == "POST"
	&& check_bitrix_sessid()
)
{
	if (CheckFields())
	{
		$nums = intval($nums);
		if ($nums > 0)
		{
			for ($i = 1; $i <= $nums; $i++)
			{
				$arFile = $_FILES["file_".$i];
				if ($arFile["name"] == '' || $arFile["tmp_name"] == "none")
					continue;

				$arFile["name"] = GetFileName($arFile["name"]);
				$fname = ${"fname_".$i};
				if ($fname == '')
					$fname = $arFile["name"];

				$path = GetDirPath($FILENAME);
				$pathto = Rel2Abs($path, $fname);
				$ext = GetFileExtension($pathto);
				$io = CBXVirtualIo::GetInstance();
				if (!$USER->IsAdmin() && in_array($ext, $arExt))
				{
					$message = new CAdminMessage(array(
						"MESSAGE" => GetMessage("FLOW_ERROR"),
						"DETAILS" => GetMessage("FLOW_FILEUPLOAD_PHPERROR")." \"".$pathto."\"",
						"TYPE" => "ERROR",
					));
				}
				elseif (!$USER->CanDoFileOperation('fm_edit_in_workflow', array( $SITE_ID, $pathto)))
				{
					$message = new CAdminMessage(array(
						"MESSAGE" => GetMessage("FLOW_ERROR"),
						"DETAILS" => GetMessage("FLOW_FILEUPLOAD_ACCESS_DENIED")." \"".$pathto."\": ".GetMessage("FLOW_MIN_RIGHTS"),
						"TYPE" => "ERROR",
					));
				}
				elseif (!$io->ValidatePathString($pathto))
				{
					$message = new CAdminMessage(array(
						"MESSAGE" => GetMessage("FLOW_ERROR"),
						"DETAILS" => GetMessage("FLOW_FILE_NAME_NOT_VALID"),
						"TYPE" => "ERROR",
					));
				}
				else
				{
					$z = CWorkflow::GetFileByID($ID, $pathto);
					if ($zr = $z->Fetch())
					{
						$message = new CAdminMessage(array(
							"MESSAGE" => GetMessage("FLOW_ERROR"),
							"DETAILS" => str_replace("#FILE#", "$pathto", GetMessage("FLOW_FILE_ALREADY_EXIST")),
							"TYPE" => "ERROR",
						));
					}
					else
					{
						$temp_file = CWorkflow::GetUniqueFilename($pathto);
						$temp_dir = CWorkflow::GetTempDir();
						if (!file_exists($temp_dir))
							mkdir($temp_dir, BX_DIR_PERMISSIONS);

						$temp_path = $temp_dir.$temp_file;
						if (!copy($arFile["tmp_name"], $temp_path))
						{
							$message = new CAdminMessage(array(
								"MESSAGE" => GetMessage("FLOW_ERROR"),
								"DETAILS" => GetMessage("FLOW_FILEUPLOAD_FILE_CREATE_ERROR")." \"".$temp_path."\"",
								"TYPE" => "ERROR",
							));
						}
						else
						{
							$arFields = array(
								"DOCUMENT_ID" => ($ID > 0) ? $ID : "null",
								"TIMESTAMP_X" => $DB->GetNowFunction(),
								"MODIFIED_BY" => "'".$USER->GetID()."'",
								"TEMP_FILENAME" => "'".$DB->ForSql($temp_file, 255)."'",
								"FILENAME" => "'".$DB->ForSql($pathto, 255)."'",
								"FILESIZE" => intval($arFile["size"]),
							);
							$FILE_ID = $DB->Insert("b_workflow_file", $arFields);
							$arUploadedFiles[] = intval($FILE_ID);
						}
					}
				}
			}
		}

		if (!$message)
		{
			$BODY = WFToPath($BODY);
			$arFields = array(
				"MODIFIED_BY" => $USER->GetID(),
				"TITLE" => $TITLE,
				"FILENAME" => $FILENAME,
				"SITE_ID" => $SITE_ID,
				"BODY" => $BODY,
				"BODY_TYPE" => $BODY_TYPE,
				"DATE_LOCK" => false,
				"LOCKED_BY" => false,
				"STATUS_ID" => $STATUS_ID,
				"COMMENTS" => $COMMENTS,
			);
			if ($ID > 0)
			{
				CWorkflow::Update($arFields, $ID);
			}
			else
			{
				if (is_file($DOC_ROOT.$fname))
				{
					$filesrc = $APPLICATION->GetFileContent($DOC_ROOT.$FILENAME);
					$arContent = ParseFileContent($filesrc);
					$PROLOG = $arContent["PROLOG"];
					$EPILOG = $arContent["EPILOG"];
				}
				else
				{
					foreach ($arTemplates as $Template)
					{
						if ($Template["file"] == $template)
						{
							$filesrc = GetTemplateContent($Template["file"]);
							$arContent = ParseFileContent($filesrc);
							$PROLOG = $arContent["PROLOG"];
							$EPILOG = $arContent["EPILOG"];
							$found = "Y";
							break;
						}
					}
					if ($found != "Y")
					{
						$PROLOG = GetDefaultProlog($TITLE);
						$EPILOG = GetDefaultEpilog();
					}
				}
				$arFields["ENTERED_BY"] = $USER->GetID();
				$arFields["PROLOG"] = $PROLOG;
				$arFields["EPILOG"] = $EPILOG;
				$ID = CWorkflow::Insert($arFields);
			}

			CWorkflow::LinkFiles2Document($arUploadedFiles, $ID);
			if (is_array($del_files))
			{
				foreach ($del_files as $del_id)
					CWorkflow::CleanUpFiles($ID, $del_id);
			}

			$strError = "";
			CWorkflow::SetStatus($ID, $STATUS_ID, intval($OLD_STATUS_ID), false);
			$strError = "";

			if (!$message)
			{
				if ($STATUS_ID == 1)
					$strNote.= GetMessage("FLOW_PUBLISHED_SUCCESS");

				if ($save <> '' || $STATUS_ID == 1)
				{
					if ($return_url <> '')
						LocalRedirect($return_url);
					else
						LocalRedirect("/bitrix/admin/workflow_list.php?lang=".LANGUAGE_ID."&set_default=Y&strNote=".urlencode($strNote));
				}
				elseif ($apply <> '')
				{
					LocalRedirect("/bitrix/admin/workflow_edit.php?lang=".LANGUAGE_ID."&ID=".$ID."&strNote=".urlencode($strNote)."&".$tabControl->ActiveTabParam().($return_url <> ''? "&return_url=".urlencode($return_url) : ""));
				}
			}
		}
	}
	else
	{
		if ($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("FLOW_ERROR"), $e);
	}
}

if(($ID > 0) && !$message)
	CWorkflow::Lock($ID);

$arDocFiles = array();
ClearVars();
$workflow = CWorkflow::GetByID($ID);
if (!($workflow->ExtractFields()))
{
	$ID=0;
	if (is_file($DOC_ROOT.$fname))
	{
		$filesrc = $APPLICATION->GetFileContent($DOC_ROOT.$fname);
		$arContent = ParseFileContent($filesrc);
		$str_TITLE = $arContent["TITLE"];
		$str_BODY = $arContent["CONTENT"];
		$get_content = "Y";
	}
	elseif ($template <> '')
	{
		foreach($arTemplates as $Template)
		{
			if($Template["file"] == $template)
			{
				$filesrc = GetTemplateContent($Template["file"]);
				$arContent = ParseFileContent($filesrc);
				$str_TITLE = $arContent["TITLE"];
				$str_BODY = $arContent["CONTENT"];
				$get_content = "Y";
				break;
			}
		}
	}
	if ($get_content != "Y")
	{
		$filesrc = GetTemplateContent($arTemplates[0]["file"]);
		$arContent = ParseFileContent($filesrc);
		$str_TITLE = $arContent["TITLE"];
		$str_BODY = $arContent["CONTENT"];
	}
	$str_FILENAME = $fname <> ''? htmlspecialcharsbx($fname) : "/untitled.php";
	$str_SITE_ID = htmlspecialcharsbx($site);
	$str_BODY_TYPE = "html";
	$str_TITLE = htmlspecialcharsbx($str_TITLE);
}
else
{
	$doc_files = CWorkflow::GetFileList($ID);
	while ($zr = $doc_files->GetNext())
		$arDocFiles[] = $zr;
	$str_BODY = htmlspecialcharsback($str_BODY);
}

if ($message)
{
	$DB->InitTableVarsForEdit("b_workflow_document", "", "str_");
}

if ($USER->CanDoFileOperation('fm_lpa', Array($str_SITE_ID, $str_FILENAME)) && !$USER->CanDoOperation('edit_php'))
{
	$content = $str_BODY;
	$arPHP = PHPParser::ParseFile($content);
	$l = count($arPHP);

	if($l > 0)
	{
		$str_BODY = '';
		$end = 0;
		$php_count = 0;
		for ($n = 0; $n < $l; $n++)
		{
			$start = $arPHP[$n][0];
			$str_BODY .= mb_substr($content, $end, $start - $end);
			$end = $arPHP[$n][1];

			//Trim php tags
			$src = $arPHP[$n][2];
			if (mb_substr($src, 0, 5) == "<?"."php")
				$src = mb_substr($src, 5);
			else
				$src = mb_substr($src, 2);
			$src = mb_substr($src, 0, -2);

			//If it's Component 2, keep the php code. If it's component 1 or ordinary PHP - than replace code by #PHPXXXX#
			$comp2_begin = '$APPLICATION->INCLUDECOMPONENT(';
			if (mb_strtoupper(mb_substr($src, 0, mb_strlen($comp2_begin))) == $comp2_begin)
				$str_BODY .= $arPHP[$n][2];
			else
				$str_BODY .= '#PHP'.str_pad(++$php_count, 4, "0", STR_PAD_LEFT).'#';
		}
		$str_BODY .= mb_substr($content, $end);
	}
	else
	{
		$str_BODY = $content;
	}
}

if ($ID>0)
{
	if ($str_STATUS_ID>1)
		$sDocTitle = GetMessage("FLOW_EDIT_RECORD", array("#ID#" => $ID));
	else
		$sDocTitle = GetMessage("FLOW_VIEW_RECORD", array("#ID#" => $ID));
}
else
{
	$sDocTitle = GetMessage("FLOW_NEW_RECORD");
}

$APPLICATION->SetTitle($sDocTitle);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"ICON" => "btn_list",
		"TEXT" => GetMessage("FLOW_RECORDS_LIST"),
		"LINK" => "workflow_list.php?lang=".LANGUAGE_ID, //"&ID=".$ID
		"TITLE" => GetMessage("FLOW_RECORDS_LIST"),
	),
);
if (intval($ID) > 0)
{
	$aMenu[] = array(
		"SEPARATOR" => "Y",
	);
	$aMenu[] = array(
		"ICON" => "btn_new",
		"TEXT" => GetMessage("FLOW_NEW_DOCUMENT"),
		"LINK" => "workflow_edit.php?lang=".LANGUAGE_ID,
		"TITLE" => GetMessage("FLOW_NEW_DOCUMENT"),
	);
	if (intval($locked_by) <= 0 || intval($locked_by) == $USER->GetID())
	{
		$aMenu[] = array(
			"ICON" => "btn_delete",
			"TEXT" => GetMessage("FLOW_DELETE_DOCUMENT"),
			"LINK" => "javascript:if(confirm('".GetMessage("FLOW_DELETE_DOCUMENT_CONFIRM")."')) window.location='workflow_edit.php?del_id=".$ID."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';",
			"TITLE" => GetMessage("FLOW_DELETE_DOCUMENT"),
		);
	}
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($message)
	echo $message->Show();

CAdminMessage::ShowNote($strNote);

$tabControl->Begin();

?>
<script>
function NewFileName(ob, counter)
{
	var str_fname;
	var fname;
	var str_file = ob.value;
	var num = ob.name;
	num  = num.substr(num.lastIndexOf("_")+1);

	str_file = str_file.replace(/\\/g, '/');
	fname = str_file.substr(str_file.lastIndexOf("/")+1);
	document.getElementById("fname_"+num).value = fname;
	if(document.getElementById("nums").value==num)
	{
		num++;
		var tbl = document.getElementById("t");
		var cnt = tbl.rows.length;
		var oRow = tbl.insertRow(-1);
		var oCell = oRow.insertCell(-1);
		oCell.innerHTML = '<input type="text" name="fname_'+num+'" size="30" maxlength="255" value="" id="fname_'+num+'">';

		oCell = oRow.insertCell(-1);
		oCell.innerHTML = '<input type="file" name="file_'+num+'" size="30" maxlength="255" value="" onChange="NewFileName(this)" id="file_'+num+'">';

		document.getElementById("nums").value = num;

		BX.onCustomEvent('onAdminTabsChange');
		BX.adminPanel.modifyFormElements(tbl);
	}
}

function ShowFile(did, fname)
{
	width=650;
	height=500;
	window.open('/bitrix/admin/workflow_get_file.php?did='+did+ '&fname='+fname,'','scrollbars=yes,resizable=yes,width='+width+',height='+height+',left='+Math.floor((screen.width - width)/2)+',top='+Math.floor((screen.height - height)/5));
}
</script>

<form method="POST" name="form1" enctype="multipart/form-data">
<?=bitrix_sessid_post()?>
<input type="hidden" name="ID" value="<?=$ID?>">
<input type="hidden" name="OLD_STATUS_ID" value="<?=$str_STATUS_ID?>">
<input type="hidden" name="lang" value="<?=LANG?>">
<input type="hidden" name="return_url" value="<?echo htmlspecialcharsbx($return_url)?>">
<?

$tabControl->BeginNextTab();
?>
	<?if ($ID<=0 && !is_file($_SERVER["DOCUMENT_ROOT"].$fname)):?>
	<tr>
		<td><?=GetMessage("FLOW_TEMPLATE")?></td>
		<td><select name="template" onchange="window.location='/bitrix/admin/workflow_edit.php?lang=<?echo LANG?>&amp;fname=<?echo UrlEncode($fname)?>&amp;template='+escape(this[this.selectedIndex].value)"><?
		foreach($arTemplates as $Template)
		{
		?><option value="<?echo htmlspecialcharsbx($Template["file"])?>"<?if($template == $Template["file"])echo " selected"?>><?echo htmlspecialcharsbx($Template["name"])?></option><?
		}
		?></select></td>
	</tr>
	<?endif;?>
	<tr>
		<td width="40%"><?=GetMessage("FLOW_STATUS")?></td>
		<td width="60%"><?
		if ($str_STATUS_ID!=1 || $message) :
			if ($ID<=0)
			{
				$z = CWorkflowStatus::GetDropDownList("N", "desc", array("!ID" => 1));
				$zr = $z->Fetch();
				$str_STATUS_ID = $zr["REFERENCE_ID"];
			}
			echo SelectBox("STATUS_ID", CWorkflowStatus::GetDropDownList(), "", $str_STATUS_ID);
		else :
		?>[<a href="workflow_status_edit.php?lang=<?=LANG?>&amp;ID=<?=$str_STATUS_ID?>"><?=$str_STATUS_ID?></a>]&nbsp;<?=$str_STATUS_TITLE?><?
		endif;
		?></td>
	</tr>
	<?if($ID>0):?>
	<tr>
		<td><?=GetMessage("FLOW_DATE_ENTER")?></td>
		<td><?=$str_DATE_ENTER?>&nbsp;[<a href="user_edit.php?ID=<?=$str_ENTERED_BY?>&lang=<?=LANG?>" title="<?=GetMessage('FLOW_USER_ALT')?>"><?=$str_ENTERED_BY?></a>]&nbsp;<?echo$str_EUSER_NAME?></td>
	</tr>
	<tr>
		<td><?=GetMessage("FLOW_DATE_MODIFY")?></td>
		<td><?=$str_DATE_MODIFY?>&nbsp;[<a href="user_edit.php?ID=<?=$str_MODIFIED_BY?>&lang=<?=LANG?>" title="<?=GetMessage('FLOW_USER_ALT')?>"><?=$str_MODIFIED_BY?></a>]&nbsp;<? echo $str_MUSER_NAME?></td>
	</tr>
	<?	if ($str_LOCK_STATUS!="green"):?>
	<tr>
		<td><?=GetMessage("FLOW_DATE_LOCK")?>:</td>
		<td><?=$str_DATE_LOCK?>&nbsp;[<a href="user_edit.php?ID=<?=$str_LOCKED_BY?>&lang=<?=LANG?>" title="<?=GetMessage('FLOW_USER_ALT')?>"><?=$str_LOCKED_BY?></a>]&nbsp;<?echo $str_LUSER_NAME?>&nbsp;<?if ($str_LOCKED_BY==$USER->GetID()):?><span class="required">(!)</span><?endif;?></td>
	</tr>
	<?endif;?>
	<?endif;?>

	<?if(CSite::IsDistinctDocRoots()):?>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("FLOW_EDIT_SITE")?></td>
		<td><?=CSite::SelectBox("SITE_ID", $str_SITE_ID);?></td>
	</tr>
	<?endif?>
	<tr class="adm-detail-required-field">
		<td><?=GetMessage("FLOW_FULL_FILENAME")?></td>
		<td>
			<input type="text" id="FILENAME" name="FILENAME" size="30"  maxlength="255" value="<?=$str_FILENAME?>">
			<input type="button"  name="browse" value="..." OnClick="BtnClick()">
			<?
			CAdminFileDialog::ShowScript
			(
				Array(
					"event" => "BtnClick",
					"arResultDest" => array("FORM_NAME" => "form1", "FORM_ELEMENT_NAME" => "FILENAME"),
					"arPath" => array("SITE" => SITE_ID, "PATH" => GetDirPath($str_FILENAME)),
					"select" => 'F',// F - file only, D - folder only
					"operation" => 'S',// O - open, S - save
					"showUploadTab" => true,
					"showAddToMenuTab" => false,
					"allowAllFiles" => true,
					"SaveConfig" => true,
				)
			);
			?>
	</tr>
	<tr>
		<td><?=GetMessage("FLOW_TITLE")?></td>
		<td><input type="text" name="TITLE" maxlength="255" value="<?=$str_TITLE?>" style="width:60%"></td>
	</tr>
	<?
	if(COption::GetOptionString("workflow", "USE_HTML_EDIT", "Y")=="Y" && CModule::IncludeModule("fileman")):
	?>
	<tr>
		<td colspan="2" align="center"><?
		$limit_php_access = ($USER->CanDoFileOperation('fm_lpa', array($str_SITE_ID, $str_FILENAME)) && !$USER->CanDoOperation('edit_php'));
		$bWithoutPHP = !$USER->CanDoOperation('edit_php') && !$limit_php_access;

		CFileMan::AddHTMLEditorFrame(
			"BODY",
			$str_BODY,
			"BODY_TYPE",
			$str_BODY_TYPE,
			400,
			"Y",
			$ID,
			GetDirPath($str_FILENAME),
			"",
			false,
			$bWithoutPHP,
			false,
			Array('limit_php_access' => $limit_php_access)
		);

		?></td>
	</tr>
	<?
	else:
	?>
	<tr>
		<td colspan="2" align="center"><? echo InputType("radio","BODY_TYPE","text",$str_BODY_TYPE,false)?>&nbsp;<?echo GetMessage("FLOW_TEXT")?>/&nbsp;<?echo InputType("radio","BODY_TYPE","html",$str_BODY_TYPE,false)?>&nbsp;HTML&nbsp;<br><textarea name="BODY" style="width:100%" rows="30" wrap="VIRTUAL"><?echo $str_BODY?></textarea></td>
	</tr>
	<?
	endif;
	?>
<?$tabControl->BeginNextTab();?>
	<?
	if (!empty($arDocFiles)):
	?>
	<tr>
		<td colspan="2">
			<table border="0" cellspacing="0" cellpadding="0" width="100%" class="internal">
				<tr class="heading">
					<td>ID</td>
					<td><?echo GetMessage("FLOW_FILENAME")?></td>
					<td><?echo GetMessage("FLOW_SIZE")?></td>
					<td><?echo GetMessage("FLOW_FILE_TIMESTAMP")?></td>
					<td><?echo GetMessage("FLOW_UPLOADED_BY")?></td>
					<td><?echo GetMessage("FLOW_DEL")?></td>
				</tr>
				<?foreach ($arDocFiles as $zr) :?>
				<tr>
					<td><?=$zr["ID"]?></td>
					<td><a title="<?=GetMessage("FLOW_VIEW_IMAGE")?>" href="javascript:void(0)" OnClick="ShowFile(<?=$ID?>,'<?=$zr["FILENAME"]?>')"><?=$zr["FILENAME"]?></a><?
					$ext = GetFileExtension($zr["FILENAME"]);
					if($USER->IsAdmin() || !in_array($ext, $arExt)) :
						?>&nbsp;&nbsp;<a href="workflow_file_download.php?did=<?=$ID?>&amp;fname=<?echo $zr["FILENAME"]?>" title="<?=GetMessage("FLOW_DOWNLOAD_FILE")?>"><img onmouseover="this.src='/bitrix/images/workflow/download_file.gif'" onmouseout="this.src='/bitrix/images/workflow/download_file_t.gif'" src="/bitrix/images/workflow/download_file_t.gif" width="16" height="16" border=0></a><?
					endif;
					?></td>
					<td><?=$zr["FILESIZE"]?></td>
					<td><?=$zr["TIMESTAMP_X"]?></td>
					<td>[<a href="user_edit.php?ID=<?echo $zr["MODIFIED_BY"]?>&lang=<?=LANG?>" title="<?=GetMessage('FLOW_USER_ALT')?>"><?echo $zr["MODIFIED_BY"]?></a>]&nbsp;<?echo $zr["USER_NAME"]?></td>
					<td><input type="checkbox" name="del_files[]" value="<?=$zr["ID"]?>"></td>
				</tr>
				<?endforeach;?>
			</table>
		</td>
	</tr>
	<?endif;?>
	<tr>
		<td colspan=2>
			<table border="0" cellspacing="0" cellpadding="0" width="100%" class="internal">
				<tr class="heading">
					<td><?echo GetMessage("FLOW_FILEUPLOAD_NAME")?></td>
					<td><?echo GetMessage("FLOW_FILEUPLOAD_FILE")?></td>
				</tr>
				<?
				for($i=1; $i<=3; $i++):
				?>
				<tr>
					<td><input type="text" name="fname_<?=$i?>" size="30" maxlength="255" value="<?=htmlspecialcharsbx(${"fname_".$i})?>" id="fname_<?=$i?>" style="width:100%"></td>
					<td style="text-align:center"><input type="file" name="file_<?=$i?>" size="30" maxlength="255" value="" onChange="NewFileName(this, <?=$i?>)" id="file_<?=$i?>">
					</td>
				</tr>
				<?endfor?>
				<input type="hidden" name="nums" value="<?echo $i;?>" id="nums">
			</table>
		</td>
	</tr>
<?$tabControl->BeginNextTab();?>
	<tr>
		<td align="center" colspan="2"><textarea name="COMMENTS" rows="15" style="width:100%;"><?echo $str_COMMENTS?></textarea></td>
	</tr>
<?
$tabControl->Buttons(array(
	"disabled" => $WORKFLOW_RIGHT <= "R" || $str_LOCK_STATUS == "red",
	"back_url" => "workflow_list.php?lang=".LANGUAGE_ID,
));
?>
</form>
<?
$tabControl->End();
$tabControl->ShowWarnings("form1", $message);
?>
<?require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>