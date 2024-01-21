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

require_once(__DIR__."/../include/prolog_admin_before.php");
define("HELP_FILE", "users/task_edit.php");

ClearVars();

if (!$USER->CanDoOperation('view_tasks'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

/***************************************************************************
Handling GET | POST
****************************************************************************/

$ID = intval($_REQUEST['ID'] ?? 0);
$COPY_ID = intval($_REQUEST["COPY_ID"] ?? 0);

if($COPY_ID > 0)
	$ID = $COPY_ID;

$message = null;

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("TAB_1"), "ICON" => "", "TITLE" => GetMessage("TAB_1_TITLE")),
	array("DIV" => "edit2", "TAB" => GetMessage("TAB_2"), "ICON" => "", "TITLE" => GetMessage('TAB_2_TITLE'))
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($_SERVER["REQUEST_METHOD"]=="POST" && (!empty($_POST['save']) || !empty($_POST['apply'])) && $USER->CanDoOperation('edit_tasks') && check_bitrix_sessid())
{
	$aMsg = Array();
	$LETTER = mb_strtoupper($_POST["LETTER"]);
	$arFields = array
	(
		"NAME" => $_POST["NAME"],
		"DESCRIPTION" => $_POST["DESCRIPTION"],
		"LETTER" => $LETTER,
		"BINDING" => $_POST["BINDING"],
		"MODULE_ID" => $_POST["MODULE_ID"]
	);

	if($ID>0 && $COPY_ID<=0)
	{
		CTask::UpdateModuleRights($ID, $_POST["MODULE_ID"], $LETTER);
		CTask::Update($arFields, $ID);
	}
	else
	{
		$ID = CTask::Add($arFields);
	}

	/** @var CAdminException $e */
	if($e = $APPLICATION->GetException())
		$aMsg = $e->messages;

	if(empty($aMsg))
	{
		if (!isset($_POST['OPERATION_ID']))
			$arOperationIds = Array();
		else
			$arOperationIds = $_POST['OPERATION_ID'];

		$old_arOperationIds = CTask::GetOperations($ID);
		if (!empty(array_diff($old_arOperationIds, $arOperationIds)) || !empty(array_diff($arOperationIds, $old_arOperationIds)))
		{
			CTask::SetOperations($ID, $arOperationIds);
		}

		if (!empty($_POST["save"]))
			LocalRedirect("task_admin.php?lang=".LANGUAGE_ID);
		elseif (!empty($_POST["apply"]))
			LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&ID=".$ID."&".$tabControl->ActiveTabParam());
	}
	else
	{
		$message = new CAdminMessage(GetMessage('TASK_SAVE_ERROR'), new CAdminException($aMsg));
	}
}

$z = CTask::GetByID($ID);
if(!$z->ExtractFields("str_") || $ID == 0)
{
	$ID = 0;
	$str_NAME = '';
	$str_DESCRIPTION = '';
	$str_LETTER = '';
	$str_SYS = 'N';
	$str_BINDING = 'module';
	$str_MODULE_ID = 'main';
}
else
{
	if($COPY_ID>0)
		$str_SYS = 'N';
}

$sDocTitle = ($ID>0 && $COPY_ID<=0? GetMessage("EDIT_TASK_TITLE", array("#ID#"=>$ID)) : GetMessage("NEW_TASK_TITLE"));
$APPLICATION->SetTitle($sDocTitle);

/***************************************************************************
				HTML form
****************************************************************************/

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"	=> GetMessage("RECORD_LIST"),
		"TITLE"	=> GetMessage("RECORD_LIST_TITLE"),
		"LINK"	=> "/bitrix/admin/task_admin.php?lang=".LANGUAGE_ID."&amp;set_default=Y",
		"ICON"	=> "btn_list"
	)
);

