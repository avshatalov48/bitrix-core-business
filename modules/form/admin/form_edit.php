<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 - 2006 Bitrix           #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/prolog.php");
$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
if($FORM_RIGHT<="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

CModule::IncludeModule('form');

ClearVars();

IncludeModuleLangFile(__FILE__);
$err_mess = "File: ".__FILE__."<br>Line: ";
define("HELP_FILE","form_list.php");
$old_module_version = CForm::IsOldVersion();
$bSimple = (COption::GetOptionString("form", "SIMPLE", "Y") == "Y") ? true : false;

$bEditTemplate = $USER->CanDoOperation('edit_php');

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("FORM_PROP"), "ICON" => "form_edit", "TITLE" => GetMessage("FORM_PROP_TITLE")),
	array("DIV" => "edit2", "TAB" => GetMessage("FORM_DESC"), "ICON" => "form_edit", "TITLE" => GetMessage("FORM_DESC_TITLE")),
	);

if ($bEditTemplate)
	$aTabs[]=array("DIV" => "edit5", "TAB" => GetMessage("FORM_VISUAL"), "ICON" => "form_edit", "TITLE" => GetMessage("FORM_TPL_MAIN"));

$aTabs[]=array("DIV" => "edit7", "TAB" => GetMessage("FORM_RESTRICTIONS"), "ICON" => "form_edit", "TITLE" => GetMessage("FORM_RESTRICTIONS_TITLE"));
if (!$bSimple)
	$aTabs[]=array("DIV" => "edit3", "TAB" => GetMessage("FORM_TPL"), "ICON" => "form_edit", "TITLE" => GetMessage("FORM_TPL_TITLE"));
$aTabs[]=array("DIV" => "edit4", "TAB" => GetMessage("FORM_EVENTS"), "ICON" => "form_edit", "TITLE" => GetMessage("FORM_EVENTS_TITLE"));
$aTabs[]=array("DIV" => "editcrm", "TAB" => GetMessage("FORM_CRM"), "ICON" => "form_edit", "TITLE" => GetMessage("FORM_CRM_TITLE"));
$aTabs[]=array("DIV" => "edit6", "TAB" => GetMessage("FORM_ACCESS"), "ICON" => "form_edit", "TITLE" => GetMessage("FORM_RIGHTS"));

$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);
$message = null;
/***************************************************************************
							GET | POST processing
***************************************************************************/


$ID = intval($_REQUEST['ID']);
$copy_id = intval($_REQUEST['copy_id']);
$reset_id = intval($_REQUEST['reset_id']);
$strError = '';

