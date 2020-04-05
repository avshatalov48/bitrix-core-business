<?
define("STOP_STATISTICS", true);
define("PUBLIC_AJAX_MODE", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!$USER->IsAdmin() || !check_bitrix_sessid())
{
	echo GetMessage('CLUWIZ_ERROR_ACCESS_DENIED');
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
	die();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/wizard.php");

$lang = $_REQUEST['lang'];
if(!preg_match('/^[a-z0-9_]{2}$/i', $lang))
	$lang = 'en';

$wizard =  new CWizard("bitrix:cluster.slave_start");
$wizard->IncludeWizardLang("scripts/drop.php", $lang);

CModule::IncludeModule('cluster');

$node_id = $_REQUEST["node_id"];
if($node_id <= 1)
	$nodeDB = false;
else
	$nodeDB = CDatabase::GetDBNodeConnection($node_id, true, false);

if(!is_object($nodeDB))
{
	echo GetMessage('CLUWIZ_CONNECTION_ERROR');
}
else
{
	$arTablesToDelete = array();
	$rsTables = $nodeDB->Query("show tables", false, '', array("fixed_connection"=>true));
	while($arTable = $rsTables->Fetch())
		$arTablesToDelete[] = $arTable["Tables_in_".$nodeDB->DBName];

	if(empty($arTablesToDelete))
	{
		echo GetMessage("CLUWIZ_ALL_DONE");
		echo '<script>EnableButton();</script>';
	}
	else
	{
		$table_name = array_pop($arTablesToDelete);
		$nodeDB->Query("drop table ".$table_name, false, '', array("fixed_connection"=>true));
		echo GetMessage('CLUWIZ_TABLE_DROPPED', array("#table_name#" => $table_name));
		echo '<br />';
		echo GetMessage('CLUWIZ_TABLE_PROGRESS', array("#tables#" => count($arTablesToDelete)));
		echo "<script>DropTables()</script>";
	}
}

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
?>