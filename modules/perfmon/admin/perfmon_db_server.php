<?
use Bitrix\Main\Loader;

define("ADMIN_MODULE_NAME", "perfmon");
define("PERFMON_STOP", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
Loader::includeModule('perfmon');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/prolog.php");

IncludeModuleLangFile(__FILE__);

$RIGHT = $APPLICATION->GetGroupRight("perfmon");
if ($RIGHT == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$bCluster = CModule::IncludeModule('cluster');

$node_id = 0;
$statDB = $DB;
$queryOptions = array("fixed_connection" => true);

$arClusterNodes = array();
if ($bCluster)
{
	$rsNodes = CClusterDBNode::GetList();
	while ($node = $rsNodes->fetch())
	{
		$arClusterNodes[$node["ID"]] = htmlspecialcharsex($node["NAME"]);
	}

	if (!empty($arClusterNodes))
	{
		$node_id = intval($_REQUEST["node_id"]);
		if ($node_id > 1)
		{
			$statDB = $DB->GetDBNodeConnection($node_id);
			$queryOptions = array();
		}
		else
		{
			$node_id = 1;
		}
	}
}

$message = null;
$data = array();

if ($statDB->type == "MYSQL")
{
	$stat = array();
	$rs = $statDB->Query("SHOW GLOBAL STATUS", true, "", $queryOptions);
	if (!$rs)
		$rs = $statDB->Query("SHOW STATUS", true, "", $queryOptions);
	while ($ar = $rs->Fetch())
		$stat[$ar["Variable_name"]] = $ar["Value"];

	$vars = array();
	$rs = $statDB->Query("SHOW GLOBAL VARIABLES", false, "", $queryOptions);
	while ($ar = $rs->Fetch())
		$vars[$ar["Variable_name"]] = $ar["Value"];

	if (isset($vars["have_innodb"]))
	{
		$have_innodb = ($vars["have_innodb"] == "YES");
	}
	else
	{
		$rs = $statDB->Query("SHOW ENGINES", true, "", $queryOptions);
		if ($rs)
		{
			while ($ar = $rs->Fetch())
			{
				if ($ar['Engine'] === 'InnoDB')
					$have_innodb = true;
			}
		}
	}

	$data = array(
		array(
			"TITLE" => GetMessage("PERFMON_STATUS_TITLE"),
			"HEADERS" => array(
				array(
					"id" => "KPI_NAME",
					"content" => GetMessage("PERFMON_KPI_NAME"),
					"align" => "left\" nowrap=\"nowrap",
					"default" => true,
				),
				array(
					"id" => "KPI_VALUE",
					"content" => GetMessage("PERFMON_KPI_VALUE"),
					"align" => "right\" nowrap=\"nowrap",
					"default" => true,
				),
				array(
					"id" => "KPI_RECOMMENDATION",
					"content" => GetMessage("PERFMON_KPI_RECOMENDATION"),
					"default" => true,
				),
			),
			"ITEMS" => array(),
		)
	);

	$arVersion = array();
	if (preg_match("/^(\\d+)\\.(\\d+)/", $vars["version"], $arVersion))
	{
		if ($arVersion[1] < 5)
			$rec = GetMessage("PERFMON_KPI_REC_VERSION_OLD");
		elseif ($arVersion[1] == 5)
			$rec = GetMessage("PERFMON_KPI_REC_VERSION_OK");
		else
			$rec = GetMessage("PERFMON_KPI_REC_VERSION_NEW");
		$data[0]["ITEMS"][] = array(
			"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_VERSION"),
			"IS_OK" => $arVersion[1] == 5,
			"KPI_VALUE" => $vars["version"],
			"KPI_RECOMMENDATION" => $rec,
		);
	}
	$uptime = array(
		"#SECONDS#" => $stat['Uptime'] % 60,
		"#MINUTES#" => intval(($stat['Uptime'] % 3600) / 60),
		"#HOURS#" => intval(($stat['Uptime'] % 86400) / (3600)),
		"#DAYS#" => intval($stat['Uptime'] / (86400)),
	);

	if ($stat['Uptime'] >= 86400)
		$rec = GetMessage("PERFMON_KPI_REC_UPTIME_OK");
	else
		$rec = GetMessage("PERFMON_KPI_REC_UPTIME_TOO_SHORT");
	$data[0]["ITEMS"][] = array(
		"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_UPTIME"),
		"IS_OK" => $stat['Uptime'] >= 86400,
		"KPI_VALUE" => GetMessage("PERFMON_KPI_VAL_UPTIME", $uptime),
		"KPI_RECOMMENDATION" => $rec,
	);

	if ($stat["Questions"] < 1)
	{
		$data[0]["ITEMS"][] = array(
			"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_QUERIES"),
			"IS_OK" => false,
			"KPI_VALUE" => $stat["Questions"],
			"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_NO_QUERIES"),
		);
	}
	else
	{
		// Server-wide memory
		$calc['server_buffers'] = $vars['key_buffer_size'];
		$server_buffers = 'key_buffer_size';
		if ($vars['tmp_table_size'] > $vars['max_heap_table_size'])
		{
			$calc['server_buffers'] += $vars['max_heap_table_size'];
			$server_buffers .= ' + max_heap_table_size';
		}
		else
		{
			$calc['server_buffers'] += $vars['tmp_table_size'];
			$server_buffers .= ' + tmp_table_size';
		}

		if (isset($vars['innodb_buffer_pool_size']))
		{
			$calc['server_buffers'] += $vars['innodb_buffer_pool_size'];
			$server_buffers .= ' + innodb_buffer_pool_size';
		}

		if (isset($vars['innodb_additional_mem_pool_size']))
		{
			$calc['server_buffers'] += $vars['innodb_additional_mem_pool_size'];
			$server_buffers .= ' + innodb_additional_mem_pool_size';
		}

		if (isset($vars['innodb_log_buffer_size']))
		{
			$calc['server_buffers'] += $vars['innodb_log_buffer_size'];
			$server_buffers .= ' + innodb_log_buffer_size';
		}

		if (isset($vars['query_cache_size']))
		{
			$calc['server_buffers'] += $vars['query_cache_size'];
			$server_buffers .= ' + query_cache_size';
		}
		$data[0]["ITEMS"][] = array(
			"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_GBUFFERS"),
			"KPI_VALUE" => CFile::FormatSize($calc['server_buffers']),
			"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_GBUFFERS", array("#VALUE#" => "<span class=\"perfmon_code\">".$server_buffers."</span>")),
		);

		// Per thread
		$calc['per_thread_buffers'] = $vars['read_buffer_size'] + $vars['read_rnd_buffer_size'] + $vars['sort_buffer_size'] + $vars['thread_stack'] + $vars['join_buffer_size'];
		$per_thread_buffers = 'read_buffer_size + read_rnd_buffer_size + sort_buffer_size + thread_stack + join_buffer_size';
		$data[0]["ITEMS"][] = array(
			"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_CBUFFERS"),
			"KPI_VALUE" => CFile::FormatSize($calc['per_thread_buffers']),
			"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_CBUFFERS", array("#VALUE#" => "<span class=\"perfmon_code\">".$per_thread_buffers."</span>")),
		);

		$max_connections = 'max_connections';
		$data[0]["ITEMS"][] = array(
			"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_CONNECTIONS"),
			"KPI_VALUE" => $vars['max_connections'],
			"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_CONNECTIONS", array("#VALUE#" => "<span class=\"perfmon_code\">".$max_connections."</span>")),
		);

		// Global memory
		$calc['total_possible_used_memory'] = $calc['server_buffers'] + ($calc['per_thread_buffers'] * $vars['max_connections']);
		$data[0]["ITEMS"][] = array(
			"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_MEMORY"),
			"KPI_VALUE" => CFile::FormatSize($calc['total_possible_used_memory']),
			"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_MEMORY"),
		);

		// Key buffers
		$total_myisam_indexes = 0;
		if ($arVersion[1] >= 5)
		{
			$rs = $statDB->Query("SELECT IFNULL(SUM(INDEX_LENGTH),0) IND_SIZE FROM information_schema.TABLES WHERE TABLE_SCHEMA NOT IN ('information_schema') AND ENGINE = 'MyISAM'", false, "", $queryOptions);
			$ar = $rs->Fetch();
			if ($ar["IND_SIZE"] > 0)
			{
				$total_myisam_indexes = $ar["IND_SIZE"];
				$calc['total_myisam_indexes'] = CFile::FormatSize($ar["IND_SIZE"]);
				$rec = GetMessage("PERFMON_KPI_REC_MYISAM_IND");
			}
			else
			{
				$calc['total_myisam_indexes'] = GetMessage("PERFMON_KPI_NO");
				$rec = GetMessage("PERFMON_KPI_REC_MYISAM_NOIND");
			}
		}
		else
		{
			$calc['total_myisam_indexes'] = '<span class="errortext">N/A</span>';
			$rec = GetMessage("PERFMON_KPI_REC_MYISAM4_IND");
		}
		$data[0]["ITEMS"][] = array(
			"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_MYISAM_IND"),
			"KPI_VALUE" => $calc['total_myisam_indexes'],
			"KPI_RECOMMENDATION" => $rec,
		);

		if ($total_myisam_indexes > 0)
		{
			if ($stat['Key_read_requests'] > 0)
				$calc['pct_keys_from_disk'] = round($stat['Key_reads'] / $stat['Key_read_requests'] * 100, 2);
			else
				$calc['pct_keys_from_disk'] = 0;
			$data[0]["ITEMS"][] = array(
				"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_KEY_MISS"),
				"IS_OK" => $calc['pct_keys_from_disk'] <= 5,
				"KPI_VALUE" => $calc['pct_keys_from_disk']."%",
				"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_KEY_MISS", array(
					"#PARAM_VALUE#" => CFile::FormatSize($vars["key_buffer_size"]),
					"#PARAM_NAME#" => "<span class=\"perfmon_code\">key_buffer_size</span>",
				)),
			);
		}

		// Query cache
		if ($vars['query_cache_size'] < 1)
			$rec = GetMessage("PERFMON_KPI_REC_QCACHE_ZERO_SIZE", array(
				"#PARAM_NAME#" => "<span class=\"perfmon_code\">query_cache_size</span>",
				"#PARAM_VALUE_LOW#" => "8M",
				"#PARAM_VALUE_HIGH#" => "128M",
			));
		elseif ($vars['query_cache_size'] > 128 * 1024 * 1024)
			$rec = GetMessage("PERFMON_KPI_REC_QCACHE_TOOLARGE_SIZE", array(
				"#PARAM_NAME#" => "<span class=\"perfmon_code\">query_cache_size</span>",
				"#PARAM_VALUE_HIGH#" => "128M",
			));
		else
			$rec = GetMessage("PERFMON_KPI_REC_QCACHE_OK_SIZE", array(
				"#PARAM_NAME#" => "<span class=\"perfmon_code\">query_cache_size</span>",
			));
		$data[0]["ITEMS"][] = array(
			"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_QCACHE_SIZE"),
			"IS_OK" => $vars['query_cache_size'] > 0 && $vars['query_cache_size'] <= 128 * 1024 * 1024,
			"KPI_VALUE" => CFile::FormatSize($vars['query_cache_size']),
			"KPI_RECOMMENDATION" => $rec,
		);

		if ($vars['query_cache_size'] > 0)
		{
			if ($stat['Com_select'] == 0)
			{
				$data[0]["ITEMS"][] = array(
					"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_QCACHE"),
					"IS_OK" => false,
					"KPI_VALUE" => "&nbsp;",
					"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_QCACHE_NO"),
				);
			}
			elseif ($stat['Com_select'] > $stat['Qcache_not_cached'])
			{
				$calc['query_cache_efficiency'] = round($stat['Qcache_hits'] / (($stat['Com_select'] - $stat['Qcache_not_cached']) + $stat['Qcache_hits']) * 100, 2);

				$value = $calc['query_cache_efficiency']."%";
				$rec = GetMessage("PERFMON_KPI_REC_QCACHE", array(
					"#PARAM_NAME#" => "<span class=\"perfmon_code\">query_cache_limit</span>",
					"#PARAM_VALUE#" => CFile::FormatSize($vars['query_cache_limit']),
					"#GOOD_VALUE#" => "20%",
				));

				$data[0]["ITEMS"][] = array(
					"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_QCACHE"),
					"IS_OK" => $stat['Com_select'] > 0 && $calc['query_cache_efficiency'] >= 20,
					"KPI_VALUE" => $value,
					"KPI_RECOMMENDATION" => $rec,
				);
			}

			if ($stat['Com_select'] > 0)
			{
				$data[0]["ITEMS"][] = array(
					"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_QCACHE_PRUNES"),
					"KPI_VALUE" => perfmon_NumberFormat($stat['Qcache_lowmem_prunes'], 0),
					"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_QCACHE_PRUNES", array(
						"#STAT_NAME#" => "<span class=\"perfmon_code\">Qcache_lowmem_prunes</span>",
						"#PARAM_VALUE#" => CFile::FormatSize($vars['query_cache_size']),
						"#PARAM_NAME#" => "<span class=\"perfmon_code\">query_cache_size</span>",
						"#PARAM_VALUE_HIGH#" => "128M",
					)),
				);
			}
		}
		// Sorting
		$calc['total_sorts'] = $stat['Sort_scan'] + $stat['Sort_range'];
		$total_sorts = 'Sort_scan + Sort_range';
		$data[0]["ITEMS"][] = array(
			"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_SORTS"),
			"KPI_VALUE" => perfmon_NumberFormat($calc['total_sorts'], 0),
			"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_SORTS", array(
				"#STAT_NAME#" => "<span class=\"perfmon_code\">".$total_sorts."</span>",
			)),
		);

		if ($calc['total_sorts'] > 0)
		{
			$calc['pct_temp_sort_table'] = round(($stat['Sort_merge_passes'] / $calc['total_sorts']) * 100, 2);
			$data[0]["ITEMS"][] = array(
				"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_SORTS_DISK"),
				"IS_OK" => $calc['pct_temp_sort_table'] <= 10,
				"KPI_VALUE" => $calc['pct_temp_sort_table']."%",
				"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_SORTS_DISK", array(
					"#STAT_NAME#" => "<span class=\"perfmon_code\">Sort_merge_passes / (Sort_scan + Sort_range)</span>",
					"#GOOD_VALUE#" => "10",
					"#PARAM1_VALUE#" => CFile::FormatSize($vars['sort_buffer_size']),
					"#PARAM1_NAME#" => "<span class=\"perfmon_code\">sort_buffer_size</span>",
					"#PARAM2_VALUE#" => CFile::FormatSize($vars['read_rnd_buffer_size']),
					"#PARAM2_NAME#" => "<span class=\"perfmon_code\">read_rnd_buffer_size</span>",
				)),
			);
		}

		// Joins
		$calc['joins_without_indexes'] = $stat['Select_range_check'] + $stat['Select_full_join'];
		$calc['joins_without_indexes_per_day'] = intval($calc['joins_without_indexes'] / ($stat['Uptime'] / 86400));
		if ($calc['joins_without_indexes_per_day'] > 250)
		{
			$data[0]["ITEMS"][] = array(
				"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_JOINS"),
				"KPI_VALUE" => perfmon_NumberFormat($calc['joins_without_indexes'], 0),
				"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_JOINS", array(
					"#STAT_NAME#" => "<span class=\"perfmon_code\">Select_range_check + Select_full_join</span>",
					"#PARAM_VALUE#" => CFile::FormatSize($vars['join_buffer_size']),
					"#PARAM_NAME#" => "<span class=\"perfmon_code\">join_buffer_size</span>",
				)),
			);
		}

		// Temporary tables
		if ($stat['Created_tmp_tables'] > 0)
		{
			$calc['tmp_table_size'] = ($vars['tmp_table_size'] > $vars['max_heap_table_size'])? $vars['max_heap_table_size']: $vars['tmp_table_size'];
			if ($stat['Created_tmp_disk_tables'] > 0)
				$calc['pct_temp_disk'] = round(($stat['Created_tmp_disk_tables'] / ($stat['Created_tmp_tables'] + $stat['Created_tmp_disk_tables'])) * 100, 2);
			else
				$calc['pct_temp_disk'] = 0;
			$pct_temp_disk = 30;

			if ($calc['pct_temp_disk'] > $pct_temp_disk && $calc['max_tmp_table_size'] < 256 * 1024 * 1024)
			{
				$is_ok = false;
				$value = $calc['pct_temp_disk']."%";
				$rec = GetMessage("PERFMON_KPI_REC_TMP_DISK_1", array(
					"#STAT_NAME#" => "<span class=\"perfmon_code\">Created_tmp_disk_tables / (Created_tmp_tables + Created_tmp_disk_tables)</span>",
					"#STAT_VALUE#" => $pct_temp_disk."%",
					"#PARAM1_NAME#" => "<span class=\"perfmon_code\">tmp_table_size</span>",
					"#PARAM1_VALUE#" => CFile::FormatSize($vars['tmp_table_size']),
					"#PARAM2_NAME#" => "<span class=\"perfmon_code\">max_heap_table_size</span>",
					"#PARAM2_VALUE#" => CFile::FormatSize($vars['max_heap_table_size']),
				));
			}
			elseif ($calc['pct_temp_disk'] > $pct_temp_disk && $calc['max_tmp_table_size'] >= 256)
			{
				$is_ok = false;
				$value = $calc['pct_temp_disk']."%";
				$rec = GetMessage("PERFMON_KPI_REC_TMP_DISK_2", array(
					"#STAT_NAME#" => "<span class=\"perfmon_code\">Created_tmp_disk_tables / (Created_tmp_tables + Created_tmp_disk_tables)</span>",
					"#STAT_VALUE#" => $pct_temp_disk."%",
				));
			}
			else
			{
				$is_ok = true;
				$value = $calc['pct_temp_disk']."%";
				$rec = GetMessage("PERFMON_KPI_REC_TMP_DISK_3", array(
					"#STAT_NAME#" => "<span class=\"perfmon_code\">Created_tmp_disk_tables / (Created_tmp_tables + Created_tmp_disk_tables)</span>",
					"#STAT_VALUE#" => $pct_temp_disk."%",
				));
			}
			$data[0]["ITEMS"][] = array(
				"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_TMP_DISK"),
				"IS_OK" => $is_ok,
				"KPI_VALUE" => $value,
				"KPI_RECOMMENDATION" => $rec,
			);
		}

		// Thread cache
		if ($vars['thread_cache_size'] == 0)
		{
			$is_ok = false;
			$value = $vars['thread_cache_size'];
			$rec = GetMessage("PERFMON_KPI_REC_THREAD_NO_CACHE", array(
				"#PARAM_VALUE#" => 4,
				"#PARAM_NAME#" => "<span class=\"perfmon_code\">thread_cache_size</span>",
			));
		}
		else
		{
			$calc['thread_cache_hit_rate'] = round(100 - (($stat['Threads_created'] / $stat['Connections']) * 100), 2);
			$is_ok = $calc['thread_cache_hit_rate'] > 50;
			$value = $calc['thread_cache_hit_rate']."%";
			$rec = GetMessage("PERFMON_KPI_REC_THREAD_CACHE", array(
				"#STAT_NAME#" => "<span class=\"perfmon_code\">1 - Threads_created / Connections</span>",
				"#GOOD_VALUE#" => "50%",
				"#PARAM_VALUE#" => $vars['thread_cache_size'],
				"#PARAM_NAME#" => "<span class=\"perfmon_code\">thread_cache_size</span>",
			));
		}
		$data[0]["ITEMS"][] = array(
			"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_THREAD_CACHE"),
			"IS_OK" => $is_ok,
			"KPI_VALUE" => $value,
			"KPI_RECOMMENDATION" => $rec,
		);

		// Open files
		if ($vars['open_files_limit'] > 0)
		{
			$calc['pct_files_open'] = round($stat['Open_files'] / $vars['open_files_limit'] * 100, 2);
			$data[0]["ITEMS"][] = array(
				"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_OPEN_FILES"),
				"IS_OK" => $calc['pct_files_open'] <= 85,
				"KPI_VALUE" => $calc['pct_files_open']."%",
				"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_OPEN_FILES", array(
					"#STAT_NAME#" => "<span class=\"perfmon_code\">Open_files / open_files_limit</span>",
					"#GOOD_VALUE#" => "85%",
					"#PARAM_VALUE#" => $vars['open_files_limit'],
					"#PARAM_NAME#" => "<span class=\"perfmon_code\">open_files_limit</span>",
				)),
			);
		}

		// Table locks
		if ($stat['Table_locks_immediate'] > 0)
		{
			if ($stat['Table_locks_waited'] == 0)
				$calc['pct_table_locks_immediate'] = 100;
			else
				$calc['pct_table_locks_immediate'] = round($stat['Table_locks_immediate'] / ($stat['Table_locks_waited'] + $stat['Table_locks_immediate']) * 100, 2);
			$data[0]["ITEMS"][] = array(
				"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_LOCKS"),
				"KPI_VALUE" => (
				$calc['pct_table_locks_immediate'] >= 95?
					$calc['pct_table_locks_immediate']."%":
					'<span class="errortext">'.$calc['pct_table_locks_immediate'].'%</span>'
				),
				"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_LOCKS", array(
					"#STAT_NAME#" => "<span class=\"perfmon_code\">Table_locks_immediate / (Table_locks_waited + Table_locks_immediate)</span>",
					"#GOOD_VALUE#" => "95%",
				)),
			);
		}

		// Performance options
		if ($vars['concurrent_insert'] == "OFF")
		{
			$data[0]["ITEMS"][] = array(
				"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_INSERTS"),
				"KPI_VALUE" => "<span class=\"errortext\">OFF</span>",
				"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_INSERTS", array(
					"#PARAM_NAME#" => "<span class=\"perfmon_code\">concurrent_insert</span>",
					"#REC_VALUE#" => "'ON'",
				)),
			);
		}
		elseif ($vars['concurrent_insert'] == "0")
		{
			$data[0]["ITEMS"][] = array(
				"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_INSERTS"),
				"KPI_VALUE" => "<span class=\"errortext\">0</span>",
				"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_INSERTS", array(
					"#PARAM_NAME#" => "<span class=\"perfmon_code\">concurrent_insert</span>",
					"#REC_VALUE#" => "1",
				)),
			);
		}

		// Aborted connections
		if ($stat['Connections'] > 0)
		{
			$calc['pct_aborted_connections'] = round(($stat['Aborted_connects'] / $stat['Connections']) * 100, 2);
			$data[0]["ITEMS"][] = array(
				"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_CONN_ABORTS"),
				"IS_OK" => $calc['pct_aborted_connections'] <= 5,
				"KPI_VALUE" => $calc['pct_aborted_connections']."%",
				"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_CONN_ABORTS"),
			);
		}

		// InnoDB
		if ($have_innodb)
		{
			if ($stat['Innodb_buffer_pool_reads'] > 0 && $stat['Innodb_buffer_pool_read_requests'] > 0)
			{
				$calc['innodb_buffer_hit_rate'] = round((1 - $stat['Innodb_buffer_pool_reads'] / $stat['Innodb_buffer_pool_read_requests']) * 100, 2);
				$data[0]["ITEMS"][] = array(
					"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_INNODB_BUFFER"),
					"IS_OK" => $calc['innodb_buffer_hit_rate'] > 95,
					"KPI_VALUE" => $calc['innodb_buffer_hit_rate']."%",
					"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_INNODB_BUFFER", array(
						"#STAT_NAME#" => "<span class=\"perfmon_code\">1 - Innodb_buffer_pool_reads / Innodb_buffer_pool_read_requests</span>",
						"#GOOD_VALUE#" => 95,
						"#PARAM_NAME#" => "<span class=\"perfmon_code\">innodb_buffer_pool_size</span>",
						"#PARAM_VALUE#" => CFile::FormatSize($vars['innodb_buffer_pool_size']),
					)),
				);
			}
			$data[0]["ITEMS"][] = array(
				"KPI_NAME" => "innodb_flush_log_at_trx_commit",
				"IS_OK" => $vars['innodb_flush_log_at_trx_commit'] == 2 || $vars['innodb_flush_log_at_trx_commit'] == 0,
				"KPI_VALUE" => $vars['innodb_flush_log_at_trx_commit'] <> ''? $vars['innodb_flush_log_at_trx_commit'] : GetMessage("PERFMON_KPI_EMPTY"),
				"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_INNODB_FLUSH_LOG", array(
					"#GOOD_VALUE#" => 2,
					"#PARAM_NAME#" => "<span class=\"perfmon_code\">innodb_flush_log_at_trx_commit</span>",
				)),
			);
			if ($vars['log_bin'] !== 'OFF')
			{
				$data[0]["ITEMS"][] = array(
					"KPI_NAME" => "sync_binlog",
					"IS_OK" => $vars['sync_binlog'] == 0 || $vars['sync_binlog'] >= 1000,
					"KPI_VALUE" => intval($vars['sync_binlog']),
					"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_SYNC_BINLOG", array(
						"#GOOD_VALUE_1#" => 0,
						"#GOOD_VALUE_2#" => 1000,
						"#PARAM_NAME#" => "<span class=\"perfmon_code\">sync_binlog</span>",
					)),
				);
			}
			$data[0]["ITEMS"][] = array(
				"KPI_NAME" => "innodb_flush_method",
				"IS_OK" => $vars['innodb_flush_method'] == "O_DIRECT",
				"KPI_VALUE" => $vars['innodb_flush_method'] <> ''? $vars['innodb_flush_method'] : GetMessage("PERFMON_KPI_EMPTY"),
				"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_INNODB_FLUSH_METHOD", array(
					"#GOOD_VALUE#" => "O_DIRECT",
					"#PARAM_NAME#" => "<span class=\"perfmon_code\">innodb_flush_method</span>",
				)),
			);
			$data[0]["ITEMS"][] = array(
				"KPI_NAME" => "transaction-isolation",
				"IS_OK" => $vars['tx_isolation'] == "READ-COMMITTED",
				"KPI_VALUE" => $vars['tx_isolation'] <> ''? $vars['tx_isolation'] : GetMessage("PERFMON_KPI_EMPTY"),
				"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_TX_ISOLATION", array(
					"#GOOD_VALUE#" => "READ-COMMITTED",
					"#PARAM_NAME#" => "<span class=\"perfmon_code\">transaction-isolation</span>",
				)),
			);
			$data[0]["ITEMS"][] = array(
				"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_INNODB_LOG_WAITS"),
				"KPI_VALUE" => $stat["Innodb_log_waits"],
				"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_INNODB_LOG_WAITS", array("#VALUE#" => CFile::FormatSize($vars["innodb_log_file_size"]))),
			);
			$data[0]["ITEMS"][] = array(
				"KPI_NAME" => GetMessage("PERFMON_KPI_NAME_BINLOG"),
				"KPI_VALUE" => $stat["Binlog_cache_disk_use"],
				"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_BINLOG", array("#VALUE#" => CFile::FormatSize($vars["binlog_cache_size"]))),
			);
		}
	}
}
elseif ($statDB->type == "ORACLE")
{
	$module_name = "";
	$rs = $statDB->Query("
		select
			event as WAIT_EVENT
			,round(RATIO_TO_REPORT(sum(time_waited)) OVER ()*100,2) AS PCTTOT
			,round(avg(average_wait)*10,2) as AVERAGE_WAIT_MS
		from
			v\$session_event
		where sid in (
			select sid
			from v\$session
			where UPPER(program) like '%'||UPPER('$module_name')||'%'
			and LOGON_TIME > sysdate-1/24
			and SERIAL# not in (1)
			and PROGRAM not like '%QMNC%'
			and UPPER(PROGRAM) not like '%Q0%'
			and UPPER(PROGRAM) not like '%J0%'
		)
		and event not in (
			'AQ Proxy Cleanup Wait',
			'ASM background timer',
			'DIAG idle wait',
			'EMON idle wait',
			'KSV master wait',
			'LNS ASYNC archive log',
			'LNS ASYNC dest activation',
			'LNS ASYNC end of log',
			'LogMiner: client waiting for transaction',
			'LogMiner: slave waiting for activate message',
			'LogMiner: wakeup event for builder',
			'LogMiner: wakeup event for preparer',
			'LogMiner: wakeup event for reader',
			'Null event',
			'PX Deq Credit: need buffer',
			'PX Deq Credit: send blkd',
			'PX Deq: Execute Reply',
			'PX Deq: Execution Msg',
			'PX Deq: Par Recov Execute',
			'PX Deq: Signal ACK',
			'PX Deq: Table Q Normal',
			'PX Deq: Table Q Sample',
			'PX Deque wait',
			'PX Idle Wait',
			'Queue Monitor Shutdown Wait',
			'Queue Monitor Slave Wait',
			'Queue Monitor Wait',
			'Space Manager: slave idle wait',
			'SQL*Net message from client',
			'SQL*Net message to client',
			'SQL*Net more data from client',
			'STREAMS apply coord waiting for slave message',
			'STREAMS apply slave idle wait',
			'STREAMS apply slave waiting for coord message',
			'STREAMS capture process filter callback wait for ruleset',
			'STREAMS fetch slave waiting for txns',
			'STREAMS waiting for subscribers to catch up',
			'Streams AQ: RAC qmn coordinator idle wait',
			'Streams AQ: deallocate messages from Streams Pool',
			'Streams AQ: delete acknowledged messages',
			'Streams AQ: qmn coordinator idle wait',
			'Streams AQ: qmn slave idle wait',
			'Streams AQ: waiting for messages in the queue',
			'Streams AQ: waiting for time management or cleanup tasks',
			'Streams fetch slave: waiting for txns',
			'class slave wait',
			'client message',
			'dispatcher timer',
			'gcs for action',
			'gcs remote message',
			'ges remote message',
			'i/o slave wait',
			'jobq slave wait',
			'knlqdeq',
			'lock manager wait for remote message',
			'master wait',
			'null event',
			'parallel query dequeue',
			'pipe get',
			'pmon timer',
			'queue messages',
			'rdbms ipc message',
			'slave wait',
			'smon timer',
			'virtual circuit status',
			'wait for activate message',
			'wait for unread message on broadcast channel',
			'wakeup event for builder',
			'wakeup event for preparer',
			'wakeup event for reader',
			'wakeup time manager'
		)
	and average_wait > 1/100
	group by event
	order by PCTTOT desc
	", true, "", $queryOptions);
	if (!$rs)
	{
		$message = new CAdminMessage(array("MESSAGE" => GetMessage("PERFMON_KPI_ORA_PERMISSIONS"), "HTML" => true));
	}
	else
	{
		$data[0] = array(
			"TITLE" => GetMessage("PERFMON_WAITS_TITLE"),
			"HEADERS" => array(
				array(
					"id" => "WAIT_EVENT",
					"content" => GetMessage("PERFMON_WAIT_EVENT"),
					"default" => true,
				),
				array(
					"id" => "WAIT_PCT",
					"content" => GetMessage("PERFMON_WAIT_PCT"),
					"align" => "right",
					"default" => true,
				),
				array(
					"id" => "WAIT_AVERAGE_WAIT_MS",
					"content" => GetMessage("PERFMON_WAIT_AVERAGE_WAIT_MS"),
					"align" => "right",
					"default" => true,
				),
				array(
					"id" => "KPI_RECOMMENDATION",
					"content" => GetMessage("PERFMON_KPI_RECOMENDATION"),
					"default" => true,
				),
			),
			"ITEMS" => array(),
		);
		while ($ar = $rs->Fetch())
			$data[0]["ITEMS"][] = array(
				"WAIT_EVENT" => $ar["WAIT_EVENT"],
				"WAIT_PCT" => $ar["PCTTOT"]."%",
				"WAIT_AVERAGE_WAIT_MS" => $ar["AVERAGE_WAIT_MS"],
				"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_ORA_REC_".mb_strtoupper(str_replace(array(" ", ":", "*"), "_", $ar["WAIT_EVENT"]))),
			);
		$param = array();
		$rs = $statDB->Query("SELECT NAME,VALUE from v\$parameter", false, "", $queryOptions);
		while ($ar = $rs->Fetch())
			$param[$ar["NAME"]] = $ar["VALUE"];

		$data[1] = array(
			"TITLE" => GetMessage("PERFMON_PARAMETERS_TITLE"),
			"HEADERS" => array(
				array(
					"id" => "PARAMETER_NAME",
					"content" => GetMessage("PERFMON_PARAMETER_NAME"),
					"default" => true,
				),
				array(
					"id" => "PARAMETER_VALUE",
					"content" => GetMessage("PERFMON_PARAMETER_VALUE"),
					"align" => "right",
					"default" => true,
				),
				array(
					"id" => "REC_PARAMETER_VALUE",
					"content" => GetMessage("PERFMON_REC_PARAMETER_VALUE"),
					"align" => "right",
					"default" => true,
				),
				array(
					"id" => "KPI_RECOMMENDATION",
					"content" => GetMessage("PERFMON_KPI_RECOMENDATION"),
					"default" => true,
				),
			),
			"ITEMS" => array(
				array(
					"PARAMETER_NAME" => "db_block_checksum",
					"PARAMETER_VALUE" => $param["db_block_checksum"],
					"REC_PARAMETER_VALUE" => "FALSE",
					"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_ORA_REC_DB_BLOCK_CHECKSUM"),
				),
				array(
					"PARAMETER_NAME" => "session_cached_cursors",
					"PARAMETER_VALUE" => $param["session_cached_cursors"],
					"REC_PARAMETER_VALUE" => "100",
					"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_ORA_REC_SESSION_CACHED_CURSORS"),
				),
				array(
					"PARAMETER_NAME" => "cursor_sharing",
					"PARAMETER_VALUE" => $param["cursor_sharing"],
					"REC_PARAMETER_VALUE" => "FORCE",
					"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_ORA_REC_CURSOR_SHARING_FORCE"),
				),
				array(
					"PARAMETER_NAME" => "parallel_max_servers",
					"PARAMETER_VALUE" => $param["parallel_max_servers"],
					"REC_PARAMETER_VALUE" => "0",
					"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_ORA_REC_PARALLEL_MAX_SERVERS"),
				),
				array(
					"PARAMETER_NAME" => "commit_write",
					"PARAMETER_VALUE" => $param["commit_write"],
					"REC_PARAMETER_VALUE" => "BATCH,NOWAIT",
					"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_ORA_REC_COMMIT_WRITE"),
				),
				array(
					"PARAMETER_NAME" => "open_cursors",
					"PARAMETER_VALUE" => $param["open_cursors"],
					"REC_PARAMETER_VALUE" => "300",
					"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_ORA_REC_OPEN_CURSORS"),
				),
				array(
					"PARAMETER_NAME" => "optimizer_mode",
					"PARAMETER_VALUE" => $param["optimizer_mode"],
					"REC_PARAMETER_VALUE" => "FIRST_ROWS",
					"KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_ORA_REC_OPTIMIZER_MODE"),
				),
			),
		);
		$rs = $statDB->Query("
			SELECT
				USER USER_NAME,
				".$statDB->DateToCharFunction('min(min_last_analyzed)')." MIN_LAST_ANALYZED,
				".$statDB->DateToCharFunction('max(max_last_analyzed)')." MAX_LAST_ANALYZED,
				case
				when (min(min_last_analyzed) < (sysdate - 7)) or (max(max_last_analyzed) < (sysdate - 7)) then 'OLD'
				else 'NEW'
				end FLAG
			from (
				select min(last_analyzed) min_last_analyzed, max(last_analyzed) max_last_analyzed from dba_indexes
				where owner = user
				union all
				select min(last_analyzed) min_last_analyzed, max(last_analyzed) max_last_analyzed from dba_tables
				where owner = user
			)
		", false, "", $queryOptions);
		$stat = $rs->Fetch();
		$data[2] = array(
			"TITLE" => GetMessage("PERFMON_STATS_TITLE"),
			"HEADERS" => array(
				array(
					"id" => "USER_NAME",
					"content" => GetMessage("PERFMON_USER_NAME"),
					"default" => true,
				),
				array(
					"id" => "MIN_LAST_ANALYZED",
					"content" => GetMessage("PERFMON_MIN_LAST_ANALYZED"),
					"align" => "right",
					"default" => true,
				),
				array(
					"id" => "MAX_LAST_ANALYZED",
					"content" => GetMessage("PERFMON_MAX_LAST_ANALYZED"),
					"align" => "right",
					"default" => true,
				),
				array(
					"id" => "KPI_RECOMMENDATION",
					"content" => GetMessage("PERFMON_KPI_RECOMENDATION"),
					"default" => true,
				),
			),
			"ITEMS" => array(
				array(
					"USER_NAME" => $stat["USER_NAME"],
					"MIN_LAST_ANALYZED" => $stat["MIN_LAST_ANALYZED"],
					"MAX_LAST_ANALYZED" => $stat["MAX_LAST_ANALYZED"],
					"KPI_RECOMMENDATION" => ($stat["FLAG"] == "NEW"?
						GetMessage("PERFMON_KPI_ORA_REC_STATS_NEW", array("#USER_NAME#" => $stat["USER_NAME"])):
						GetMessage("PERFMON_KPI_ORA_REC_STATS_OLD", array("#USER_NAME#" => $stat["USER_NAME"]))
					),
				),
			),
		);
	}
}
elseif ($statDB->type == "MSSQL")
{
}

