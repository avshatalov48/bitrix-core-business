<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$isAdminSection = (isset($arParams['IS_ADMIN_SECTION']));

use Bitrix\Main\Page\Asset;

\Bitrix\Main\Loader::includeModule('rest');
\Bitrix\Main\Loader::includeModule('ui');
CUtil::InitJSCore(['window', 'ajax', 'bp_selector', 'clipboard', 'marketplace', 'bp_field_type']);
\Bitrix\Main\UI\Extension::load([
	'ui.hint',
	'bizproc.automation',
	'bizproc.globals',
	'ui.icon-set.main',
	'ui.icon-set.actions',
	'ui.buttons',
	'main.popup',
	'ui.design-tokens',
]);

if ($isAdminSection)
{
	$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/pubstyles.css");
	$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/jspopup.css");
	$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/calendar.css");
}
else
{
	Asset::getInstance()->addCss("/bitrix/themes/.default/pubstyles.css");
	Asset::getInstance()->addCss("/bitrix/themes/.default/jspopup.css");
	Asset::getInstance()->addCss("/bitrix/themes/.default/calendar.css");

}
Asset::getInstance()->addJs('/bitrix/js/main/utils.js');
Asset::getInstance()->addJs('/bitrix/js/main/popup_menu.js');
Asset::getInstance()->addJs('/bitrix/js/main/admin_tools.js');
Asset::getInstance()->addJs('/bitrix/js/main/public_tools.js');
Asset::getInstance()->addJs('/bitrix/js/bizproc/bizproc.js');
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");
//////////////////////////////////////////////////////////////////////////////

$ID = $arResult["ID"];

$aMenu = [];

$listMenuItem = [];
if (!array_key_exists('SKIP_BP_TEMPLATES_LIST', $arParams) || $arParams['SKIP_BP_TEMPLATES_LIST'] !== 'Y')
{
	$listMenuItem = [
		"TEXT" => (
			(!empty($arParams["BIZPROC_EDIT_MENU_LIST_MESSAGE"]))
				? htmlspecialcharsbx($arParams["BIZPROC_EDIT_MENU_LIST_MESSAGE"])
				: GetMessage("BIZPROC_WFEDIT_MENU_LIST")
		),
		"TITLE" => (
			(!empty($arParams["BIZPROC_EDIT_MENU_LIST_TITLE_MESSAGE"]))
				? htmlspecialcharsbx($arParams["BIZPROC_EDIT_MENU_LIST_TITLE_MESSAGE"])
				: GetMessage("BIZPROC_WFEDIT_MENU_LIST_TITLE")
		),
		"LINK" => $arResult['LIST_PAGE_URL'],
		"ICON" => "btn_list",
	];
}

if ($isAdminSection && $listMenuItem)
{
	$aMenu[] = $listMenuItem;
}

$aMenu[] = [
	"TEXT"  => GetMessage("BIZPROC_WFEDIT_MENU_PARAMS"),
	"TITLE" => GetMessage("BIZPROC_WFEDIT_MENU_PARAMS_TITLE"),
	"LINK"  => "javascript:BCPShowParams();",
	"ICON"  => "btn_settings",
];

$aMenu[] = [
	'TEXT' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_WFEDIT_MENU_GLOBAL_VARIABLES'),
	'TITLE' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_WFEDIT_MENU_GLOBAL_VARIABLES_TITLE'),
	'LINK' => 'javascript:BX.Bizproc.WorkflowEditComponent.Globals.showGlobalVariables();',
];
$aMenu[] = [
	'TEXT' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_WFEDIT_MENU_GLOBAL_CONSTANTS'),
	'TITLE' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_WFEDIT_MENU_GLOBAL_CONSTANTS_TITLE'),
	'LINK' => 'javascript:BX.Bizproc.WorkflowEditComponent.Globals.showGlobalConstants();',
];

if (!$isAdminSection && $listMenuItem)
{
	$aMenu[] = $listMenuItem;
}

$aMenu[] = ["SEPARATOR" => true];

