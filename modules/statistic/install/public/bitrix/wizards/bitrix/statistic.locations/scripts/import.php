<?
define("STOP_STATISTICS", true);
define("PUBLIC_AJAX_MODE", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/wizard.php");

$lang = $_REQUEST['lang'];
if(!preg_match('/^[a-z0-9_]{2}$/i', $lang))
	$lang = 'en';

$wizard =  new CWizard("bitrix:statistic.locations");
$wizard->IncludeWizardLang("scripts/import.php", $lang);

if($APPLICATION->GetGroupRight('statistic') < "W")
{
	echo GetMessage('STATWIZ_IMPORT_ERROR_ACCESS_DENIED');
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
	die();
}

CModule::IncludeModule('statistic');

$STEP = intval($_REQUEST['STEP']);
$import_type = $_REQUEST["import_type"];

$file_position = intval($_REQUEST["file_position"]);
if($file_position)
	$total_reindex = array(
		$_SESSION["STATWIZ_TOTAL_REINDEX"],
		$file_position,
	);
else
	$total_reindex = array(
		0,
		$file_position,
	);

//We have to strongly check all about file names at server side
$ABS_FILE_NAME = false;
if(isset($_REQUEST["file_name"]) && ($_REQUEST["file_name"] <> ''))
{
	$filename = "bitrix/modules/statistic/ip2country/".trim(str_replace("\\", "/", trim($_REQUEST["file_name"])), "/");
	$FILE_NAME = Rel2Abs($_SERVER["DOCUMENT_ROOT"], "/".$filename);
	if((mb_strlen($FILE_NAME) > 1) && ($FILE_NAME === "/".$filename) && ($APPLICATION->GetFileAccessPermission($FILE_NAME) >= "W"))
	{
		$ABS_FILE_NAME = $_SERVER["DOCUMENT_ROOT"].$FILE_NAME;
	}
}

if(!$ABS_FILE_NAME)
{
	echo GetMessage('STATWIZ_IMPORT_ERROR_FILE');
}
else
{
	switch($STEP)
	{
		case 0:
			echo GetMessage('STATWIZ_IMPORT_FILE_LOADING');
			echo "<script>Import(1)</script>";
			break;

		case 1:

			$fp = fopen($ABS_FILE_NAME, "r");
			if($fp)
				$file_type = CCity::GetCSVFormatType($fp);
			else
				$file_type = false;

			if($file_type === "IP-TO-COUNTRY")
				i2c_create_db($total_reindex, $reindex_success, $step_reindex, $_SESSION["STATWIZ_INT_PREV"], 5, $FILE_NAME, "ip-to-country.com");
			elseif($file_type === "MAXMIND-IP-COUNTRY")
				i2c_create_db($total_reindex, $reindex_success, $step_reindex, $_SESSION["STATWIZ_INT_PREV"], 5, $FILE_NAME, "maxmind.com");
			elseif($file_type === "MAXMIND-CITY-LOCATION")
				$reindex_success = "Y";
			elseif($file_type === "MAXMIND-IP-LOCATION")
				$reindex_success = "Y";
			else
				$reindex_success = "Y";

			if($reindex_success === "Y")
			{
				echo '<script>Import(2)</script>';
			}
			else
			{
				$_SESSION["STATWIZ_TOTAL_REINDEX"] = $total_reindex[0];
				echo "<script>Import(1, {file_position:'".intval($total_reindex[1])."',AMOUNT:".intval(filesize($ABS_FILE_NAME)).",POS:".intval($total_reindex[1])."})</script>";
			}

			break;
		case 2:

			$reindex_success = CCity::LoadCSV($FILE_NAME, 10, $file_position);

			if($reindex_success === "Y")
			{
				echo '<script>Import(3)</script>';
			}
			else
			{
				echo "<script>Import(2, {file_position:'".intval($file_position)."',AMOUNT:".intval(filesize($ABS_FILE_NAME)).",POS:".intval($file_position)."})</script>";
			}

			break;
		case 3:
			echo GetMessage("STATWIZ_IMPORT_ALL_DONE")."<br>";
			echo '<script>EnableButton();</script>';
			break;
	}
}

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
?>