$sTableID = "tbl_perfmon_db_server";

$APPLICATION->SetTitle(GetMessage("PERFMON_DB_SERVER_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($message)
	echo $message->Show();

if (count($data))
{
	foreach ($data as $i => $arTable)
	{
		$lAdmin = new CAdminList($sTableID.$i);
		$lAdmin->BeginPrologContent();
		if (array_key_exists("TITLE", $arTable))
		{
			echo "<h3>".$arTable["TITLE"]."</h3>\n";
			if ($node_id > 0)
			{
				?>
				<form method="GET" action="<? echo $APPLICATION->GetCurPageParam() ?>"
					enctype="multipart/form-data" name="editform" id="editform"><?
				$arr = array(
					"reference" => array_values($arClusterNodes),
					"reference_id" => array_keys($arClusterNodes),
				);
				echo SelectBoxFromArray("node_id", $arr, $node_id, "", "", true, "editform"), "<br>";
				?></form><?
			}
		}
		$lAdmin->EndPrologContent();
		$lAdmin->AddHeaders($arTable["HEADERS"]);
		$rsData = new CDBResult;
		$rsData->InitFromArray($arTable["ITEMS"]);
		$rsData = new CAdminResult($rsData, $sTableID);
		$j = 0;
		while ($arRes = $rsData->NavNext(true, "f_"))
		{
			$row =& $lAdmin->AddRow($j++, $arRes);
			foreach ($arRes as $key => $value)
			{
				if ($key == "KPI_VALUE" && array_key_exists("IS_OK", $arRes) && !$arRes["IS_OK"])
					$row->AddViewField($key, "<span class=\"errortext\">".$value."</span>");
				else
					$row->AddViewField($key, $value);
			}
		}
		$lAdmin->CheckListMode();
		$lAdmin->DisplayList();
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