if($ID > 0 && $COPY_ID <= 0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");

	$aMenu[] = array(
		"TEXT"	=> GetMessage("MAIN_NEW_RECORD"),
		"TITLE"	=> GetMessage("MAIN_NEW_RECORD_TITLE"),
		"LINK"	=> "/bitrix/admin/task_edit.php?lang=".LANGUAGE_ID,
		"ICON"	=> "btn_new"
	);
	$aMenu[] = array(
		"TEXT"	=> GetMessage("MAIN_COPY_RECORD"),
		"TITLE"	=> GetMessage("MAIN_COPY_RECORD_TITLE"),
		"LINK"	=> "/bitrix/admin/task_edit.php?lang=".LANGUAGE_ID."&amp;COPY_ID=".$ID,
		"ICON"	=> "btn_copy"
	);
	if ($USER->CanDoOperation('edit_tasks') && $str_SYS != 'Y')
	{
		$aMenu[] = array(
			"TEXT"	=> GetMessage("MAIN_DELETE_RECORD"),
			"TITLE"	=> GetMessage("MAIN_DELETE_RECORD_TITLE"),
			"LINK"	=> "javascript:if(confirm('".GetMessage("MAIN_DELETE_RECORD_CONF")."')) window.location='/bitrix/admin/task_admin.php?ID=".$ID."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."&action_button=delete';",
			"ICON"	=> "btn_delete"
		);
	}
}

$context = new CAdminContextMenu($aMenu);
$context->Show();
?>
<?
if($message)
	echo $message->Show();
?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="form1">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?echo LANG?>">
<input type="hidden" name="ID" value="<?echo $ID?>">
<?if($COPY_ID <> ''):?><input type="hidden" name="COPY_ID" value="<?echo htmlspecialcharsbx($COPY_ID)?>"><?endif?>
<?
$tabControl->Begin();

$tabControl->BeginNextTab();
if (isset($_POST['NAME']))
{
	$str_NAME = htmlspecialcharsbx($_POST['NAME']);
	$str_DESCRIPTION = htmlspecialcharsbx($_POST['DESCRIPTION'] ?? '');
	$str_MODULE_ID = htmlspecialcharsbx($_POST['MODULE_ID'] ?? '');
	$str_BINDING = htmlspecialcharsbx($_POST['BINDING'] ?? '');
	$str_LETTER = htmlspecialcharsbx($_POST['LETTER'] ?? '');
}

$dbOperations = COperation::GetList();

