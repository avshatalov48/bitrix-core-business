<?
define("STOP_STATISTICS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
{
	echo GetMessage('WSL_IMPORT_ERROR_ACCESS_DENIED');
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
	die();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/wizard.php");

$wizard =  new CWizard("bitrix:sale.locations");
$wizard->IncludeWizardLang("scripts/import.php");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/location_import.php");

define('ZIP_WRITE_TO_LOG', 0);
define('DLZIPFILE', 'zip_ussr.csv');

$arImportParams = array(
	'STEP' => intval($_REQUEST['STEP']),
	'CSVFILE' => $_REQUEST['CSVFILE'],
	'LOADZIP' => $_REQUEST['LOADZIP'],
	'SYNC' => $_REQUEST['SYNC'],
	'STEP_LENGTH' => $_REQUEST['STEP_LENGTH'],
	'DLZIPFILE' => DLZIPFILE
);

$arImportResult = saleLocationImport($arImportParams);

if($arImportResult['ERROR'] <> '' )
{
	echo $arImportResult['ERROR'];

	if($arImportResult['STEP'] !== false)
		echo '<script>Import('.$arImportResult['STEP'].');</script>';
}
elseif (isset($arImportResult['COMPLETE']) && $arImportResult['COMPLETE'] === true)
{
	echo GetMessage('WSL_IMPORT_ALL_DONE');
	echo '<script>EnableButton();</script>';
}
elseif( $arImportResult['STEP'] !== false)
{
	if($arImportResult['MESSAGE'] <> '')
		echo $arImportResult['MESSAGE'];

	if(intval($arImportResult['AMOUNT']) > 0 && intval($arImportResult['POS']) > 0)
		echo "<script>Import(".$arImportResult['STEP'].", {AMOUNT:".CUtil::JSEscape($arImportResult['AMOUNT']).",POS:".CUtil::JSEscape($arImportResult['POS'])."})</script>";
	else
		echo "<script>Import(".$arImportResult['STEP'].")</script>";

	if(isset($arImportResult['PB_REMOVE']) && $arImportResult['PB_REMOVE'] == true)
		echo '<script>jsPB.Remove(true);</script>';
}

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");

function writeToLog($cur_op)
{
	if (defined('ZIP_WRITE_TO_LOG') && ZIP_WRITE_TO_LOG === 1)
	{
		global $start_time;

		list($usec, $sec) = explode(" ", microtime());
		$cur_time = ((float)$usec + (float)$sec);

		$fp = fopen('log.txt', 'a');
		fwrite($fp, $cur_time.": ");
		fwrite($fp, $cur_op."\r\n");
		fclose($fp);
	}
}
?>