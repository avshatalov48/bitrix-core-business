<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

IncludeModuleLangFile(__FILE__);
CModule::IncludeModule("fileman");

$APPLICATION->SetTitle(GetMessage('FM_ST_ACCESS_TITLE'));

if (!$USER->CanDoOperation('fileman_edit_all_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/classes/general/sticker.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

// Get stickers tasks with names
$arTasks = CSticker::GetTasks();

//Fetch user groups
$arGroups = array();
$db_groups = CGroup::GetList($order="sort", $by="asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
while($arRes = $db_groups->Fetch())
	$arGroups[] = $arRes;

$defaultAccess = COption::GetOptionString('fileman', 'stickers_default_access', false);
if ($defaultAccess === false)
	foreach ($arTasks as $id => $task)
	{
		if ($task['letter'] == 'D')
		{
			$defaultAccess = $id;
			break;
		}
	}

if($REQUEST_METHOD=="POST" && $_POST['saveperm'] == 'Y' && check_bitrix_sessid())
{
	//Clear all
	if ($_REQUEST['clear_all'] == "Y")
		CSticker::DeleteAll();

	// Settings
	COption::SetOptionString("fileman", "stickers_hide_bottom", $_REQUEST['set_hide_bottom'] == "Y" ? "Y" : "N");
	COption::SetOptionString("fileman", "stickers_start_sizes", $_REQUEST['set_sizes']);
	COption::SetOptionString("fileman", "stickers_use_hotkeys", $_REQUEST['use_hotkeys'] == "Y" ? "Y" : "N");

	// Access
	$arTaskPerm = Array();
	foreach ($arGroups as $group)
	{
		$tid = ${"TASKS_".$group["ID"]};
		if ($tid)
			$arTaskPerm[$group["ID"]] = intVal($tid);
	}
	CSticker::SaveAccessPermissions($arTaskPerm);
	COption::SetOptionString('fileman', 'stickers_default_access', intVal($_REQUEST['st_default_access']));
	$defaultAccess = intVal($_REQUEST['st_default_access']);
}

$arTaskPerm = CSticker::GetAccessPermissions();

$strTaskOpt = "";
foreach ($arTasks as $id => $task)
	$strTaskOpt .= '<option value="'.$id.'">'.(strlen($task['letter']) > 0 ? '['.$task['letter'].'] ' : '').$task['title'].'</option>';

$strGroupsOpt = '<option value="">('.GetMessage('FM_ST_SELECT_GROUP').')</option>';
$arGroupIndex = array();
foreach ($arGroups as $group)
{
	$arGroupIndex[$group['ID']] = $group['NAME'];
	$strGroupsOpt .= '<option value="'.$group['ID'].'">'.htmlspecialcharsex($group['NAME']).' ['.intVal($group['ID']).']</option>';
}
?>

<form method="POST" action="<?= $APPLICATION->GetCurPage()?>?lang=<?= LANGUAGE_ID?>" name="st_access_form">
<input type="hidden" name="site" value="<?= htmlspecialcharsbx($site) ?>">
<input type="hidden" name="saveperm" value="Y">
<input type="hidden" id="bxst_clear_all" name="clear_all" value="N">
<input type="hidden" name="lang" value="<?= LANGUAGE_ID?>">
<?= bitrix_sessid_post()?>

<?
$aTabs = array(
	array("DIV" => "stickers_settings", "TAB" => GetMessage("FM_ST_SETTINGS"), "ICON" => "fileman", "TITLE" => GetMessage("FM_ST_SETTINGS_TITLE")),
	array("DIV" => "stickers_access", "TAB" => GetMessage("FM_ST_ACCESS"), "ICON" => "fileman", "TITLE" => GetMessage("FM_ST_ACCESS_TITLE")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>


<?$tabControl->BeginNextTab();?>
<tr>
	<td colspan="2">
		<table>
		<tr>
			<td class="adm-detail-content-cell-l" width="40%">
				<input type="checkbox" name="set_hide_bottom" id="set_hide_bottom" value="Y" <? if (COption::GetOptionString("fileman", "stickers_hide_bottom", "Y") == "Y") {echo "checked";}?>/>
			</td>
			<td class="adm-detail-content-cell-r" width="60%"><label for="set_hide_bottom"><?= GetMessage('FM_ST_SET_HIDE_BOTTOM')?></label></td>
		</tr>
		<tr style="display: none;">
			<td class="adm-detail-content-cell-l">
				<input type="checkbox" name="set_supafly" id="set_supafly" value="Y"/>
			</td>
			<td class="adm-detail-content-cell-r"><label for="set_supafly"><?= GetMessage('FM_ST_SET_SUPAFLY')?></label></td>
		</tr>
		<tr style="display: none;">
			<td class="adm-detail-content-cell-l">
				<input type="checkbox" name="set_smart_marker" id="set_smart_marker" value="Y" />
			</td>
			<td class="adm-detail-content-cell-r"><label for="set_smart_marker"><?= GetMessage('FM_ST_SET_SMART_MARKER')?></label></td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l">
				<input type="checkbox" name="use_hotkeys" id="use_hotkeys" value="Y" <?if(COption::GetOptionString("fileman", "stickers_use_hotkeys", "Y") == "Y"){echo "checked";}?>/>
			</td>
			<td class="adm-detail-content-cell-r"><label for="use_hotkeys"><?= GetMessage('FM_ST_USE_HOTKEYS')?></label></td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l"><label for="set_sizes"><?= GetMessage('FM_ST_SET_SIZES')?>:</label></td>
			<td class="adm-detail-content-cell-r">
				<?$size = COption::GetOptionString("fileman", "stickers_start_sizes", "350_200");?>
				<select name="set_sizes" id="set_sizes">
					<option value="280_160" <? if ($size == "280_160") {echo "selected";}?>>280 x 160</option>
					<option value="350_200" <? if ($size == "350_200") {echo "selected";}?>>350 x 200</option>
					<option value="400_250" <? if ($size == "400_250") {echo "selected";}?>>400 x 250</option>
				</select>
			</td>
		</tr>

		<tr>
			<td colSpan="2" class="adm-detail-content-cell-r">
				<a href="javascript: void('');" onclick="if (confirm('<?= GetMessage('FM_ST_CLEAR_ALL_CONFIRM');?>')) {BX('bxst_clear_all').value='Y'; document.forms.st_access_form.submit(); return false;}"><?= GetMessage('FM_ST_CLEAR_ALL');?></a>
			</td>
		</tr>
		</table>
	</td>
</tr>
<?$tabControl->BeginNextTab();?>

<tr>
	<td colspan="2">
	<script>
	function addGroup()
	{
		var tbl = BX('bxst_access_table');
		var r = tbl.insertRow(tbl.rows.length - 2);

		var grSel = BX.adjust(r.insertCell(-1), {props: {className: 'field-name', width: '50%'}}).appendChild(BX('bxst_group_sel').cloneNode(true));
		grSel.removeAttribute('id');

		var taskSel = BX.adjust(r.insertCell(-1), {props: {width: '50%'}}).appendChild(BX('bxst_task_sel').cloneNode(true));
		taskSel.removeAttribute('id');

		grSel.onchange = function()
		{
			if (this.value.length > 0)
				taskSel.name = "TASKS_" + this.value;
			else
				taskSel.name = "";
		};
	}
	</script>
	<table class="edit-table" id="bxst_access_table">
		<tr>
			<td class="field-name" width="50%"><label for="st_default_access"><b><?= GetMessage('FM_ST_ACCESS_DEFAULT')?>:</b></label></td>
			<td  width="50%">
				<select name="st_default_access" id="st_default_access">
				<?foreach ($arTasks as $id => $task):?>
					<option value="<?= $id?>" <? if($id == $defaultAccess){echo 'selected';}?>>
					<? echo(strlen($task['letter']) > 0 ? '['.$task['letter'].'] ' : '').$task['title']; ?></option>
				<?endforeach;?>
				</select></td>
		</tr>

		<?foreach($arTaskPerm as $group_id => $task_id):?>
		<tr>
			<td class="field-name" width="50%"><label for="TASKS_<?= $group_id?>"><?= htmlspecialcharsex($arGroupIndex[$group_id])." [<a title=\"".GetMessage("FM_ST_EDIT_GROUP_TITLE")."\" href=\"/bitrix/admin/group_edit.php?ID=".$group_id."&amp;lang=".LANGUAGE_ID."\">".$group_id."</a>]"?>:</label></td>
			<td  width="50%">
				<select name="TASKS_<?= $group_id?>" id="TASKS_<?= $group_id?>">
					<option value="">&lt;  <?= GetMessage('FM_ST_ACCESS_DEFAULT')?> &gt;</option>
					<?foreach ($arTasks as $id => $task):?>
						<option value="<?= $id?>" <?if ($task_id == $id){ echo" selected";}?>><?= htmlspecialcharsex((strlen($task['letter']) > 0 ? '['.$task['letter'].'] ' : '').$task['title'])?></option>
					<?endforeach;?>
				</select>
			</td>
		</tr>
		<?endforeach;?>

			<tr>
				<td class="field-name" width="50%">

				</td>
				<td  width="50%">

				</td>
			</tr>

			<tr>
				<td colSpan="2" align="center">
				<a href="javascript: void('');" onclick="addGroup(); return false;"><?= GetMessage('FM_ST_ADD_GROUP_TASK')?></a>
				</td>
			</tr>
	</table>
		<?= BeginNote();?>
		<?= GetMessage("FM_ST_ACCESS_NOTE", array('#LINK_BEGIN#' => '<a href="/bitrix/admin/settings.php?lang='.LANGUAGE_ID.'&mid=fileman&tabControl_active_tab=edit3&'.bitrix_sessid_get().'">', '#LINK_END#' => '</a>'));?>
		<?= EndNote();?>
		<div style="display: none;">
		<select id="bxst_group_sel"><?= $strGroupsOpt?></select>
		<select id="bxst_task_sel">
			<option value=""><?= '< '.strtolower(GetMessage('FM_ST_ACCESS_DEFAULT')).' >'?></option>
			<?= $strTaskOpt?>
		</select>
		</div>
	</td>
</tr>

<?$tabControl->EndTab();?>

<?
$tabControl->Buttons(
	array(
		"disabled" => false,
		"back_url" => "/bitrix/admin/?lang=".LANGUAGE_ID."&".bitrix_sessid_get()
	)
);
?>

<?$tabControl->End();?>

</form>



<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
