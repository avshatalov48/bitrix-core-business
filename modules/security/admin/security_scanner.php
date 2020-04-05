<?
define("BX_SESSION_ID_CHANGE", false);
define("ADMIN_MODULE_NAME", "security");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule('security');
IncludeModuleLangFile(__FILE__);

/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 */
if(!$USER->IsAdmin())
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if($_SERVER['REQUEST_METHOD'] == "POST" && check_bitrix_sessid() && $_POST["action"].$_POST["save"].$_POST["apply"] != "")
{
	$result = "error";

	if(isset($_POST["action"]) && $_POST["action"] == "save")
	{
		if(isset($_POST["results"]) && is_array($_POST["results"]))
		{
			CUtil::JSPostUnescape();
			$resultsForSave = $_POST["results"];
		}
		else
		{
			$resultsForSave = array();
		}
		if(CSecuritySiteChecker::addResults($resultsForSave))
		{
			$result = "ok";
		}
	}
	elseif(isset($_POST["action"]) && $_POST["action"] == "check")
	{
		$isFirstStart = isset($_POST["first_start"]) && $_POST["first_start"] == "Y";
		$isCheckRequirementsNeeded = !isset($_REQUEST["check_requirements"]) || $_REQUEST["check_requirements"] != "N";
		$neededTestPackages = "";
		$result = CSecuritySiteChecker::runTestPackage($neededTestPackages, $isFirstStart, $isCheckRequirementsNeeded);
	}
	else
	{
			$result = "Action not found!";
	}

	$APPLICATION->RestartBuffer();
	header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	die(CUtil::PhpToJsObject($result));
}

CUtil::InitJSCore();
$APPLICATION->AddHeadScript('/bitrix/js/security/admin/page/scanner.js');

CSecuritySiteChecker::clearTemporaryData();

$aTabs = array(
	array(
		"DIV" => "main",
		"TAB" => GetMessage("SEC_SCANNER_MAIN_TAB"),
		"TITLE"=>GetMessage("SEC_SCANNER_TITLE"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, false, true);

$lastTestingInfo = CSecuritySiteChecker::getLastTestingInfo();
if(isset($lastTestingInfo["results"]))
{
	$lastResults = $lastTestingInfo["results"];
}
else
{
	$lastResults = array();
}

if(!empty($lastResults))
{
	$criticalResultsCount = CSecuritySiteChecker::calculateCriticalResults($lastResults);
}
else
{
	$criticalResultsCount = 0;
}

if(isset($lastTestingInfo["test_date"]))
{
	$lastDate = $lastTestingInfo["test_date"];
}
else
{
	$lastDate = "";
}

$maxExecutionTime = ini_get("max_execution_time");
$startupError = null;
if ($maxExecutionTime > 0 && $maxExecutionTime < 30)
{
	$startupError = array(
		"MESSAGE" => GetMessage("SEC_SCANNER_EXECUTION_TIME_TITLE"),
		"TYPE" => "ERROR",
		"DETAILS" => GetMessage("SEC_SCANNER_EXECUTION_TIME_DESCRIPTION", array('#MIN#' => 30)),
		"HTML"=> false
	);
}

$APPLICATION->SetTitle(GetMessage("SEC_SCANNER_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($startupError):
	CAdminMessage::ShowMessage($startupError);
else:
?>
	<div id="error_container" class="adm-security-error-container" style="display:none;">
		<?
		CAdminMessage::ShowMessage(array(
			"MESSAGE" => GetMessage("SEC_SCANNER_CRITICAL_ERRORS_TITLE"),
			"TYPE" => "ERROR",
			"DETAILS" => "",
			"HTML"=>true
		));
		?>
	</div>
	<form method="POST" action="security_scanner.php?lang=<?=LANG?><?=$_GET["return_url"]? "&amp;return_url=".urlencode($_GET["return_url"]): ""?>" name="settings_form">
	<?$tabControl->Begin();?>
	<?$tabControl->BeginNextTab();?>
	<div class="adm-security-wrap">
		<div id="start_container" class="adm-security-first-step">
			<div id="first_start" class="adm-security-text-block" <?=(!CSecuritySiteChecker::isNewTestNeeded())? "style=\"display:none;\"" : ""?>>
			<?=GetMessage("SEC_SCANNER_CRITICAL_FIRST_START")?>
			</div>
			<span id="start_button" class="adm-btn adm-btn-green"><?=GetMessage("SEC_SCANNER_START_BUTTON")?></span>
		</div>
		<div id="status_bar" class="adm-security-status-bar" style="display:none;">
			<div id="progress_bar" style="width: 500px;" class="adm-progress-bar-outer">
				<div id="progress_bar_inner" style="width: 0px;" class="adm-progress-bar-inner"></div>
				<div id="progress_text" style="width: 500px;" class="adm-progress-bar-inner-text">0%</div>
			</div>
			<div id="current_test"></div>
			<span id="stop_button" class="adm-btn stop-button"><?=GetMessage("SEC_SCANNER_STOP_BUTTON")?></span>
		</div>
		<div id="results_info" class="adm-security-results-info adm-security-title" <?=(empty($lastResults) && empty($lastDate))? "style=\"display:none;\"" : ""?>>
			<div id="problems_count" style="width: 500px; float: left;"><?=!empty($lastResults)? (GetMessage("SEC_SCANNER_PROBLEMS_COUNT").count($lastResults).GetMessage("SEC_SCANNER_CRITICAL_PROBLEMS_COUNT").$criticalResultsCount): (!empty($lastTestingInfo)? GetMessage("SEC_SCANNER_NO_PROBLEMS") : "")?></div>
			<div id="last_activity" style="width: 100%; text-align: right;"><?=($lastDate != "")? GetMessage("SEC_SCANNER_TEST_DATE", array("#DATE#" => $lastDate)): ""?></div>
			<div style="clear:both;"></div>
		</div>
		<div id="results" class="adm-security-third-step" <?=(empty($lastResults))? "style=\"display:none;\"" : ""?>></div>
	</div>
	<?$tabControl->End();?>
	</form>

	<script id="scanner_messages" type="application/json"><?=\Bitrix\Main\Web\Json::encode(IncludeModuleLangFile(__FILE__, false, true))?></script>
	<script id="scanner_results" type="application/json"><?=\Bitrix\Main\Web\Json::encode($lastResults)?></script>
<?endif;?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>