if ($ID > 0)
{
	$F_RIGHT = CForm::GetPermission($ID);
	if ($F_RIGHT<25) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

// copying
if ($copy_id > 0 && check_bitrix_sessid() && $F_RIGHT >= 30)
{
	$new_id = CForm::Copy($copy_id);
	if (strlen($strError)<=0 && intval($new_id)>0)
	{
		LocalRedirect("/bitrix/admin/form_edit.php?ID=".$new_id."&lang=".LANGUAGE_ID);
	}
}

// cleaning
if ($reset_id > 0 && check_bitrix_sessid() && $F_RIGHT >= 30)
{
	CForm::Reset($reset_id);
	LocalRedirect("/bitrix/admin/form_edit.php?ID=".$reset_id."&lang=".LANGUAGE_ID);
}

$w = CGroup::GetList($v1="dropdown", $v2="asc", array("ADMIN"=>"N"));
$arGroups = array();
while ($wr=$w->Fetch())
{
	$arGroups[] = array(
		"ID" => $wr["ID"],
		"NAME" => "[<a title=\"".GetMessage("FORM_GROUP_EDIT")."\" href=\"/bitrix/admin/group_edit.php?ID=".intval($wr["ID"])."&lang=".LANGUAGE_ID."\">".intval($wr["ID"])."</a>] ".htmlspecialcharsbx($wr["NAME"]),
	);
}

$z = CLanguage::GetList($v1, $v2, array("ACTIVE" => "Y"));
$arFormMenuLang = array();
while ($zr=$z->Fetch())
{
	$arFormMenuLang[] = array("LID"=>$zr["LID"], "NAME"=>$zr["NAME"]);
}

$rs = CSite::GetList(($by="sort"), ($order="asc"));
$arrSites = array();
while ($ar = $rs->Fetch())
{
	if ($ar["DEF"]=="Y") $def_site_id = $ar["ID"];
	$arrSites[$ar["ID"]] = $ar;
}

if ((strlen($_REQUEST['save'])>0 || strlen($_REQUEST['apply'])>0) && $_SERVER['REQUEST_METHOD']=="POST" && ($F_RIGHT>=30 || $ID<=0) && check_bitrix_sessid())
{
	$arIMAGE_ID = $_FILES["IMAGE_ID"];
	$arIMAGE_ID["MODULE_ID"] = "form";
	$arIMAGE_ID["del"] = $_REQUEST["IMAGE_ID_del"];

	$SID = $_REQUEST['SID'];

	if ($bSimple && strlen($SID) <= 0)
	{
		$SID = "SIMPLE_FORM_".randString(8);
	}

	$RESTRICT_STATUS = $_REQUEST['RESTRICT_STATUS'];
	$RESTRICT_USER = intval($_REQUEST['RESTRICT_USER']);
	$RESTRICT_TIME = intval($_REQUEST['RESTRICT_TIME']);
	$RESTRICT_TIME_MULTIPLYER = intval($_REQUEST['RESTRICT_TIME_MULTIPLYER']);

	$RESTRICT_TIME *= $RESTRICT_TIME_MULTIPLYER;

	$arRestrictStatus = array();
	if (is_array($RESTRICT_STATUS))
	{
		foreach ($RESTRICT_STATUS as $key => $value)
		{
			$arRestrictStatus[] = intval($value);
		}
	}

	$arFields = array(
		"NAME"						=> $_REQUEST['NAME'],
		"SID"						=> $SID,
		"C_SORT"					=> $_REQUEST['C_SORT'],
		"BUTTON"					=> $_REQUEST['BUTTON'],
		"USE_CAPTCHA"				=> $_REQUEST['USE_CAPTCHA'] == "Y" ? "Y" : "N",
		"DESCRIPTION"				=> $_REQUEST['FORM_DESCRIPTION'],
		"DESCRIPTION_TYPE"			=> $_REQUEST['FORM_DESCRIPTION_TYPE'],
		"SHOW_TEMPLATE"				=> $_REQUEST['SHOW_TEMPLATE'],
		"SHOW_RESULT_TEMPLATE"		=> $_REQUEST['SHOW_RESULT_TEMPLATE'],
		"PRINT_RESULT_TEMPLATE"		=> $_REQUEST['PRINT_RESULT_TEMPLATE'],
		"EDIT_RESULT_TEMPLATE"		=> $_REQUEST['EDIT_RESULT_TEMPLATE'],
		"USE_RESTRICTIONS"			=> $_REQUEST['USE_RESTRICTIONS'] == "Y" ? "Y" : "N",
		"RESTRICT_USER"				=> $RESTRICT_USER,
		"RESTRICT_TIME"				=> $RESTRICT_TIME,
		"arRESTRICT_STATUS"			=> $arRestrictStatus,
		"STAT_EVENT1"				=> $_REQUEST['STAT_EVENT1'],
		"STAT_EVENT2"				=> $_REQUEST['STAT_EVENT2'],
		"STAT_EVENT3"				=> $_REQUEST['STAT_EVENT3'],
		"arIMAGE"					=> $arIMAGE_ID,
		"arSITE"					=> $_REQUEST['arSITE'],
		"arMAIL_TEMPLATE"			=> $_REQUEST['arMAIL_TEMPLATE'],
	);

	if ($bEditTemplate)
	{
		$arFields['FILTER_RESULT_TEMPLATE'] = $_REQUEST['FILTER_RESULT_TEMPLATE'];
		$arFields['TABLE_RESULT_TEMPLATE'] = $_REQUEST['TABLE_RESULT_TEMPLATE'];

		$FORM_TEMPLATE = $_REQUEST['FORM_TEMPLATE'];
		$USE_DEFAULT_TEMPLATE = $_REQUEST['USE_DEFAULT_TEMPLATE'] == "N" && strlen($FORM_TEMPLATE) > 0 ? "N" : "Y";

		$arFields["FORM_TEMPLATE"] = $FORM_TEMPLATE;
		$arFields["USE_DEFAULT_TEMPLATE"] = $USE_DEFAULT_TEMPLATE;
		$arFields['USE_CAPTCHA'] = $arFields['USE_CAPTCHA'] == "Y" && ($USE_DEFAULT_TEMPLATE == "Y" || $USE_DEFAULT_TEMPLATE == "N" && CForm::isCAPTCHAInTemplate($FORM_TEMPLATE)) ? "Y" : "N";
	}

	// menu
	$arFields["arMENU"] = array();
	foreach ($arFormMenuLang as $arrL)
	{
		$arFields["arMENU"][$arrL["LID"]] = $_REQUEST["MENU_".$arrL["LID"]];
	}

	// access rights
	$arFields["arGROUP"] = array();
	foreach ($arGroups as $arrG)
	{
		$arFields["arGROUP"][$arrG["ID"]] = $_REQUEST["PERMISSION_".$arrG["ID"]];
	}

	$res = intval(CForm::Set($arFields, $ID));

	if ($res>0)
	{
		if ($bEditTemplate && $USE_DEFAULT_TEMPLATE == "N")
		{
			// structure
			$FORM_STRUCTURE = $_REQUEST["FORM_STRUCTURE"];

			$arrFS = CheckSerializedData($FORM_STRUCTURE) ? unserialize($FORM_STRUCTURE) : array();

			if (CFormOutput::CheckTemplate($FORM_TEMPLATE, $arrFS))
			{
				$GLOBALS['CACHE_MANAGER']->ClearByTag('form_'.$res);
				foreach ($arrFS as $arQuestion)
				{
					$arQuestionFields = array(
						"FORM_ID" 	 => $res,
						"TITLE" 	 => $arQuestion["CAPTION_UNFORM"],
						"TITLE_TYPE" => $arQuestion["isHTMLCaption"] == "N" ? "text" : "html",
						"SID" 		 => $arQuestion["FIELD_SID"],
						"REQUIRED" 	 => $arQuestion["isRequired"] == "N" ? "N" : "Y",
						"IN_RESULTS_TABLE" => $arQuestion["inResultsTable"] == "Y" ? "Y" : "N",
						"IN_EXCEL_TABLE" => $arQuestion["inExcelTable"] == "Y" ? "Y" : "N",
						"ACTIVE"	 => CForm::isFieldInTemplate($arQuestion["FIELD_SID"], $FORM_TEMPLATE) ? "Y" : "N",
						'FILTER_TITLE' => $arQuestion['FILTER_TITLE'],
					);

					$FIELD_ID = $arQuestion["isNew"] == "Y" ? false : $arQuestion["ID"];

					$QID = CFormField::Set($arQuestionFields, $FIELD_ID, 'Y', 'N');

					if ($QID)
					{
						foreach ($arQuestion["structure"] as $arAnswer)
						{
							if (strlen($arAnswer["MESSAGE"]) <= 0)
							{
								if (
									$arAnswer['ANS_NEW'] != 'Y'
									&&
									in_array($arAnswer['FIELD_TYPE'], array('dropdown', 'multiselect', 'checkbox', 'radio'))
								)
								{
									CFormAnswer::Delete($arAnswer['ID'], $QID);
								}

								continue;
							}

							if(isset($arAnswer['DEFAULT']))
							{
								if ($arAnswer["FIELD_TYPE"] == "dropdown" || $arAnswer['FIELD_TYPE'] == "multiselect")
								{
									if ($arAnswer["DEFAULT"] == "Y")
										$arAnswer["FIELD_PARAM"] = "SELECTED";
									else
										$arAnswer["FIELD_PARAM"] = "";
								}

								if ($arAnswer["FIELD_TYPE"] == "checkbox" || $arAnswer['FIELD_TYPE'] == "radio")
								{
									if ($arAnswer["DEFAULT"] == "Y")
										$arAnswer["FIELD_PARAM"] = "CHECKED";
									else
										$arAnswer["FIELD_PARAM"] = "";
								}
							}

							$arAnswerFields = array(
								"FIELD_ID" 	=> $QID,
								"MESSAGE" 	=> $arAnswer["MESSAGE"],
								"C_SORT" 	=> $arAnswer["C_SORT"],
								"ACTIVE" 	=> $arAnswer["ACTIVE"],
								"VALUE" 	=> $arAnswer["VALUE"],

								"FIELD_TYPE" 	=> $arAnswer["FIELD_TYPE"],
								"FIELD_WIDTH" 	=> $arAnswer["FIELD_WIDTH"],
								"FIELD_HEIGHT" 	=> $arAnswer["FIELD_HEIGHT"],
								"FIELD_PARAM" 	=> $arAnswer["FIELD_PARAM"],
							);

							$ANS_ID = $arAnswer["ANS_NEW"] == "Y" ? false : $arAnswer["ID"];
							CFormAnswer::Set($arAnswerFields, $ANS_ID);
						}
					}
				}
			}
		}

		if ($bSimple)
		{
			// mail template
			$arr = CForm::GetTemplateList("MAIL","xxx",$res);
			if ($_REQUEST['USE_MAIL_TEMPLATE'] && count($arr['reference_id']) == 0)
					CForm::SetMailTemplate($res, "Y");
			elseif (!$_REQUEST['USE_MAIL_TEMPLATE'] && count($arr['reference_id']) > 0)
			{
				reset($arr['reference_id']);
				while (list($num,$tmp_id)=each($arr['reference_id']))
					CEventMessage::Delete($tmp_id);
			}
			$arr = CForm::GetTemplateList("MAIL","xxx",$res);

			$arFields['SID'] = "SIMPLE_FORM_$res";
			$arFields['arMAIL_TEMPLATE'] = $arr['reference_id'];

			CForm::Set($arFields, $res);

			// create default status
			if ($ID==0)
			{
				$arFields_status = array(
					"FORM_ID"		=> $res,
					"C_SORT"		=> 100,
					"ACTIVE"		=> "Y",
					"TITLE"			=> "DEFAULT",
					"DESCRIPTION"		=> "DEFAULT",
					"CSS"			=> "statusgreen",
					"DEFAULT_VALUE"		=> "Y",
					"arPERMISSION_VIEW"	=> array(0),
					"arPERMISSION_MOVE"	=> array(0),
					"arPERMISSION_EDIT"	=> array(0),
					"arPERMISSION_DELETE"	=> array(0),
					);
				CFormStatus::Set($arFields_status, 0);
			}
		}

		if (strlen($strError)<=0 && $ID > 0)
		{
			$arCrmParams = array(
				'CRM_ID' => $_REQUEST['CRM_ID'],
				'LINK_TYPE' => $_REQUEST['CRM_LINK_TYPE'],
				'CRM_FIELDS' => $_REQUEST['CRM_FIELD'],
				'FORM_FIELDS' => $_REQUEST['CRM_FORM_FIELD'],
			);

			CFormCrm::SetForm($ID, $arCrmParams);
		}

		$ID = $res;

		if (strlen($strError)<=0)
		{
			if (strlen($_REQUEST['save'])>0)
			{
				if (!empty($_REQUEST["back_url"])) LocalRedirect("/".ltrim($_REQUEST["back_url"], "/"));
				else LocalRedirect("/bitrix/admin/form_list.php?lang=".LANGUAGE_ID);

			}
			else LocalRedirect("/bitrix/admin/form_edit.php?ID=".$ID."&lang=".LANGUAGE_ID."&".$tabControl->ActiveTabParam().(!empty($_REQUEST["back_url"]) ? "&back_url=".urlencode($_REQUEST["back_url"]) : ""));

			exit();
		}
	}

	$DB->PrepareFields("b_form");

	$str_FORM_TEMPLATE = $FORM_TEMPLATE;
}


//$rsForm = CForm::GetByID($ID);
$arForm = CForm::GetByID_admin($ID, 'form');

if (!$arForm || !extract($arForm, EXTR_PREFIX_ALL, 'str'))
{
	$ID = 0;
	$str_STAT_EVENT1 = "form";
	$str_DESCRIPTION_TYPE = "text";
	$str_BUTTON = GetMessage("FORM_SAVE");
	$str_C_SORT = CForm::GetNextSort();
	$str_USE_CAPTCHA = "N";
	$str_USE_DEFAULT_TEMPLATE = "N";
	$str_USE_RESTRICTIONS = "N";
	$str_RESTRICT_USER = 0;
	$str_RESTRICT_TIME = 0;
	$arRESTRICT_STATUS = array();
}
else
{
	if (strlen($strError)<=0)
	{
		$z = CForm::GetMenuList(array("FORM_ID"=>$ID), "N");
		while ($zr = $z->Fetch()) ${"MENU_".$zr["LID"]} = $zr["MENU"];

		$arSITE = CForm::GetSiteArray($ID);
		$arMAIL_TEMPLATE = CForm::GetMailTemplateArray($ID);
		if (!is_set($str_FORM_TEMPLATE)) $str_FORM_TEMPLATE = CForm::GetFormTemplateByID($ID);

		$arRESTRICT_STATUS = explode(",", $str_RESTRICT_STATUS);
	}
}

if (strlen($strError)>0) $DB->InitTableVarsForEdit("b_form", "", "str_");

if ($ID>0)
{
	$sDocTitle = str_replace("#ID#", $ID, GetMessage("FORM_EDIT_RECORD"));
	$sDocTitle = str_replace("#NAME#", $str_NAME, $sDocTitle);
}
else $sDocTitle = GetMessage("FORM_NEW_RECORD");

$APPLICATION->SetTitle($sDocTitle);

if ($ID > 0)
{
	$txt = "(".htmlspecialcharsbx($arForm['SID']).")&nbsp;".htmlspecialcharsbx($str_NAME);
	$link = "form_edit.php?lang=".LANGUAGE_ID."&ID=".$ID;
	$adminChain->AddItem(array("TEXT"=>$txt, "LINK"=>$link));
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
/***************************************************************************
							HTML form
****************************************************************************/

if (!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1):

	if ($ID > 0):
		$context = new CAdminContextMenuList($arForm['ADMIN_MENU']);
		$context->Show();

		echo BeginNote('width="100%"');
?>
	<b><?=GetMessage("FORM_FORM_NAME")?></b>
	[<a title='<?=GetMessage("FORM_EDIT_FORM")?>' href='form_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$ID?>'><?=$ID?></a>]&nbsp;(<?=htmlspecialcharsbx($arForm["SID"])?>)&nbsp;<?=htmlspecialcharsbx($arForm["NAME"])?>
<?
		echo EndNote();
	endif;


	$aMenu = $ID > 0 ? array() : array(
		array(
			"TEXT"	=> GetMessage("FORM_LIST"),
			"TITLE"	=> GetMessage("FORM_RECORDS_LIST"),
			"ICON"	=> "btn_list",
			"LINK"	=> "/bitrix/admin/form_list.php?lang=".LANGUAGE_ID
		)
	);

	if ($ID>0 && (CForm::IsAdmin() || $F_RIGHT>=30))
	{
		if (count($aMenu) > 0)
			$aMenu[] = array("SEPARATOR"=>"Y");

		if (CForm::IsAdmin())
		{
			$aMenu[] = array(
				"TEXT"	=> GetMessage("FORM_NEW"),
				"TITLE"	=> GetMessage("FORM_CREATE"),
				"ICON"	=> "btn_new",
				"LINK"	=> "form_edit.php?lang=".LANGUAGE_ID,
				);

			$aMenu[] = array(
				"TEXT"	=> GetMessage("FORM_CP"),
				"TITLE"	=> GetMessage("FORM_COPY"),
				"ICON"	=> "btn_copy",
				"LINK"	=> "form_edit.php?copy_id=".$ID."&ID=".$ID."&lang=".LANGUAGE_ID."&".bitrix_sessid_get(),
				);
		}

		if ($F_RIGHT>=30)
		{
			$aMenu[] = array(
				"TEXT"	=> GetMessage("FORM_DELETE_RESULTS"),
				"TITLE"	=> GetMessage("FORM_DELETE_RESULTS_TITLE"),
				"ICON"	=> "btn_delete",
				"LINK"	=> "javascript:if(confirm('".GetMessage("FORM_CONFIRM_DELETE_RESULTS")."'))window.location='form_edit.php?ID=".$ID. "&reset_id=".$ID."&".bitrix_sessid_get()."&lang=".LANGUAGE_ID."';",
				);
		}

		if (CForm::IsAdmin())
		{
			$aMenu[] = array(
				"ICON"	=> "btn_delete",
				"TEXT"	=> GetMessage("FORM_DELETE_TEXT"),
				"TITLE"	=> GetMessage("FORM_DELETE_TITLE"),
				"LINK"	=> "javascript:if(confirm('".GetMessage("FORM_CONFIRM_DELETE")."'))window.location='form_list.php?action=delete&ID=".$ID. "&".bitrix_sessid_get()."&lang=".LANGUAGE_ID."';",
				);
		}
	}

	if (count($aMenu) > 0)
	{
		$context = new CAdminContextMenu($aMenu);
		$context->Show();
	}
endif; // (!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1)

$FORM = new CFormOutput();
//initialize&check form
$FORM->Init(array("WEB_FORM_ID"=>$ID), true);

if($strError)
{
	$aMsg=array();
	$arrErr = explode("<br>",$strError);
	reset($arrErr);
	while (list(,$err)=each($arrErr)) $aMsg[]['text']=$err;

	$e = new CAdminException($aMsg);
	$GLOBALS["APPLICATION"]->ThrowException($e);

	$message = new CAdminMessage(GetMessage("FORM_ERROR_SAVE"), $e);
	echo $message->Show();
}
echo ShowNote($strNote);

if ($bEditTemplate):
?>
<script>
function formSubmit()
{
	return oForm.serializeForm();
}
</script>
<?
endif;
?>
<form name="form1" method="POST" action="<?echo $APPLICATION->GetCurPage()?>" enctype="multipart/form-data" <?if ($bEditTemplate):?> onsubmit="return formSubmit();"<?endif;?>>
<?=bitrix_sessid_post()?>
<input type="hidden" name="ID" value=<?=$ID?> />
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>" />
<input type="hidden" name="FORM_STRUCTURE" value="" />
<?
$tabControl->Begin();
?>
<?
//********************
//General Tab
//********************
$tabControl->BeginNextTab();
?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?=GetMessage("FORM_NAME")?></td>
		<td width="60%"><input type="text" name="NAME" size="60" maxlength="255" value="<?=htmlspecialcharsbx($str_NAME)?>"></td>
	</tr>
	<?if (!$bSimple):?>
	<tr class="adm-detail-required-field">
		<td><?=GetMessage("FORM_SID")?></td>
		<td><input onchange="javascript:set_event2()" type="text" name="SID" size="30" maxlength="50" value="<?=htmlspecialcharsbx($str_SID)?>"></td>
	</tr>
	<?endif;?>
	<tr>
		<td><?=GetMessage("FORM_C_SORT")?></td>
		<td><input type="text" name="C_SORT" size="5" maxlength="18" value="<?echo intval($str_C_SORT)?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("FORM_MENU")?></td>
		<td>
			<table border="0" cellspacing="1" cellpadding="2" style="width: 0%;"><?
				reset($arFormMenuLang);
				foreach ($arFormMenuLang as $arrL):
				?>
				<tr>
					<td width="0%" nowrap><?=$arrL["NAME"]?></td>
					<td><input type="text" name="MENU_<?=htmlspecialcharsbx($arrL["LID"], ENT_QUOTES)?>" size="30" value="<?=htmlspecialcharsex(${"MENU_".htmlspecialcharsbx($arrL["LID"], ENT_QUOTES)})?>"></td>
				</tr>
				<? endforeach; ?>
			</table></td>
	</tr>
	<tr>
		<td valign=top><?=GetMessage("FORM_SITE_CAPTION")?></td>
		<td>
			<div class="adm-list">


		<?
		reset($arrSites);
		while(list($sid, $arrS) = each($arrSites)):
			$checked = ((is_array($arSITE) && in_array($sid, $arSITE)) || ($ID<=0 && $def_site_id==$sid)) ? "checked" : "";
			?>
			<div class="adm-list-item">
				<div class="adm-list-control"><input type="checkbox" name="arSITE[]" value="<?=htmlspecialcharsbx($sid)?>" id="<?=htmlspecialcharsbx($sid)?>" <?=$checked?>></div>
				<div class="adm-list-label"><label for="<?=htmlspecialcharsbx($sid)?>"><?echo "[<a class=tablebodylink href='/bitrix/admin/site_edit.php?LID=".htmlspecialcharsbx($sid)."&lang=".LANGUAGE_ID."'>".htmlspecialcharsbx($sid)."</a>]&nbsp;".htmlspecialcharsbx($arrS["NAME"])?></label></div>
			</div>
			<?
		endwhile;
		?></div></td>
	</tr>
<?
	if ($bSimple)
	{
		$arr = CForm::GetTemplateList("MAIL","xxx",$ID);
		if (count($arr['reference_id']) > 0)
		{
			$str_USE_MAIL = 'checked OnClick="template_warn()"';
?>
<script type="text/javascript">
function template_warn()
{
	if (document.getElementById('mail_check').checked==false)
		alert('<?=GetMessage("FORM_SAMPLES_WARN")?>');
}
</script>
<?
		}
		else
			$str_USE_MAIL = '';
?>
	<tr>
		<td><?=GetMessage("FORM_SEND_RESULTS")?></td>
		<td>
			<input type="checkbox" id="mail_check" name="USE_MAIL_TEMPLATE" <?=$str_USE_MAIL?>>
			[<a href="/bitrix/admin/message_admin.php?find_type_id=FORM_FILLING_<?=$str_SID?>&set_filter=Y"><?echo GetMessage("FORM_VIEW_TEMPLATE_LIST")?></a>]
		</td>
	</tr>
<?
	}
?>
	<tr>
		<td><?=GetMessage("FORM_BUTTON")?></td>
		<td><input type="text" name="BUTTON" size="30" maxlength="255" value="<?=htmlspecialcharsbx($str_BUTTON)?>"></td>
	</tr>

	<tr>
		<td><?=GetMessage("FORM_USE_CAPTCHA")?></td>
		<td><?echo InputType("checkbox", "USE_CAPTCHA", "Y", $str_USE_CAPTCHA, false); ?></td>
	</tr>
<?
//********************
//Descr Tab
//********************
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="40%"><?=GetMessage("FORM_IMAGE")?></td>
		<td width="60%"><?
			echo CFile::InputFile("IMAGE_ID", 20, $str_IMAGE_ID);
			if (!is_array($str_IMAGE_ID) && strlen($str_IMAGE_ID)>0 || is_array($str_IMAGE_ID) && count($str_IMAGE_ID) > 0):
				?><br><?
				echo CFile::ShowImage($str_IMAGE_ID, 200, 200, "border=0", "", true);
			endif;
			?></td>
	</tr>
	<?
	if(COption::GetOptionString("form", "USE_HTML_EDIT")=="Y" && CModule::IncludeModule("fileman")):?>
	<tr>
		<td align="center" colspan="2">
		<?
		CFileMan::AddHTMLEditorFrame(
			"FORM_DESCRIPTION",
			$str_DESCRIPTION,
			"FORM_DESCRIPTION_TYPE",
			$str_DESCRIPTION_TYPE,
			array(
				'height' => 450,
				'width' => '100%'
			)
		);
		?></td>
	</tr>
	<?else:?>
	<tr>
		<td align="center" colspan="2"><? echo InputType("radio","FORM_DESCRIPTION_TYPE","text",$str_DESCRIPTION_TYPE,false)?>&nbsp;<?echo GetMessage("FORM_TEXT")?>/&nbsp;<? echo InputType("radio","FORM_DESCRIPTION_TYPE","html",$str_DESCRIPTION_TYPE,false)?>HTML</td>
	</tr>
	<tr>
		<td align="center" colspan="2"><textarea name="FORM_DESCRIPTION" style="width:100%" rows="23"><?echo $str_DESCRIPTION?></textarea></td>
	</tr>
	<?endif;?>

<?
//********************
//Main Template Tab Tab
//********************
if ($bEditTemplate):
	$tabControl->BeginNextTab();

	if ($str_USE_DEFAULT_TEMPLATE != "N") $str_USE_DEFAULT_TEMPLATE = "Y";
?>
	<tr>
		<td colspan="2">
			<input type="radio" id="USE_DEFAULT_TEMPLATE_Y" name="USE_DEFAULT_TEMPLATE" value="Y" <?=$str_USE_DEFAULT_TEMPLATE == "Y" ? "CHECKED" : ""?> onclick="BX.hide(BX('form_tpl_editor'))" /> <label for="USE_DEFAULT_TEMPLATE_Y"><?=GetMessage("FORM_USE_DEFAULT_TEMPLATE")?></label><br />
			<input type="radio" id="USE_DEFAULT_TEMPLATE_N" name="USE_DEFAULT_TEMPLATE" value="N" <?=$str_USE_DEFAULT_TEMPLATE == "N" ? "CHECKED" : ""?> onclick="BX.show(BX('form_tpl_editor'))" /> <label for="USE_DEFAULT_TEMPLATE_N"><?=GetMessage("FORM_USE_CUSTOM_TEMPLATE")?></label>
		</td>
	</tr>
<?
	if(COption::GetOptionString("form", "USE_HTML_EDIT")=="Y" && CModule::IncludeModule("fileman")):
?>
<script>
var _global_newinput_counter = 0;
var _global_newanswer_counter = 0;
var _global_BX_UTF = <?if (defined('BX_UTF') && BX_UTF === true):?>true<?else:?>false<?endif?>;
</script><script src="/bitrix/js/form/form_info.js?<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/form/form_info.js')?>"></script><script>
var arrInputObjects = [];

<?
if (!empty($strError))
{
	echo CFormOutput::PrepareFormData($arrFS, $i);
}
else
{
	$i = 0;
	foreach ($FORM->arQuestions as $FIELD_SID => $arQuestion)
	{
		if ($arQuestion["ADDITIONAL"] == "Y") continue;

		?>
arrInputObjects[<?=$i++?>] = new CFormAnswer(
	'<?=$FIELD_SID?>',
	'<?=CUtil::JSEscape(htmlspecialcharsbx($FORM->__admin_ShowInputCaption($FIELD_SID, "tablebodytext", true)))?><?=($arQuestion['ACTIVE'] == 'N' ? ' ('.GetMessage('F_QUESTION_INACTIVE').')' : '')?>',
	'<?=($FORM->arQuestions[$FIELD_SID]["TITLE_TYPE"]=="html" ? "Y" : "N")?>',
	'<?=CUtil::JSEscape($FORM->__admin_ShowInputCaption($FIELD_SID, "tablebodytext", true))?>',
	'<?=($FORM->arQuestions[$FIELD_SID]["REQUIRED"]=="Y" ? "Y" : "N")?>',
	'<?=$FORM->__admin_GetInputType($FIELD_SID);?>',
	<?=$FORM->__admin_GetInputAnswersStructure($FIELD_SID);?>,
	false,
	<?=$arQuestion["ID"]?>,
	'<?=($FORM->arQuestions[$FIELD_SID]["IN_RESULTS_TABLE"]=="Y" ? "Y" : "N")?>',
	'<?=($FORM->arQuestions[$FIELD_SID]["IN_EXCEL_TABLE"]=="Y" ? "Y" : "N")?>'
);

<?
	}
}
?>

var __arr_input_types = ['text', 'textarea', 'radio', 'checkbox', 'dropdown', 'multiselect', 'date', 'image', 'file', 'email', 'url', 'password'<?if (!$bSimple):?>, 'hidden'<?endif;?>];
var __arr_input_types_titles = ['<?=GetMessage('F_TYPES_TEXT')?>', '<?=GetMessage('F_TYPES_TEXTAREA')?>', '<?=GetMessage('F_TYPES_RADIO')?>', '<?=GetMessage('F_TYPES_CHECKBOX')?>', '<?=GetMessage('F_TYPES_DROPDOWN')?>', '<?=GetMessage('F_TYPES_MULTISELECT')?>', '<?=GetMessage('F_TYPES_DATE')?>', '<?=GetMessage('F_TYPES_IMAGE')?>', '<?=GetMessage('F_TYPES_FILE')?>', '<?=GetMessage('F_TYPES_EMAIL')?>', '<?=GetMessage('F_TYPES_URL')?>', '<?=GetMessage('F_TYPES_PASSWORD')?>'<?if (!$bSimple):?>, '<?=GetMessage('F_TYPES_HIDDEN')?>'<?endif;?>];

var __arr_api_methods = ['ShowFormTitle', 'ShowFormDescription', 'ShowFormErrors', 'ShowFormNote', 'ShowFormImage', 'ShowInputCaption', 'ShowRequired', 'ShowDateFormat', 'ShowInputCaptionImage', 'ShowCaptcha', 'ShowCaptchaField', 'ShowCaptchaImage', 'ShowSubmitButton', 'ShowApplyButton', 'ShowResetButton', 'ShowResultStatus', 'ShowResultStatusForm'];

var __arr_api_methods_params = {
	ShowFormTitle:['CSS_STYLE'],
	ShowFormDescription:['CSS_STYLE'],
	ShowFormErrors:[],
	ShowFormNote:[],
	ShowFormImage:['ALIGN', 'MAX_HEIGHT', 'MAX_WIDTH', 'ENLARGE_SHOW', 'ENLARGE_TITLE', 'HSPACE', 'VSPACE', 'BORDER'],
	ShowInputCaption:['FIELD_SID', 'CSS_STYLE'],
	ShowRequired:[],
	ShowDateFormat:['CSS_STYLE'],
	ShowInputCaptionImage:['FIELD_SID', 'ALIGN', 'MAX_HEIGHT', 'MAX_WIDTH', 'ENLARGE_SHOW', 'ENLARGE_TITLE', 'HSPACE', 'VSPACE', 'BORDER'],
	ShowCaptcha:[],
	ShowCaptchaField:[],
	ShowCaptchaImage:[],
	ShowSubmitButton:['CAPTION', 'CSS_STYLE'],
	ShowApplyButton:['CAPTION', 'CSS_STYLE'],
	ShowResetButton:['CAPTION', 'CSS_STYLE'],
	ShowResultStatus:['NOT_SHOW_CSS'],
	ShowResultStatusForm:[]
};

__arr_api_methods_params_captions = {
	CSS_STYLE:'<?=GetMessageJS('FORM_API_PARAMS_CAPTIONS_CSS_STYLE');?>',
	ALIGN:'<?=GetMessageJS('FORM_API_PARAMS_CAPTIONS_ALIGN');?>',
	MAX_HEIGHT:'<?=GetMessageJS('FORM_API_PARAMS_CAPTIONS_MAX_HEIGHT');?>',
	MAX_WIDTH:'<?=GetMessageJS('FORM_API_PARAMS_CAPTIONS_MAX_WIDTH');?>',
	ENLARGE_SHOW:'<?=GetMessageJS('FORM_API_PARAMS_CAPTIONS_ENLARGE_SHOW');?>',
	ENLARGE_TITLE:'<?=GetMessageJS('FORM_API_PARAMS_CAPTIONS_ENLARGE_TITLE');?>',
	HSPACE:'<?=GetMessageJS('FORM_API_PARAMS_CAPTIONS_HSPACE');?>',
	VSPACE:'<?=GetMessageJS('FORM_API_PARAMS_CAPTIONS_VSPACE');?>',
	BORDER:'<?=GetMessageJS('FORM_API_PARAMS_CAPTIONS_BORDER');?>',
	FIELD_SID:'<?=GetMessageJS('FORM_API_PARAMS_CAPTIONS_FIELD_SID');?>',
	CAPTION:'<?=GetMessageJS('FORM_API_PARAMS_CAPTIONS_CAPTION');?>',
	NOT_SHOW_CSS: '<?=GetMessageJS('FORM_API_PARAMS_CAPTIONS_NOT_SHOW_CSS')?>'
}

var __arr_api_methods_title = ['<?=GetMessageJS('F_API_SHOWFORMTITLE')?>', '<?=GetMessageJS('F_API_SHOWFORMDESCRIPTION')?>', '<?=GetMessageJS('F_API_SHOWFORMERRORS')?>', '<?=GetMessageJS('F_API_SHOWFORMNOTE')?>', '<?=GetMessageJS('F_API_SHOWFORMIMAGE')?>', '<?=GetMessageJS('F_API_SHOWINPUTCAPTION')?>', '<?=GetMessageJS('F_API_SHOWREQUIRED')?>', '<?=GetMessageJS('F_API_SHOWDATEFORMAT')?>', '<?=GetMessageJS('F_API_SHOWINPUTCAPTIONIMAGE')?>', '<?=GetMessageJS('F_API_SHOWCAPTCHA')?>', '<?=GetMessageJS('F_API_SHOWCAPTCHAFIELD')?>', '<?=GetMessageJS('F_API_SHOWCAPTCHAIMAGE')?>', '<?=GetMessageJS('F_API_SHOWSUBMITBUTTON')?>', '<?=GetMessageJS('F_API_SHOWAPPLYBUTTON')?>', '<?=GetMessageJS('F_API_SHOWRESETBUTTON')?>', '<?=GetMessageJS('F_API_SHOWRESULTSTATUS')?>', '<?=GetMessageJS('F_API_SHOWRESULTSTATUSFORM')?>'];

var __arr_field_titles = {FIELD_SID: '<?=GetMessageJS('FORM_TITLE_FIELD_SID');?>', CAPTION_UNFORM:'<?=GetMessageJS('FORM_TITLE_FIELD_CAPTION');?>', isHTMLCaption:'<?=GetMessageJS('FORM_TITLE_FIELD_CAPTION_HTML');?>', isRequired:'<?=GetMessageJS('FORM_TITLE_FIELD_REQUIRED');?>', type:'<?=GetMessageJS('FORM_TITLE_FIELD_TYPE');?>', structure:'<?=GetMessageJS('FORM_TITLE_FIELD_STRUCTURE');?>', inResultsTable:'<?=GetMessageJS('FORM_TITLE_FIELD_IN_RESULTS_TABLE');?>', inExcelTable:'<?=GetMessageJS('FORM_TITLE_FIELD_IN_EXCEL_TABLE');?>'};

var oForm = new CFormInfo(arrInputObjects);

var __arr_messages = {
	FORM_TASKBAR_CFORM: '<?=GetMessageJS('FORM_TASKBAR_CFORM')?>',
	FORM_TASKBAR_CFORMOUTPUT: '<?=GetMessageJS('FORM_TASKBAR_CFORMOUTPUT')?>',
	FORM_TASKBAR_API: '<?=GetMessageJS('FORM_TASKBAR_API')?>',
	FORM_METHOD_HAS_NO_PARAMS: '<?=GetMessageJS('FORM_METHOD_HAS_NO_PARAMS');?>',
	FORM_FIELD_WIDTH_VAL: '<?=GetMessageJS("FORM_FIELD_WIDTH_VAL")?>',
	FORM_FIELD_HEIGHT_VAL: '<?=GetMessageJS("FORM_FIELD_HEIGHT_VAL")?>',
	FORM_ANSWER_VAL: '<?=GetMessageJS("FORM_ANSWER_VAL")?>',
	FORM_SORT_VAL: '<?=GetMessageJS("FORM_SORT_VAL")?>',
	FORM_DEF_VAL: '<?=GetMessageJS("FORM_DEF_VAL")?>',
	FORM_FIELD_DEF_VAL: '<?=GetMessageJS("FORM_FIELD_DEF_VAL")?>',
	FORM_FIELD_SIZE_VAL: '<?=GetMessageJS("FORM_FIELD_SIZE_VAL")?>',
	FORM_FIELD_MULTIPLE_WARNING: '<?=GetMessageJS("FORM_FIELD_MULTIPLE_WARNING")?>'
}
</script><script src="/bitrix/js/form/form_taskbar.js?<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/form/form_taskbar.js')?>"></script>
	<tr>
		<td colspan="2"><div id="form_tpl_editor" style="display: <?=$str_USE_DEFAULT_TEMPLATE == "Y" ? "none" : "block"?>;">
<?
		$site = is_array($arSITE) ? $arSITE[0] : LANG;
		$arTplList = CSite::GetTemplateList($site);
		$tpl = "";
		while ($ar = $arTplList->Fetch())
		{
			if (strlen($tpl) == 0) $tpl = $ar["TEMPLATE"];
			if (strlen(trim($ar["CONDITION"])) == 0)
			{
				$tpl = $ar["TEMPLATE"];
				break;
			}
		}

		CFileMan::ShowHTMLEditControl(
			"FORM_TEMPLATE",
			htmlspecialcharsback($str_FORM_TEMPLATE),
			array(
				"site" => $arSITE[0],
				"templateID" => $tpl,
				"bUseOnlyDefinedStyles"=>COption::GetOptionString("fileman", "show_untitled_styles", "N")!="Y",
				"bWithoutPHP"=>false,
				"arToolbars"=>Array("standart", "style", "formating", "source", "template", "table"),
				"arTaskbars"=>Array("BXFormElementsTaskbar", "BXPropertiesTaskbar"),
				"toolbarConfig" => CFileman::GetEditorToolbarConfig("form_edit".(defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1 ? "_public" : "")),
				"sBackUrl" => "",
				"fullscreen" => false,
				'width' => '100%',
				'height' => '500',
				'use_editor_3' => 'N'
			)
		);

		?>
<script>
oBXEditorUtils.addPHPParser(oForm.PHPParser);
oBXEditorUtils.addTaskBar('BXFormElementsTaskbar', 2, "<?=GetMessageJS('FORM_TASKBARSET_TITLE')?>", []);
if (window.arButtons['Optimize'])
	arButtons['Optimize'][1].hideCondition = function(pMainObj){return pMainObj.name == "FORM_TEMPLATE";}
</script>
		</div></td>
	</tr>
	<?else:?>
	<tr>
		<td align="center" colspan="2"><div id="form_tpl_editor" style="display: <?=$str_USE_DEFAULT_TEMPLATE == "Y" ? "none" : "block"?>;"><textarea name="FORM_TEMPLATE" style="width:100%" rows="23"><?echo $str_FORM_TEMPLATE?></textarea></div></td>
	</tr>
	<?endif;?>
<?
endif;
//********************
//Restrictions Tab
//********************
$tabControl->BeginNextTab();

$RESTRICT_TIME_MULTIPLYER = 1;

$arRestrictTimeMultiplyerTitle = array(1 => GetMessage('FORM_RESTRICT_TIME_SEC'), 60 => GetMessage('FORM_RESTRICT_TIME_MIN'), 3600 => GetMessage('FORM_RESTRICT_TIME_HOUR'), 86400 => GetMessage('FORM_RESTRICT_TIME_DAY'));
$arRestrictTimeMultiplyer = array_keys($arRestrictTimeMultiplyerTitle);

if (intval($str_RESTRICT_TIME) > 0)
{
	$str_RESTRICT_TIME = intval($str_RESTRICT_TIME);
	for ($i = count($arRestrictTimeMultiplyer)-1; $i>=0; $i--)
	{
		if ($str_RESTRICT_TIME < $arRestrictTimeMultiplyer[$i]) continue;
		if ($str_RESTRICT_TIME % $arRestrictTimeMultiplyer[$i] == 0)
		{
			$RESTRICT_TIME_MULTIPLYER = $arRestrictTimeMultiplyer[$i];
			$str_RESTRICT_TIME /= $RESTRICT_TIME_MULTIPLYER;
			break;
		}
	}
}
?>
	<script>
		function change_restrictions()
		{
			var use_rest = document.form1.USE_RESTRICTIONS.checked;
			if (use_rest)
			{
				document.form1.RESTRICT_USER.disabled = false;
				document.form1.RESTRICT_TIME.disabled = false;
				document.form1.RESTRICT_TIME_MULTIPLYER.disabled = false;
				<?
				if (!$bSimple && $ID > 0):
				?>
				document.getElementById('RESTRICT_STATUS').disabled = false;
				<?
				endif;
				?>
			}
			else
			{
				document.form1.RESTRICT_USER.disabled = true;
				document.form1.RESTRICT_TIME.disabled = true;
				document.form1.RESTRICT_TIME_MULTIPLYER.disabled = true;
				<?
				if (!$bSimple && $ID > 0):
				?>
				document.getElementById('RESTRICT_STATUS').disabled = true;
				<?
				endif;
				?>
			}
		}

		jsUtils.addEvent(window, 'load', change_restrictions);
	</script>
	<tr>
		<td colspan="2">
			<?php echo BeginNote().GetMessage('FORM_RESTRICTIONS_NOTE').EndNote(); ?>
			<input type="checkbox" onclick="change_restrictions()" name="USE_RESTRICTIONS" value="Y" id="USE_RESTRICTIONS"<?=$str_USE_RESTRICTIONS == "Y" ? "checked=\"checked\"" : ""?> />
			<label for="USE_RESTRICTIONS"><?=GetMessage("FORM_USE_RESTRICTIONS")?></label>
		</td>
	</tr>
	<tr>
		<td width="40%"><?=GetMessage('FORM_RESTRICT_USER')?>: </td>
		<td width="60%"><input type="text" name="RESTRICT_USER" value="<?=$str_RESTRICT_USER?>" size="10" <?=$str_USE_RESTRICTIONS == "Y" ? "" : "disabled=\"1\""?> /></td>
	</tr>
	<tr>
		<td><?=GetMessage('FORM_RESTRICT_TIME')?>: </td>
		<td><input type="text" name="RESTRICT_TIME" value="<?=$str_RESTRICT_TIME?>" size="10" <?=$str_USE_RESTRICTIONS == "Y" ? "" : "disabled=\"1\""?> />
			<select name="RESTRICT_TIME_MULTIPLYER">
	<?foreach ($arRestrictTimeMultiplyerTitle as $mult => $title):?>
			<option value="<?=$mult?>"<?=$mult == $RESTRICT_TIME_MULTIPLYER ? " selected=\"selected\"" : ""?>><?=$title?></option>
	<?endforeach?>
		</select></td>
	</tr>
	<?
if (!$bSimple && $ID > 0):
	$rsStatusList = CFormStatus::GetList($ID, $by="s_sort", $order="asc", array("ACTIVE" => "Y"), $is_filtered);
	?>
	<tr>
		<td><?=GetMessage('FORM_RESTRICT_STATUS')?>: </td>
		<td><select name="RESTRICT_STATUS[]" id="RESTRICT_STATUS" multiple="multiple" rows="3" <?=$str_USE_RESTRICTIONS == "Y" ? "" : "disabled=\"1\""?>>
			<?
	while ($arStatus = $rsStatusList->GetNext())
	{
		?>
		<option value="<?=$arStatus["ID"]?>" <?=is_array($arRESTRICT_STATUS) && in_array($arStatus["ID"], $arRESTRICT_STATUS) ? "selected=\"selected\"" : ""?>>[<?=$arStatus["ID"]?>] <?=$arStatus["TITLE"]?></option><?
	}
			?>
		</select></td>
	</tr>

	<?
endif;
	?>
<?

if (!$bSimple)
{
//********************
//Templates Tab
//********************
$tabControl->BeginNextTab();
?>
<script>
<!--
var bInProcess = false;

function GenerateMailTemplate()
{
	if (bInProcess) return;

	var url = '/bitrix/admin/form_mail.php?lang=<?=LANGUAGE_ID?>&<?=bitrix_sessid_get()?>&WEB_FORM_ID=<?=intval($ID)?>';
	CHttpRequest.Action = function() {CloseWaitWindow(); bInProcess = false;}
	ShowWaitWindow();
	bInProcess = true;
	CHttpRequest.Send(url);
}

function _processData(arReturn)
{
	//alert(arReturn.NOTE);
	//alert(arReturn.TEMPLATES);

	var obTable = document.getElementById('form_templates_table');
	var obContainer = document.getElementById('form_templates');

	if (arReturn.TEMPLATES && arReturn.TEMPLATES.length > 0)
	{
		//obContainer.removeChild(obContainer.firstChild);
		if (null == obTable)
		{
			var obTable = document.createElement('TABLE');
			obTable.id = 'form_templates_table';
			obTable.setAttribute('cellspacing', '0');
			obTable.setAttribute('cellpadding', '0');
			obTable.appendChild(document.createElement('TBODY'));

			obContainer.insertBefore(obTable, obContainer.firstChild);
		}

		for (var i=0; i<arReturn.TEMPLATES.length; i++)
		{
			var obRow = obTable.tBodies[0].insertRow(-1);
			obRow.id = 'ft_' + arReturn.TEMPLATES[i].ID;

			var obCell = obRow.insertCell(-1);
			obCell.setAttribute('nowrap', 'nowrap');
			obCell.style.padding = '0px';

			if (jsUtils.IsIE())
				var obCheckbox = document.createElement('<input type="checkbox" id="' + arReturn.TEMPLATES[i].ID + '" name="arMAIL_TEMPLATE[]">');
			else
			{
				var obCheckbox = document.createElement('INPUT');
				obCheckbox.type = 'checkbox';
				obCheckbox.id = arReturn.TEMPLATES[i].ID;
				obCheckbox.name = 'arMAIL_TEMPLATE[]';
			}

			obCheckbox.value = arReturn.TEMPLATES[i].ID;
			obCell.appendChild(obCheckbox);
			obCell.innerHTML += '[<a class="tablebodylink" href="/bitrix/admin/message_edit.php?ID=' + arReturn.TEMPLATES[i].ID + '&lang=<?=LANGUAGE_ID?>">' + arReturn.TEMPLATES[i].ID + '</a>]&nbsp;';

			var obLabel = document.createElement('LABEL');
			obLabel.setAttribute('for', arReturn.TEMPLATES[i].ID);
			obLabel.appendChild(document.createTextNode('(' + arReturn.TEMPLATES[i].FIELDS.LID + ') ' + arReturn.TEMPLATES[i].FIELDS.SUBJECT.substring(0, 50) + ' ...'));
			obCell.appendChild(obLabel);

			var obCell = obRow.insertCell(-1);
			obCell.setAttribute('nowrap', 'nowrap');
			obCell.style.padding = '0px';

			obCell.innerHTML = '&nbsp;[&nbsp;<a href="javascript:void(0)" onclick="DeleteMailTemplate(\'' + arReturn.TEMPLATES[i].ID + '\')"><?=CUtil::JSEscape(GetMessage("FORM_DELETE_MAIL_TEMPLATE"))?></a>&nbsp;]';
		}

		BX.adminPanel.modifyFormElements(obTable);
	}
}

function DeleteMailTemplate(template_id)
{
	if (bInProcess) return;

	if (confirm('<?echo CUtil::JSEscape(GetMessage('FORM_CONFIRM_DEL_MAIL_TEMPLATE'))?>'))
	{
		function __process(data)
		{
			var obTable = document.getElementById('form_templates_table');
			obTable.tBodies[0].removeChild(document.getElementById('ft_' + template_id));
			CloseWaitWindow();
			bInProcess = false;
		}

		//var url = 'message_admin.php?action=delete&ID=' + template_id + '&lang=<?echo LANGUAGE_ID?>&<?=bitrix_sessid_get()?>';
		var url = '/bitrix/admin/form_mail.php?action=delete&ID=' + template_id + '&lang=<?echo LANGUAGE_ID?>&<?=bitrix_sessid_get()?>&WEB_FORM_ID=<?=intval($ID)?>';

		CHttpRequest.Action = __process;
		ShowWaitWindow();
		bInProcess = true;
		CHttpRequest.Send(url);
	}
}

function set_event2()
{
	v = document.form1.STAT_EVENT2.value;
	if (v.length<=0)
	{
		<?if ($ID<=0):?>
		document.form1.STAT_EVENT2.value = document.form1.SID.value.toLowerCase();
		<?endif;?>
	}
}
//-->
</script>
<?
	if ($old_module_version=="Y"):

	$strSql = "SELECT ID FROM b_form_result WHERE FORM_ID='".$ID."' ORDER BY ID desc";
	$z = $DB->Query($strSql, false, $err_mess.__LINE__);
	$zr = $z->Fetch();
	$RESULT_ID = intval($zr["ID"]);

	$arList = CForm::GetTemplateList("SHOW_RESULT");
	?>
	<tr>
		<td width="40%"><?=GetMessage("FORM_SHOW_RESULT_TEMPLATE")?></td>
		<td width="60%"><?echo SelectBoxFromArray("SHOW_RESULT_TEMPLATE", $arList, $str_SHOW_RESULT_TEMPLATE);
		?><?if ($RESULT_ID>0) :?>&nbsp;[&nbsp;<a href="/bitrix/admin/form_result_view.php?lang=<?=LANGUAGE_ID?>&WEB_FORM_ID=<?=$ID?>&RESULT_ID=<?=$RESULT_ID?>"><?=GetMessage("FORM_PREVIEW")?></a>&nbsp;]<?endif;?></td>
	</tr>
<?
	$arList = CForm::GetTemplateList("PRINT_RESULT");
?>

	<tr>
		<td><?=GetMessage("FORM_PRINT_RESULT_TEMPLATE")?></td>
		<td><?echo SelectBoxFromArray("PRINT_RESULT_TEMPLATE", $arList, $str_PRINT_RESULT_TEMPLATE);
		?></td>
	</tr>
<?
	$arList = CForm::GetTemplateList("EDIT_RESULT");
?>
	<tr>
		<td><?=GetMessage("FORM_EDIT_RESULT_TEMPLATE")?></td>
		<td><?echo SelectBoxFromArray("EDIT_RESULT_TEMPLATE", $arList, $str_EDIT_RESULT_TEMPLATE);
		?><?if ($RESULT_ID>0) :?>&nbsp;[&nbsp;<a href="/bitrix/admin/form_result_edit.php?lang=<?=LANGUAGE_ID?>&WEB_FORM_ID=<?=$ID?>&RESULT_ID=<?=$RESULT_ID?>"><?=GetMessage("FORM_PREVIEW")?></a>&nbsp;]<?endif;?></td>
	</tr>
	<?endif;?>
	<?if ($ID>0):?>
	<tr>
		<td width="40%" valign="top"><?=GetMessage("FORM_MAIL_TEMPLATE")?></td>
		<td width="60%" valign="top" nowrap style="padding:0px" id="form_templates">
			<?
			$arr = CForm::GetTemplateList("MAIL","xxx",$ID);
			if (is_array($arr) && count($arr)>0):
				$arrMAIL = array();
				reset($arr);
				if (is_array($arr["reference_id"]))
				{
					foreach ($arr['reference_id'] as $key => $value)
						$arrMAIL[$value] = $arr["reference"][$key];
				}
				?>
				<?
				if (count($arrMAIL) > 0) echo '<table cellspacing="0" cellpadding="0" id="form_templates_table"><tbody>'
				?>
				<?
				foreach ($arrMAIL as $mail_id => $mail_name):
					$checked = (is_array($arMAIL_TEMPLATE) && in_array($mail_id, $arMAIL_TEMPLATE)) ? "checked" : "";
				?>
					<tr id="ft_<?=htmlspecialcharsbx($mail_id)?>">
						<td nowrap style="padding:0px"><input type="checkbox" name="arMAIL_TEMPLATE[]" value="<?=htmlspecialcharsbx($mail_id)?>" id="<?=htmlspecialcharsbx($mail_id)?>" <?=$checked?>><?echo "[<a class=tablebodylink href='/bitrix/admin/message_edit.php?ID=".htmlspecialcharsbx($mail_id)."&lang=".LANGUAGE_ID."'>".htmlspecialcharsbx($mail_id). "</a>]";?>&nbsp;<label for="<?=htmlspecialcharsbx($mail_id)?>"><?=htmlspecialcharsbx($mail_name)?></label></td>
						<td nowrap style="padding:0px">&nbsp;[&nbsp;<a href="javascript:void(0)" onclick="DeleteMailTemplate('<?=htmlspecialcharsbx($mail_id)?>')"><?=GetMessage("FORM_DELETE_MAIL_TEMPLATE")?></a>&nbsp;]</td>
					</tr>
				<?endforeach;?>
				<?
				if (count($arrMAIL) > 0) echo '</tbody></table>';
				?>
				<?
			endif;

			if ($F_RIGHT>=30) :
			?>
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td colspan=2 style="padding:0px"><?if (count($arrMAIL)>0) echo "<br>"?>&nbsp;[&nbsp;<a title="<?=GetMessage("FORM_GENERATE_TEMPLATE_ALT")?>" onClick="GenerateMailTemplate()" href="javascript:void(0)"><?echo GetMessage("FORM_CREATE_S")?></a>&nbsp;]<?
						if (count($arrMAIL)>0):
							?>&nbsp;&nbsp;&nbsp;[&nbsp;<a href="/bitrix/admin/message_admin.php?find_type_id=FORM_FILLING_<?=$str_SID?>&set_filter=Y"><?echo GetMessage("FORM_VIEW_TEMPLATE_LIST")?></a>&nbsp;]<?
						endif;
						?></td>
					</tr>
				</table>
			<?
			endif;
			?>
		</td>
	</tr>
	<?endif;?>
		<?

	if($bEditTemplate):
		CAdminFileDialog::ShowScript(Array(
			"event" => "BtnClick1",
			"arResultDest" => Array("FORM_NAME" => "form1", "FORM_ELEMENT_NAME" => "FILTER_RESULT_TEMPLATE"),
			"arPath" => Array("PATH" => '/'),
			"select" => 'F',
			"operation" => 'O',
			"showUploadTab" => true,
			"saveConfig" => true
		));

		CAdminFileDialog::ShowScript(Array(
			"event" => "BtnClick2",
			"arResultDest" => Array("FORM_NAME" => "form1", "FORM_ELEMENT_NAME" => "TABLE_RESULT_TEMPLATE"),
			"arPath" => Array("PATH" => '/'),
			"select" => 'F',
			"operation" => 'O',
			"showUploadTab" => true,
			"saveConfig" => true
		));
	?>
	<tr>
		<td><?=GetMessage("FORM_FILTER_RESULT_TEMPLATE")?></td>
		<td><input type="text" name="FILTER_RESULT_TEMPLATE" size="37" value="<?echo htmlspecialcharsbx($str_FILTER_RESULT_TEMPLATE)?>">&nbsp;<input type="button" name="browse" value="..." onClick="BtnClick1()"></td>
	</tr>
	<tr>
		<td><?=GetMessage("FORM_TABLE_RESULT_TEMPLATE")?></td>
		<td><input type="text" name="TABLE_RESULT_TEMPLATE" size="37" value="<?echo htmlspecialcharsbx($str_TABLE_RESULT_TEMPLATE)?>">&nbsp;<input type="button" name="browse" value="..." onClick="BtnClick2()"></td>
	</tr>
	<?
	endif;
	?>
	<tr>
		<td>
<?
}

//********************
//Stats Tab
//********************
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="40%">event1:</td>
		<td width="60%"><input type="text" name="STAT_EVENT1" maxlength="255" size="30" value="<?=htmlspecialcharsbx($str_STAT_EVENT1)?>"></td>
	</tr>
	<tr>
		<td>event2:</td>
		<td><input type="text" name="STAT_EVENT2" maxlength="255" size="30" value="<?=htmlspecialcharsbx($str_STAT_EVENT2)?>"><br><?echo GetMessage("FORM_EVENT12")?></td>
	</tr>
	<tr>
		<td>event3:</td>
		<td><input type="text" name="STAT_EVENT3" maxlength="255" size="30" value="<?=htmlspecialcharsbx($str_STAT_EVENT3)?>"><br><?echo GetMessage("FORM_EVENT3")?></td>
	</tr>
<?
//********************
//CRM Tab
//********************
$tabControl->BeginNextTab();

if ($ID <= 0):
?>
<tr>
	<td colspan="2" align="center"><?echo BeginNote(),GetMessage('FORM_CRM_NOT_SAVED'),EndNote();?></td>
</tr>
	<?
else:
	$arCRMServers = array();
	$dbRes = CFormCrm::GetList(array('NAME' => 'ASC', 'ID' => 'ASC'), array());
	while ($arServer = $dbRes->Fetch())
	{
		$arCRMServers[] = $arServer;
	}

	$dbRes = CFormCrm::GetByFormID($ID);
	$bLinkCreated = false;
	if ($arFormCrmLink = $dbRes->Fetch())
	{
		$bLinkCreated = true;

		$dbRes = CFormCrm::GetFields($arFormCrmLink['ID']);
		$arFormCrmFields = array();
		while ($arFld = $dbRes->Fetch())
		{
			$arFormCrmFields[] = $arFld;
		}
	}

	$dbRes = CFormField::GetList($ID, 'ALL', $by, $order, array(), $is_filtered);
	$arFormFields = array();
	while ($arFld = $dbRes->Fetch())
	{
		$arFormFields[] = $arFld;
	}

	if (false && !$bLinkCreated):
?>
	<tr>
		<td colspan="2" align="center"><?echo BeginNote(),GetMessage('FORM_CRM_NOT_SET'),EndNote();?></td>
	</tr>
<?
	else:
?>
	<script type="text/javascript">BX.ready(BX.defer(function(){loadCrmFields('<?=$arFormCrmLink['CRM_ID']?>', function() {
<?
	if ($bLinkCreated):
		foreach ($arFormCrmFields as $ar):
?>
		addCrmField('<?=CUtil::JSEscape($ar['CRM_FIELD'])?>', '<?=$ar['FIELD_ID'] > 0 ? $ar['FIELD_ID'] : $ar['FIELD_ALT']?>', true);
<?
		endforeach;
	endif;
?>

	})}));</script>
<?
	endif;

	CJSCore::Init(array('ajax', 'popup'));
?>
<style>
.form-crm-settings {width: 300px;}
.form-crm-settings table {width: 100%;}
.form-crm-settings table td {padding: 4px;}
.form-crm-settings, .form-crm-settings table {font-size: 11px;}
.form-crm-settings-hide-auth .form-crm-auth {display: none;}
.form-crm-settings input {width: 180px;}
.form-action-button {display: inline-block; height: 17px; width: 17px;}
.action-edit {background: scroll transparent url(/bitrix/images/form/options_buttons.gif) no-repeat 0 0; }
.action-delete {background: scroll transparent url(/bitrix/images/form/options_buttons.gif) no-repeat -29px 0; }
</style>
<script type="text/javascript">
function _showPass(el)
{
	el.parentNode.replaceChild(BX.create('INPUT', {
		props: {
			type: el.type == 'text' ? 'password' : 'text',
			name: el.name,
			value: el.value
		}
	}), el);
}

function showCrmForm(data)
{
	var popup_id = Math.random();

	data = data || {ID:'new_' + popup_id}

	var content = '<div class="form-crm-settings"><form name="form_'+popup_id+'"><table cellpadding="0" cellspacing="2" border="0"><tr><td align="right"><?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_ROW_TITLE'))?>:</td><td><input type="text" name="NAME" value="'+BX.util.htmlspecialchars(data.NAME||'')+'"></td></tr><tr><td align="right"><?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_FORM_URL_SERVER'))?>:</td><td><input type="text" name="URL_SERVER" value="'+BX.util.htmlspecialchars(data.URL_SERVER||'')+'"></td></tr><tr><td align="right"><?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_FORM_URL_PATH'))?>:</td><td><input type="text" name="URL_PATH" value="'+BX.util.htmlspecialchars(data.URL_PATH||'<?=FORM_CRM_DEFAULT_PATH?>')+'"></td></tr><tr><td colspan="2" align="center"><b><?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_ROW_AUTH'))?></b></td></tr><tr><td align="right"><?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_ROW_AUTH_LOGIN'))?>:</td><td><input type="text" name="LOGIN" value="'+BX.util.htmlspecialchars(data.LOGIN||'')+'"></td></tr><tr><td align="right"><?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_ROW_AUTH_PASSWORD'))?>:</td><td><input type="password" name="PASSWORD" value="'+BX.util.htmlspecialchars(data.PASSWORD||'')+'"></td></tr><tr><td></td><td><a href="javascript:void(0)" onclick="_showPass(document.forms[\'form_'+popup_id+'\'].PASSWORD); BX.hide(this.parentNode);"><?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_ROW_AUTH_PASSWORD_SHOW'))?></a></td></tr></table></form></div>';

	var wnd = new BX.PopupWindow('popup_' + popup_id, window, {
		titleBar: {content: BX.create('SPAN', {text: '<?=CUtil::JSEscape(GetMessage('FORM_CRM_TITLEBAR_NEW'))?>'})},
		draggable: true,
		autoHide: false,
		closeIcon: true,
		closeByEsc: true,
		content: content,
		buttons: [
			new BX.PopupWindowButton({
				text : BX.message('JS_CORE_WINDOW_SAVE'),
				className : "popup-window-button-accept",
				events : {
					click : function(){CRMSave(wnd, data, document.forms['form_'+popup_id])}
				}
			}),
			new BX.PopupWindowButtonLink({
				text : BX.message('JS_CORE_WINDOW_CANCEL'),
				className : "popup-window-button-link-cancel",
				events : {
					click : function() {wnd.close()}
				}
			})
		]
	});

	wnd.show();
}