if (!array_key_exists("SKIP_BP_TYPE_SELECT", $arParams) || $arParams["SKIP_BP_TYPE_SELECT"] != "Y")
{
	$arSubMenu = [];

	$arSubMenu[] = [
		"TEXT"    => GetMessage("BIZPROC_WFEDIT_MENU_ADD_STATE_1"),
		"TITLE"   => GetMessage("BIZPROC_WFEDIT_MENU_ADD_STATE_TITLE_1"),
		"ONCLICK" => "if(confirm('".GetMessage("BIZPROC_WFEDIT_MENU_ADD_WARN")."'))window.location='".str_replace("#ID#", "0", $arResult["EDIT_PAGE_TEMPLATE"]).(mb_strpos($arResult["EDIT_PAGE_TEMPLATE"], "?")? "&" : "?")."init=statemachine';"
	];

	$arSubMenu[] = [
		"TEXT"    => GetMessage("BIZPROC_WFEDIT_MENU_ADD_SEQ"),
		"TITLE"   => GetMessage("BIZPROC_WFEDIT_MENU_ADD_SEQ_TITLE_1"),
		"ONCLICK" => "if(confirm('".GetMessage("BIZPROC_WFEDIT_MENU_ADD_WARN")."'))window.location='".str_replace("#ID#", "0", $arResult["EDIT_PAGE_TEMPLATE"]).(mb_strpos($arResult["EDIT_PAGE_TEMPLATE"], "?")? "&" : "?")."';"
	];

	$aMenu[] = [
		"TEXT"  => GetMessage("BIZPROC_WFEDIT_MENU_ADD"),
		"TITLE" => GetMessage("BIZPROC_WFEDIT_MENU_ADD_TITLE"),
		"ICON"  => "btn_new",
		"MENU"  => $arSubMenu
	];
}

$aMenu[] = ["SEPARATOR" => true];
$aMenu[] = [
	"TEXT"  => GetMessage("BIZPROC_WFEDIT_MENU_EXPORT"),
	"TITLE" => GetMessage("BIZPROC_WFEDIT_MENU_EXPORT_TITLE"),
	"LINK"  => "javascript:BCPProcessExport();",
	"ICON"  => "",
];
$aMenu[] = [
	"TEXT"  => GetMessage("BIZPROC_WFEDIT_MENU_IMPORT"),
	"TITLE" => GetMessage("BIZPROC_WFEDIT_MENU_IMPORT_TITLE"),
	"LINK"  => "javascript:BCPProcessImport();",
	"ICON"  => "",
];

