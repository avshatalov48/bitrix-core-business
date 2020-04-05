<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

IncludeModuleLangFile(__FILE__);

CModule::IncludeModule("fileman");

$APPLICATION->SetTitle(GetMessage('FM_ML_ACCESS_TITLE'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if (!CMedialib::CanDoOperation('medialib_view_collection', 0) || !CMedialib::CanDoOperation('medialib_access', 0))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aContext = Array();
$aContext[] = Array(
	"TEXT" => GetMessage("FM_ML_BACK_IN_ML"),
	"ICON" => "",
	"LINK" => "/bitrix/admin/fileman_medialib_admin.php?lang=".LANGUAGE_ID."&".bitrix_sessid_get(),
	"TITLE" => GetMessage("FM_ML_BACK_IN_ML")
);
$menu = new CAdminContextMenu($aContext);
$menu->Show();

function __CanDoAccess($colId)
{
	return CMedialib::CanDoOperation('medialib_view_collection', $colId) && CMedialib::CanDoOperation('medialib_access', $colId);
}

$ctRes = CMedialib::GetCollectionTree(array('CheckAccessFunk' => '__CanDoAccess'));
$curColId = isset($col_id, $ctRes['Collections'][$col_id]) ? intVal($col_id) : 0;
//Fetch groups
$arGroups = array();
$db_groups = CGroup::GetList($order="sort", $by="asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
while($arRes = $db_groups->Fetch())
	$arGroups[] = $arRes;

if($REQUEST_METHOD=="POST" && strlen($saveperm) > 0 && check_bitrix_sessid()) // TODO: access
{
	$arTaskPerm = array();
	for ($i = 0, $l = count($arGroups); $i < $l; $i++)
	{
		$id = $arGroups[$i]['ID'];
		if (isset($_POST['g_'.$id]) && intVal($_POST['g_'.$id]) > 0)
			$arTaskPerm[$id] = intVal($_POST['g_'.$id]);
	}
	CMedialib::SaveAccessPermissions($curColId, $arTaskPerm);
}

$arGroupTask = CMedialib::GetAccessPermissionsArray($curColId, $ctRes['Collections']);

$arTasks = Array();
$res = CTask::GetList(Array('LETTER' => 'asc'), Array('MODULE_ID' => 'fileman', 'BINDING' => 'medialib'));
while($arRes = $res->Fetch())
{
	$name = $arRes['TITLE'];
	if (strlen($name) == 0)
		$name = $arRes['NAME'];

	$arTasks[$arRes['ID']] = Array('title' => $name, 'letter' => $arRes['LETTER']);
}
?>

<form method="POST" action="<?= $APPLICATION->GetCurPage()?>?" name="ml_access_form">
<input type="hidden" name="site" value="<?= htmlspecialcharsbx($site) ?>">
<input type="hidden" name="saveperm" value="Y">
<input type="hidden" name="lang" value="<?= LANG ?>">
<?= bitrix_sessid_post()?>

<?
$aTabs = array(
	array("DIV" => "medialib_access", "TAB" => GetMessage("FM_ML_TAB_NAME"), "ICON" => "fileman", "TITLE" => GetMessage("FM_ML_TAB_TITLE")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>
<?$tabControl->BeginNextTab();?>

<tr>
	<td colspan="2">
		<?= GetMessage('ML_SELECT_COLLECTION')?>: <select name="col_id" id="item_cols_sel_<?=$i?>" onchange="colsOnChange(this);">
		<option value="0"><?= GetMessage('ML_ACCESS_FOR_ALL')?></option>
		<?= CMedialib::_BuildCollectionsSelectOptions($ctRes['Collections'], $ctRes['arColTree'], 0, $curColId)?></select>
	</td>
</tr>

<tr>
	<td colspan="2">
		<? /* INTERNAL TABLE */ ?>

	<table class="internal">
		<tr class="heading">
				<td valign="middle" align="center" nowrap>
					<?= GetMessage("ML_ACCESS_GROUP")?>
				</td>
				<td valign="top" align="center" nowrap>
					<?= GetMessage("ML_ACCESS_TASK")?>
				</td>
		</tr>
			<?
			//for each groups
			foreach ($arGroups as $arGroup)
			{
				$arGroup['ID'] = intVal($arGroup['ID']);
			?>
			<tr valign="top">
				<td>
					[<a href="/bitrix/admin/group_edit.php?ID=<?= $arGroup['ID']?>&lang=<?=LANGUAGE_ID?>"><?= $arGroup['ID']?></a>]&nbsp;<?= htmlspecialcharsex($arGroup['NAME'])?>:
				</td>
				<td>
					<select name="g_<?= $arGroup['ID']?>" class="typeselect">
						<?foreach ($arTasks as $id => $ar):?>
							<option value="<?=$id?>"<?if ($arGroupTask[$arGroup['ID']] == $id) echo" selected";?>><?= htmlspecialcharsex($ar['title']);?></option>
						<?endforeach;?>
					</select>
				</td>
			</tr>
			<?
			}
			?>
	</table>

		<? /* INTERNAL TABLE */ ?>
	</td>
</tr>

<?$tabControl->EndTab();?>

<?
$tabControl->Buttons(
	array(
		"disabled" => false,
		"back_url" => "fileman_medialib_admin.php?".$addUrl."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()
	)
);
?>

<?$tabControl->End();?>

</form>
<script>
function colsOnChange(pSel){window.location = "/bitrix/admin/fileman_medialib_access.php?col_id=" + pSel.value + "&lang=<?= LANGUAGE_ID?>&<?= bitrix_sessid_get()?>";}
</script>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>