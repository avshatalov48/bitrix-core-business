<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule('bizproc') || !CModule::IncludeModule('bizprocdesigner'))
	return;

$APPLICATION->SetTitle(GetMessage("BIZPROC_WFEDIT_TITLE_EDIT"));

$arResult['DOCUMENT_TYPE'] = $arParams["DOCUMENT_TYPE"];
$arResult['ID'] = $arParams["ID"];

$arResult['LIST_PAGE_URL'] = $arParams['LIST_PAGE_URL'];
$arResult["EDIT_PAGE_TEMPLATE"] = $arParams["EDIT_PAGE_TEMPLATE"];
$backUrl = (isset($_REQUEST['back_url']) && $_REQUEST['back_url'][0] === '/' && $_REQUEST['back_url'][1] !== '/') ? (string)$_REQUEST['back_url'] : null;

define("MODULE_ID", $arParams["MODULE_ID"]);
define("ENTITY", $arParams["ENTITY"]);

$arResult['DOCUMENT_TYPE'] = preg_replace("/[^0-9A-Za-z_-]/", "", $arResult['DOCUMENT_TYPE']);

$document_type = $arResult['DOCUMENT_TYPE'];

$strFatalError = false;
$canWrite = false;
$arTemplate = false;
$ID = IntVal($arResult['ID']);
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
		$arWorkflowConstants = $arTemplate["CONSTANTS"];
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

	if ($_GET['init'] == 'statemachine')
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

	$arWorkflowParameters =  array();
	$arWorkflowVariables = array();
	$arWorkflowConstants = array();
}

if(!$canWrite)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$saveUrl = $arResult["LIST_PAGE_URL"];
$applyUrl = str_replace("#ID#", $ID, $arResult["EDIT_PAGE_TEMPLATE"]);
if ($backUrl)
{
	$saveUrl = $backUrl;
	$applyUrl = CHTTP::urlAddParams($applyUrl, array('back_url' => $backUrl), array('encode' => true));
	$arResult['BACK_URL'] = $backUrl;
}

