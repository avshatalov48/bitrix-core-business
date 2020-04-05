<?
global $DB;
$db_type = strtolower($DB->type);

CModule::AddAutoloadClasses(
	"perfmon",
	array(
		"perfmon" => "install/index.php",
		"CPerfomanceKeeper" => "classes/general/keeper.php",
		"CAllPerfomanceHit" => "classes/general/hit.php",
		"CPerfomanceHit" => "classes/general/hit.php",
		"CPerfomanceComponent" => "classes/general/component.php",
		"CAllPerfomanceSQL" => "classes/general/sql.php",
		"CPerfomanceSQL" => "classes/".$db_type."/sql.php",
		"CAllPerfomanceTable" => "classes/general/table.php",
		"CPerfomanceTable" => "classes/".$db_type."/table.php",
		"CPerfomanceTableList" => "classes/".$db_type."/table.php",
		"CAllPerfomanceError" => "classes/general/error.php",
		"CPerfomanceError" => "classes/general/error.php",
		"CPerfomanceMeasure" => "classes/general/measure.php",
		"CPerfAccel" => "classes/general/measure.php",
		"CPerfCluster" =>  "classes/general/cluster.php",
		"CPerfomanceSchema" => "classes/general/schema.php",
		"CPerfomanceIndexSuggest" => "classes/general/index_suggest.php",
		"CPerfQuery" =>  "classes/general/query.php",
		"CPerfQueryStat" => "classes/general/query_stat.php",
		"CPerfomanceIndexComplete" => "classes/general/index_complete.php",
		"CPerfomanceHistory" => "classes/general/history.php",
		"CPerfomanceCache" => "classes/general/cache.php",
		"CSqlFormat" => "classes/general/sql_format.php",
		"Bitrix\\Perfmon\\Sql\\BaseObject" => "lib/sql/base_object.php",
		"Bitrix\\Perfmon\\Sql\\Table" => "lib/sql/table.php",
	)
);
?>