?>
<script>
	var BCPEmptyWorkflow =  <?=$ID > 0 ? 'false' : 'true'?>;

	function BCPProcessExport()
	{
		if (BCPEmptyWorkflow)
		{
			alert('<?= GetMessageJS("BIZPROC_EMPTY_EXPORT") ?>');
			return false;
		}
		<? $u = \Bitrix\Main\Engine\UrlManager::getInstance()->create('export', [
				'c' => 'bitrix:bizproc.workflow.edit',
				'mode' => \Bitrix\Main\Engine\Router::COMPONENT_MODE_AJAX,
				'templateId' => $ID,
				'signedParameters' => $this->getComponent()->getSignedParameters(),
			]);
		?>
		window.open('<?=CUtil::JSEscape($u)?>');
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
					window.BPTemplateIsModified = false;
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
			content: '<fo' + 'rm action="<?= CUtil::JSEscape(POST_FORM_ACTION_URI) ?>" method="POST" id="import_template_form" enctype="multipart/form-data"><table cellspacing="0" cellpadding="0" border="0" width="100%"><tr valign="top"><td width="15%" align="right"><?= GetMessageJS("BIZPROC_IMPORT_FILE") ?>:</td><td align="left"><input type="file" size="35" name="import_template_file" value=""></td></tr></table><input type="hidden" name="import_template" value="Y"><input type="hidden" id="id_import_template_name" name="import_template_name" value=""><input type="hidden" name="import_template_description" id="id_import_template_description" value=""><input type="hidden" id="id_import_template_autostart" name="import_template_autostart" value=""><?= bitrix_sessid_post() ?></form>',
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

		var btnSave = BX('bizprocdesigner-btn-save');
		var btnApply = BX('bizprocdesigner-btn-apply');
		if (btnSave)
		{
			BX.removeClass(btnSave, 'ui-btn-wait');
		}
		if (btnApply)
		{
			BX.removeClass(btnApply, 'ui-btn-wait');
		}
	}

	<?$v = str_replace("&amp;", "&", POST_FORM_ACTION_URI);?>

	function BCPSaveUserParams()
	{
		var data = JSToPHP(arUserParams, 'USER_PARAMS');

		jsExtLoader.onajaxfinish = BCPSaveTemplateComplete;
		jsExtLoader.startPost('<?= CUtil::JSEscape($v) ?><?if(mb_strpos($v, "?")):?>&<?else:?>?<?endif?><?=bitrix_sessid_get()?>&saveajax=Y&saveuserparams=Y', data);
	}

	function BCPSaveTemplate(save)
	{
		arWorkflowTemplate = Array(rootActivity.Serialize());
		var data =
			'workflowTemplateName=' + encodeURIComponent(workflowTemplateName) + '&' +
			'workflowTemplateDescription=' + encodeURIComponent(workflowTemplateDescription) + '&' +
			'workflowTemplateAutostart=' + encodeURIComponent(workflowTemplateAutostart) + '&' +
			'workflowTemplateIsSystem=' + encodeURIComponent(workflowTemplateIsSystem) + '&' +
			'workflowTemplateSort=' + encodeURIComponent(workflowTemplateSort) + '&' +
			JSToPHP(arWorkflowParameters, 'arWorkflowParameters') + '&' +
			JSToPHP(arWorkflowVariables, 'arWorkflowVariables') + '&' +
			JSToPHP(arWorkflowConstants, 'arWorkflowConstants') + '&' +
			JSToPHP(arWorkflowTemplate, 'arWorkflowTemplate');

		jsExtLoader.onajaxfinish = BCPSaveTemplateComplete;
		jsExtLoader.startPost('<?=CUtil::JSEscape($v)?><?if(mb_strpos($v, "?")):?>&<?else:?>?<?endif?><?=bitrix_sessid_get()?>&saveajax=Y' +
			(save ? '' : '&apply=Y'),
			data);
	}

	function BCPShowParams()
	{
		<?
		$dts = $arResult['DOCUMENT_TYPE_SIGNED'];
		$u =  "/bitrix/tools/bizproc_wf_settings.php?mode=public&bxpublic=Y&lang=".LANGUAGE_ID."&dts=".$dts;
		?>
		(new BX.CAdminDialog({
			'content_url': '<?=CUtil::JSEscape($u)?>',
			'content_post': 'workflowTemplateName=' + encodeURIComponent(workflowTemplateName) + '&' +
				'workflowTemplateDescription=' + encodeURIComponent(workflowTemplateDescription) + '&' +
				'workflowTemplateAutostart=' + encodeURIComponent(workflowTemplateAutostart) + '&' +
				'workflowTemplateIsSystem=' + encodeURIComponent(workflowTemplateIsSystem) + '&' +
				'workflowTemplateSort=' + encodeURIComponent(workflowTemplateSort) + '&' +
				'document_type=' + encodeURIComponent(document_type) + '&' +
				'<?= bitrix_sessid_get() ?>' + '&' +
				JSToPHP(arWorkflowParameters, 'arWorkflowParameters') + '&' +
				JSToPHP(arWorkflowVariables, 'arWorkflowVariables') + '&' +
				JSToPHP(arWorkflowConstants, 'arWorkflowConstants') + '&' +
				JSToPHP(Array(rootActivity.Serialize()), 'arWorkflowTemplate'),
			'height': 500,
			'width': 800,
			'resizable': false
		})).Show();
	}

	BX.Bizproc.WorkflowEditComponent.Globals.init({
		documentTypeSigned: '<?= CUtil::JSEscape($arResult['DOCUMENT_TYPE_SIGNED']) ?>'
	});

