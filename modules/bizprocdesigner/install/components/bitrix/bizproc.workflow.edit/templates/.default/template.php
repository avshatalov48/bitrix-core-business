<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
use Bitrix\Main\Page\Asset;

Asset::getInstance()->addCss("/bitrix/themes/.default/pubstyles.css");
Asset::getInstance()->addCss("/bitrix/themes/.default/jspopup.css");
Asset::getInstance()->addCss("/bitrix/themes/.default/calendar.css");
Asset::getInstance()->addJs('/bitrix/js/main/utils.js');
Asset::getInstance()->addJs('/bitrix/js/main/popup_menu.js');
Asset::getInstance()->addJs('/bitrix/js/main/admin_tools.js');
CUtil::InitJSCore(["window", "ajax", 'bp_selector', 'clipboard']);
Asset::getInstance()->addJs('/bitrix/js/main/public_tools.js');
Asset::getInstance()->addJs('/bitrix/js/bizproc/bizproc.js');
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");
//////////////////////////////////////////////////////////////////////////////

$ID = $arResult["ID"];

$aMenu = Array();

$aMenu[] = array(
	"TEXT"  => GetMessage("BIZPROC_WFEDIT_MENU_PARAMS"),
	"TITLE" => GetMessage("BIZPROC_WFEDIT_MENU_PARAMS_TITLE"),
	"LINK"  => "javascript:BCPShowParams();",
	"ICON"  => "btn_settings",
);

$aMenu[] = array("SEPARATOR" => "Y");

$aMenu[] = array(
	"TEXT"  => ((strlen($arParams["BIZPROC_EDIT_MENU_LIST_MESSAGE"]) > 0) ? htmlspecialcharsbx($arParams["BIZPROC_EDIT_MENU_LIST_MESSAGE"]) : GetMessage("BIZPROC_WFEDIT_MENU_LIST")),
	"TITLE" => ((strlen($arParams["BIZPROC_EDIT_MENU_LIST_TITLE_MESSAGE"]) > 0) ? htmlspecialcharsbx($arParams["BIZPROC_EDIT_MENU_LIST_TITLE_MESSAGE"]) : GetMessage("BIZPROC_WFEDIT_MENU_LIST_TITLE")),
	"LINK"  => $arResult['LIST_PAGE_URL'],
	"ICON"  => "btn_list",
);

if (!array_key_exists("SKIP_BP_TYPE_SELECT", $arParams) || $arParams["SKIP_BP_TYPE_SELECT"] != "Y")
{
	$arSubMenu = Array();

	$arSubMenu[] = array(
		"TEXT"    => GetMessage("BIZPROC_WFEDIT_MENU_ADD_STATE"),
		"TITLE"   => GetMessage("BIZPROC_WFEDIT_MENU_ADD_STATE_TITLE"),
		"ONCLICK" => "if(confirm('".GetMessage("BIZPROC_WFEDIT_MENU_ADD_WARN")."'))window.location='".str_replace("#ID#", "0", $arResult["EDIT_PAGE_TEMPLATE"]).(strpos($arResult["EDIT_PAGE_TEMPLATE"], "?") ? "&" : "?")."init=statemachine';"
	);

	$arSubMenu[] = array(
		"TEXT"    => GetMessage("BIZPROC_WFEDIT_MENU_ADD_SEQ"),
		"TITLE"   => GetMessage("BIZPROC_WFEDIT_MENU_ADD_SEQ_TITLE"),
		"ONCLICK" => "if(confirm('".GetMessage("BIZPROC_WFEDIT_MENU_ADD_WARN")."'))window.location='".str_replace("#ID#", "0", $arResult["EDIT_PAGE_TEMPLATE"]).(strpos($arResult["EDIT_PAGE_TEMPLATE"], "?") ? "&" : "?")."';"
	);

	$aMenu[] = array(
		"TEXT"  => GetMessage("BIZPROC_WFEDIT_MENU_ADD"),
		"TITLE" => GetMessage("BIZPROC_WFEDIT_MENU_ADD_TITLE"),
		"ICON"  => "btn_new",
		"MENU"  => $arSubMenu
	);
}

$aMenu[] = array("SEPARATOR" => true);
$aMenu[] = array(
	"TEXT"=>GetMessage("BIZPROC_WFEDIT_MENU_EXPORT"),
	"TITLE"=>GetMessage("BIZPROC_WFEDIT_MENU_EXPORT_TITLE"),
	"LINK"=>"javascript:BCPProcessExport();",
	"ICON"=>"",
);
$aMenu[] = array(
	"TEXT"=>GetMessage("BIZPROC_WFEDIT_MENU_IMPORT"),
	"TITLE"=>GetMessage("BIZPROC_WFEDIT_MENU_IMPORT_TITLE"),
	"LINK"=>"javascript:BCPProcessImport();",
	"ICON"=>"",
);

