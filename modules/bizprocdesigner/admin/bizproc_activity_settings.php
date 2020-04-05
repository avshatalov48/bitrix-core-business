<?
define("NOT_CHECK_FILE_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/include.php");
IncludeModuleLangFile(__FILE__);

$popupWindow = new CJSPopup(GetMessage("BIZPROC_AS_TITLE"));

$popupWindow->ShowTitlebar(GetMessage("BIZPROC_AS_TITLE"));

CBPHelper::decodeTemplatePostData($_POST);

$activityName = $_REQUEST['id'];
$activityType = $_REQUEST['activity'];
$document_type = $_POST['document_type'];

$currentSiteId = $_REQUEST['current_site_id'];

$popupWindow->StartDescription("bx-edit-settings");

$canWrite = CBPDocument::CanUserOperateDocumentType(
		CBPCanUserOperateOperation::CreateWorkflow,
		$GLOBALS["USER"]->GetID(),
		array(MODULE_ID, ENTITY, $document_type)
	);

if(!$canWrite)
{
	$popupWindow->ShowError(GetMessage("ACCESS_DENIED"));
	die();
}


$runtime = CBPRuntime::GetRuntime();
$runtime->StartRuntime();

$arActivityDescription = $runtime->GetActivityDescription($activityType);
if ($arActivityDescription == null)
	die ("Bad activity type!".htmlspecialcharsbx($activityType));

if($arActivityDescription["DESCRIPTION"])
	echo htmlspecialcharsbx($arActivityDescription["DESCRIPTION"]);
else
	echo GetMessage("BIZPROC_AS_DESC");

$runtime->IncludeActivityFile($activityType);

$popupWindow->EndDescription();
$popupWindow->StartContent();

$arWorkflowTemplate = $_POST['arWorkflowTemplate'];
$arWorkflowParameters = $_POST['arWorkflowParameters'];
$arWorkflowVariables = $_POST['arWorkflowVariables'];

$arErrors = array();
if($_POST["save"] == "Y" && check_bitrix_sessid())
{
	$res = CBPActivity::CallStaticMethod(
		$activityType,
		"GetPropertiesDialogValues",
		array(
			array(MODULE_ID, ENTITY, $_POST['document_type']),
			$activityName,
			&$arWorkflowTemplate,
			&$arWorkflowParameters,
			&$arWorkflowVariables,
			$_POST,
			&$arErrors
		)
	);

	$bShowId = false;
	if($_POST["activity_id"]!=$activityName)
	{
		$bShowId = true;
		if($_POST["activity_id"]=='')
			$arErrors[] = Array('message'=>GetMessage("BP_ACT_SET_ID_EMPTY"));
		elseif(is_array(CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $_POST["activity_id"])))
				$arErrors[] = Array('message'=>str_replace('#ID#', htmlspecialcharsbx($_POST["activity_id"]), GetMessage("BP_ACT_SET_ID_DUP")));
		else
			$bShowId = false;
	}

	if($res && count($arErrors)<=0)
	{
		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		if (!is_array($arCurrentActivity["Properties"]))
			$arCurrentActivity["Properties"] = array();

		$arCurrentActivity["Properties"]["Title"] = $_POST["title"];
		$arCurrentActivity["Name"] = $_POST["activity_id"];
		?>
		<script>
		arWorkflowParameters = <?=CUtil::PhpToJSObject($arWorkflowParameters)?>;
		arWorkflowVariables = <?=CUtil::PhpToJSObject($arWorkflowVariables)?>;
		arWorkflowTemplate = <?=CUtil::PhpToJSObject($arWorkflowTemplate[0])?>;
		BPTemplateIsModified = true;
		ReDraw();
		<?=$popupWindow->jsPopup?>.CloseDialog();
		</script>
		<?
		die();
	}
}

function PHPToHiddens($ob, $name)
{
	global $APPLICATION;
	if (strtolower(LANG_CHARSET) != 'utf-8')
	{
		$ob = $APPLICATION->ConvertCharsetArray($ob, LANG_CHARSET, 'utf-8');
		$ob = CBPHelper::decodeArrayKeys($ob, true);
	}
	$ob = json_encode($ob);
	//if(is_array($ob))
	//{
	//	$s="";
	//	foreach($ob as $k=>$v)
	//		$s .= PHPToHiddens($v, $name."[".$k."]");
	//	return $s;
	//}
	return '<input type="hidden" name="'.htmlspecialcharsbx($name).'" value="'.htmlspecialcharsbx($ob).'">';
}

