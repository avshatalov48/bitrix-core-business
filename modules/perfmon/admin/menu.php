<?php
IncludeModuleLangFile(__FILE__);
/** @var CMain $APPLICATION */
/** @var CDatabase $DB */

if (CMain::GetGroupRight('perfmon') != 'D')
{
	require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/prolog.php';

	$connection = \Bitrix\Main\Application::getConnection();

	$aMenu = [
		'parent_menu' => 'global_menu_settings',
		'section' => 'perfmon',
		'sort' => 1850,
		'text' => GetMessage('PERFMON_MNU_SECT'),
		'title' => GetMessage('PERFMON_MNU_SECT_TITLE'),
		'icon' => 'perfmon_menu_icon',
		'page_icon' => 'perfmon_page_icon',
		'items_id' => 'menu_perfmon',
		'items' => [
			[
				'text' => GetMessage('PERFMON_MNU_PANEL'),
				'url' => 'perfmon_panel.php?lang=' . LANGUAGE_ID,
				'more_url' => ['perfmon_panel.php'],
				'title' => GetMessage('PERFMON_MNU_PANEL_ALT'),
			],
		],
	];

	$aMenu['items'][] = [
		'text' => GetMessage('PERFMON_MNU_PAGES'),
		'url' => 'perfmon_hit_grouped.php?lang=' . LANGUAGE_ID,
		'more_url' => ['perfmon_hit_grouped.php'],
		'title' => GetMessage('PERFMON_MNU_PAGES_ALT'),
	];

	$aMenu['items'][] = [
		'text' => GetMessage('PERFMON_MNU_HIT_LIST'),
		'url' => 'perfmon_hit_list.php?lang=' . LANGUAGE_ID,
		'more_url' => ['perfmon_hit_list.php'],
		'title' => GetMessage('PERFMON_MNU_HIT_LIST_ALT'),
	];

	if (
		COption::GetOptionString('perfmon', 'sql_log') === 'Y'
		|| COption::GetOptionString('perfmon', 'cache_log') === 'Y'
	)
	{
		$aMenu['items'][] = [
			'text' => GetMessage('PERFMON_MNU_COMP_LIST'),
			'url' => 'perfmon_comp_list.php?lang=' . LANGUAGE_ID,
			'more_url' => ['perfmon_comp_list.php'],
			'title' => GetMessage('PERFMON_MNU_COMP_LIST_ALT'),
		];
	}

	if (
		COption::GetOptionString('perfmon', 'sql_log') === 'Y'
		|| COption::GetOptionString('perfmon', 'cache_log') === 'Y'
	)
	{
		$aMenu['items'][] = [
			'text' => GetMessage('PERFMON_MNU_SQL_LIST2'),
			'url' => 'perfmon_sql_list.php?lang=' . LANGUAGE_ID,
			'more_url' => ['perfmon_sql_list.php'],
			'title' => GetMessage('PERFMON_MNU_SQL_LIST_ALT'),
		];
	}

	if (COption::GetOptionString('perfmon', 'cache_log') === 'Y')
	{
		$aMenu['items'][] = [
			'text' => GetMessage('PERFMON_MNU_CACHE_LIST'),
			'url' => 'perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&group=none',
			'more_url' => ['perfmon_cache_list.php'],
			'title' => GetMessage('PERFMON_MNU_CACHE_LIST_ALT'),
		];
	}

	$aMenu['items'][] = [
		'text' => GetMessage('PERFMON_MNU_TABLES'),
		'url' => 'perfmon_tables.php?lang=' . LANGUAGE_ID,
		'more_url' => [
			'perfmon_tables.php',
			'perfmon_table.php',
			'perfmon_row_edit.php',
		],
		'title' => GetMessage('PERFMON_MNU_TABLES_ALT'),
	];
	$connections = [];
	$defaultConnection = \Bitrix\Main\Application::getConnection();
	$configParams = \Bitrix\Main\Config\Configuration::getValue('connections');
	if (is_array($configParams))
	{
		foreach ($configParams as $connectionName => $connectionParams)
		{
			$connections[] = [
				'text' => $connectionName,
				'url' => 'perfmon_tables.php?lang=' . LANGUAGE_ID . '&connection=' . urlencode($connectionName),
				'more_url' => [
					'perfmon_tables.php?connection=' . urlencode($connectionName),
					'perfmon_table.php?connection=' . urlencode($connectionName),
					'perfmon_row_edit.php?connection=' . urlencode($connectionName),
				],
			];
		}
	}
	if (count($connections) > 1)
	{
		//unset($aMenu['items'][count($aMenu['items']) - 1]['url']);
		$aMenu['items'][count($aMenu['items']) - 1]['items_id'] = 'menu_perfmon_table_list';
		$aMenu['items'][count($aMenu['items']) - 1]['items'] = $connections;
	}

	if ($connection->getType() === 'mysql')
	{
		$aMenu['items'][] = [
			'text' => GetMessage('PERFMON_MNU_INDEXES'),
			'items_id' => 'menu_perfmon_index_list',
			'items' => [
				[
					'text' => GetMessage('PERFMON_MNU_INDEX_SUGGEST'),
					'url' => 'perfmon_index_list.php?lang=' . LANGUAGE_ID,
					'more_url' => ['perfmon_index_list.php', 'perfmon_index_detail.php'],
					'title' => GetMessage('PERFMON_MNU_INDEX_SUGGEST_ALT'),
				],
				[
					'text' => GetMessage('PERFMON_MNU_INDEX_COMPLETE'),
					'url' => 'perfmon_index_complete.php?lang=' . LANGUAGE_ID,
					'title' => GetMessage('PERFMON_MNU_INDEX_COMPLETE_ALT'),
				],
			],
		];
	}

	$aMenu['items'][] = [
		'text' => GetMessage('PERFMON_MNU_PHP'),
		'url' => 'perfmon_php.php?lang=' . LANGUAGE_ID,
		'more_url' => ['perfmon_php.php'],
		'title' => GetMessage('PERFMON_MNU_PHP_ALT'),
	];

	if ($connection->getType() === 'mysql')
	{
		$aMenu['items'][] = [
			'text' => GetMessage('PERFMON_MNU_DB_SERVER'),
			'url' => 'perfmon_db_server.php?lang=' . LANGUAGE_ID,
			'more_url' => ['perfmon_db_server.php'],
			'title' => GetMessage('PERFMON_MNU_DB_SERVER_ALT'),
		];
	}

	if (COption::GetOptionString('perfmon', 'warning_log') === 'Y')
	{
		$rs = $DB->Query('SELECT count(*) C from b_perf_error');
		$ar = $rs->Fetch();
		$c = intval($ar['C']);
		if ($c > 0)
		{
			$text = GetMessage('PERFMON_MNU_ERROR_LIST') . ' (' . $c . ')';
		}
		else
		{
			$text = GetMessage('PERFMON_MNU_ERROR_LIST');
		}
		$aMenu['items'][] = [
			'text' => $text,
			'url' => 'perfmon_error_list.php?lang=' . LANGUAGE_ID,
			'more_url' => ['perfmon_error_list.php'],
			'title' => GetMessage('PERFMON_MNU_ERROR_LIST_ALT'),
		];
	}

	$aMenu['items'][] = [
		'text' => GetMessage('PERFMON_MNU_HISTORY'),
		'url' => 'perfmon_history.php?lang=' . LANGUAGE_ID,
		'more_url' => ['perfmon_history.php'],
		'title' => GetMessage('PERFMON_MNU_HISTORY_ALT'),
	];

	return $aMenu;
}
return false;