?>
<script>
var BCPEmptyWorkflow =  <?=$ID>0 ? 'false' : 'true'?>;
function BCPProcessExport()
{
	if (BCPEmptyWorkflow)
	{
		alert('<?= GetMessageJS("BIZPROC_EMPTY_EXPORT") ?>');
		return false;
	}
	<?$v = str_replace("&amp;", "&", str_replace("#ID#", $ID, $arResult["EDIT_PAGE_TEMPLATE"]));?>
	window.open('<?=CUtil::JSEscape($v)?><?if(strpos($v, "?")):?>&<?else:?>?<?endif?>export_template=Y&<?=bitrix_sessid_get()?>');
}

function BCPProcessImport()
{
	if (!confirm("<?= GetMessageJS("BIZPROC_WFEDIT_MENU_IMPORT_PROMT") ?>"))
		return;

	var btnOK = new BX.CWindowButton({
		'title': '<?= GetMessageJS("BIZPROC_IMPORT_BUTTON") ?>',
		'action': function()
		{
			BX.showWait();

			var _form = document.getElementById('import_template_form');

			var _name = document.getElementById('id_import_template_name');
			var _descr = document.getElementById('id_import_template_description');
			var _auto = document.getElementById('id_import_template_autostart');

			if (_form)
			{
				_name.value = workflowTemplateName;
				_descr.value = workflowTemplateDescription;
				_auto.value = encodeURIComponent(workflowTemplateAutostart);
				_form.submit();
			}

			this.parentWindow.Close();
		}
	});

	new BX.CDialog({
		title: '<?= GetMessageJS("BIZPROC_IMPORT_TITLE") ?>',
		content: '<fo'+'rm action="<?= CUtil::JSEscape(POST_FORM_ACTION_URI) ?>" method="POST" id="import_template_form" enctype="multipart/form-data"><table cellspacing="0" cellpadding="0" border="0" width="100%"><tr valign="top"><td width="15%" align="right"><?= GetMessageJS("BIZPROC_IMPORT_FILE") ?>:</td><td align="left"><input type="file" size="35" name="import_template_file" value=""></td></tr></table><input type="hidden" name="import_template" value="Y"><input type="hidden" id="id_import_template_name" name="import_template_name" value=""><input type="hidden" name="import_template_description" id="id_import_template_description" value=""><input type="hidden" id="id_import_template_autostart" name="import_template_autostart" value=""><?= bitrix_sessid_post() ?></form>',
		buttons: [btnOK, BX.CDialog.btnCancel],
		width: 500,
		height: 150
	}).Show();
}

function BCPSaveTemplateComplete(data)
{
	if (data != '<!--SUCCESS-->')
	{
		alert('<?=GetMessageJS('BIZPROC_WFEDIT_SAVE_ERROR')?>');
		return;
	}
	BCPEmptyWorkflow = false;
}

<?$v = str_replace("&amp;", "&", POST_FORM_ACTION_URI);?>

function BCPSaveUserParams()
{
	var data = JSToPHP(arUserParams, 'USER_PARAMS');

	jsExtLoader.onajaxfinish = BCPSaveTemplateComplete;
	jsExtLoader.startPost('<?= CUtil::JSEscape($v) ?><?if(strpos($v, "?")):?>&<?else:?>?<?endif?><?=bitrix_sessid_get()?>&saveajax=Y&saveuserparams=Y', data);
}

function BCPSaveTemplate(save)
{
	arWorkflowTemplate = Array(rootActivity.Serialize());
	var data =
			'workflowTemplateName=' + encodeURIComponent(workflowTemplateName) + '&' +
			'workflowTemplateDescription=' + encodeURIComponent(workflowTemplateDescription) + '&' +
			'workflowTemplateAutostart=' + encodeURIComponent(workflowTemplateAutostart) + '&' +
			JSToPHP(arWorkflowParameters, 'arWorkflowParameters') + '&' +
			JSToPHP(arWorkflowVariables, 'arWorkflowVariables') + '&' +
			JSToPHP(arWorkflowConstants, 'arWorkflowConstants') + '&' +
			JSToPHP(arWorkflowTemplate, 'arWorkflowTemplate');

	jsExtLoader.onajaxfinish = BCPSaveTemplateComplete;
	jsExtLoader.startPost('<?=CUtil::JSEscape($v)?><?if(strpos($v, "?")):?>&<?else:?>?<?endif?><?=bitrix_sessid_get()?>&saveajax=Y'+
		(save ? '': '&apply=Y'),
		data);
}