echo PHPToHiddens($arWorkflowTemplate, 'arWorkflowTemplate');
echo PHPToHiddens($arWorkflowParameters, 'arWorkflowParameters');
echo PHPToHiddens($arWorkflowVariables, 'arWorkflowVariables');

CBPDocument::AddShowParameterInit(MODULE_ID, "all", $_POST['document_type'], ENTITY);
?>
<?=bitrix_sessid_post()?>
<input type="hidden" name="activity" value="<?=htmlspecialcharsbx($activityType)?>">
<input type="hidden" name="document_type" value="<?=htmlspecialcharsbx($document_type)?>">
<input type="hidden" name="id" value="<?=htmlspecialcharsbx($activityName)?>">
<input type="hidden" name="current_site_id" value="<?=htmlspecialcharsbx($currentSiteId)?>">

<? $tableID = "tbl-activity-".randString(5);?>
<table class="adm-detail-content-table edit-table" id="<?=$tableID?>">
<?
if(count($arErrors)>0)
{
	echo '<tr><td colspan="2">';
	foreach($arErrors as $e)
		echo '<font color="red">'.htmlspecialcharsbx($e["message"]).'</font><br>';
	echo '</td></tr>';
}

if ($_POST["postback"] == "Y")
{
	$val = $_POST["title"];
	$activity_id = $_POST["activity_id"];
}
else
{
	$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
	$val = $arCurrentActivity["Properties"]["Title"];
	$activity_id = $activityName;
}
?>
<script>
function HideShowId()
{
	var act_id = BX('id_activity_name');
	if(act_id.style.display == 'none')
		act_id.style.display = '';
	else
		act_id.style.display = 'none';
}
</script>
<tr>
	<td align="right" width="40%"><?echo GetMessage("BIZPROC_AS_ACT_TITLE")?></td>
	<td width="60%">
		<table width="100%"><tr>
		<td width="95%"><?= CBPDocument::ShowParameterField("string", "title", $val, array("size" => 50, "id"=>"bpastitle")) ?></td>
		<td width="5%">[<a href="javascript:void(0)" onclick="HideShowId()" title="<?echo GetMessage("BP_ACT_SET_ID_SHOWHIDE")?>"><?echo GetMessage("BP_ACT_SET_ID")?></a>]</td>
		</tr></table>
	</td>
</tr>
<tr <?if(!$bShowId):?> style="display:none"<?endif?> id="id_activity_name">
	<td align="right" width="40%"><?echo GetMessage("BP_ACT_SET_ID_ROW")?></td>
	<td width="60%"><input type="text" name="activity_id" value="<?=htmlspecialcharsbx($activity_id)?>" size="50"></td>
</tr>

<?
$z = CBPActivity::CallStaticMethod(
	$activityType,
	"GetPropertiesDialog",
	array(
		array(MODULE_ID, ENTITY, $_POST['document_type']),
		$activityName,
		$arWorkflowTemplate,
		$arWorkflowParameters,
		$arWorkflowVariables,
		($_POST["postback"] == "Y" ? $_POST : null),
		$popupWindow->GetFormName(),
		$popupWindow,
		$currentSiteId
	)
);

echo $z;
?>
</table>
<script>
setTimeout("document.getElementById('bpastitle').focus();", 100);

(function() {
	var table = BX("<?=$tableID?>");
	if (!table)
		return;

	for (var bodyIndex = 0, bodiesLen = table.tBodies.length; bodyIndex < bodiesLen; bodyIndex++)
	{
		var n = table.tBodies[bodyIndex].rows.length;
		for (var i = 0 ;  i < n; i++)
		{
			if (table.tBodies[bodyIndex].rows[i].cells.length > 1)
			{
				BX.addClass(table.tBodies[bodyIndex].rows[i].cells[0], "adm-detail-content-cell-l");
				BX.addClass(table.tBodies[bodyIndex].rows[i].cells[1], "adm-detail-content-cell-r");
			}
		}
	}
	BX.namespace('BX.Bizproc');
	if (typeof BX.Bizproc.Selector !== 'undefined')
		BX.Bizproc.Selector.initSelectors();
})();


</script>

<input type="hidden" name="save" value="Y" />
<input type="hidden" name="postback" value="Y" />
<?
$popupWindow->EndContent();
$popupWindow->StartButtons();
$popupWindow->ShowStandardButtons();
$popupWindow->EndButtons();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
?>