<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/prolog.php");

$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
if($FORM_RIGHT<="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

CModule::IncludeModule('form');

ClearVars();

IncludeModuleLangFile(__FILE__);
$err_mess = "File: ".__FILE__."<br>Line: ";
define("HELP_FILE","form_status_list.php");
$old_module_version = CForm::IsOldVersion();
$strError="";

$aTabs = array ();
$aTabs[]=array("DIV" => "edit1", "TAB" => GetMessage("FORM_PROP"), "ICON" => "form_edit", "TITLE" => GetMessage("FORM_PROP_TITLE"));
$aTabs[]=array("DIV" => "edit2", "TAB" => GetMessage("FORM_MAIL"), "ICON" => "form_edit", "TITLE" => GetMessage("FORM_MAIL_TITLE"));
$aTabs[]=array("DIV" => "edit3", "TAB" => GetMessage("FORM_ACCESS"), "ICON" => "form_edit", "TITLE" => GetMessage("FORM_ACCESS_TITLE"));


$tabControl = new CAdminTabControl("tabControl", $aTabs);
$message = null;


/***************************************************************************
							GET | POST processing
***************************************************************************/

$ID = intval($ID);
$WEB_FORM_ID = intval($WEB_FORM_ID);
$arForm = CForm::GetByID_admin($WEB_FORM_ID);
if (false === $arForm)
{
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	echo "<a href='form_list.php?lang=".LANGUAGE_ID."' >".GetMessage("FORM_FORM_LIST")."</a>";
	echo ShowError(GetMessage("FORM_NOT_FOUND"));
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$txt = "(".htmlspecialcharsbx($arForm['SID']).")&nbsp;".htmlspecialcharsbx($arForm['NAME']);
$link = "form_edit.php?lang=".LANGUAGE_ID."&ID=".$WEB_FORM_ID;
$adminChain->AddItem(array("TEXT"=>$txt, "LINK"=>$link));

$F_RIGHT = CForm::GetPermission($WEB_FORM_ID);
if($F_RIGHT<25) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

// copying
if (intval($copy_id)>0 && check_bitrix_sessid() && $F_RIGHT >= 30)
{
	$new_id = CFormStatus::Copy($copy_id);
	if ($strError == '' && intval($new_id)>0)
	{
		LocalRedirect("form_status_edit.php?ID=".$new_id."&WEB_FORM_ID=".$WEB_FORM_ID."&lang=".LANGUAGE_ID ."&strError=".urlencode($strError));
	}
}

$DEFAULT_STATUS_ID = intval(CFormStatus::GetDefault($WEB_FORM_ID));

if (($save <> '' || $apply <> '') && $REQUEST_METHOD=="POST" && $F_RIGHT >= 30 && check_bitrix_sessid())
{
	$arFields = array(
		"FORM_ID"				=> $WEB_FORM_ID,
		"C_SORT"				=> $C_SORT,
		"ACTIVE"				=> $ACTIVE,
		"TITLE"					=> $TITLE,
		"DESCRIPTION"			=> $DESCRIPTION,
		"CSS"					=> $CSS,
		"HANDLER_OUT"			=> $HANDLER_OUT,
		"HANDLER_IN"			=> $HANDLER_IN,
		"DEFAULT_VALUE"			=> $DEFAULT_VALUE,
		"arPERMISSION_VIEW"		=> $arPERMISSION_VIEW,
		"arPERMISSION_MOVE"		=> $arPERMISSION_MOVE,
		"arPERMISSION_EDIT"		=> $arPERMISSION_EDIT,
		"arPERMISSION_DELETE"	=> $arPERMISSION_DELETE,
		"arMAIL_TEMPLATE"		=> $arMAIL_TEMPLATE,
		);
	$res = intval(CFormStatus::Set($arFields, $ID));
	if ($res>0)
	{
		$ID = $res;
		if ($strError == '')
		{
			if ($save <> '') LocalRedirect("form_status_list.php?WEB_FORM_ID=".$WEB_FORM_ID."&lang=".LANGUAGE_ID);
			else LocalRedirect("form_status_edit.php?ID=".$ID."&WEB_FORM_ID=".$WEB_FORM_ID."&lang=".LANGUAGE_ID."&".$tabControl->ActiveTabParam());
		}
	}
	$DB->PrepareFields("b_form_status");
}

if ($ID > 0)
	$rsStatus = CFormStatus::GetByID($ID);

if ($ID <= 0 || !$rsStatus || !$rsStatus->ExtractFields())
{
	$ID=0;
	$str_ACTIVE = "Y";
	$str_C_SORT = CFormStatus::GetNextSort($WEB_FORM_ID);
	$str_CSS = "statusgreen";
	$str_DEFAULT_VALUE = (intval($arForm["STATUSES"])<=0) ? "Y" : "N";
}
else
{
	CFormStatus::GetPermissionList($ID, $arPERMISSION_VIEW, $arPERMISSION_MOVE, $arPERMISSION_EDIT, $arPERMISSION_DELETE);

	if ($strError == '')
	{
		//$arSITE = CForm::GetSiteArray($ID);
		$arMAIL_TEMPLATE = CFormStatus::GetMailTemplateArray($ID);
	}
}

if ($strError <> '') $DB->InitTableVarsForEdit("b_form_status", "", "str_");

$sDocTitle = ($ID>0) ? str_replace("#ID#", $ID, GetMessage("FORM_EDIT_RECORD")) : GetMessage("FORM_NEW_RECORD");
$APPLICATION->SetTitle($sDocTitle);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
/***************************************************************************
								HTML form
****************************************************************************/

$context = new CAdminContextMenuList($arForm['ADMIN_MENU']);
$context->Show();

echo BeginNote('width="100%"');?>
<b><?=GetMessage("FORM_FORM_NAME")?></b> [<a title='<?=GetMessage("FORM_EDIT_FORM")?>' href='form_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$WEB_FORM_ID?>'><?=$WEB_FORM_ID?></a>]&nbsp;(<?=htmlspecialcharsbx($arForm["SID"])?>)&nbsp;<?=htmlspecialcharsbx($arForm["NAME"])?>
<?echo EndNote();

$aMenu = array();

if ($F_RIGHT>=30 && $ID>0)
{
	$aMenu[] = array(
		"ICON"	=> "btn_new",
		"TEXT"	=> GetMessage("FORM_CREATE"),
		"TITLE"	=> GetMessage("FORM_CREATE_TITLE"),
		"LINK"	=> "form_status_edit.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID
		);

	$aMenu[] = array(
		"ICON"	=> "btn_copy",
		"TITLE"	=> GetMessage("FORM_COPY"),
		"TEXT"	=> GetMessage("FORM_CP"),
		"LINK"	=> "form_status_edit.php?ID=".$ID."&amp;copy_id=".$ID."&lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID."&".bitrix_sessid_get()
		);

	$aMenu[] = array(
		"ICON"	=> "btn_delete",
		"TEXT"	=> GetMessage("FORM_DELETE_TITLE"),
		"TITLE"	=> GetMessage("FORM_DELETE_TITLE"),
		"LINK"	=> "javascript:if(confirm('".GetMessage("FORM_CONFIRM_DELETE")."'))window.location='form_status_list.php?action=delete&ID=".$ID."&WEB_FORM_ID=".$WEB_FORM_ID."&".bitrix_sessid_get()."&lang=".LANGUAGE_ID."';",
		"WARNING"=>"Y"
		);

	$context = new CAdminContextMenu($aMenu);
	$context->Show();
}

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
	$message = new CAdminMessage(GetMessage("FORM_ERROR_SAVE"), $e);
	echo $message->Show();
}
ShowNote($strNote);
?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID?>" name="form_status_edit">
<?=bitrix_sessid_post()?>
<input type="hidden" name="ID" value=<?=$ID?>>
<?
$tabControl->Begin();
?>
<?
//********************
//General Tab
//********************
$tabControl->BeginNextTab();
?>
	<? if ($str_TIMESTAMP_X <> '' && $str_TIMESTAMP_X!="00.00.0000 00:00:00") : ?>
	<tr>
		<td><?=GetMessage("FORM_TIMESTAMP")?></td>
		<td><?=$str_TIMESTAMP_X?></td>
	</tr>
	<? endif; ?>
	<? if ($ID>0) : ?>
	<tr>
		<td><?=GetMessage("FORM_RESULTS_STATUS")?></td>
		<td><a title="<?=GetMessage("FORM_RESULTS_TITLE")?>" href="form_result_list.php?lang=<?=LANGUAGE_ID?>&WEB_FORM_ID=<?=$WEB_FORM_ID?>&find_status=<?echo $ID?>&set_filter=Y"><?echo intval($str_RESULTS)?></a></td>
	</tr>
	<?endif;?>
	<tr>
		<td width="40%"><?=GetMessage("FORM_ACTIVE")?></td>
		<td width="60%"><?=InputType("checkbox","ACTIVE","Y",$str_ACTIVE,false)?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?=GetMessage("FORM_TITLE")?></td>
		<td><input type="text" name="TITLE" size="50" maxlength="255" value="<?=$str_TITLE?>"></td>
	</tr>
	<tr>
		<td><?=GetMessage("FORM_DESCRIPTION")?></td>
		<td><textarea name="DESCRIPTION" rows="3" cols="38"><?echo $str_DESCRIPTION?></textarea></td>
	</tr>
	<tr>
		<td><?=GetMessage("FORM_SORTING")?></td>
		<td><input type="text" name="C_SORT" size="5" value="<?=$str_C_SORT?>"></td>
	</tr>
	<?
	$disabled = "disabled";
	if ($DEFAULT_STATUS_ID<=0 || $DEFAULT_STATUS_ID==$ID) $disabled = "";
	?>
	<tr>
		<td><?=GetMessage("FORM_DEFAULT_VALUE")?></td>
		<td><?=InputType("checkbox","DEFAULT_VALUE","Y",$str_DEFAULT_VALUE,false,"", $disabled)?></td>
	</tr>
	<tr>
		<td><?=GetMessage("FORM_CSS")?></td>
		<td><input type="text" name="CSS" size="10" value="<?=$str_CSS?>"></td>
	</tr>
	<tr>
		<td><?=GetMessage("FORM_HANDLER_OUT")?></td>
		<td><input type="text" name="HANDLER_OUT" size="60" value="<?=$str_HANDLER_OUT?>"></td>
	</tr>
	<tr>
		<td><?=GetMessage("FORM_HANDLER_IN")?></td>
		<td><input type="text" name="HANDLER_IN" size="60" value="<?=$str_HANDLER_IN?>"></td>
	</tr>
<?
//********************
//Mail templates tab
//********************

$tabControl->BeginNextTab();
?>

<script>
<!--
var bInProcess = false;

function GenerateMailTemplate()
{
	if (bInProcess) return;

	var url = '/bitrix/admin/form_status_mail.php?lang=<?=LANGUAGE_ID?>&<?=bitrix_sessid_get()?>&WEB_FORM_ID=<?=$WEB_FORM_ID;?>&STATUS_ID=<?=intval($ID)?>';
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

			var obCheckbox = BX.create('INPUT', {
				props: {
					type: 'checkbox',
					id: arReturn.TEMPLATES[i].ID,
					name: 'arMAIL_TEMPLATE[]',
					value: arReturn.TEMPLATES[i].ID
				}
			});

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

		setTimeout(function() {
			var r = BX.findChildren(obTable, {tag: /^(input|select|textarea)$/i}, true);
			if (r && r.length > 0)
			{
				for (var i=0,l=r.length;i<l;i++)
				{
					if (r[i].form && r[i].form.BXAUTOSAVE)
						r[i].form.BXAUTOSAVE.RegisterInput(r[i]);
					else
						break;
				}
			}
		}, 10);
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
		var url = '/bitrix/admin/form_status_mail.php?action=delete&ID=' + template_id + '&lang=<?echo LANGUAGE_ID?>&<?=bitrix_sessid_get()?>&WEB_FORM_ID=<?=intval($ID)?>';

		CHttpRequest.Action = __process;
		ShowWaitWindow();
		bInProcess = true;
		CHttpRequest.Send(url);
	}
}
//-->
</script>
	<?if ($ID>0):?>
	<tr>
		<td width="40%" valign="top"><?=GetMessage("FORM_MAIL_TEMPLATE")?></td>
		<td width="60%" valign="top" nowrap style="padding:0px" id="form_templates">
			<?
			$arrMAIL = array();
			$arr = CFormStatus::GetTemplateList($ID);
			if (is_array($arr) && count($arr)>0):
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
							?>&nbsp;&nbsp;&nbsp;[&nbsp;<a href="/bitrix/admin/message_admin.php?find_type_id=FORM_STATUS_CHANGE_<?=$arForm['SID']?>_<?=$ID?>&set_filter=Y"><?echo GetMessage("FORM_VIEW_TEMPLATE_LIST")?></a>&nbsp;]<?
						endif;
						?></td>
					</tr>
				</table>
			<?
			endif;
			?>
		</td>
	</tr>
	<?else:?>
	<tr>
		<td>