$arOperations = Array();
$arBindings = Array();
?>
<script>
var arOperations = [];
var arBingings = {};
<?
while ($arOperation = $dbOperations->Fetch())
{
	$mid = $arOperation["MODULE_ID"];
	if (!isset($arBindings[$mid]))
	{
		$arBindings[$mid] = Array();
		?>arBingings['<?=$mid?>'] = {};<?
	}
	if (!in_array($arOperation["BINDING"], $arBindings[$mid]))
	{
		$arBindings[$mid][] = $b = $arOperation["BINDING"];
		$bindingTitle = CTask::GetLangTitle($b, $mid);
		if ($bindingTitle == $b)
		{
			$bindingTitle = CTask::GetLangTitle($bindingTitle, "main");
		}
		?>arBingings['<?=$mid?>'].<?=$b?> = "<?=$bindingTitle?>";<?
	}

	$arOperations[] = array(
		'ID' => $arOperation["ID"],
		'NAME' => $arOperation["NAME"],
		'TITLE' => COperation::GetLangTitle($arOperation["NAME"], $arOperation["MODULE_ID"]),
		'BINDING' => $arOperation["BINDING"],
		'MODULE_ID' => $arOperation["MODULE_ID"],
		'DESCRIPTION' => COperation::GetLangDescription($arOperation["NAME"],$arOperation["DESCRIPTION"], $arOperation["MODULE_ID"])
	);
}
\Bitrix\Main\Type\Collection::sortByColumn($arOperations, 'TITLE');
?>
</script>

	<tr class="adm-detail-required-field">
		<td width="40%"><?=GetMessage('NAME')?></td>
		<td width="60%"><input type="text" name="NAME" size="40" maxlength="100" value="<? echo CTask::GetLangTitle($str_NAME, $str_MODULE_ID);?>"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?=GetMessage('MODULE_ID')?></td>
		<td>
		<script>
		var arModules = ['main'];
		</script>
		<select name="MODULE_ID" id="__module_id_select">
			<option value="main" <? echo ($str_MODULE_ID == 'main') ? 'selected' : '';?>><?=GetMessage('KERNEL')?></option>
		<?
		$modules = COperation::GetAllowedModules();
		foreach($modules as $MID):
			if ($MID == "main")
				continue;
			if (!($m = CModule::CreateModuleObject($MID)))
				continue;
		?>
			<script>arModules.push('<?=$MID?>');</script>
			<option value="<?=htmlspecialcharsbx($MID)?>"<?echo ($str_MODULE_ID == $MID? ' selected' : '');?>><?=htmlspecialcharsbx($m->MODULE_NAME);?></option>
		<?endforeach;?>
		</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage('SYS_TITLE')?>:</td>
		<td><?echo ($str_SYS == 'Y') ? GetMessage("MAIN_YES") : GetMessage("MAIN_NO")?></td>
	</tr>
	<tr>
		<td><?=GetMessage('TASK_BINDING')?>:</td>
		<td>
		<?
		if (!isset($arBindings[$str_MODULE_ID]) || count($arBindings[$str_MODULE_ID]) < 1)
			$arBindings[$str_MODULE_ID] = Array('module');
		?>
		<select name="BINDING" id="__binding_select">
			<?
			for ($i = 0, $l = count($arBindings[$str_MODULE_ID]); $i < $l; $i++)
			{
				$b = $arBindings[$str_MODULE_ID][$i];
				$bindingTitle = CTask::GetLangTitle($b, $str_MODULE_ID);
				if ($bindingTitle == $b)
				{
					$bindingTitle = CTask::GetLangTitle($bindingTitle, "main");
				}
				echo '<option value="'.$b.'" '.($b == $str_BINDING ? 'selected' : '').'>'.$bindingTitle.'</option>';
			}
			?>
		</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage('LETTER')?>:</td>
		<td>
		<input type="text" name="LETTER" size="1" maxlength="1" value="<?=$str_LETTER?>">
		</td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage('DESCRIPTION')?></td>
		<td><textarea name="DESCRIPTION" cols="30" rows="5"><? echo CTask::GetLangDescription($str_NAME, $str_DESCRIPTION, $str_MODULE_ID);?></textarea>
		</td>
	</tr>
	<?$tabControl->BeginNextTab();?>
	<tr>
		<td colspan="2" align="center">
		<table border="0" cellpadding="5" cellspacing="0" align="center">
			<tr>
				<td width="10%" align="center">&nbsp;</td>
				<td width="90%">&nbsp;</td>
			</tr>
			<?
			$arTaskOperations = $_POST['OPERATION_ID'] ?? CTask::GetOperations($ID);

			$ind = -1;
			foreach($arOperations as $arOperation)
			{
				$ind++;
				?>
				<tr id="operation_row_<?=$ind?>"
				<?echo (($arOperation["MODULE_ID"] != $str_MODULE_ID) || ($arOperation["BINDING"] != $str_BINDING)) ? 'style="display: none"' : ''?>>
					<td align="right" style="padding: 0 10px 0 10px">
						<input type="checkbox" name="OPERATION_ID[]" id="OPERATION_ID_<?=$ind ?>" value="<?=$arOperation["ID"]?>" <? echo (in_array($arOperation["ID"], $arTaskOperations)) ? " checked" : ''?>>
						<script>
						arOperations['<?=$ind?>'] = {
							name : '<?=CUtil::JSEscape($arOperation["TITLE"])?>',
							module_id : '<?=$arOperation["MODULE_ID"]?>',
							binding : '<?=$arOperation["BINDING"]?>'
						}
						</script>
					</td>
					<td align="left">
					<label for="OPERATION_ID_<?= $ind ?>"
						title="<?=htmlspecialcharsbx($arOperation["DESCRIPTION"]);?>">
						<?=htmlspecialcharsbx($arOperation["TITLE"])?>
						<?if($arOperation["TITLE"] != $arOperation['NAME']):?>
						(<?=htmlspecialcharsbx($arOperation['NAME'])?>)
						<?endif;?>
					</label></td>
				</tr>
				<?
			}
			?>
			<tr>
				<td align = "center" colSpan = "2"><div id = '__noneopermess' style="display: none;"></div></td>
			</tr>
		</table>
		<script>
		var __module_id_select = document.getElementById('__module_id_select');
		var __binding_select = document.getElementById('__binding_select');
		var _noneopermess = document.getElementById('__noneopermess');
		var noOperMess = "<?=GetMessageJS('TASK_NONE_OPERATIONS');?>";

		__module_id_select.onchange = function(e)
		{
			var arB = arBingings[this.value];
			if (arB)
			{
				__binding_select.options.length = 0;
				for (var k in arB)
					__binding_select.options[__binding_select.options.length] = new Option(arB[k], k);
			}

			var binding = __binding_select.value;
			var operation_count = arOperations.length, ch;
			var bShowNoneOperMess = true;
			for (var i = 0; i < operation_count; i++)
			{
				ch = document.getElementById('OPERATION_ID_'+i);
				if (arOperations[i].module_id == this.value && arOperations[i].binding == binding)
				{
					document.getElementById('operation_row_'+i).style.display = (jsUtils.IsIE() ? 'block' : 'table-row');
					if (arOperations[i].was_checked)
						ch.checked = true;
					if (bShowNoneOperMess)
						bShowNoneOperMess = false;
				}
				else
				{
					document.getElementById('operation_row_'+i).style.display = 'none';
					if (ch.checked)
					{
						ch.checked = false;
						arOperations[i].was_checked = true;
					}
				}
			}
			showNoneOperMess(bShowNoneOperMess);
		};

		__binding_select.onchange = function(e)
		{
			var operation_count = arOperations.length, ch;
			var bShowNoneOperMess = true;
			for (var i=0;i < operation_count; i++)
			{
				ch = document.getElementById('OPERATION_ID_'+i);
				var module_id = __module_id_select.value;
				if (arOperations[i].binding == this.value && arOperations[i].module_id == module_id)
				{
					document.getElementById('operation_row_'+i).style.display = (jsUtils.IsIE() ? 'block' : 'table-row');
					if (arOperations[i].was_checked)
						ch.checked = true;
					if (bShowNoneOperMess)
						bShowNoneOperMess = false;
				}
				else
				{
					document.getElementById('operation_row_'+i).style.display = 'none';
					if (ch.checked)
					{
						ch.checked = false;
						arOperations[i].was_checked = true;
					}
				}
			}
			showNoneOperMess(bShowNoneOperMess);
		};

		var showNoneOperMess = function(bShow)
		{
			if (bShow)
			{
				var m_title, bind_title;
				var m_id = __module_id_select.value;
				var l = __module_id_select.options.length;
				for (var i = 0; i < l; i++)
				{
					if (m_id == __module_id_select.options[i].value)
						m_title = __module_id_select.options[i].innerHTML;
				}
				var b_id = __binding_select.value;
				l = __binding_select.options.length;
				for (i = 0; i < l; i++)
				{
					if (b_id == __binding_select.options[i].value)
						bind_title = __binding_select.options[i].innerHTML;
				}
				var _t = noOperMess.replace(/#MODULE_ID#/, m_title);
				_noneopermess.innerHTML = _t.replace(/#BINDING#/, bind_title);
				_noneopermess.style.display = 'block';
			}
			else
				_noneopermess.style.display = 'none';
		};
		</script>
		</td>
	</tr>
<?
$tabControl->Buttons(array("disabled" => (!$USER->CanDoOperation('edit_tasks') || $str_SYS == 'Y'), "back_url"=>"task_admin.php?lang=".LANGUAGE_ID));
$tabControl->End();
?>
</form>

<?$tabControl->ShowWarnings("form1", $message);?>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>