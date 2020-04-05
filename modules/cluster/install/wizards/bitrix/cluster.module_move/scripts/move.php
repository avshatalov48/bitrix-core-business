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
$wizard->IncludeWizardLang("scripts/move.php", $lang);

CModule::IncludeModule('cluster');

$STEP = intval($_REQUEST['STEP']);

$from_node_id = intval($_REQUEST["from_node_id"]);
if($from_node_id < 2)
	$nodeDB1 = $GLOBALS["DB"];
else
	$nodeDB1 = CDatabase::GetDBNodeConnection($from_node_id, true, false);

$to_node_id = intval($_REQUEST["to_node_id"]);
if($to_node_id < 2)
	$nodeDB2 = $GLOBALS["DB"];
else
	$nodeDB2 = CDatabase::GetDBNodeConnection($to_node_id, true, false);

$arTables = false;
foreach(GetModuleEvents("cluster", "OnGetTableList", true) as $arEvent)
{
	if($_REQUEST["module"] === $arEvent["TO_MODULE_ID"])
	{
		$arTables = ExecuteModuleEventEx($arEvent);
		break;
	}
}

if(!is_object($nodeDB1) || !is_object($nodeDB2))
{
	echo GetMessage('CLUWIZ_CONNECTION_ERROR');
}
elseif($STEP < 2 && !is_array($arTables))
{
	echo GetMessage('CLUWIZ_NOMODULE_ERROR');
}
elseif($STEP < 2)
{
	COption::SetOptionString($_REQUEST["module"], "dbnode_status", 'move');

	$DB->Query("DELETE FROM b_cluster_table WHERE MODULE_ID = '".$DB->ForSQL($_REQUEST["module"])."'", false, '', array("fixed_connection"=>true));
	foreach($arTables["TABLES"] as $table_name => $key_column)
	{

		$rsIndexes = $nodeDB1->Query("SHOW INDEX FROM `".$DB->ForSql($table_name)."`", true, '', array("fixed_connection"=>true));
		if($rsIndexes)
		{
			$arIndexes = array();
			while($ar = $rsIndexes->Fetch())
				if($ar["Non_unique"] == "0")
					$arIndexes[$ar["Key_name"]][$ar["Seq_in_index"]-1] = $ar["Column_name"];

			foreach($arIndexes as $IndexName => $arIndexColumns)
				if(count($arIndexColumns) != 1)
					unset($arIndexes[$IndexName]);

			if(count($arIndexes) > 0)
			{
				foreach($arIndexes as $IndexName => $arIndexColumns)
				{
					foreach($arIndexColumns as $SeqInIndex => $ColumnName)
						$key_column = $ColumnName;
					break;
				}
			}
			else
			{
				$key_column = false;
			}
		}
		else
		{
			$key_column = false;
		}


		$DB->Add("b_cluster_table", array(
			"MODULE_ID" => $_REQUEST["module"],
			"TABLE_NAME" => $table_name,
			"KEY_COLUMN" => $key_column,
			"FROM_NODE_ID" => $from_node_id,
			"TO_NODE_ID" => $to_node_id,
			"LAST_ID" => false,
		));
	}
	echo GetMessage("CLUWIZ_INIT");
	echo '<script>MoveTables(2)</script>';
}
else
{
	$strError = "";
	$end_time = time()+5;
	do
	{
		$rsTables = $DB->Query("SELECT * FROM b_cluster_table WHERE MODULE_ID = '".$DB->ForSQL($_REQUEST["module"])."' ORDER BY ID", false, '', array("fixed_connection"=>true));
		$arTable = $rsTables->Fetch();
		if($arTable)
		{
			if(strlen($arTable["LAST_ID"]) <= 0)
				$strError = CreateNodeTable($nodeDB1, $nodeDB2, $arTable["TABLE_NAME"]);

			if($strError)
			{
				echo $strError;
				break;
			}

			$arTable["COLUMNS"] = GetTableColumns($nodeDB1, $arTable["TABLE_NAME"]);

			$i = intval($arTable["REC_COUNT"]);
			$di = 0;
			$last_id = '';
			$strInsert = "";
			if(strlen($arTable["KEY_COLUMN"]) > 0)
			{
				$strSelect = "
					SELECT *
					FROM ".$arTable["TABLE_NAME"]."
					".(strlen($arTable["LAST_ID"]) > 0? "WHERE ".$arTable["KEY_COLUMN"]." > '".$arTable["LAST_ID"]."'": "")."
					ORDER BY ".$arTable["KEY_COLUMN"]."
					LIMIT 1000
				";
			}
			else
			{
				$strSelect = "
					SELECT *
					FROM ".$arTable["TABLE_NAME"]."
					LIMIT ".(strlen($arTable["LAST_ID"]) > 0? $arTable["LAST_ID"].", ": "")."1000
				";
			}
			$rsSource = $nodeDB1->Query($strSelect, false, '', array("fixed_connection"=>true));
			while($arSource = $rsSource->Fetch())
			{
				$i++;
				$di++;

				if(!$strInsert)
					$strInsert = "insert into ".$arTable["TABLE_NAME"]." values";
				else
					$strInsert .= ",";

				foreach($arSource as $key => $value)
				{
					if(!isset($value) || is_null($value))
						$arSource[$key] = 'NULL';
					elseif($arTable["COLUMNS"][$key] == 0)
						$arSource[$key] = $value;
					elseif($arTable["COLUMNS"][$key] == 1)
					{
						if(empty($value) && $value != '0')
							$arSource[$key] = '\'\'';
						else
							$arSource[$key] = '0x' . bin2hex($value);
					}
					elseif($arTable["COLUMNS"][$key] == 2)
					{
						$arSource[$key] = "'".$DB->ForSql($value)."'";
					}
				}

				$strInsert .= "\n(".implode(", ", $arSource).")";

				if($arTable["KEY_COLUMN"])
					$last_id = $arSource[$arTable["KEY_COLUMN"]];
				else
					$last_id = $i;

				if(strlen($strInsert) > 102400)
				{
					$nodeDB2->Query($strInsert, false, '', array("fixed_connection"=>true));
					$strInsert = "";
					$DB->Query("
						UPDATE b_cluster_table
						SET LAST_ID = ".$last_id."
						,REC_COUNT = ".$i."
						WHERE ID = '".$arTable["ID"]."'
					", false, '', array("fixed_connection"=>true));
				}
				//sleep(1);
				if(time() > $end_time)
					break;
			}

			if(strlen($strInsert))
			{
				$nodeDB2->Query($strInsert, false, '', array("fixed_connection"=>true));
			}

			if($arSource)
			{
				$DB->Query("
					UPDATE b_cluster_table
					SET LAST_ID = ".(strlen($arTable["KEY_COLUMN"]) > 0?
							$arSource[$arTable["KEY_COLUMN"]]:
							$i)."
					,REC_COUNT = ".$i."
					WHERE ID = '".$arTable["ID"]."'
				", false, '', array("fixed_connection"=>true));
			}
			elseif(strlen($last_id))
			{
				$DB->Query("
					UPDATE b_cluster_table
					SET LAST_ID = ".$last_id."
					,REC_COUNT = ".$i."
					WHERE ID = '".$arTable["ID"]."'
				", false, '', array("fixed_connection"=>true));
			}
			else
			{
				$DB->Query("
					DELETE FROM b_cluster_table
					WHERE ID = '".$arTable["ID"]."'
				", false, '', array("fixed_connection"=>true));
			}
		}
		else
		{
			if($to_node_id > 1)
				COption::SetOptionString($_REQUEST["module"], "dbnode_id", $to_node_id);
			else
				COption::SetOptionString($_REQUEST["module"], "dbnode_id", "N");
			COption::SetOptionString($_REQUEST["module"], "dbnode_status", 'ok');
			CClusterDBNode::SetOnline($to_node_id);
			$ob = new CClusterDBNode;
			if($from_node_id > 1)
				$ob->Update($from_node_id, array("STATUS"=>"READY"));
		}
	} while (is_array($arTable) && time() < $end_time);

	if(is_array($arTable))
	{
		echo GetMessage('CLUWIZ_TABLE_PROGRESS', array(
			"#table_name#" => $arTable["TABLE_NAME"],
			"#records#" => $i,
		));
		echo "<script>MoveTables(2)</script>";
	}
	else
	{
		if($_REQUEST["status"] == "READY")
			echo GetMessage("CLUWIZ_ALL_DONE1");
		else
			echo GetMessage("CLUWIZ_ALL_DONE2");
		echo '<script>EnableButton();</script>';
	}
}


function CreateNodeTable($nodeDB1, $nodeDB2, $TableName)
{
	$rs = $nodeDB1->Query("show create table `".$nodeDB1->ForSQL($TableName)."`", false, '', array("fixed_connection"=>true));
	$ar = $rs->Fetch();
	if(!$ar)
	{
		return GetMessage('CLUWIZ_QUERY_ERROR');
	}
	else
	{
		$rs = $nodeDB2->Query($ar["Create Table"], false, '', array("fixed_connection"=>true));
		if(!$rs)
			return GetMessage('CLUWIZ_QUERY_ERROR');
	}
	return "";
}

function GetTableColumns($nodeDB, $TableName)
{
	$arResult = array();

	$sql = "SHOW COLUMNS FROM `".$TableName."`";
	$res = $nodeDB->Query($sql, false, '', array("fixed_connection"=>true));
	while($row = $res->Fetch())
	{
		if(preg_match("/^(\w*int|year|float|double|decimal)/", $row["Type"]))
			$arResult[$row["Field"]] = 0;
		elseif(preg_match("/^(\w*binary)/", $row["Type"]))
			$arResult[$row["Field"]] = 1;
		else
			$arResult[$row["Field"]] = 2;
	}

	return $arResult;
}

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
?>