//////////////////////////////////////////
// AJAX
//////////////////////////////////////////
if($_SERVER['REQUEST_METHOD']=='POST' && $_REQUEST['saveajax']=='Y' && check_bitrix_sessid())
{
	$APPLICATION->RestartBuffer();
	CBPHelper::decodeTemplatePostData($_POST);

	if($_REQUEST['saveuserparams']=='Y')
	{
		$d = serialize($_POST['USER_PARAMS']);
		if (\Bitrix\Main\Text\BinaryString::getLength($d) > 64000)
		{
			?><!--SUCCESS--><script>
			alert('<?=GetMessageJS("BIZPROC_USER_PARAMS_SAVE_ERROR")?>');
			</script><?
			die();
		}
		CUserOptions::SetOption("~bizprocdesigner", "activity_settings", $d);
		die('<!--SUCCESS-->');
	}

	$arFields = Array(
		"DOCUMENT_TYPE" => array(MODULE_ID, ENTITY, $document_type),
//		"ACTIVE" 		=> $_POST["ACTIVE"],
		"AUTO_EXECUTE" 	=> $_POST["workflowTemplateAutostart"],
		"NAME" 			=> $_POST["workflowTemplateName"],
		"DESCRIPTION" 	=> $_POST["workflowTemplateDescription"],
		"TEMPLATE" 		=> $_POST["arWorkflowTemplate"],
		"PARAMETERS"	=> $_POST["arWorkflowParameters"],
		"VARIABLES" 	=> $_POST["arWorkflowVariables"],
		"CONSTANTS" 	=> $_POST["arWorkflowConstants"],
		"USER_ID"		=> intval($USER->GetID()),
		"MODIFIER_USER" => new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser),
		);

	if(!is_array($arFields["VARIABLES"]))
		$arFields["VARIABLES"] = array();
	if(!is_array($arFields["CONSTANTS"]))
		$arFields["CONSTANTS"] = array();

	/**
	 * @param CBPWorkflowTemplateValidationException $e
	 */
	function wfeexception_handler($e)
	{
		$errorMessages = array();
		$errors = [];
		if (method_exists($e, 'getErrors'))
		{
			$errors = $e->getErrors();
			foreach($errors as $error)
			{
				$errorMessages[] = CUtil::JSEscape($error['message']);
			}
		}
		else
		{
			$errorMessages[] = CUtil::JSEscape($e->getMessage());
		}
		?><!--SUCCESS--><script>
			alert('<?=GetMessageJS("BIZPROC_WFEDIT_SAVE_ERROR")?>\n<?=implode('\n', $errorMessages)?>');
			(function(){
				var i, setFocus = true, activity, error, errors = [];
				errors = <?=\Bitrix\Main\Web\Json::encode($errors);?>;

				for (i = 0; i < errors.length; ++i)
				{
					error = errors[i];
					if (error.activityName)
					{
						activity = window.rootActivity.findChildById(error.activityName);
						/** @var BizProcActivity activity */
						if (activity)
						{
							activity.SetError(true, setFocus);
							setFocus = false;
						}
					}
				}
			})();
		</script><?
		die();
	}
	//set_exception_handler('wfeexception_handler');
	try
	{
		if($ID>0)
		{
			CBPWorkflowTemplateLoader::Update($ID, $arFields);
		}
		else
		{
			$ID = CBPWorkflowTemplateLoader::Add($arFields);
			$applyUrl = str_replace("#ID#", $ID, $arResult["EDIT_PAGE_TEMPLATE"]);
			if ($backUrl)
			{
				$applyUrl = CHTTP::urlAddParams($applyUrl, array('back_url' => $backUrl), array('encode' => true));
			}
		}
	}
	catch (Exception $e)
	{
		wfeexception_handler($e);
	}
	//restore_exception_handler();
	?><!--SUCCESS--><script>
		BPTemplateIsModified = false;
		window.location = '<?=($_REQUEST["apply"]=="Y"? CUtil::JSEscape($applyUrl) : CUtil::JSEscape($saveUrl))?>';
	</script><?
	die();
}

if($_SERVER['REQUEST_METHOD']=='GET' && $_REQUEST['export_template']=='Y' && check_bitrix_sessid())
{
	$APPLICATION->RestartBuffer();
	if ($ID > 0)
	{
		$datum = CBPWorkflowTemplateLoader::ExportTemplate($ID);

		header("HTTP/1.1 200 OK");
		header("Content-Type: application/force-download; name=\"bp-".$ID.".bpt\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".(function_exists('mb_strlen')?mb_strlen($datum, 'ISO-8859-1'):strlen($datum)));
		header("Content-Disposition: attachment; filename=\"bp-".$ID.".bpt\"");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Expires: 0");
		header("Pragma: public");

		echo $datum;
	}
	die();
}

if($_SERVER['REQUEST_METHOD']=='POST' && $_REQUEST['import_template']=='Y' && check_bitrix_sessid())
{
	$APPLICATION->RestartBuffer();
	//CUtil::DecodeUriComponent($_POST);

	$r = 0;
	$errTmp = "";
	if (is_uploaded_file($_FILES['import_template_file']['tmp_name']))
	{
		$f = fopen($_FILES['import_template_file']['tmp_name'], "rb");
		$datum = fread($f, filesize($_FILES['import_template_file']['tmp_name']));
		fclose($f);

		try
		{
			$r = CBPWorkflowTemplateLoader::ImportTemplate(
				$ID,
				array(MODULE_ID, ENTITY, $document_type),
				$_POST["import_template_autostart"],
				$_POST["import_template_name"],
				$_POST["import_template_description"],
				$datum
			);
		}
		catch (Exception $e)
		{
			$errTmp = preg_replace("#[\r\n]+#", " ", $e->getMessage());
		}
	}
	?>
	<script>
	<?if (intval($r) <= 0):?>
		alert('<?= GetMessageJS("BIZPROC_WFEDIT_IMPORT_ERROR").(strlen($errTmp) > 0 ? ": ".CUtil::JSEscape($errTmp) : "" ) ?>');
	<?else:?>
		<?$ID = $r;?>
	<?endif;
	$applyUrl = str_replace("#ID#", $ID, $arResult["EDIT_PAGE_TEMPLATE"]);
	if ($backUrl)
	{
		$applyUrl = CHTTP::urlAddParams($applyUrl, array('back_url' => $backUrl), array('encode' => true));
	}
	?>
	window.location = '<?=CUtil::JSEscape($applyUrl)?>';
	</script>
	<?
	die();
}

