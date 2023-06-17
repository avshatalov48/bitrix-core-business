<?
/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 */
if (!array_key_exists("component_name", $_GET))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/public/component_props.php");
	die();
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

$APPLICATION->ShowAjaxHead();

function PageParams($bUrlEncode = true)
{
	$amp = $bUrlEncode ? '&amp;' : '&';
	return
		'component_name='.urlencode(CUtil::addslashes($_GET["component_name"])).
		$amp.'component_template='.urlencode(CUtil::addslashes($_GET["component_template"])).
		$amp.'template_id='.urlencode(CUtil::addslashes($_GET["template_id"])).
		$amp.'lang='.urlencode(CUtil::addslashes(LANGUAGE_ID)).
		$amp.'src_path='.urlencode(CUtil::addslashes($_GET["src_path"])).
		$amp.'src_line='.intval($_GET["src_line"]).
		$amp.'src_page='.urlencode(CUtil::addslashes($_GET["src_page"])).
		$amp.'src_site='.urlencode(CUtil::addslashes($_GET["src_site"]));
}

$io = CBXVirtualIo::GetInstance();

$src_path = $io->CombinePath("/", $_GET["src_path"]);
$src_line = intval($_GET["src_line"]);

if(!$USER->CanDoOperation('edit_php') && !$USER->CanDoFileOperation('fm_lpa', array($_GET["src_site"], $src_path)))
{
	die(GetMessage("ACCESS_DENIED"));
}

$bLimitPhpAccess = !$USER->CanDoOperation('edit_php');

CModule::IncludeModule("fileman");

$componentName = $_GET["component_name"];
$componentTemplate = $_GET["component_template"];
$templateId = $_GET["template_id"];
$relPath = $io->ExtractPathFromPath($src_path);

CComponentParamsManager::Init(array(
	'requestUrl' => '/bitrix/admin/fileman_component_params.php',
	'relPath' => $relPath
));

IncludeModuleLangFile(__FILE__);

CUtil::JSPostUnescape();

$obJSPopup = new CJSPopup('',
	array(
		'TITLE' => GetMessage("comp_prop_title")
	)
);

$obJSPopup->ShowTitlebar();

$strWarning = "";
$arValues = array();
$arTemplate = false;
$arComponent = false;
$arComponentDescription = false;
$arParameterGroups = array();
$filesrc = "";
$abs_path = "";
$curTemplate = "";

if(!CComponentEngine::CheckComponentName($componentName))
	$strWarning .= GetMessage("comp_prop_error_name")."<br>";

if($strWarning == "")
{
	// try to read parameters from script file
	/* Try to open script containing the component call */
	if(!$src_path || $src_line <= 0)
	{
		$strWarning .= GetMessage("comp_prop_err_param")."<br>";
	}
	else
	{
		$abs_path = $io->RelativeToAbsolutePath($src_path);
		$f = $io->GetFile($abs_path);
		$filesrc = $f->GetContents();
		if(!$filesrc || $filesrc == "")
			$strWarning .= GetMessage("comp_prop_err_open")."<br>";
	}

	if($strWarning == "")
	{
		$arComponent = PHPParser::FindComponent($componentName, $filesrc, $src_line);
		if ($arComponent === false)
			$strWarning .= GetMessage("comp_prop_err_comp")."<br>";
		else
			$arValues = $arComponent["DATA"]["PARAMS"];
	}
}

if($strWarning == "")
{
	if($_SERVER["REQUEST_METHOD"] == "POST" && $_GET["action"] == "refresh")
	{
		// parameters were changed by "ok" button
		// we need to refresh the component description with new values
		$arValues = array_merge($arValues, $_POST);
	}

	$curTemplate = (isset($_POST["COMPONENT_TEMPLATE"])) ? $_POST["COMPONENT_TEMPLATE"] : $componentTemplate;

	$data = CComponentParamsManager::GetComponentProperties(
		$componentName,
		$curTemplate,
		$templateId,
		$arValues
	);
	$data['description'] = CComponentUtil::GetComponentDescr($componentName);

	/* save parameters to file */
	if($_SERVER["REQUEST_METHOD"] == "POST" && $_GET["action"] == "save" && $arComponent !== false)
	{
		if (!check_bitrix_sessid())
		{
			$strWarning .= GetMessage("comp_prop_err_save")."<br>";
		}
		else
		{
			$aPostValues = array_merge($arValues, $_POST);
			unset($aPostValues["sessid"]);
			unset($aPostValues["bxpiheight"]);
			unset($aPostValues["bxpiwidth"]);

			CComponentUtil::PrepareVariables($aPostValues);
			foreach ($aPostValues as $name => $value)
			{
				if (is_array($value))
				{
					if (count($value) == 1 && isset($value[0]) && $value[0] == "")
					{
						$aPostValues[$name] = [];
					}
				}
				elseif ($bLimitPhpAccess && mb_substr($value, 0, 2) == '={' && mb_substr($value, -1) == '}')
				{
					$aPostValues[$name] = $arValues[$name];
				}
			}

			//check template name
			$sTemplateName = "";
			$arComponentTemplates = CComponentUtil::GetTemplatesList($componentName, $templateId);
			foreach($arComponentTemplates as $templ)
			{
				if($templ["NAME"] == $_POST["COMPONENT_TEMPLATE"])
				{
					$sTemplateName = $templ["NAME"];
					break;
				}
			}

			$functionParams = "";
			if(!empty($arComponent["DATA"]["FUNCTION_PARAMS"]))
			{
				$functionParams = ",\n".
					"\tarray(\n".
					"\t\t".PHPParser::ReturnPHPStr2($arComponent["DATA"]["FUNCTION_PARAMS"])."\n".
					"\t)";
			}

			$code = ($arComponent["DATA"]["VARIABLE"]? $arComponent["DATA"]["VARIABLE"]." = ":"").
				"\$APPLICATION->IncludeComponent(\n".
				"\t\"".$arComponent["DATA"]["COMPONENT_NAME"]."\", \n".
				"\t\"".$sTemplateName."\", \n".
				"\tarray(\n".
				"\t\t".PHPParser::ReturnPHPStr2($aPostValues)."\n".
				"\t),\n".
				"\t".($arComponent["DATA"]["PARENT_COMP"] <> ''? $arComponent["DATA"]["PARENT_COMP"] : "false").
				$functionParams.
				"\n);";

			$filesrc_for_save = mb_substr($filesrc, 0, $arComponent["START"]).$code.mb_substr($filesrc, $arComponent["END"]);

			$f = $io->GetFile($abs_path);
			$arUndoParams = array(
				'module' => 'fileman',
				'undoType' => 'edit_component_props',
				'undoHandler' => 'CFileman::UndoEditFile',
				'arContent' => array(
					'absPath' => $abs_path,
					'content' => $f->GetContents()
				)
			);

			if($APPLICATION->SaveFileContent($abs_path, $filesrc_for_save))
			{
				CUndo::ShowUndoMessage(CUndo::Add($arUndoParams));
				$obJSPopup->Close();
			}
			else
			{
				$strWarning .= GetMessage("comp_prop_err_save")."<br>";
			}
		}
	}
}
$componentPath = CComponentEngine::MakeComponentPath($componentName);

