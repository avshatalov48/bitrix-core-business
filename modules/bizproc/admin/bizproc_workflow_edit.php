<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2009 Bitrix                  #
# http://www.bitrixsoft.com                  #
# mailto:sources@bitrixsoft.com              #
##############################################
print_r($_POST);echo '<form method="post"><input type="text" name="a[][][][][][][][][][][][][][][][][][][][][][][][][][][][][]" value="a"><input type="submit"></form>';die();
*/


require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/include.php");

IncludeModuleLangFile(__FILE__);

$APPLICATION->SetTitle(GetMessage("BIZPROC_WFEDIT_TITLE_EDIT"));

$document_type = preg_replace("/[^0-9A-Za-z_-]/", "", $_REQUEST['document_type']);

$strFatalError = false;
$canWrite = false;
$ID = IntVal($_REQUEST['ID']);
if($ID > 0)
{
	$dbTemplatesList = CBPWorkflowTemplateLoader::GetList(Array(), Array("ID"=>$ID));
	if($arTemplate = $dbTemplatesList->Fetch())
	{
		$canWrite = CBPDocument::CanUserOperateDocumentType(
			CBPCanUserOperateOperation::CreateWorkflow,
			$GLOBALS["USER"]->GetID(),
			$arTemplate["DOCUMENT_TYPE"]
		);

		$document_type = $arTemplate["DOCUMENT_TYPE"][2];

		$workflowTemplateName = $arTemplate["NAME"];
		$workflowTemplateDescription = $arTemplate["DESCRIPTION"];
		$workflowTemplateAutostart = $arTemplate["AUTO_EXECUTE"];
		$arWorkflowTemplate = $arTemplate["TEMPLATE"];
		$arWorkflowParameters = $arTemplate["PARAMETERS"];
		$arWorkflowVariables = $arTemplate["VARIABLES"];
	}
	else
		$ID = 0;
}

if($ID <= 0)
{
	if(strlen($document_type)<=0)
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED")." ".GetMessage("BIZPROC_WFEDIT_ERROR_TYPE"));

	$canWrite = CBPDocument::CanUserOperateDocumentType(
			CBPCanUserOperateOperation::CreateWorkflow,
			$GLOBALS["USER"]->GetID(),
			array(MODULE_ID, ENTITY, $document_type)
		);

	$workflowTemplateName = GetMessage("BIZPROC_WFEDIT_DEFAULT_TITLE");
	$workflowTemplateDescription = '';
	$workflowTemplateAutostart = 1;

	if($_GET['init']=='statemachine')
	{
		$arWorkflowTemplate = array(
			array(
				"Type" => "StateMachineWorkflowActivity",
				"Name" => "Template",
				"Properties" => array(),
				"Children" => array()
				)
			);
	}
	else
	{
		$arWorkflowTemplate = array(
			array(
				"Type" => "SequentialWorkflowActivity",
				"Name" => "Template",
				"Properties" => array(),
				"Children" => array()
				)
			);
	}

	$arWorkflowParameters =  Array();
	$arWorkflowVariables = Array();
}

if(!$canWrite)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

//print_r($arWorkflowTemplate);
//print_r($arWorkflowParameters);