function BCPShowParams()
{
	(new BX.CAdminDialog({
	'content_url': "/bitrix/admin/<?=MODULE_ID?>_bizproc_wf_settings.php?mode=public&bxpublic=Y&lang=<?=LANGUAGE_ID?>&entity=<?=ENTITY?>",
	'content_post': 'workflowTemplateName=' 		+ encodeURIComponent(workflowTemplateName) + '&' +
			'workflowTemplateDescription=' 	+ encodeURIComponent(workflowTemplateDescription) + '&' +
			'workflowTemplateAutostart=' 	+ encodeURIComponent(workflowTemplateAutostart) + '&' +
			'document_type=' 				+ encodeURIComponent(document_type) + '&' +
			'<?= bitrix_sessid_get() ?>' + '&' +
			JSToPHP(arWorkflowParameters, 'arWorkflowParameters')  + '&' +
			JSToPHP(arWorkflowVariables, 'arWorkflowVariables')  + '&' +
			JSToPHP(arWorkflowConstants, 'arWorkflowConstants') + '&' +
			JSToPHP(Array(rootActivity.Serialize()), 'arWorkflowTemplate'), 
	'height': 500,
	'width': 800,
	'resizable' : false
	})).Show();
}
</script>
<div style="background-color: #FFFFFF;">
<?
if($arParams['SHOW_TOOLBAR']=='Y'):
?>
<?
	$APPLICATION->IncludeComponent(
		"bitrix:main.interface.toolbar",
		"",
		array(
			"BUTTONS"=>$aMenu,
		),
		$component, array("HIDE_ICONS" => "Y")
	);
?>
<?endif;

global $JSMESS;
$JSMESS = Array();
function GetJSLangMess($f, $actId)
{
	$MESS = Array();
	if(file_exists($f."/lang/en/".$actId.".js.php"))
		include($f."/lang/en/".$actId.".js.php");
	if(file_exists($f."/lang/".LANGUAGE_ID."/".$actId.".js.php"))
		include($f."/lang/".LANGUAGE_ID."/".$actId.".js.php");

	global $JSMESS;
	foreach($MESS as $k=>$v)
		$JSMESS[$k] = $v;
}

foreach($arResult['ACTIVITIES'] as $actId => $actProps)
{
	$actPath = substr($actProps["PATH_TO_ACTIVITY"], strlen($_SERVER["DOCUMENT_ROOT"]));
	if(file_exists($actProps["PATH_TO_ACTIVITY"]."/".$actId.".js"))
	{
		Asset::getInstance()->addJs($actPath.'/'.$actId.'.js');
		GetJSLangMess($actProps["PATH_TO_ACTIVITY"], $actId);
	}

	if(file_exists($actProps["PATH_TO_ACTIVITY"]."/".$actId.".css"))
		Asset::getInstance()->addCss($actPath.'/'.$actId.'.css');

	if(file_exists($actProps["PATH_TO_ACTIVITY"]."/icon.gif"))
		$arResult['ACTIVITIES'][$actId]['ICON'] = $actPath.'/icon.gif';

	unset($arResult['ACTIVITIES'][$actId]['PATH_TO_ACTIVITY']);
}
?>
<script>
var arAllActivities = <?=CUtil::PhpToJSObject($arResult['ACTIVITIES'])?>;
var arAllActGroups = <?=CUtil::PhpToJSObject($arResult['ACTIVITY_GROUPS'])?>;
var arWorkflowParameters = <?=CUtil::PhpToJSObject($arResult['PARAMETERS'])?>;
var arWorkflowVariables = <?=CUtil::PhpToJSObject($arResult['VARIABLES'])?>;
var arWorkflowConstants = <?=CUtil::PhpToJSObject($arResult['CONSTANTS'])?>;
var arWorkflowTemplate = <?=CUtil::PhpToJSObject($arResult['TEMPLATE'][0])?>;
var arDocumentFields = <?=CUtil::PhpToJSObject($arResult['DOCUMENT_FIELDS'])?>;

var workflowTemplateName = <?=CUtil::PhpToJSObject($arResult['TEMPLATE_NAME'])?>;
var workflowTemplateDescription = <?=CUtil::PhpToJSObject($arResult['TEMPLATE_DESC'])?>;
var workflowTemplateAutostart = <?=CUtil::PhpToJSObject($arResult['TEMPLATE_AUTOSTART'])?>;

var document_type = <?=CUtil::PhpToJSObject($arResult['DOCUMENT_TYPE'])?>;
var MODULE_ID = <?=CUtil::PhpToJSObject(MODULE_ID)?>;
var ENTITY = <?=CUtil::PhpToJSObject(ENTITY)?>;
var BPMESS = <?=CUtil::PhpToJSObject($JSMESS)?>;
var BPDesignerUseJson = true;
var BPTemplateIsModified = false;