$arAllActGroups = array(
		"document" => GetMessage("BIZPROC_WFEDIT_CATEGORY_DOC"),
		'task' => GetMessage('BIZPROC_WFEDIT_CATEGORY_TASKS'),
		"logic" => GetMessage("BIZPROC_WFEDIT_CATEGORY_CONSTR"),
		"interaction" => GetMessage("BIZPROC_WFEDIT_CATEGORY_INTER"),
		"rest" => GetMessage("BIZPROC_WFEDIT_CATEGORY_REST"),
);

$runtime = CBPRuntime::GetRuntime();
$runtime->StartRuntime();
$arAllActivities = $runtime->SearchActivitiesByType("activity", array(MODULE_ID, ENTITY, $document_type));

foreach ($arAllActivities as $activity)
{
	if (!empty($activity['CATEGORY']['OWN_ID']) && !empty($activity['CATEGORY']['OWN_NAME']))
		$arAllActGroups[$activity['CATEGORY']['OWN_ID']] = $activity['CATEGORY']['OWN_NAME'];
}
$arAllActGroups['other'] = GetMessage("BIZPROC_WFEDIT_CATEGORY_OTHER");

if($ID>0)
	$APPLICATION->SetTitle(GetMessage("BIZPROC_WFEDIT_TITLE_EDIT"));
else
	$APPLICATION->SetTitle(GetMessage("BIZPROC_WFEDIT_TITLE_ADD"));

$arResult['DOCUMENT_TYPE'] = $document_type;

$arResult['ACTIVITY_GROUPS'] = $arAllActGroups;
$arResult['ACTIVITIES'] = $arAllActivities;

$arResult['TEMPLATE_NAME'] = $workflowTemplateName;
$arResult['TEMPLATE_DESC'] = $workflowTemplateDescription;
$arResult['TEMPLATE_AUTOSTART'] = $workflowTemplateAutostart;
$arResult['TEMPLATE'] = $arWorkflowTemplate;
$arResult['TEMPLATE_CHECK_STATUS'] = CBPWorkflowTemplateLoader::checkTemplateActivities($arWorkflowTemplate);
$arResult['PARAMETERS'] = $arWorkflowParameters;
$arResult['VARIABLES'] = $arWorkflowVariables;
$arResult['CONSTANTS'] = $arWorkflowConstants;

/** @var CBPDocumentService $documentService */
$documentService = $runtime->GetService('DocumentService');
$arResult['DOCUMENT_FIELDS'] = $documentService->GetDocumentFields(array(MODULE_ID, ENTITY, $document_type));

$arResult["ID"] = $ID;

$defUserParamsStr = serialize(array("groups" => array()));
$userParamsStr = CUserOptions::GetOption("~bizprocdesigner", "activity_settings", $defUserParamsStr);
if (empty($userParamsStr) || !CheckSerializedData($userParamsStr))
	$userParamsStr = $defUserParamsStr;

$arResult["USER_PARAMS"] = unserialize($userParamsStr);

$this->IncludeComponentTemplate();
?>