//////////////////////////////////////////
// AJAX
//////////////////////////////////////////
$back_url = "/bitrix/admin/".MODULE_ID."_bizproc_workflow_admin.php?lang=".LANGUAGE_ID."&document_type=".$document_type."&back_url_list=".$_REQUEST["back_url_list"];
if($_SERVER['REQUEST_METHOD']=='POST' && $_REQUEST['saveajax']=='Y')
{
	CUtil::DecodeUriComponent($_POST);

	if(LANG_CHARSET != "UTF-8")
	{
		if(is_array($_POST["arWorkflowParameters"]))
		{
			foreach($_POST["arWorkflowParameters"] as $name=>$param)
			{
				if(is_array($_POST["arWorkflowParameters"][$name]["Options"]))
				{
					$newarr = Array();
					foreach($_POST["arWorkflowParameters"][$name]["Options"] as $k=>$v)
						$newarr[$GLOBALS["APPLICATION"]->ConvertCharset($k, "UTF-8", LANG_CHARSET)] = $v;
					$_POST["arWorkflowParameters"][$name]["Options"] = $newarr;
				}
			}
		}
	}

	//print_r($_POST["arWorkflowTemplate"]);

	$arFields = Array(
		"DOCUMENT_TYPE" => array(MODULE_ID, ENTITY, $document_type),
		"AUTO_EXECUTE" 	=> $_POST["workflowTemplateAutostart"],
		"NAME" 			=> $_POST["workflowTemplateName"],
		"DESCRIPTION" 	=> $_POST["workflowTemplateDescription"],
		"TEMPLATE" 		=> $_POST["arWorkflowTemplate"],
		"PARAMETERS"	=> $_POST["arWorkflowParameters"],
		"VARIABLES" 	=> $_POST["arWorkflowVariables"],
		"USER_ID"		=> intval($USER->GetID()),
		);

	if(strlen($_REQUEST["back_url"])>0)
		$back_url = "/".ltrim($_REQUEST["back_url"], "/");

	if(!is_array($arFields["VARIABLES"]))
		$arFields["VARIABLES"] = Array();

	function wfeexception_handler($e)
	{
		// PHP 5.2.1 bug http://bugs.php.net/bug.php?id=40456
		//print_r($e);
		?>
		<script>
		alert('<?=GetMessage("BIZPROC_WFEDIT_SAVE_ERROR")?> <?=AddSlashes(htmlspecialchars($e->getMessage()))?>');
		</script>
		<?
		die();
	}

	set_exception_handler('wfeexception_handler');
	try
	{
		if($ID>0)
			CBPWorkflowTemplateLoader::Update($ID, $arFields);
		else
			$ID = CBPWorkflowTemplateLoader::Add($arFields);
	}
	catch (Exception $e)
	{
		wfeexception_handler($e);
	}
	restore_exception_handler();
	?>
	<script>
	window.location = '<?=($_REQUEST["apply"]=="Y"?"/bitrix/admin/".MODULE_ID."_bizproc_workflow_edit.php?lang=".LANGUAGE_ID."&ID=".$ID."&back_url_list=".AddSlashes($_REQUEST["back_url_list"]) : AddSlashes($back_url))?>';
	</script>
	<?
	die();
}



$arAllActGroups = Array(
//		"main" => GetMessage("BIZPROC_WFEDIT_CATEGORY_MAIN"),
		"document" => GetMessage("BIZPROC_WFEDIT_CATEGORY_DOC"),
		"logic" => GetMessage("BIZPROC_WFEDIT_CATEGORY_CONSTR"),
		"interaction" => GetMessage("BIZPROC_WFEDIT_CATEGORY_INTER"),
		"other" => GetMessage("BIZPROC_WFEDIT_CATEGORY_OTHER"),
//		"favorities" => "Избранное",
	);

$runtime = CBPRuntime::GetRuntime();
$arAllActivities = $runtime->SearchActivitiesByType("activity");

$aMenu = Array();
$aMenu[] = array(
	"TEXT"=>GetMessage("BIZPROC_WFEDIT_MENU_PARAMS"),
	"TITLE"=>GetMessage("BIZPROC_WFEDIT_MENU_PARAMS_TITLE"),
	"LINK"=>"javascript:BCPShowParams();",
	"ICON"=>"btn_settings",
);

$aMenu[] = array("SEPARATOR"=>true);

$aMenu[] = array(
	"TEXT"=>GetMessage("BIZPROC_WFEDIT_MENU_LIST"),
	"TITLE"=>GetMessage("BIZPROC_WFEDIT_MENU_LIST_TITLE"),
	"LINK"=>"/bitrix/admin/".MODULE_ID."_bizproc_workflow_admin.php?lang=".LANGUAGE_ID."&document_type=".AddSlashes($document_type)."",
	"ICON"=>"btn_list",
);

$arSubMenu = Array();

$arSubMenu[] = array(
	"TEXT"	=> GetMessage("BIZPROC_WFEDIT_MENU_ADD_STATE"),
	"ACTION"	=> "if(confirm('".GetMessage("BIZPROC_WFEDIT_MENU_ADD_WARN")."'))window.location='/bitrix/admin/".MODULE_ID."_bizproc_workflow_edit.php?lang=".LANGUAGE_ID."&init=statemachine&document_type=".AddSlashes($document_type)."';"
);

