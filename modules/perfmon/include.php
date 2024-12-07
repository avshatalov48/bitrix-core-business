<?php

CModule::AddAutoloadClasses(
	'perfmon',
	[
		'perfmon' => 'install/index.php',
		'CPerfomanceKeeper' => 'classes/general/keeper.php',
		'CPerfomanceHit' => 'classes/general/hit.php',
		'CPerfomanceComponent' => 'classes/general/component.php',
		'CPerfomanceSQL' => 'classes/general/sql.php',
		'CPerfomanceTable' => 'classes/general/table.php',
		'CPerfomanceTableList' => 'classes/general/table.php',
		'CPerfomanceError' => 'classes/general/error.php',
		'CPerfomanceMeasure' => 'classes/general/measure.php',
		'CPerfAccel' => 'classes/general/measure.php',
		'CPerfCluster' => 'classes/general/cluster.php',
		'CPerfomanceSchema' => 'classes/general/schema.php',
		'CPerfomanceIndexSuggest' => 'classes/general/index_suggest.php',
		'CPerfQuery' => 'classes/general/query.php',
		'CPerfQueryStat' => 'classes/general/query_stat.php',
		'CPerfomanceIndexComplete' => 'classes/general/index_complete.php',
		'CPerfomanceHistory' => 'classes/general/history.php',
		'CPerfomanceCache' => 'classes/general/cache.php',
		'CSqlFormat' => 'classes/general/sql_format.php',
		'Bitrix\\Perfmon\\Sql\\BaseObject' => 'lib/sql/base_object.php',
		'Bitrix\\Perfmon\\Sql\\Table' => 'lib/sql/table.php',
	]
);