function CRMSave(wnd, data_old, form)
{
	var URL = form.URL_SERVER.value;
	if (URL.substring(URL.length-1,1) != '/' && form.URL_PATH.value.substring(0,1) != '/')
		URL += '/';
	URL += form.URL_PATH.value;

	var flds = ['ID', 'NAME', 'URL', 'LOGIN','PASSWORD'],
		data = {
			ID: data_old.ID,
			NAME: form.NAME.value,
			URL:  URL,
			LOGIN: !!form.LOGIN ? form.LOGIN.value : '',
			PASSWORD: !!form.PASSWORD ? form.PASSWORD.value : ''
		};

	var res = false, r = /^(http|https):\/\/([^\/]+)(.*)$/i;
	if (data.URL)
	{
		res = r.test(data.URL);
		if (!res)
		{
			var proto = data.URL.match(/\.bitrix24\./) ? 'https' : 'http';

			data.URL = proto + '://' + data.URL;
			res = r.test(data.URL);
		}
	}

	if (!res)
	{
		alert('<?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_WRONG_URL'))?>');
	}
	else
	{
		var query_str = '';

		for (var i = 0; i < flds.length; i++)
		{
			query_str += (query_str == '' ? '' : '&') + 'CRM['+data.ID+']['+flds[i]+']='+BX.util.urlencode(data[flds[i]]);
		}

		BX.ajax({
			method: 'POST',
			dataType: 'json',
			url: '/bitrix/admin/settings.php?mid=form&saveCrm=Y&ajax=Y&<?=bitrix_sessid_get()?>',
			data: query_str,
			onsuccess: CRMRedraw
		});

		if (!!wnd)
			wnd.close();
	}
}

