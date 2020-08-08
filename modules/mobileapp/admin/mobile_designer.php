<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage mobileapp
 * @copyright 2001-2014 Bitrix
 */
use Bitrix\Main\Web\Json;

/**
 * Bitrix vars
 * @global CAllUser $USER
 * @global CAllMain $APPLICATION
 */

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
CModule::IncludeModule("mobileapp");
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);
$APPLICATION->SetTitle(GetMessage("MOBILEAPP_APP_DESIGNER_TITLE"));


if (!$USER->isAdmin())
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");


$ieVersion = IsIE();
if (IsIE() !== false && IsIE() < 9)
{
	CAdminMessage::ShowMessage(array(
		"TYPE" => "ERROR",
		"MESSAGE"=>GetMessage("MOBILEAPP_WRONG_BROWSER"),
		"DETAILS" => GetMessage("MOBILEAPP_WRONG_BROWSER_DETAIL"),
		"HTML" => true,
	));
	require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php");
	return;
}

CUtil::InitJSCore(Array("mdesigner"));
$action = $_REQUEST["action"];
$templates = $_REQUEST["action"];


function __DSGetInitData()
{
	$map = new \Bitrix\MobileApp\Designer\ConfigMap();
	$params = $map->getParamsByGroups();
	$groups = array_keys($params);
	$tmpLangs = array_change_key_case($map->getLangMessages(), CASE_LOWER);


	$langs = array();
	foreach ($tmpLangs as $k => $v)
	{
		$langs[str_replace("_", "/", $k)] = $v;
	}

	$result = \Bitrix\Mobileapp\Designer\AppTable::getList(
		array(
			"select" => array("CODE", "FOLDER", "DESCRIPTION", "SHORT_NAME", "NAME", "CONFIG.PLATFORM", "CONFIG.PARAMS")
		)
	);

	$fetchedApps = $result->fetchAll();
	$apps = array();
	$count = count($fetchedApps);


	for ($i = 0; $i < $count; $i++)
	{
		$apps[] = array(
			"code" => $fetchedApps[$i]["CODE"],
			"name" => $fetchedApps[$i]["NAME"],
			"folder" => $fetchedApps[$i]["FOLDER"],
			"desc" => $fetchedApps[$i]["DESCRIPTION"],
			"params" => $fetchedApps[$i]["MOBILEAPP_DESIGNER_APP_CONFIG_PARAMS"],
			"platform" => $fetchedApps[$i]["MOBILEAPP_DESIGNER_APP_CONFIG_PLATFORM"]
		);
	}


	$dbres = \CSiteTemplate::GetList();
	$templates = array();
	while ($template = $dbres->Fetch())
	{
		if(array_key_exists("MOBILE",$template) && $template["MOBILE"] == "Y")
		{
			$templates[] = $template;
		}
	}


	$data = array(
		"map" => array(
			"groups" => $groups,
			"groupedParams" => $params,
			"params" => $map->getDescriptionConfig(),
			"lang" => $tmpLangs
		),
		"apps" => $apps,
		"templates" => $templates
	);

	return $data;
}



