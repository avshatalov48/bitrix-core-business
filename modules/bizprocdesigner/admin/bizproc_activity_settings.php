<?php

define("NOT_CHECK_FILE_PERMISSIONS", true);
define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_js.php");

\Bitrix\Main\Loader::includeModule('bizproc');
IncludeModuleLangFile(__FILE__);

\Bitrix\Main\Loader::includeModule('ui');
\Bitrix\Main\UI\Extension::load(['ui.alerts']);

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

$popupWindow = new CJSPopup(GetMessage("BIZPROC_AS_TITLE_1"));

$popupWindow->ShowTitlebar(GetMessage("BIZPROC_AS_TITLE_1"));

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
{
	die ("Bad activity type!" . htmlspecialcharsbx($activityType));
}

if($arActivityDescription["DESCRIPTION"])
{
	echo htmlspecialcharsbx($arActivityDescription["DESCRIPTION"]);
}
else
{
	echo GetMessage("BIZPROC_AS_DESC_1");
}

$runtime->IncludeActivityFile($activityType);

$popupWindow->EndDescription();
$popupWindow->StartContent();

$arWorkflowTemplate = $_POST['arWorkflowTemplate'];
$arWorkflowParameters = $_POST['arWorkflowParameters'];
$arWorkflowVariables = $_POST['arWorkflowVariables'];
$arWorkflowConstants = $_POST['arWorkflowConstants'];

$wfGlobalConstants = \Bitrix\Bizproc\Workflow\Type\GlobalConst::getAll($documentType);
$wfGlobalVariables = \Bitrix\Bizproc\Workflow\Type\GlobalVar::getAll($documentType);
$documentFields = \Bitrix\Bizproc\Automation\Helper::getDocumentFields($documentType);

$arErrors = [];
$bShowId = false;

if (!empty($_POST["save"]) && check_bitrix_sessid())
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

	if($_POST["activity_id"] != $activityName)
	{
		$bShowId = true;
		if($_POST["activity_id"] == '')
		{
			$arErrors[] = ['message' => GetMessage("BP_ACT_SET_ID_EMPTY_1")];
		}
		elseif(is_array(CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $currentRequest["activity_id"])))
		{
			$arErrors[] = [
				'message' => str_replace(
					'#ID#',
					htmlspecialcharsbx($currentRequest["activity_id"]),
					GetMessage("BP_ACT_SET_ID_DUP_1")
				),
			];
		}
		else
		{
			$bShowId = false;
		}
	}

	if($res && count($arErrors)<=0)
	{
		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		if (!is_array($arCurrentActivity["Properties"]))
		{
			$arCurrentActivity["Properties"] = [];
		}

		$arCurrentActivity["Properties"]["Title"] = $currentRequest["title"];
		$arCurrentActivity["Properties"]["EditorComment"] = $currentRequest["activity_editor_comment"];
		$arCurrentActivity["Name"] = $currentRequest["activity_id"];
		?>
		<script>
		arWorkflowParameters = <?= CUtil::PhpToJSObject($arWorkflowParameters) ?>;
		arWorkflowVariables = <?= CUtil::PhpToJSObject($arWorkflowVariables) ?>;
		arWorkflowTemplate = <?= CUtil::PhpToJSObject($arWorkflowTemplate[0]) ?>;
		BPTemplateIsModified = true;
		ReDraw();
		<?= $popupWindow->jsPopup?>.CloseDialog();
		</script>
		<?php
		die();
	}
}

function PHPToHiddens($ob, $name)
{
	$ob = \Bitrix\Main\Web\Json::encode($ob);
	return '<input type="hidden" name="' . htmlspecialcharsbx($name) . '" value="' . htmlspecialcharsbx($ob) . '">';
}

echo PHPToHiddens($arWorkflowTemplate, 'arWorkflowTemplate');
echo PHPToHiddens($arWorkflowParameters, 'arWorkflowParameters');
echo PHPToHiddens($arWorkflowVariables, 'arWorkflowVariables');
echo PHPToHiddens($arWorkflowConstants, 'arWorkflowConstants');