<?
		echo BeginNote(), GetMessage("FORM_STATUS_NOT_SAVED"), EndNote();
?>
		</td>
	</tr>
	<?endif;?>
<?
//********************
//Access Tab
//********************
$tabControl->BeginNextTab();
?>

	<?
	$arr_ref = array();
	$arr_ref_id = array();
	$arr_ref[] = GetMessage("FORM_OWNER");
	$arr_ref_id[] = 0;
	$z = CGroup::GetDropDownList("and ACTIVE='Y' and ID>1");
	while ($zr=$z->Fetch())
	{
		$reference_id = $zr["REFERENCE_ID"];
		$reference = $zr["REFERENCE"];
		if ($reference_id == '') $reference_id = $zr["reference_id"];
		if ($reference == '') $reference = $zr["reference"];
		$arr_ref[] = $reference;
		$arr_ref_id[] = $reference_id;
	}
	?>
	<tr>
		<td><?=GetMessage('FORM_MOVE_RIGHTS')." [MOVE]";?><br><img src="/bitrix/images/form/mouse.gif" width="44" height="21" border=0 alt=""></td>
		<td><?
		echo SelectBoxMFromArray("arPERMISSION_MOVE[]", array("REFERENCE" => $arr_ref, "REFERENCE_ID" => $arr_ref_id), $arPERMISSION_MOVE, "", false, 8, "");
		?></td>
	</tr>
	<tr>
		<td><?echo GetMessage('FORM_VIEW_RIGHTS')." [VIEW]"?><br><img src="/bitrix/images/form/mouse.gif" width="44" height="21" border=0 alt=""></td>
		<td><?
		echo SelectBoxMFromArray("arPERMISSION_VIEW[]", array("REFERENCE" => $arr_ref, "REFERENCE_ID" => $arr_ref_id), $arPERMISSION_VIEW, "", false, 8, "");
		?></td>
	</tr>
	<tr>
		<td><?echo GetMessage('FORM_EDIT_RIGHTS')." [EDIT]"?><br><img src="/bitrix/images/form/mouse.gif" width="44" height="21" border=0 alt=""></td>
		<td><?
		echo SelectBoxMFromArray("arPERMISSION_EDIT[]", array("REFERENCE" => $arr_ref, "REFERENCE_ID" => $arr_ref_id), $arPERMISSION_EDIT, "", false, 8, "");
		?></td>
	</tr>
	<tr>
		<td><?echo GetMessage('FORM_DELETE_RIGHTS')." [DELETE]"?><br><img src="/bitrix/images/form/mouse.gif" width="44" height="21" border=0 alt=""></td>
		<td><?
		echo SelectBoxMFromArray("arPERMISSION_DELETE[]", array("REFERENCE" => $arr_ref, "REFERENCE_ID" => $arr_ref_id), $arPERMISSION_DELETE, "", false, 8, "");
		?></td>
	</tr>
<?
$tabControl->EndTab();
$tabControl->Buttons(array("disabled"=>($F_RIGHT<30), "back_url"=>"form_list.php?lang=".LANGUAGE_ID));
$tabControl->End();
?>
</form>
<? require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");