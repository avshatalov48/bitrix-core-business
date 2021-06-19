<?php

define("NOT_CHECK_FILE_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

\Bitrix\Main\Loader::includeModule('bizproc');
IncludeModuleLangFile(__FILE__);

if (empty($_POST['document_type']))
{
	die();
}

if (!defined('MODULE_ID') && !defined('ENTITY') && isset($_REQUEST['dts']))
{
	$dts = \CBPDocument::unSignDocumentType($_REQUEST['dts']);
	if ($dts)
	{
		define('MODULE_ID', $dts[0]);
		define('ENTITY', $dts[1]);
	}
}

$popupWindow = new CJSPopup(GetMessage("BIZPROC_AS_TITLE"));

$popupWindow->ShowTitlebar(GetMessage("BIZPROC_AS_TITLE"));

CBPHelper::decodeTemplatePostData($_POST);

$activityName = $_REQUEST['id'];
$activityType = $_REQUEST['activity'];
$document_type = $_POST['document_type'];
$documentType = [MODULE_ID, ENTITY, $_POST['document_type']];

$currentSiteId = $_REQUEST['current_site_id'];

$popupWindow->StartDescription("bx-edit-settings");

$canWrite = CBPDocument::CanUserOperateDocumentType(
		CBPCanUserOperateOperation::CreateWorkflow,
		$GLOBALS["USER"]->GetID(),
		$documentType
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
$arWorkflowConstants = $_POST['arWorkflowConstants'];

$arErrors = array();

if ($_POST["save"] == "Y" && check_bitrix_sessid())
{
	//TODO: Experimental
	$currentRequest = $_POST;
	unset(
		$currentRequest['arWorkflowTemplate'],
		$currentRequest['arWorkflowParameters'],
		$currentRequest['arWorkflowVariables'],
		$currentRequest['arWorkflowConstants']
	);
	$currentRequest = \Bitrix\Bizproc\Automation\Helper::unConvertProperties(
		$currentRequest,
		$documentType
	);

	$res = CBPActivity::CallStaticMethod(
		$activityType,
		"GetPropertiesDialogValues",
		array(
			$documentType,
			$activityName,
			&$arWorkflowTemplate,
			&$arWorkflowParameters,
			&$arWorkflowVariables,
			$currentRequest,
			&$arErrors,
			$arWorkflowConstants
		)
	);

	$bShowId = false;
	if($_POST["activity_id"]!=$activityName)
	{
		$bShowId = true;
		if($_POST["activity_id"]=='')
			$arErrors[] = Array('message'=>GetMessage("BP_ACT_SET_ID_EMPTY"));
		elseif(is_array(CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $currentRequest["activity_id"])))
				$arErrors[] = Array('message'=>str_replace('#ID#', htmlspecialcharsbx($currentRequest["activity_id"]), GetMessage("BP_ACT_SET_ID_DUP")));
		else
			$bShowId = false;
	}

	if($res && count($arErrors)<=0)
	{
		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		if (!is_array($arCurrentActivity["Properties"]))
			$arCurrentActivity["Properties"] = array();

		$arCurrentActivity["Properties"]["Title"] = $currentRequest["title"];
		$arCurrentActivity["Properties"]["EditorComment"] = $currentRequest["activity_editor_comment"];
		$arCurrentActivity["Name"] = $currentRequest["activity_id"];
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
	$ob = \Bitrix\Main\Web\Json::encode($ob);
	return '<input type="hidden" name="'.htmlspecialcharsbx($name).'" value="'.htmlspecialcharsbx($ob).'">';
}

echo PHPToHiddens($arWorkflowTemplate, 'arWorkflowTemplate');
echo PHPToHiddens($arWorkflowParameters, 'arWorkflowParameters');
echo PHPToHiddens($arWorkflowVariables, 'arWorkflowVariables');
echo PHPToHiddens($arWorkflowConstants, 'arWorkflowConstants');

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

$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
if ($_POST["postback"] == "Y")
{
	$activityTitle = $_POST["title"];
	$editorComment = $_POST["activity_editor_comment"];
	$activity_id = $_POST["activity_id"];
}
else
{
	$activityTitle = $arCurrentActivity["Properties"]["Title"];
	$editorComment = $arCurrentActivity["Properties"]["EditorComment"];
	$activity_id = $activityName;
}
?>
<script>
function HideShowId(id)
{
	var act_id = BX(id || 'id_activity_name');
	if(act_id.style.display == 'none')
		act_id.style.display = '';
	else
		act_id.style.display = 'none';
}
</script>
<tr>
	<td align="right" width="25%"><?echo GetMessage("BIZPROC_AS_ACT_TITLE")?></td>
	<td width="75%">
		<table width="100%">
			<tr>
				<td width="90%">
					<?= CBPDocument::ShowParameterField("string", "title", $activityTitle, array("size" => 50, "id"=>"bpastitle")) ?>
				</td>
				<td width="5%">
					[<a href="javascript:void(0)" onclick="HideShowId()" title="<?echo GetMessage("BP_ACT_SET_ID_SHOWHIDE")?>"><?echo GetMessage("BP_ACT_SET_ID")?></a>]
				</td>
				<td width="5%">
					[<a href="javascript:void(0)" onclick="HideShowId('id_activity_comment')" title="<?echo GetMessage("BP_ACT_SET_COMMENT_SHOWHIDE")?>"><?echo GetMessage("BP_ACT_SET_COMMENT")?></a>]
				</td>
			</tr>
		</table>
	</td>
</tr>
<tr <?if(!$bShowId):?> style="display:none"<?endif?> id="id_activity_name">
	<td align="right" width="25%"><?echo GetMessage("BP_ACT_SET_ID_ROW")?></td>
	<td width="75%"><input type="text" name="activity_id" value="<?=htmlspecialcharsbx($activity_id)?>" size="50"></td>
</tr>
<tr <?if(empty($editorComment)):?>style="display:none"<?endif?> id="id_activity_comment">
	<td align="right" width="25%"><?echo GetMessage("BP_ACT_SET_COMMENT_ROW")?></td>
	<td width="75%"><textarea cols="70" rows="3" name="activity_editor_comment"><?=htmlspecialcharsbx($editorComment)?></textarea></td>
</tr>

<?php

//TODO: Experimental
if ($arCurrentActivity && is_array($arCurrentActivity['Properties']))
{
	$arCurrentActivity['Properties'] = \Bitrix\Bizproc\Automation\Helper::convertProperties(
		$arCurrentActivity['Properties'],
		$documentType,
		false
	);
}

$z = CBPActivity::CallStaticMethod(
	$activityType,
	"GetPropertiesDialog",
	array(
		$documentType,
		$activityName,
		$arWorkflowTemplate,
		$arWorkflowParameters,
		$arWorkflowVariables,
		($_POST["postback"] == "Y" ? $_POST : null),
		$popupWindow->GetFormName(),
		$popupWindow,
		$currentSiteId,
		$arWorkflowConstants
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

	BX.addClass(table, "bizprocdesigner-properties-dialog-table");
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

	var form = table.closest('form');
	if (form)
	{
		BX.bind(form, 'keydown', function(event)
		{
			if (event.keyCode === 13 && (event.ctrlKey || event.metaKey))
			{
				BX.fireEvent(form, 'submit');
			}
		});

		setTimeout(function()
		{
			var saveButton = form.closest('.bx-core-adm-dialog').querySelector('[name="savebtn"]');
			if (saveButton)
			{
				saveButton.value += ' (Ctrl+Enter)';
			}
		}, 100);
	}
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
