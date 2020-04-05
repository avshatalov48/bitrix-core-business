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

$wizard =  new CWizard("bitrix:cluster.module_move");
$wizard->IncludeWizardLang("scripts/drop.php", $lang);

CModule::IncludeModule('cluster');

$to_node_id = $_REQUEST["to_node_id"];
if($to_node_id < 2)
	$nodeDB = $GLOBALS["DB"];
else
	$nodeDB = CDatabase::GetDBNodeConnection($to_node_id, true, false);

$arTables = false;
foreach(GetModuleEvents("cluster", "OnGetTableList", true) as $arEvent)
{
	if($_REQUEST["module"] === $arEvent["TO_MODULE_ID"])
	{
		$arTables = ExecuteModuleEventEx($arEvent);
		break;
	}
}

if(!is_object($nodeDB))
{
	echo GetMessage('CLUWIZ_CONNECTION_ERROR');
}
elseif(!is_array($arTables))
{
	echo GetMessage('CLUWIZ_NOMODULE_ERROR');
}
else
{
	$arTablesToDelete = array();
	foreach($arTables["TABLES"] as $table_name => $key_column)
	{
		if($nodeDB->TableExists($table_name))
			$arTablesToDelete[] = $table_name;
	}

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
		echo GetMessage('CLUWIZ_TABLE_PROGRESS', array("#tables#" => count($arTablesToDelete)));
		echo "<script>DropTables()</script>";
	}
}

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
?>