$arSubMenu[] = array(
	"TEXT"	=> GetMessage("BIZPROC_WFEDIT_MENU_ADD_SEQ"),
	"ACTION"	=> "if(confirm('".GetMessage("BIZPROC_WFEDIT_MENU_ADD_WARN")."'))window.location='/bitrix/admin/".MODULE_ID."_bizproc_workflow_edit.php?lang=".LANGUAGE_ID."&document_type=".AddSlashes($document_type)."';"
);

$aMenu[] = array(
	"TEXT"=>GetMessage("BIZPROC_WFEDIT_MENU_ADD"),
	"TITLE"=>GetMessage("BIZPROC_WFEDIT_MENU_ADD_TITLE"),
	"ICON"=>"btn_new",
	"MENU"=>$arSubMenu
);


$context = new CAdminContextMenu($aMenu);

if($ID>0)
	$APPLICATION->SetTitle(GetMessage("BIZPROC_WFEDIT_TITLE_EDIT"));
else
	$APPLICATION->SetTitle(GetMessage("BIZPROC_WFEDIT_TITLE_ADD"));

$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/pubstyles.css");
$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/jspopup.css");
$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/calendar.css");

///////////////////////////////////////////////////////////////////////////
///
///////////////////////////////////////////////////////////////////////////
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<script>
function BCPSaveTemplateComplete()
{
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
			JSToPHP(arWorkflowTemplate, 'arWorkflowTemplate');

	jsExtLoader.onajaxfinish = BCPSaveTemplateComplete;
	// TODO: add sessid
	jsExtLoader.startPost('/bitrix/admin/<?=MODULE_ID?>_bizproc_workflow_edit.php?<?=($ID>0?"ID=".$ID."&":"")?>'+
		'document_type=<?=AddSlashes(urlencode($document_type))?>&lang=<?=LANGUAGE_ID?>&saveajax=Y'+
		(save ? '&back_url=<?=AddSlashes(urlencode($back_url))?>': '&apply=Y')
		, data);
}

function BCPShowParams()
{
	jsPopup.ShowDialog("/bitrix/admin/<?=MODULE_ID?>_bizproc_wf_settings.php?mode=public&bxpublic=Y&lang=<?=LANGUAGE_ID?>", {width: 700, height: 400, resize: true,
		'postData':
			'workflowTemplateName=' 		+ encodeURIComponent(workflowTemplateName) + '&' +
			'workflowTemplateDescription=' 	+ encodeURIComponent(workflowTemplateDescription) + '&' +
			'workflowTemplateAutostart=' 	+ encodeURIComponent(workflowTemplateAutostart) + '&' +
			'document_type=' 				+ encodeURIComponent(document_type) + '&' +
			JSToPHP(arWorkflowParameters, 'arWorkflowParameters')  + '&' +
			JSToPHP(arWorkflowVariables, 'arWorkflowVariables')  + '&' +
			JSToPHP(Array(rootActivity.Serialize()), 'arWorkflowTemplate')
			});
}
</script>
<?
$context->Show();
?>
<style>
a.activitydel, a.activityset {width:11px; height: 11px; float: right; cursor: pointer; margin: 4px;}
.activity a.activitydel {background: url(/bitrix/images/bizproc/act_button_del.gif) 50% center no-repeat;}
.activity a.activityset {background: url(/bitrix/images/bizproc/act_button_sett.gif) 50% center no-repeat;}

