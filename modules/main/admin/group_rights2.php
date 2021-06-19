<?
if (!$USER->IsAdmin())
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$md = CModule::CreateModuleObject($module_id);

$arGROUPS = array();
$arFilter = Array("ACTIVE"=>"Y");
if($md->SHOW_SUPER_ADMIN_GROUP_RIGHTS != "Y")
	$arFilter["ADMIN"] = "N";

$z = CGroup::GetList("sort", "asc", $arFilter);
while($zr = $z->Fetch())
{
	$ar = array();
	$ar["ID"] = intval($zr["ID"]);
	$ar["NAME"] = htmlspecialcharsbx($zr["NAME"]);
	$arGROUPS[] = $ar;
}

if($REQUEST_METHOD=="POST" && $Update <> '' && $USER->IsAdmin() && check_bitrix_sessid())
{
	// установка прав групп
	COption::SetOptionString($module_id, "GROUP_DEFAULT_TASK", $GROUP_DEFAULT_TASK, "Task for groups by default");
	$letter = ($l = CTask::GetLetter($GROUP_DEFAULT_TASK)) ? $l : 'D';
	COption::SetOptionString($module_id, "GROUP_DEFAULT_RIGHT", $letter, "Right for groups by default");

	$arTasksInModule = Array();
	foreach($arGROUPS as $value)
	{
		$tid = ${"TASKS_".$value["ID"]};
		if ($tid)
			$arTasksInModule[$value["ID"]] = Array('ID'=>$tid);

		$rt = ($tid) ? CTask::GetLetter($tid) : '';
		if ($rt <> '' && $rt != "NOT_REF")
			$APPLICATION->SetGroupRight($module_id, $value["ID"], $rt);
		else
			$APPLICATION->DelGroupRight($module_id, array($value["ID"]));
	}
	CGroup::SetTasksForModule($module_id, $arTasksInModule);
}

$GROUP_DEFAULT_TASK = COption::GetOptionString($module_id, "GROUP_DEFAULT_TASK", "");
if ($GROUP_DEFAULT_TASK == '')
{
	$GROUP_DEFAULT_RIGHT = COption::GetOptionString($module_id, "GROUP_DEFAULT_RIGHT", "D");
	$GROUP_DEFAULT_TASK = CTask::GetIdByLetter($GROUP_DEFAULT_RIGHT, $module_id, 'module');
	if ($GROUP_DEFAULT_TASK)
		COption::SetOptionString($module_id, "GROUP_DEFAULT_TASK", $GROUP_DEFAULT_TASK);
}
?>
<tr>
	<td width="40%"><b><?=GetMessage("MAIN_BY_DEFAULT");?></b></td>
	<td width="60%"><?
	$arTasksInModule = CTask::GetTasksInModules(true,$module_id,'module');
	$arTasks = $arTasksInModule[$module_id];
	echo SelectBoxFromArray("GROUP_DEFAULT_TASK", $arTasks, htmlspecialcharsbx($GROUP_DEFAULT_TASK));
	?><?=bitrix_sessid_post()?></td>
</tr>
<?
$arUsedGroups = array();
$arTaskInModule = CGroup::GetTasksForModule($module_id);
foreach($arGROUPS as $value):
	$v = (isset($arTaskInModule[$value["ID"]]['ID'])? $arTaskInModule[$value["ID"]]['ID'] : false);
	if($v):
		$arUsedGroups[$value["ID"]] = true;
?>
<tr>
	<td><?=$value["NAME"]." [<a title=\"".GetMessage("MAIN_USER_GROUP_TITLE")."\" href=\"/bitrix/admin/group_edit.php?ID=".$value["ID"]."&amp;lang=".LANGUAGE_ID."\">".$value["ID"]."</a>]:"?><?
	if ($value["ID"]==1 && $md->SHOW_SUPER_ADMIN_GROUP_RIGHTS=="Y"):
		echo "<br><small>".GetMessage("MAIN_SUPER_ADMIN_RIGHTS_COMMENT")."</small>";
	endif;
	?></td>
	<td><?
	echo SelectBoxFromArray("TASKS_".$value["ID"], $arTasks, $v, GetMessage("MAIN_DEFAULT"));
	?></td>
</tr>
<?
	endif;
endforeach;

if(count($arGROUPS) > count($arUsedGroups)):
?>
<tr>
	<td><select style="width:300px" onchange="settingsSetGroupID(this)">
		<option value=""><?echo GetMessage("group_rights_select")?></option>
<?
foreach($arGROUPS as $group):
	if($arUsedGroups[$group["ID"]] == true)
		continue;
?>
		<option value="<?=$group["ID"]?>"><?=$group["NAME"]." [".$group["ID"]."]"?></option>
<?endforeach?>
	</select></td>
	<td><?
	echo SelectBoxFromArray("", $arTasks, "", GetMessage("MAIN_DEFAULT"));
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td style="padding-bottom:10px;">
<script type="text/javascript">
function settingsSetGroupID(el)
{
	var tr = jsUtils.FindParentObject(el, "tr");
	var sel = jsUtils.FindChildObject(tr.cells[1], "select");
	sel.name = "TASKS_"+el.value;
}

function settingsAddRights(a)
{
	var row = jsUtils.FindParentObject(a, "tr");
	var tbl = row.parentNode;

	var tableRow = tbl.rows[row.rowIndex-1].cloneNode(true);
	tbl.insertBefore(tableRow, row);

	var sel = jsUtils.FindChildObject(tableRow.cells[1], "select");
	sel.name = "";
	sel.selectedIndex = 0;

	sel = jsUtils.FindChildObject(tableRow.cells[0], "select");
	sel.selectedIndex = 0;
}
</script>
<a href="javascript:void(0)" onclick="settingsAddRights(this)" hidefocus="true" class="adm-btn"><?echo GetMessage("group_rights_add")?></a>
	</td>
</tr>
<?endif?>