var CURRENT_SITE_ID = <?=CUtil::PhpToJSObject(SITE_ID)?>;

var arUserParams = <?=CUtil::PhpToJSObject($arResult['USER_PARAMS'])?>;


var arAllId = {};
var rootActivity;

function BizProcRender(oActivity, divParent, t)
{
	rootActivity = CreateActivity(oActivity);
	rootActivity.Draw(divParent);
}

function ReDraw()
{
	var p;
	if(rootActivity.Type == 'SequentialWorkflowActivity')
	{
		if(rootActivity.swfWorkspaceDiv)
			p = rootActivity.swfWorkspaceDiv.scrollTop;

		while(rootActivity.childActivities.length>0)
			rootActivity.RemoveChild(rootActivity.childActivities[0]);

		rootActivity.Init(arWorkflowTemplate);
		rootActivity.DrawActivities();

		rootActivity.swfWorkspaceDiv.scrollTop = p;
	}
	else
	{
		if(rootActivity._redrawObject)
		{
			if(rootActivity._redrawObject.swfWorkspaceDiv)
				p = rootActivity._redrawObject.swfWorkspaceDiv.scrollTop;

			while(rootActivity._redrawObject.childActivities.length>0)
				rootActivity._redrawObject.RemoveChild(rootActivity._redrawObject.childActivities[0]);

			var act = FindActivityById(arWorkflowTemplate, rootActivity._redrawObject.Name);

			rootActivity._redrawObject.Init(act);
			rootActivity._redrawObject.DrawActivities();

			rootActivity._redrawObject.swfWorkspaceDiv.scrollTop = p;
		}
		else
		{
			var d = rootActivity.Table.parentNode;
			var modificationFlag = BPTemplateIsModified;

			while(rootActivity.childActivities.length>0)
				rootActivity.RemoveChild(rootActivity.childActivities[0]);

			rootActivity.Init(arWorkflowTemplate);
			rootActivity.RemoveResources();
			rootActivity.Draw(d);

			BPTemplateIsModified = modificationFlag;
		}
	}
}


function start()
{
	var t = document.getElementById('wf1');
	if (!t)
	{
		setTimeout(function () {start();}, 1000);
		return;
	}
	BizProcRender(arWorkflowTemplate, document.getElementById('wf1'));
	<?if($ID<=0):?>
	BCPShowParams();
	<?endif;?>
}
setTimeout("start()", 0);

window.onbeforeunload = function()
{
	return BPTemplateIsModified ? '<?=GetMessageJS('BIZPROC_WFEDIT_BEFOREUNLOAD')?>' : null;
};

function BPImportToClipboard()
{
	var dataString = JSON.stringify({
		template: rootActivity.Serialize(),
		parameters: arWorkflowParameters,
		variables: arWorkflowVariables,
		constants: arWorkflowConstants
	});

	BX.clipboard.copy(encodeURIComponent(dataString));
}

function BPExportFromString(rawString)
{
	try
	{
		var data = JSON.parse(decodeURIComponent(rawString));
	}
	catch (e)
	{
		data = {}
	}

	if (data.parameters && BX.type.isPlainObject(data.parameters))
	{
		arWorkflowParameters = data.parameters;
	}
	if (data.variables && BX.type.isPlainObject(data.variables))
	{
		arWorkflowVariables = data.variables;
	}
	if (data.constants && BX.type.isPlainObject(data.constants))
	{
		arWorkflowConstants = data.constants;
	}

	if (data.template && BX.type.isPlainObject(data.template))
	{
		arWorkflowTemplate = data.template;
		ReDraw();
	}
}

</script>

<? if (!$arResult['TEMPLATE_CHECK_STATUS']):
	echo ShowError(GetMessage('BIZPROC_WFEDIT_CHECK_ERROR'));
endif;
?>
<form>

<div id="wf1" style="width: 100%; border-bottom: 2px #efefef dotted; " ></div>

<div id="bizprocsavebuttons">
<br>
<input type="button" onclick="BCPSaveTemplate(true);" value="<?echo GetMessage("BIZPROC_WFEDIT_SAVE_BUTTON")?>">
<input type="button" onclick="BCPSaveTemplate();" value="<?echo GetMessage("BIZPROC_WFEDIT_APPLY_BUTTON")?>">
<input type="button" onclick="window.location='<?=htmlspecialcharsbx(CUtil::JSEscape(isset($arResult['BACK_URL']) ? $arResult['BACK_URL'] : $arResult['LIST_PAGE_URL']))?>';" value="<?echo GetMessage("BIZPROC_WFEDIT_CANCEL_BUTTON")?>">
</div>

</form>
</div>