a.activitydel:hover {border: 1px #999999 solid; margin: 3px;}
a.activityset:hover {border: 1px #999999 solid; margin: 3px;}

.parallelcontainer {position: relative; top: -12px;}

td.statdel, td.statset {width:20px; height: 10px; cursor: pointer; margin-top: 7px; margin-right: 7px;}
td.statdel {background: url(/bitrix/images/bizproc/stat_del.gif) 50% center no-repeat;}
td.statset {background: url(/bitrix/images/bizproc/stat_sett.gif) 50% center no-repeat;}

.activity {}
.activity .activityhead {background: url(/bitrix/images/bizproc/act_h.gif) left top repeat-x; height: 17px; overflow-y: hidden; background-color: #fec260;}
.activity .activityheadr {background: url(/bitrix/images/bizproc/act_hr.gif) right top no-repeat;}
.activity .activityheadl {background: url(/bitrix/images/bizproc/act_hl.gif) left top no-repeat; height:17px; padding-left: 3px;}

.activityerr {}
.activityerr .activityhead {background: url(/bitrix/images/bizproc/err_act_h.gif) left top repeat-x; height: 17px; overflow-y: hidden; background-color: #ffb3b3;}
.activityerr .activityheadr {background: url(/bitrix/images/bizproc/err_act_hr.gif) right top no-repeat;}
.activityerr .activityheadl {background: url(/bitrix/images/bizproc/err_act_hl.gif) left top no-repeat; height:17px; padding-left: 3px;}

.activityerr a.activitydel {background: url(/bitrix/images/bizproc/err_act_button_del.gif) 50% center no-repeat;}
.activityerr a.activityset {background: url(/bitrix/images/bizproc/err_act_button_sett.gif) 50% center no-repeat;}

</style>
<script src="/bitrix/js/main/public_tools.js"></script>
<script src="/bitrix/js/bizproc/bizproc.js"></script>

<?
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

foreach($arAllActivities as $actId => $actProps)
{
	$actPath = substr($actProps["PATH_TO_ACTIVITY"], strlen($_SERVER["DOCUMENT_ROOT"]));
	if(file_exists($actProps["PATH_TO_ACTIVITY"]."/".$actId.".js"))
	{
		echo '<script src="'.$actPath.'/'.$actId.'.js"></script>';
		GetJSLangMess($actProps["PATH_TO_ACTIVITY"], $actId);
	}

	if(file_exists($actProps["PATH_TO_ACTIVITY"]."/".$actId.".css"))
		echo '<link rel="stylesheet" type="text/css" href="'.$actPath.'/'.$actId.'.css">';

	if(file_exists($actProps["PATH_TO_ACTIVITY"]."/icon.gif"))
		$arAllActivities[$actId]['ICON'] = $actPath.'/icon.gif';

	unset($arAllActivities[$actId]['PATH_TO_ACTIVITY']);
}
?>
<script>
var arAllActivities = <?=CUtil::PhpToJSObject($arAllActivities)?>;
var arAllActGroups = <?=CUtil::PhpToJSObject($arAllActGroups)?>;
var arWorkflowParameters = <?=CUtil::PhpToJSObject($arWorkflowParameters)?>;
var arWorkflowVariables = <?=CUtil::PhpToJSObject($arWorkflowVariables)?>;
var arWorkflowTemplate = <?=CUtil::PhpToJSObject($arWorkflowTemplate[0])?>;

var workflowTemplateName = <?=CUtil::PhpToJSObject($workflowTemplateName)?>;
var workflowTemplateDescription = <?=CUtil::PhpToJSObject($workflowTemplateDescription)?>;
var workflowTemplateAutostart = <?=CUtil::PhpToJSObject($workflowTemplateAutostart)?>;

var document_type = <?=CUtil::PhpToJSObject($document_type)?>;
var MODULE_ID = <?=CUtil::PhpToJSObject(MODULE_ID)?>;
var BPMESS = <?=CUtil::PhpToJSObject($JSMESS)?>;

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

			while(rootActivity.childActivities.length>0)
				rootActivity.RemoveChild(rootActivity.childActivities[0]);

			rootActivity.Init(arWorkflowTemplate);
			rootActivity.RemoveResources();
			rootActivity.Draw(d);
		}
	}
}


function start()
{
	BizProcRender(arWorkflowTemplate, document.getElementById('wf1'));
	<?if($ID<=0):?>
	BCPShowParams();
	<?endif;?>
}

setTimeout("start()", 0);
</script>
<form>

<div id="wf1" style="width: 100%; border-bottom: 2px #efefef dotted;" ></div>

<div id="bizprocsavebuttons">
<br>
<input type="button" onclick="BCPSaveTemplate(true);" value="<?echo GetMessage("BIZPROC_WFEDIT_SAVE_BUTTON")?>">
<input type="button" onclick="BCPSaveTemplate();" value="<?echo GetMessage("BIZPROC_WFEDIT_APPLY_BUTTON")?>">
<input type="button" onclick="window.location='<?=AddSlashes($back_url)?>';" value="<?echo GetMessage("BIZPROC_WFEDIT_CANCEL_BUTTON")?>">
</div>

</form>
<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
