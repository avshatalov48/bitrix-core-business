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
 */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
define("HELP_FILE", "settings/sites/template_edit.php");

CModule::IncludeModule("fileman");

//Workaround for Chrome: http://code.google.com/p/chromium/issues/detail?id=79014
//"If the XSS auditor is blocking script that you mean to execute, you can disable it by sending a 'X-XSS-Protection: 0' header."
header("X-XSS-Protection: 0");

ClearVars();

$edit_php = $USER->CanDoOperation('edit_php');
if(!$edit_php && !$USER->CanDoOperation('view_other_settings') && !$USER->CanDoOperation('lpa_template_edit'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isEditingMessageThemePage = $APPLICATION->GetCurPage() == '/bitrix/admin/message_theme_edit.php';

IncludeModuleLangFile(__FILE__);

$lpa = ($USER->CanDoOperation('lpa_template_edit') && !$edit_php); // Limit PHP access: for non admin users
$lpa_view = !$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('lpa_template_edit'); //

$strError = "";
$strOK = "";
$bVarsFromForm = false;
$codeEditorId = false;

$ID = _normalizePath($_REQUEST["ID"] ?? '');

if($lpa && (!isset($_REQUEST['edit']) || $_REQUEST['edit'] != "Y") && $ID == '') // In lpa mode users can only edit existent templates
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$bEdit = false;
$templFields = array();

$str_ID = '';
$str_NAME = '';
$str_DESCRIPTION = '';
$str_SORT = '';
$str_TYPE = '';
$str_CONTENT = '';
$str_STYLES = '';
$str_TEMPLATE_STYLES = '';
if($ID <> '' && (!isset($_REQUEST['edit']) || $_REQUEST['edit'] != "N"))
{
	$templ = CSiteTemplate::GetByID($ID);
	if(($templFields = $templ->ExtractFields("str_")))
		$bEdit = true;
}

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB1"), "ICON" => "template_edit", "TITLE" => ($isEditingMessageThemePage ? GetMessage("MAIN_TAB1_TITLE_THEME") : GetMessage("MAIN_TAB1_TITLE"))),
	array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB2"), "ICON" => "template_edit", "TITLE" => GetMessage("MAIN_TAB2_TITLE")),
	array("DIV" => "edit3", "TAB" => GetMessage("MAIN_TAB4"), "ICON" => "template_edit", "TITLE" => GetMessage("MAIN_TAB4_TITLE")),
);
if($bEdit)
	$aTabs[] = 	array("DIV" => "edit4", "TAB" => GetMessage("MAIN_TAB3"), "ICON" => "template_edit", "TITLE" => GetMessage("MAIN_TAB3_TITLE"));

$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($_SERVER["REQUEST_METHOD"] == "POST" && (!empty($_POST['save']) || !empty($_POST['apply'])) && check_bitrix_sessid() && ($edit_php || $lpa))
{
	if ($lpa)
	{
		$CONTENT = LPA::Process($_POST["CONTENT"] ?? '', htmlspecialcharsback($str_CONTENT));
		//Add ..->ShowPanel() and WORK_AREA
		$ucont = mb_strtolower($CONTENT);
		$sp = '<?$APPLICATION->ShowPanel();?>';
		$body = '<body>';
		$wa = '#WORK_AREA#';
		$body_pos = mb_strpos($ucont, $body);
		$sp_pos = mb_strpos($ucont, mb_strtolower($sp));
		$wa_pos = mb_strpos($ucont, mb_strtolower($wa), $body_pos);
		if ($body_pos !== false && $sp_pos === false) // Add $APPLICATION->ShowPanel();
			$CONTENT = mb_substr($CONTENT, 0, $body_pos + mb_strlen($body)).$sp.mb_substr($CONTENT, $body_pos + mb_strlen($body));
		if ($wa_pos === false)
			$CONTENT .= $wa;
	}
	else
	{
		$CONTENT = $_POST["CONTENT"] ?? '';
	}

	if(class_exists('CFileMan') && method_exists("CFileMan", "CheckOnAllowedComponents"))
	{
		if (!CFileMan::CheckOnAllowedComponents($CONTENT))
		{
			$str_err = $APPLICATION->GetException();
			if($str_err && ($err = $str_err ->GetString()))
				$strError .= $err;
			$bVarsFromForm = true;
		}
	}

	if($strError == "")
	{
		$stylesDesc = array();
		$maxind = $_POST['maxind'] ?? '';
		for($i = 0; $i <= $maxind; $i++)
		{
			if(!isset($_POST["CODE_".$i]) || trim($_POST["CODE_".$i]) == '')
				continue;
			$code = ltrim($_POST["CODE_".$i], ".");
			$stylesDesc[$code] = $_POST["VALUE_".$i] ?? '';
		}

		$ST = new CSiteTemplate();
		$arFields = array(
			"ID" => $ID,
			"NAME" => $_POST["NAME"] ?? '',
			"DESCRIPTION" => $_POST["DESCRIPTION"] ?? '',
			"CONTENT" => $CONTENT,
			"STYLES" => $_POST["STYLES"] ?? '',
			"TEMPLATE_STYLES" => $_POST["TEMPLATE_STYLES"] ?? '',
			"SORT" => $_POST["SORT"] ?? '',
			"TYPE" => $_POST["TYPE"] ?? '',
			"STYLES_DESCRIPTION" => $stylesDesc,
		);

		if (isset($_REQUEST['edit']) && $_REQUEST['edit']=="Y")
			$res = $ST->Update($ID, $arFields);
		else
			$res = ($ST->Add($arFields) <> '');

		if(!$res)
		{
			$strError .= $ST->LAST_ERROR."<br>";
			$bVarsFromForm = true;
		}
		else
		{
			$useeditor_param = (isset($_REQUEST["CONTENT_editor"]) && $_REQUEST["CONTENT_editor"] == 'on') ? '&usehtmled=Y' : '';
			if (!empty($_POST["save"]))
				LocalRedirect(BX_ROOT."/admin/".($isEditingMessageThemePage ? "message_theme_admin.php" : "template_admin.php")."?lang=".LANGUAGE_ID.$useeditor_param);
			else
				LocalRedirect(BX_ROOT."/admin/".($isEditingMessageThemePage ? "message_theme_edit.php" : "template_edit.php")."?lang=".LANGUAGE_ID."&ID=".$ID."&".$tabControl->ActiveTabParam().$useeditor_param);
		}
	}
}

if($bVarsFromForm)
{
	$str_ID = htmlspecialcharsbx($_POST["ID"] ?? '');
	$str_NAME = htmlspecialcharsbx($_POST["NAME"] ?? '');
	$str_DESCRIPTION = htmlspecialcharsbx($_POST["DESCRIPTION"] ?? '');
	$str_SORT = htmlspecialcharsbx($_POST["SORT"] ?? '');
	$str_TYPE = htmlspecialcharsbx($_POST["TYPE"] ?? '');
	$str_CONTENT = htmlspecialcharsbx($_POST["CONTENT"] ?? '');
	$str_STYLES = htmlspecialcharsbx($_POST["STYLES"] ?? '');
	$str_TEMPLATE_STYLES = htmlspecialcharsbx($_POST["TEMPLATE_STYLES"] ?? '');
	$usehtmled = (isset($_REQUEST["CONTENT_editor"]) && $_REQUEST["CONTENT_editor"] == 'on') ? 'Y' : 'N';
}

if ($lpa || $lpa_view)
{
	$str_CONTENT = htmlspecialcharsback($str_CONTENT);
	$arPHP = PHPParser::ParseFile($str_CONTENT);
	$l = count($arPHP);
	if ($l > 0)
	{
		$new_content = '';
		$end = 0;
		$php_count = 0;

		for ($n = 0; $n < $l; $n++)
		{
			$start = $arPHP[$n][0];
			$s_cont = mb_substr($str_CONTENT, $end, $start - $end);
			$end = $arPHP[$n][1];
			$new_content .= $s_cont;

			$src = $arPHP[$n][2];
			$src = mb_substr($src, (mb_substr($src, 0, 5) == "<?"."php")? 5 : 2, -2); // Trim php tags

			$comp2_begin = '$APPLICATION->INCLUDECOMPONENT(';
			if (mb_strtoupper(mb_substr($src, 0, mb_strlen($comp2_begin))) == $comp2_begin) //If it's Component 2, keep the php code
				$new_content .= $arPHP[$n][2];
			else //If it's component 1 or ordinary PHP - than replace code by #PHPXXXX# (XXXX - count of PHP scripts)
				$new_content .= '#PHP'.str_pad(++$php_count, 4, "0", STR_PAD_LEFT).'#';
		}
		$new_content .= mb_substr($str_CONTENT, $end);
	}
	$str_CONTENT = htmlspecialcharsex($new_content);
}

$APPLICATION->AddHeadScript("/bitrix/js/main/template_edit.js");

if($bEdit)
	$APPLICATION->SetTitle(($isEditingMessageThemePage ? GetMessage("MAIN_T_EDIT_TITLE_EDIT_THEME") : GetMessage("MAIN_T_EDIT_TITLE_EDIT")));
else
	$APPLICATION->SetTitle(($isEditingMessageThemePage ? GetMessage("MAIN_T_EDIT_TITLE_NEW_THEME") : GetMessage("MAIN_T_EDIT_TITLE_NEW")));

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

CAdminMessage::ShowMessage($strError);
CAdminMessage::ShowNote($strOK);

$aMenu = array(
	array(
		"TEXT"	=> ($isEditingMessageThemePage ? GetMessage("MAIN_T_EDIT_TEMPL_LIST_THEME") : GetMessage("MAIN_T_EDIT_TEMPL_LIST")),
		"LINK"	=> "/bitrix/admin/".($isEditingMessageThemePage ? "message_theme_admin.php" : "template_admin.php")."?lang=".LANGUAGE_ID."&set_default=Y",
		"TITLE"	=> GetMessage("MAIN_T_EDIT_TEMPL_LIST_TITLE"),
		"ICON"	=> "btn_list"
	)
);

if ($ID <> '' && $edit_php)
{
	$aMenu[] = array("SEPARATOR"=>"Y");

	$aMenu[] = array(
		"TEXT"	=> GetMessage("MAIN_NEW_RECORD"),
		"LINK"	=> "/bitrix/admin/".($isEditingMessageThemePage ? "message_theme_edit.php" : "template_edit.php")."?lang=".LANGUAGE_ID,
		"TITLE"	=> GetMessage("MAIN_NEW_RECORD_TITLE"),
		"ICON"	=> "btn_new"
	);

	$aMenu[] = array(
		"TEXT"	=> GetMessage("MAIN_COPY_RECORD"),
		"LINK"	=> "/bitrix/admin/".($isEditingMessageThemePage ? "message_theme_admin.php" : "template_admin.php")."?lang=".LANGUAGE_ID."&ID=".urlencode($ID)."&action=copy&".bitrix_sessid_get(),
		"TITLE"	=> GetMessage("MAIN_COPY_RECORD_TITLE"),
		"ICON"	=> "btn_copy"
	);

	$aMenu[] = array(
		"TEXT"	=> GetMessage("MAIN_DELETE_RECORD"),
		"LINK"	=> "javascript:if(confirm('".GetMessage("MAIN_DELETE_RECORD_CONF")."')) window.location='/bitrix/admin/".($isEditingMessageThemePage ? "message_theme_admin.php" : "template_admin.php")."?ID=".urlencode(urlencode($ID))."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."&action=delete';",
		"TITLE"	=> GetMessage("MAIN_DELETE_RECORD_TITLE"),
		"ICON"	=> "btn_delete"
	);
}

$context = new CAdminContextMenu($aMenu);
$context->Show();
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="bform">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?echo LANG?>">
<input type="hidden" name="edit" value="<?echo ($bEdit? 'Y':'N')?>">
<?
$tabControl->Begin();

$tabControl->BeginNextTab();
?>
	<tr class="adm-detail-required-field">
		<td width="40%">ID:</td>
		<td width="60%"><?
			if($bEdit):
				echo $str_ID;
				?><input type="hidden" name="ID" value="<?echo $str_ID?>">
				(<a title="<?=GetMessage("MAIN_PREVIEW_FOLDER")?>" href="fileman_admin.php?lang=<?=LANG?>&amp;path=<?=urlencode($templFields["PATH"])?>"><?echo $str_PATH?>/</a>)
			<?
			else:
				?><input type="text" name="ID" size="20" maxlength="255" value="<? echo $str_ID?>"><?
			endif;
			?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("MAIN_T_EDIT_NAME")?></td>
		<td><input type="text" name="NAME" size="40" maxlength="50" value="<? echo $str_NAME?>"></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("MAIN_T_EDIT_DESCRIPTION")?></td>
		<td><textarea name="DESCRIPTION" cols="30" rows="3"><?echo $str_DESCRIPTION?></textarea></td>
	</tr>
	<tr>
		<td><?echo GetMessage("SITE_TEMPL_EDIT_SORT")?></td>
		<td><input type="text" name="SORT" size="20" value="<? echo $str_SORT?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("MAIN_TEMPLATE_TYPE")?></td>
		<td>
			<select name="TYPE">
				<option value="" <?=($str_TYPE==""?"selected":"")?>><?echo GetMessage("MAIN_TEMPLATE_TYPE_SITE")?></option>
				<option value="mail" <?=($str_TYPE=="mail"?"selected":"")?>><?echo GetMessage("MAIN_TEMPLATE_TYPE_MAIL")?></option>
			</select>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("MAIN_T_EDIT_CONTENT", array("#WORK_AREA#"=>'<a href="javascript:void(0)" onclick="InsertWorkArea();" title="'.GetMessage("MAIN_T_EDIT_INSERT_WORK_AREA").'">#WORK_AREA#</a>'))?></td>
	</tr>

	<tr>
		<td align="center" colspan="2">
			<?if(COption::GetOptionString('fileman', "use_code_editor", "Y") == "Y")
			{
				$codeEditorId = CCodeEditor::Show(
					array(
						'textareaId' => 'bxed_CONTENT',
						'height' => 500
					));
			}?>

			<textarea rows="28" cols="60" style="width:100%" id="bxed_CONTENT" name="CONTENT" wrap="off"><?echo htmlspecialcharsbx(htmlspecialcharsback($str_CONTENT))?></textarea>

		</td>
	</tr>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td align="center" colspan="2"><textarea rows="25" cols="60" style="width:100%" id="__STYLES" name="STYLES" wrap="off"><?echo $str_STYLES?></textarea></td>
	</tr>
	<tr class="heading">
		<td align="center" colspan="2"><?echo GetMessage("STYLE_DESCRIPTIONS")?></td>
	</tr>
	<tr>
		<td align="center" colspan="2">
		<?
		$io = CBXVirtualIo::GetInstance();
		$stylesPath = $io->RelativeToAbsolutePath(($templFields["PATH"] ?? '')."/.styles.php");
		$arStyles = array();
		if($bVarsFromForm)
		{
			$i = 0;
			while(isset($_POST["CODE_".$i]))
			{
				$arStyles[$_POST["CODE_".$i]] = $_POST["VALUE_".$i];
				$i++;
			}
		}
		else
		{
			$io = CBXVirtualIo::GetInstance();
			if ($io->FileExists($stylesPath))
			{
				$arStyles = CSiteTemplate::__GetByStylesTitle($stylesPath);
			}
		}
		?>
		<script>
		function _MoreRProps()
		{
			var prt = BX("proptab");
			var cnt = parseInt(BX("maxind").value) + 1;
			var r = prt.insertRow(prt.rows.length - 1);
			var c = r.insertCell(-1);
			c.innerHTML = '<input type="text" id="CODE_'+cnt+'" name="CODE_'+cnt+'" value="" size="30">';
			c = r.insertCell(-1);
			c.innerHTML = '<input type="text" name="VALUE_'+cnt+'" id="VALUE_'+cnt+'" value="" size="60">';
			BX("maxind").value = cnt;
		}
	</script>
			<table border="0" cellspacing="1" cellpadding="3" id="proptab"  class="internal">
				<tr class="heading">
					<td width="210px"><?echo GetMessage("MAIN_STYLE_NAME")?></td>
					<td width="380px"><?echo GetMessage("MAIN_STYLE_DESCRIPTION")?></td>
				</tr>
				<?
				$arStylesDesc = Array();
				$i = 0;
				if (!is_array($arStyles))
					$arStyles = Array();

				foreach($arStyles as $style_ => $title_)
				{
					if (is_array($title_))
						continue;
					?>
					<tr>
						<td  >
							<input type="text" name="CODE_<?=$i?>" id="CODE_<?=$i?>" value="<?=htmlspecialcharsbx($style_)?>" size="30">
						</td>
						<td>
							<input type="text" name="VALUE_<?=$i?>" id="VALUE_<?=$i?>" value="<?=htmlspecialcharsbx($title_)?>" size="60">
						</td>
					</tr>
					<?
					$i++;
				}

				$ind = $i-1;
				?>
				<tr>
					<td colspan="2">
						<input type="hidden" id="maxind" name="maxind" value="<?echo $ind; ?>">
						<input type="hidden" id="styles_path" name="styles_path" value="<?=htmlspecialcharsbx($stylesPath)?>">
						<input type="button" name="propeditmore"  value="<?echo GetMessage("MAIN_STYLE_MORE")?>" onClick="_MoreRProps()">
					</td>
				</tr>
			</table>

		<?if (count($arStyles)<1):?>
			<script>_MoreRProps();</script>
		<?endif;?>

		</td>
	</tr>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td align="center" colspan="2"><textarea rows="25" cols="60" style="width:100%" id="__TEMPLATE_STYLES" name="TEMPLATE_STYLES" wrap="off"><?echo $str_TEMPLATE_STYLES?></textarea></td>
	</tr>
<?if($bEdit):?>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2">
			<table cellspacing="0" class="internal">
			<?
			$dbFiles = CSiteTemplate::GetContent($ID);
			while($arFiles = $dbFiles->GetNext()):
				if($arFiles["NAME"]=="header.php" || $arFiles["NAME"]=="footer.php" || $arFiles["NAME"]=="styles.css" || $arFiles["NAME"]=="template_styles.css" || $arFiles["NAME"]=="description.php")
					continue;
				if($arFiles["TYPE"]<>"F")
					continue;
				$fType = GetFileType($arFiles["NAME"]);
			?>
			<tr>
				<td><?=htmlspecialcharsbx($arFiles["NAME"] ?? '')?></td>
				<td><?=htmlspecialcharsbx($arFiles["DESCRIPTION"] ?? '')?></td>
				<td>
					<?if($fType == 'SOURCE'):?>
						<a title ="<?=GetMessage("MAIN_MOD_FILE").htmlspecialcharsbx($arFiles["NAME"])?>" href="fileman_file_edit.php?lang=<?=LANG?>&amp;full_src=Y&amp;path=<?=urlencode($arFiles["ABS_PATH"])?>&amp;back_url=<?=urlencode($_SERVER["REQUEST_URI"])?>"><?echo GetMessage("MAIN_T_EDIT_CHANGE")?></a>
					<?elseif($fType == 'IMAGE' || $fType == 'FLASH'):?>
						<?echo ShowImage($arFiles["ABS_PATH"], $iMaxW=50, $iMaxH=50, $sParams=null, $strImageUrl="", $bPopup=true, $sPopupTitle=GetMessage("template_edit_open_pic"));?>
					<?endif?>
				</td>
			</tr>
			<?endwhile;?>
			</table>
		</td>
	</tr>
	<tr>
		<td align="left" colspan="2">
			<a title="<?=GetMessage("MAIN_T_EDIT_ADD_TITLE")?>" href="fileman_file_edit.php?lang=<?=LANG?>&amp;full_src=Y&amp;back_url=<?=urlencode($_SERVER["REQUEST_URI"])?>&amp;path=<?=urlencode($templFields["PATH"])?>&amp;new=y"><?echo GetMessage("MAIN_T_EDIT_ADD")?></a><br>
			<a title="<?echo GetMessage("template_edit_upload_title")?>" href="fileman_file_upload.php?lang=<?=LANG?>&amp;path=<?=urlencode($templFields["PATH"])?>"><?echo GetMessage("template_edit_upload")?></a><br>
			<a title="<?echo GetMessage("template_edit_structure_title")?>" href="fileman_admin.php?lang=<?=LANG?>&amp;path=<?=urlencode($templFields["PATH"])?>"><?echo GetMessage("template_edit_structure")?></a>
		</td>
	</tr>
<?endif?>
<?
$tabControl->Buttons();
$aParams = array("disabled" => (!$edit_php && !$lpa), "back_url" => "".($isEditingMessageThemePage ? "message_theme_admin.php" : "template_admin.php")."?lang=".LANGUAGE_ID);
$dis = (!$edit_php && !$lpa);
?>
<input <?echo ($dis ? "disabled":"")?> type="submit" name="save" value="<?=GetMessage("admin_lib_edit_save")?>" title="<?=GetMessage("admin_lib_edit_save_title")?>" class="adm-btn-save">
<input <?echo ($dis ? "disabled":"")?> type="submit" name="apply" value="<?=GetMessage("admin_lib_edit_apply")?>" title="<?GetMessage("admin_lib_edit_apply_title")?>">
<?
if (($USER->CanDoOperation('edit_other_settings') || $USER->CanDoOperation('lpa_template_edit')) && !empty($ID) && !$isEditingMessageThemePage):
	$signer = new Bitrix\Main\Security\Sign\Signer();
	$sign = $signer->sign($ID, "template_preview".bitrix_sessid());
?>
<input type="button" value="<?=GetMessage('FILEMAN_PREVIEW_TEMPLATE')?>" name="template_preview" onclick="preview_template('<?=htmlspecialcharsbx(CUtil::JSEscape($ID))?>', '<?= bitrix_sessid()?>', '<?=htmlspecialcharsbx(CUtil::JSEscape($sign))?>');" title="<?=GetMessage('FILEMAN_PREVIEW_TEMPLATE_TITLE')?>">
<?
endif;
?>
<input type="button" value="<?=GetMessage("admin_lib_edit_cancel")?>" name="cancel" onClick="window.location='<?=CUtil::JSEscape($aParams["back_url"])?>'" title="<?=GetMessage("admin_lib_edit_cancel_title")?>">
<?$tabControl->End();?>
</form>

<script>
	<? if ($codeEditorId): ?>
		BX.ready(function(){
			var codeEditor = top.BXCodeEditors['<?= $codeEditorId?>'];
			window.InsertWorkArea = function()
			{
				var
					wa = '#WORK_AREA#',
					line = codeEditor.oSel.to.line,
					ch = codeEditor.oSel.to.ch + wa.length;

				if (codeEditor.highlightMode)
				{
					codeEditor.Action(function()
					{
						codeEditor.ReplaceRange(wa, codeEditor.oSel.from, codeEditor.oSel.to);
						codeEditor.SetCursor(line, ch);
						codeEditor.FocusInput();
						codeEditor.OnFocus();
					})();
				}
				else
				{
					var
						taSel = codeEditor.GetTASelection(),
						from = taSel.start,
						to = taSel.end,
						source = codeEditor.GetTAValue().replace(/\r/g, "");

					source = source.substr(0, from) + wa + source.substr(to);
					codeEditor.SetTAValue(source);

					if (BX.browser.IsIE())
					{
						codeEditor.SetIETASelection(to + wa.length, to + wa.length);
					}
					else
					{
						codeEditor.pTA.selectionStart = to + wa.length;
						codeEditor.pTA.selectionEnd = to + wa.length;
					}
					codeEditor.CheckLineSelection(true);
					BX.focus(codeEditor.pTA);
				}
			};
		});
	<?else:?>
		BX.ready(function(){
			window.InsertWorkArea = function()
			{
				BX('bxed_CONTENT').value += '#WORK_AREA#';
			};
		});
	<?endif?>

</script>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