function CRMRedraw(data)
{
	var s = document.forms.form1.CRM_ID, i=0;
	for (i=s.options.length-1; i>1; i--)
	{
		s.remove(i);
	}

	for (i=0; i<data.length;i++)
	{
		var o = s.add(new Option(data[i].NAME||'<?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_UNTITLED'))?>', data[i].ID));

		if (data[i].NEW == 'Y')
		{
			s.selectedIndex = i+2;
			loadCrmFields(data[i].ID, null, {LOGIN:data[i].LOGIN, PASSWORD: data[i].PASSWORD});
		}
	}
}


window.crm_fields = [];
function loadCrmFields(ID, cb, additional)
{
	if (ID === 'Y')
	{
		showCrmForm();
		return false;
	}

	var s = BX('field_crm');
	if (ID > 0)
	{
		BX('crm_settings_heading').style.display = '';
		//BX('crm_settings_1').style.display = '';
		BX('crm_settings_2').style.display = '';
		BX('crm_settings_3').style.display = '';

		BX.ajax.loadJSON('/bitrix/admin/form_crm.php?action=check&ID='+ID+'&<?=bitrix_sessid_get();?>', additional, function(res)
		{
			BX.cleanNode(s);

			if (!!res)
			{
				if (res.result == 'ok' && !!res.fields)
				{
					window.crm_fields = res.fields;

					for (var i = 0; i < res.fields.length; i++)
					{
						var t = (res.fields[i].NAME || res.fields[i].ID) + (res.fields[i].REQUIRED == 'true' ? ' *' : '');
						s.add(new Option(t, res.fields[i].ID));
					}

					setTimeout(checkCrmRequiredFields, 15);
				}
				else
				{
					window.crm_fields = [];
				}
			}
			else
			{
				window.crm_fields = [];
			}

			setTimeout(cb, 10);
		});
	}
	else
	{
		BX('crm_settings_heading').style.display = 'none';
		//BX('crm_settings_1').style.display = 'none';
		BX('crm_settings_2').style.display = 'none';
		BX('crm_settings_3').style.display = 'none';

		BX.cleanNode(s);
	}
}