if ($action <> '')
{
	$status = false;
	$data = array();

	if(!check_bitrix_sessid())
	{
		$data["error"] = "sessid check is failed";
	}
	else
	{
		switch ($action)
		{
			case "getInitData":

				$data = __DSGetInitData();
				$status = true;

				break;
			case "save":
				$app = $_REQUEST["code"];
				$platform = $_REQUEST["platform"];
				$params = $_REQUEST["config"];
				$status = \Bitrix\MobileApp\Designer\Manager::updateConfig($app, $platform, $params);
				break;
			case "removePlatform":
				$code = $_REQUEST["code"];
				$platform = $_REQUEST["platform"];
				$status = Bitrix\MobileApp\Designer\Manager::removeConfig($code, $platform);
				break;
			case "createPlatform":
				$status = \Bitrix\MobileApp\Designer\Manager::addConfig($_REQUEST["code"], $_REQUEST["platform"], array());
				break;
			case "createApp":
				$code = $_REQUEST["code"];
				$appTemplateName = $_REQUEST["appTemplateName"];
				$createTemplate = ($_REQUEST["createNew"] === "Y");
				$bindTemplate = ($_REQUEST["bindTemplate"] === "Y");
				$useOffline = ($_REQUEST["useOffline"] === "Y") ;
				$fields = array(
					"FOLDER" => $_REQUEST["folder"],
					"NAME" => $_REQUEST["name"],
				);

				if (!\Bitrix\Main\Application::isUtfMode())
				{
					$fields = \Bitrix\Main\Text\Encoding::convertEncodingArray($fields, "UTF-8", SITE_CHARSET);
					$code = \Bitrix\Main\Text\Encoding::convertEncoding($code, "UTF-8", SITE_CHARSET);
					$templateName = \Bitrix\Main\Text\Encoding::convertEncoding($templateName, "UTF-8", SITE_CHARSET);
				}
				$initConfig = array();
				if($useOffline)
				{
					$initConfig =  array(
							"offline/launch_mode"=>"offline_only",
							"offline/file_list"=>array(
								"index.html"=>"/".$fields["FOLDER"]."/offline/index.html",
								"script.js"=>"/".$fields["FOLDER"]."/offline/script.js",
								"style.css"=>"/".$fields["FOLDER"]."/offline/style.css",
							),
							"offline/main"=>"index.html"
					);

					$data["config"] = $initConfig;

				}


				$result = \Bitrix\MobileApp\Designer\Manager::createApp($code, $fields, $initConfig); //creating global config inside

				if($result == \Bitrix\MobileApp\Designer\Manager::IS_ALREADY_EXISTS)
				{
					$APPLICATION->RestartBuffer();
					echo CUtil::PhpToJSObject(
						array(
						"status"=>"is_already_exists"
					));
					die();
				}



				$status = ($result == \Bitrix\MobileApp\Designer\Manager::SUCCESS);

				if ($status)
				{
					\Bitrix\MobileApp\Designer\Manager::copyFromTemplate($fields["FOLDER"], $code, $useOffline, $appTemplateName);

					if ($bindTemplate)
					{
						$templateId = $_REQUEST["template_id"];
						\Bitrix\MobileApp\Designer\Manager::bindTemplate($templateId, $fields["FOLDER"], $createTemplate);
					}
				}

				break;
			case "removeApp":
				$code = $_REQUEST["code"];
				$status = Bitrix\MobileApp\Designer\Manager::removeApp($code);
				$data["code"] = $code;
				break;
			case "getFiles":
				$code = $_REQUEST["code"];
				$files = \Bitrix\MobileApp\Designer\Manager::getAppFiles($code);
				$data = array(
					"status" => true,
					"files" => $files
				);
				break;

		}
	}

	$data["status"] = ($status !== false) ? "ok" : "fail";

	$APPLICATION->RestartBuffer();
	echo Json::encode($data);
	die();
}
?>

<div id="designer-wrapper" class="designer-prop-wrapper">
</div>
<?
AddEventHandler("mobileapp", "onDesignerFileUploaded", Array("Bitrix\\MobileApp\\Designer\\Manager", "registerFileInApp"));
AddEventHandler("mobileapp", "onDesignerFileRemoved", Array("Bitrix\\MobileApp\\Designer\\Manager", "unregisterFileInApp"));

$APPLICATION->IncludeComponent
(
	"bitrix:mobileapp.colorpicker",
	"",
	Array(
		"SHOW_BUTTON" => "Y",
		"ID" => 'picker',
		"NAME" => "asd"
	),
	null,
	array("HIDE_ICONS" => "Y")
);


$componentParams = array(
	'INPUT_NAME' => 'FILE_NEW',
	'INPUT_NAME_UNSAVED' => 'FILE_NEW_TMP',
	'MAX_FILE_SIZE' => 0,
	'MODULE_ID' => 'mobileapp',
	'ALLOW_UPLOAD' => "F",
	'CONTROL_ID' => "designer",
	'ALLOW_UPLOAD_EXT'=>"jpg,png,jpeg"
);

$GLOBALS['APPLICATION']->IncludeComponent('bitrix:mobileapp.designer.file.input', 'drag_n_drop', $componentParams, false);
$initData = __DSGetInitData();
$initDataJS = Json::encode($initData);
?>


<script>
	BX.ready(function ()
	{
		if (BX.browser.IsIE() && BX.browser.DetectIeVersion() < 9)
		{
			return false;
		}

		window.designer = new BX.Mobile.Designer({
			containerId: "designer-wrapper",
			platforms:<?=Json::encode(\Bitrix\MobileApp\Designer\ConfigTable::getSupportedPlatforms())?>
		});
		window.designer.init();
	});
</script>
<div>
	<?

CAdminFileDialog::ShowScript(Array
(
	"event" => "openFileDialog",
	"arResultDest" => Array("FUNCTION_NAME" => "designerEditorFileChosen"),
	"arPath" => Array(),
	"select" => "F", // F - file only, D - folder only, DF - files & dirs
	"operation" => 'O',
	"showUploadTab" => false,
	"showAddToMenuTab" => false,
	"fileFilter" => "js,html,htm,png,jpeg,jpg,svg,gif,txt,css",
	"allowAllFiles" => true,
	"SaveConfig" => true
));
?>
</div>

<?
require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php"); ?>