if($strWarning !== "")
{
	$obJSPopup->ShowValidationError($strWarning);
	?>
	<script>
		(function()
		{
			if (BX && BX.WindowManager)
			{
				var oPopup = BX.WindowManager.Get();
				if (oPopup && oPopup.PARTS && oPopup.PARTS.CONTENT_DATA)
				{
					oPopup.PARTS.CONTENT_DATA.style.display = 'none';
				}
			}
		})();
	</script>
	<?
}

$obJSPopup->StartContent();?>

<?if($strWarning === ""):?>
<script>
(function()
{
	function CompDialogManager(params)
	{
		this.Init(params);
	}

	CompDialogManager.prototype =
	{
		Init: function(params)
		{
			this.pDiv = BX('bx-comp-params-wrap');
			var oPopup = BX.WindowManager.Get();
			oPopup.PARTS.CONTENT_DATA.className = 'bxcompprop-adm-dialog-content';

			BX.addClass(oPopup.PARTS.CONTENT, 'bxcompprop-adm-dialog');

			BX.addCustomEvent(oPopup, 'onWindowResize', function()
			{
				BX.onCustomEvent(oBXComponentParamsManager, 'OnComponentParamsResize', [
					parseInt(oPopup.PARTS.CONTENT_DATA.style.width),
					parseInt(oPopup.PARTS.CONTENT_DATA.style.height)
				]);
			});

			oBXComponentParamsManager.params = {
				name: params.name,
				parent: params.parent,
				template: params.template,
				exParams: params.exParams,
				currentValues: params.currentValues || {},
				container: this.pDiv,
				siteTemplate: params.siteTemplate
			};

			BX.addCustomEvent(oBXComponentParamsManager, 'onComponentParamsBuilt', function()
			{
				BX.onCustomEvent(oBXComponentParamsManager, 'OnComponentParamsResize', [
					parseInt(oPopup.PARTS.CONTENT_DATA.style.width),
					parseInt(oPopup.PARTS.CONTENT_DATA.style.height)
				]);
			});

			oBXComponentParamsManager.BuildComponentParams(params.data, oBXComponentParamsManager.params);

			BX.addCustomEvent(oBXComponentParamsManager, 'onComponentParamsBeforeRefresh', BX.proxy(this.DisableSaveButton, this));
			BX.addCustomEvent(oBXComponentParamsManager, 'onComponentParamsBuilt', BX.proxy(this.EnableSaveButton, this));
		},

		EnableSaveButton: function()
		{
			BX('bx-comp-params-save-button').disabled = null;
		},
		DisableSaveButton: function()
		{
			BX('bx-comp-params-save-button').disabled = 'disabled';
		}
	};

	window.publicComponentDialogManager = new CompDialogManager(<?=CUtil::PhpToJSObject(array(
		'name' => $componentName,
		'template' => $curTemplate,
		'siteTemplate' => $templateId,
		'currentValues' => $arValues,
		'data' => $data
	))?>);

})();
</script>
<div id="bx-comp-params-wrap" class="bxcompprop-wrap-public"></div>
<?CComponentParamsManager::DisplayFileDialogsScripts();?>
<?endif; /*($strWarning === "") */?>

<?$obJSPopup->StartButtons();?>
	<input type="button" id="bx-comp-params-save-button" value="<?= GetMessage("comp_prop_save")?>" onclick="<?=$obJSPopup->jsPopup?>.PostParameters('<?= PageParams().'&amp;action=save'?>');" title="<?= GetMessage("comp_prop_save_title")?>" name="save" class="adm-btn-save" />
	<input type="button" value="<?= GetMessage("comp_prop_cancel")?>" onclick="<?=$obJSPopup->jsPopup?>.CloseDialog()" title="<?= GetMessage("comp_prop_cancel_title")?>" />
<?$obJSPopup->EndButtons();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");?>