</script>
<?php if (isset($arParams['SHOW_ADMIN_TOOLBAR']) && $arParams['SHOW_ADMIN_TOOLBAR'] == 'Y')
{
	$context = new CAdminContextMenu($aMenu);
	$context->Show();
}
?>
<div style="background-color: #FFFFFF;<?php if($isAdminSection): ?>padding: 10px<?php endif;?>">
	<?php if (isset($arParams['SHOW_TOOLBAR']) && $arParams['SHOW_TOOLBAR'] == 'Y')
	{
		$APPLICATION->IncludeComponent(
			"bitrix:main.interface.toolbar",
			"",
			[
				"BUTTONS" => $aMenu,
			],
			$component, ["HIDE_ICONS" => "Y"]
		);
	}

	global $JSMESS;
	$JSMESS = [];
	$getJsMsg = function ($f, $actId)
	{
		global $JSMESS;
		$MESS = \Bitrix\Main\Localization\Loc::loadLanguageFile($f."/".$actId.".js.php");

		foreach ($MESS as $k => $v)
		{
			$JSMESS[$k] = $v;
		}
	};

	foreach ($arResult['ACTIVITIES'] as $actId => $actProps)
	{
		$actPath = mb_substr($actProps["PATH_TO_ACTIVITY"], mb_strlen($_SERVER["DOCUMENT_ROOT"]));
		if (file_exists($actProps["PATH_TO_ACTIVITY"]."/".$actId.".js"))
		{
			Asset::getInstance()->addJs($actPath.'/'.$actId.'.js');
			$getJsMsg($actProps["PATH_TO_ACTIVITY"], $actId);
		}

		if (file_exists($actProps["PATH_TO_ACTIVITY"]."/".$actId.".css"))
		{
			if ($isAdminSection)
			{
				$APPLICATION->SetAdditionalCSS($actPath.'/'.$actId.'.css');
			}
			else
			{
				Asset::getInstance()->addCss($actPath.'/'.$actId.'.css');
			}
		}

		if (file_exists($actProps["PATH_TO_ACTIVITY"]."/icon.gif"))
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
		var arWorkflowGlobalConstants = <?= CUtil::PhpToJSObject($arResult['GLOBAL_CONSTANTS']) ?>;
		var arWorkflowGlobalVariables = <?= CUtil::PhpToJSObject($arResult['GLOBAL_VARIABLES']) ?>;
		var wfGVarVisibilityNames = <?= CUtil::PhpToJSObject($arResult['GLOBAL_VARIABLES_VISIBILITY_NAMES']) ?>;
		var wfGConstVisibilityNames = <?= CUtil::PhpToJSObject($arResult['GLOBAL_CONSTANTS_VISIBILITY_NAMES']) ?>;
		var arWorkflowTemplate = <?=CUtil::PhpToJSObject($arResult['TEMPLATE'][0])?>;
		var arDocumentFields = <?=CUtil::PhpToJSObject($arResult['DOCUMENT_FIELDS'])?>;

		var workflowTemplateName = <?=CUtil::PhpToJSObject($arResult['TEMPLATE_NAME'])?>;
		var workflowTemplateDescription = <?=CUtil::PhpToJSObject($arResult['TEMPLATE_DESC'])?>;
		var workflowTemplateAutostart = <?=CUtil::PhpToJSObject($arResult['TEMPLATE_AUTOSTART'])?>;
		var workflowTemplateIsSystem = <?=CUtil::PhpToJSObject($arResult['TEMPLATE_IS_SYSTEM'])?>;
		var workflowTemplateSort = <?=CUtil::PhpToJSObject($arResult['TEMPLATE_SORT'])?>;

		var document_type = <?=CUtil::PhpToJSObject($arResult['DOCUMENT_TYPE'])?>;
		var document_type_signed = '<?=CUtil::JSEscape($arResult['DOCUMENT_TYPE_SIGNED'])?>';
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
			if (rootActivity.Type == 'SequentialWorkflowActivity')
			{
				if (rootActivity.swfWorkspaceDiv)
					p = rootActivity.swfWorkspaceDiv.scrollTop;

				while (rootActivity.childActivities.length > 0)
					rootActivity.RemoveChild(rootActivity.childActivities[0]);

				rootActivity.Init(arWorkflowTemplate);
				rootActivity.DrawActivities();

				rootActivity.swfWorkspaceDiv.scrollTop = p;
			}
			else
			{
				if (rootActivity._redrawObject)
				{
					if (rootActivity._redrawObject.swfWorkspaceDiv)
						p = rootActivity._redrawObject.swfWorkspaceDiv.scrollTop;

					while (rootActivity._redrawObject.childActivities.length > 0)
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

					while (rootActivity.childActivities.length > 0)
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
				setTimeout(function()
				{
					start();
				}, 1000);
				return;
			}
			BizProcRender(arWorkflowTemplate, document.getElementById('wf1'));

			if (history.scrollRestoration)
			{
				history.scrollRestoration = "manual";
			}

			const hash = window.location.hash;
			if (hash)
			{
				const activity = window.rootActivity.findChildById(hash.slice(1));
				/** @var BizProcActivity activity */
				if (activity)
				{
					activity.focusAndBlink();
				}
			}
			else
			{
				var workArea = document.getElementById('workarea-content');
				if (workArea)
				{
					BX.scrollToNode(workArea);
				}
			}

			<?if($ID <= 0):?>
			BCPShowParams();
			<?endif;?>
		}

		BX.ready(start);
		window.onbeforeunload = function()
		{
			return BPTemplateIsModified ? '<?=GetMessageJS('BIZPROC_WFEDIT_BEFOREUNLOAD')?>' : null;
		};
	</script>

	<? if (!$arResult['TEMPLATE_CHECK_STATUS']):
		echo ShowError(GetMessage('BIZPROC_WFEDIT_CHECK_ERROR_1'));
	endif;
	?>
	<form>
		<div id="wf1" style="width: 100%; border-bottom: 2px #efefef dotted; padding-bottom: 10px; position: relative; z-index: 1"></div>

		<?php if (!$isAdminSection):

			$APPLICATION->IncludeComponent('bitrix:ui.button.panel', '', [
				'BUTTONS' =>
					[
						[
							'ID' => 'bizprocdesigner-btn-save',
							'TYPE' => 'save',
							'ONCLICK' => 'BCPSaveTemplate(true); return false;',
						],
						[
							'ID' => 'bizprocdesigner-btn-apply',
							'TYPE' => 'apply',
							'ONCLICK' => 'BCPSaveTemplate(); return false;',
						],
						[
							'TYPE' => 'cancel',
							'ONCLICK' => "window.location='".
								CUtil::JSEscape(isset($arResult['BACK_URL']) ? $arResult['BACK_URL'] : $arResult['LIST_PAGE_URL'])."';"
						]
					],
				'ALIGN' => 'left'
			]);
		else:?>
		<div id="bizprocsavebuttons">
			<br>
			<input type="button"
				onclick="BCPSaveTemplate(true);"
				value="<? echo GetMessage("BIZPROC_WFEDIT_SAVE_BUTTON") ?>">
			<input type="button"
				onclick="BCPSaveTemplate();"
				value="<? echo GetMessage("BIZPROC_WFEDIT_APPLY_BUTTON") ?>">
			<input type="button"
				onclick="window.location='<?= htmlspecialcharsbx(CUtil::JSEscape(isset($arResult['BACK_URL']) ? $arResult['BACK_URL'] : $arResult['LIST_PAGE_URL'])) ?>';"
				value="<? echo GetMessage("BIZPROC_WFEDIT_CANCEL_BUTTON") ?>">
		</div>
		<?php endif;?>
	</form>
</div>