function checkCrmRequiredFields()
{
	var f = document.forms.form1, flds = f['CRM_FIELD[]'], i = 0, current_flds = {};
	if (!flds)
		flds = [];
	else if (BX.type.isDomNode(flds))
		flds = [flds];


	for (i = 0; i<flds.length; i++)
	{
		if (flds[i].tagName.toUpperCase() != 'SELECT')
			current_flds[flds[i].value] = true;
	}

	var list = '', list_files = '';
	for(i = 0; i<window.crm_fields.length; i++)
	{
		if (window.crm_fields[i].REQUIRED == 'true' && !current_flds[window.crm_fields[i].ID])
		{
			addCrmField(window.crm_fields[i].ID, null, true);
		}

		if (window.crm_fields[i].TYPE == 'file' && current_flds[window.crm_fields[i].ID])
		{
			list_files += '<li>'+window.crm_fields[i].NAME+'</li>';
		}
	}

	if (list_files.length > 0)
	{
		BX('bx_crm_note_content_1').innerHTML = list_files;
		BX('bx_crm_note_1').style.display = 'block';
	}
	else
	{
		BX('bx_crm_note_1').style.display = 'none';
	}
}

function addCrmField(cv, fv, bSkipCheck)
{
	var crm_field = BX('field_crm'), form_field = BX('field_form');
	if (null == cv)
	{
		cv = crm_field.value; fv = form_field.value;
	}
	else if (null == fv)
	{
		fv = BX.clone(form_field);
		fv.id = null;
	}

	if (cv && fv)
	{
		var t = BX('crm_table'),
			r = t.tBodies[0].insertRow(t.tBodies[0].rows.length-1),
			id = '';

		r.appendChild(BX.create('INPUT', {props: {
			type: 'hidden',
			name: 'CRM_FIELD['+id+']',
			value: cv
		}}));
		if (!BX.type.isDomNode(fv))
		{
			r.appendChild(BX.create('INPUT', {props: {
				type: 'hidden',
				name: 'CRM_FORM_FIELD['+id+']',
				value: fv
			}}));
		}

		var t = cv;
		for (var i = 0; i < crm_field.options.length; i++)
		{
			if (crm_field.options[i].value == cv)
			{
				t = crm_field.options[i].text; break;
			}
		}

		if (t.substring(t.length-2) == ' *')
		{
			t = BX.util.htmlspecialchars(t.substring(0, t.length-2)) + '<span class="required">*</span>';
		}
		else
		{
			t = BX.util.htmlspecialchars(t);
		}

		r.insertCell(-1).innerHTML = t;

		if (!BX.type.isDomNode(fv))
		{
			t = '';
			for (var i = 0; i < form_field.options.length; i++)
			{
				if (form_field.options[i].value == fv)
				{
					t = form_field.options[i].text; break;
				}
			}

			r.insertCell(-1).innerHTML = BX.util.htmlspecialchars(t);
		}
		else
		{
			r.insertCell(-1).appendChild(fv);
			if (crm_field.value == cv)
				crm_field.selectedIndex = crm_field.selectedIndex+1;
		}

		r.insertCell(-1).appendChild(BX.create('A', {
			props: {className: 'form-action-button action-delete'},
			attrs: {href: 'javascript:void(0)'},
			events: {
				click: function(){
					r.parentNode.removeChild(r);

					// hack
					try {
						if (BX.type.isDomNode(document.forms.form1['CRM_FIELD[]']))
							document.forms.form1['CRM_FIELD[]'] = undefined;
					} catch(e) {}

					checkCrmRequiredFields();
				}
			}
		}));
	}

	if (!bSkipCheck)
		checkCrmRequiredFields();
}
</script>
<tr>
	<td width="50%"><?=GetMessage('FORM_FIELD_CRM');?>:</td>
	<td><select name="CRM_ID" onchange="loadCrmFields(this.value)">
		<option><?=GetMessage('FORM_FIELD_CRM_NO')?></option>
		<option value="Y"><?=GetMessage('FORM_FIELD_CRM_NEW')?></option>