CBPDocument::AddShowParameterInit(MODULE_ID, "all", $_POST['document_type'], ENTITY);
?>
<?= bitrix_sessid_post() ?>
<input type="hidden" name="activity" value="<?= htmlspecialcharsbx($activityType) ?>">
<input type="hidden" name="document_type" value="<?= htmlspecialcharsbx($document_type) ?>">
<input type="hidden" name="id" value="<?= htmlspecialcharsbx($activityName) ?>">
<input type="hidden" name="current_site_id" value="<?= htmlspecialcharsbx($currentSiteId) ?>">
<?php
	if(count($arErrors)>0)
	{
		foreach($arErrors as $e)
			echo '<div><font color="red">'.htmlspecialcharsbx($e["message"]) . '</font></div>';
	}
?>
<?php $tableID = "tbl-activity-".randString(5); ?>
<table class="adm-detail-content-table edit-table" id="<?= $tableID ?>">
<?php

$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
$brokenLinks = [];
if (!empty($_POST["postback"]))
{
	$activityTitle = $_POST["title"];
	$editorComment = $_POST["activity_editor_comment"];
	$activity_id = $_POST["activity_id"];
}
else
{
	$activityTitle = $arCurrentActivity["Properties"]["Title"];
	$editorComment = $arCurrentActivity["Properties"]["EditorComment"] ?? '';
	$activity_id = $activityName;

	$usages = [];
	try
	{
		// todo: think about how to take it to another place
		CBPActivity::IncludeActivityFile('SequentialWorkflowActivity');
		$rootActivity = CBPActivity::createInstance('SequentialWorkflowActivity', 'Template');

		$activityInstance = CBPActivity::createInstance($arCurrentActivity['Type'], $arCurrentActivity['Name']);
		if ($activityInstance)
		{
			$activityInstance->initializeFromArray($arCurrentActivity['Properties']);

			$rootActivity->FixUpParentChildRelationship($activityInstance);
			$rootActivity->SetProperties($arWorkflowParameters);
			$rootActivity->SetVariablesTypes($arWorkflowVariables);

			$children = $rootActivity->CollectNestedActivities();
			if (is_array($children))
			{
				$usages = $children[0]->collectUsages();
			}
		}
	}
	catch (Exception $e)
	{
		// relevant only for whileactivity
		$usages = [];
	}

	$checkMap = [
		\Bitrix\Bizproc\Workflow\Template\SourceType::DocumentField => $documentFields,
		\Bitrix\Bizproc\Workflow\Template\SourceType::GlobalConstant => $wfGlobalConstants,
		\Bitrix\Bizproc\Workflow\Template\SourceType::GlobalVariable => $wfGlobalVariables,
		\Bitrix\Bizproc\Workflow\Template\SourceType::Variable => $arWorkflowVariables,
		\Bitrix\Bizproc\Workflow\Template\SourceType::Constant => $arWorkflowConstants,
		\Bitrix\Bizproc\Workflow\Template\SourceType::Parameter => $arWorkflowParameters
	];
	// {=Template:TargetUser}
	$checkMap[\Bitrix\Bizproc\Workflow\Template\SourceType::Parameter]['TargetUser'] = [];

	foreach ($usages as $usage)
	{
		$object = $usage[0];
		$field = $usage[1];
		$returnField = $usage[2] ?? null;

		if (array_key_exists($object, $checkMap))
		{
			if (!array_key_exists($field, $checkMap[$object]))
			{
				if ($object === \Bitrix\Bizproc\Workflow\Template\SourceType::Parameter)
				{
					$object = 'Template';
				}

				$brokenLinks[] = htmlspecialcharsbx('{=' . $object . ':' . $field . '}');
			}
		}
		elseif ($object === \Bitrix\Bizproc\Workflow\Template\SourceType::Activity)
		{
			$activityUsage = CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $field);
			if (!array_key_exists($returnField, $runtime->getActivityReturnProperties($activityUsage)))
			{
				$brokenLinks[] = htmlspecialcharsbx('{=' . $field . ':' . $returnField . '}');
			}
		}
	}
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
function ShowBrokenLinkDetail(element)
{
	BX.Dom.style(BX('bp_act_set_broken_link_detail'), 'height', (BX('bp_act_set_broken_link_detail').scrollHeight) + 'px');
	BX.Dom.remove(element);
}
</script>
	<?php if ($brokenLinks):?>
		<div class="ui-alert ui-alert-warning ui-alert-icon-info" id="bp_act_set_broken_link" style="width: auto;">
			<div class="ui-alert-message">
				<div>
					<span>
						<?= htmlspecialcharsbx(\Bitrix\Main\Localization\Loc::getMessage(
							'BP_ACT_SET_BROKEN_LINK_MESSAGE_ERROR'
						)) ?>
					</span>
					<span class="bizprocdesigner-activity-broken-link-show-more" onclick="ShowBrokenLinkDetail(this)">
						<?= htmlspecialcharsbx(
							\Bitrix\Main\Localization\Loc::getMessage('BP_ACT_SET_BROKEN_LINK_MESSAGE_ERROR_SHOW_LINKS')
						) ?>
					</span>
				</div>
				<div class="bizprocdesigner-activity-broken-link-detail" id="bp_act_set_broken_link_detail">
					<?= (implode('<br>', $brokenLinks)) ?>
				</div>
			</div>
			<span class="ui-alert-close-btn" onclick="HideShowId('bp_act_set_broken_link')"></span>
		</div>
	<?php endif ?>