<?
	foreach ($arCRMServers as $arCrm):
		if (strlen($arCrm['NAME']) <= 0)
		{
			$arCrm['NAME'] = GetMessage('FORM_TAB_CRM_UNTITLED');
		}

?>
		<option value="<?=intval($arCrm['ID'])?>"<?=$bLinkCreated && $arFormCrmLink['CRM_ID']==$arCrm['ID']?' selected="selected"' : ''?>><?=htmlspecialcharsbx($arCrm['NAME'])?></option>
<?
	endforeach;
?>
	</select>&nbsp;&nbsp;<a href="/bitrix/admin/settings.php?lang=<?=LANGUAGE_ID?>&amp;mid=form&amp;tabControl_active_tab=edit_crm"><?=GetMessage('FORM_CRM_GOTOLIST')?></a></td>
</tr>
<tr id="crm_settings_3"<?=!$bLinkCreated?' style="display:none;"':''?>>
	<td><?=GetMessage('FORM_FIELD_LINK_TYPE');?>:</td>
	<td>
		<input type="radio" name="CRM_LINK_TYPE" value="<?=CFormCrm::LINK_AUTO?>" id="CRM_LINK_TYPE_<?=CFormCrm::LINK_AUTO?>"<?=!$bLinkCreated || $arFormCrmLink['LINK_TYPE']==CFormCrm::LINK_AUTO?' checked="checked"' : ''?> /><label for="CRM_LINK_TYPE_<?=CFormCrm::LINK_AUTO?>"><?=GetMessage('FORM_FIELD_LINK_TYPE_AUTO')?></label>
		<input type="radio" name="CRM_LINK_TYPE" value="<?=CFormCrm::LINK_MANUAL?>" id="CRM_LINK_TYPE_<?=CFormCrm::LINK_MANUAL?>"<?=$bLinkCreated && $arFormCrmLink['LINK_TYPE']==CFormCrm::LINK_MANUAL?' checked="checked"' : ''?> /><label for="CRM_LINK_TYPE_<?=CFormCrm::LINK_MANUAL?>"><?=GetMessage('FORM_FIELD_LINK_TYPE_MANUAL')?></label>
	</td>