<tr>
	<td align="right" width="25%"><?= GetMessage("BIZPROC_AS_ACT_TITLE") ?></td>
	<td width="75%">
		<table width="100%">
			<tr>
				<td width="90%">
					<?= CBPDocument::ShowParameterField("string", "title", $activityTitle, array("size" => 50, "id"=>"bpastitle")) ?>
				</td>
				<td width="5%">
					[<a href="javascript:void(0)" onclick="HideShowId()" title="<?= GetMessage("BP_ACT_SET_ID_SHOWHIDE_1") ?>"><?= GetMessage("BP_ACT_SET_ID") ?></a>]
				</td>
				<td width="5%">
					[<a href="javascript:void(0)" onclick="HideShowId('id_activity_comment')" title="<?= GetMessage("BP_ACT_SET_COMMENT_SHOWHIDE_1") ?>"><?= GetMessage("BP_ACT_SET_COMMENT") ?></a>]
				</td>
			</tr>
		</table>
	</td>
</tr>
<tr <?php if(!$bShowId):?> style="display:none"<?php endif ?> id="id_activity_name">
	<td align="right" width="25%"><?= GetMessage("BP_ACT_SET_ID_ROW_1") ?></td>
	<td width="75%"><input type="text" name="activity_id" value="<?= htmlspecialcharsbx($activity_id) ?>" size="50"></td>
</tr>
<tr <?php if(empty($editorComment)): ?>style="display:none"<?php endif ?> id="id_activity_comment">
	<td align="right" width="25%"><?= GetMessage("BP_ACT_SET_COMMENT_ROW") ?></td>
	<td width="75%"><textarea cols="70" rows="3" name="activity_editor_comment"><?= htmlspecialcharsbx($editorComment) ?></textarea></td>
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
		(!empty($_POST["postback"]) ? $_POST : null),
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
	var table = BX("<?= $tableID ?>");
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
<style>
	.bizprocdesigner-activity-broken-link-show-more {
		border-bottom: 1px dashed rgba(145,113,30,.4);
		cursor: pointer;
		transition: .2s;
	}

	.bizprocdesigner-activity-broken-link-show-more:hover {
		border-bottom-color: rgba(145,113,30,1);
	}

	.bizprocdesigner-activity-broken-link-detail {
		height: 0;
		margin-top: 10px;
		overflow: hidden;
		transition: height 0.3s linear;
	}
</style>

<input type="hidden" name="save" value="Y" />
<input type="hidden" name="postback" value="Y" />
<?php
$popupWindow->EndContent();
$popupWindow->StartButtons();
$popupWindow->ShowStandardButtons();
$popupWindow->EndButtons();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin_js.php");