</tr>
<tr class="heading" id="crm_settings_heading"<?=!$bLinkCreated?' style="display:none;"':''?>>
	<td colspan="2"><?=GetMessage('FORM_FIELD_CRM_FIELDS');?></td>
</tr>
<tr id="crm_settings_2"<?=!$bLinkCreated?' style="display:none;"':''?>>
	<td colspan="2">
		<div id="bx_crm_note" style="display: none;" align="center"><?=BeginNote();?><?=GetMessage('FORM_CRM_REQUIRED_NOTE')?><blockquote id="bx_crm_note_content"></blockquote><?=EndNote();?></div>
		<div id="bx_crm_note_1" style="display: none;" align="center"><?=BeginNote();?><?=GetMessage('FORM_CRM_FILES_NOTE')?><blockquote id="bx_crm_note_content_1"></blockquote><?=EndNote();?></div>
		<table class="internal" cellspacing="0" cellpadding="0" border="0" align="center" width="80%" id="crm_table">
			<thead>
				<tr class="heading">
					<td width="50%"><?=GetMessage('FORM_FIELD_CRM_FIELDS_CRM')?></td>
					<td width="50%"><?=GetMessage('FORM_FIELD_CRM_FIELDS_FORM')?></td>
					<td width="17"></td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<select name="CRM_FIELD[]" id="field_crm" style="width: 270px;"></select>
					</td>
					<td>
						<select name="CRM_FORM_FIELD[]" id="field_form" style="width: 270px;">
		<option value="FORM_NAME"><?=GetMessage('FORM_FIELD_CRM_FIELDS_FORM_NAME')?></option>
		<option value="FORM_SID"><?=GetMessage('FORM_FIELD_CRM_FIELDS_FORM_SID')?></option>
		<option value="SITE_ID"><?=GetMessage('FORM_FIELD_CRM_FIELDS_SITE_ID')?></option>
		<option value="RESULT_ID"><?=GetMessage('FORM_FIELD_CRM_FIELDS_RESULT_ID')?></option>
		<option value="FORM_ALL"><?=GetMessage('FORM_FIELD_CRM_FIELDS_FORM_ALL')?></option>
		<option value="FORM_ALL_HTML"><?=GetMessage('FORM_FIELD_CRM_FIELDS_FORM_ALL_HTML')?></option>
		<option value="NEW"><?=GetMessage('FORM_FIELD_CRM_FIELDS_NEW')?></option>
<?
	foreach ($arFormFields as $arFld):
?>
		<option value="<?=$arFld['ID']?>">[<?=htmlspecialcharsbx($arFld['SID'])?>] <?=htmlspecialcharsbx($arFld['TITLE'])?><?=$arFld['REQUIRED']=='Y'? ' *' : ''?></option>
<?
	endforeach;
?>
	</select>
					</td>
					<td></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="3"><input type="button" onclick="addCrmField(); return false;" value="<?=htmlspecialcharsbx(GetMessage('FORM_CRM_ADD'))?>"></td>
				</tr>
			</tfoot>
		</table>
	</td>
</tr>

<?
endif;

//********************
//Access Tab
//********************
$tabControl->BeginNextTab();
?>
	<?
	reset($arGroups);
	$arr = CForm::GetPermissionList();

	if ($bSimple)
	{
		$arr['reference'][3] = GetMessage("FORM_SIMPLE_RESULTS");
		unset($arr['reference_id'][4]);
		$arrSelect=array();
		reset($arr['reference_id']);
		while(list($num,)=each($arr['reference_id']))
		{
			$arrSelect['reference_id'][]=$arr['reference_id'][$num];
			$arrSelect['reference'][]=$arr['reference'][$num];
		}
	}
	else
	{
		$arrSelect=$arr;
	}

	reset($arGroups);
	while (list(,$group)=each($arGroups)) :
	?>
	<tr>
		<td width="40%"><?=$group["NAME"].":"?></td>
		<td width="60%"><?
		$perm = CForm::GetPermission($ID, array($group["ID"]), "Y");

		// for simple method: change 20 (work with other results) access mode to 15
		/*
		if ($bSimple)
			$perm = $perm==20 ? 15 : $perm;
		*/

		echo SelectBoxFromArray("PERMISSION_".$group["ID"], $arrSelect, $perm, "", 'style="width: 80%;"');
		?></td>
	</tr>
	<?endwhile;?>
<?

$tabControl->EndTab();
$tabControl->Buttons(array("disabled"=>(!(($ID>0 && $F_RIGHT>=30) || CForm::IsAdmin())), "back_url"=>(strlen($back_url) > 0 ? $back_url : "form_list.php?lang=".LANGUAGE_ID)));
$tabControl->End();
?>
</form>
<?
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>