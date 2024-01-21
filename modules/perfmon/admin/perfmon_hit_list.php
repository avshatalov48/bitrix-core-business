<?php
use Bitrix\Main\Loader;

define('ADMIN_MODULE_NAME', 'perfmon');
define('PERFMON_STOP', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
/** @var CMain $APPLICATION */
/** @var CDatabase $DB */
/** @var CUser $USER */
Loader::includeModule('perfmon');
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/prolog.php';

IncludeModuleLangFile(__FILE__);

$RIGHT = CMain::GetGroupRight('perfmon');
if ($RIGHT == 'D')
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

class CPerfmonHitList extends CAdminListPage
{
	public function getSelectedFields()
	{
		$arSelectedFields = parent::getSelectedFields();
		$arSelectedFields[] = 'ID';
		$arSelectedFields[] = 'SQL_LOG';
		$arSelectedFields[] = 'SERVER_NAME';
		$arSelectedFields[] = 'SERVER_PORT';
		return $arSelectedFields;
	}

	public function getDataSource($arOrder, $arFilter, $arSelect)
	{
		return CPerfomanceHit::GetList(
			$arOrder,
			$arFilter,
			false,
			['nPageSize' => CAdminResult::GetNavSize($this->sTableID)],
			$arSelect
		);
	}

	public function getFooter()
	{
		return [
			[
				'title' => GetMessage('MAIN_ADMIN_LIST_SELECTED'),
				'value' => $this->data->SelectedRowsCount(),
			],
		];
	}
}

class CPerfmonHitListColumnFullDate extends CAdminListColumn
{
	public function getRowView($arRes)
	{
		return $arRes['FULL_DATE_HIT'];
	}
}

class CPerfmonListColumnRequestUri extends CAdminListColumn
{
	public $max_display_url = 0;

	public function __construct($id, $info, $max_display_url)
	{
		parent::__construct($id, $info);
		$this->max_display_url = $max_display_url;
	}

	public function getRowView($arRes)
	{
		$url = str_replace(
			['show_sql_stat_immediate=Y', 'show_sql_stat=Y', 'show_page_exec_time=Y', '&&'],
			['', '', '', '&'],
			$arRes['REQUEST_URI']
		);
		if (mb_strpos($url, '?') === false)
		{
			$url .= '?';
		}
		if (mb_strpos($url, '=') !== false)
		{
			$url .= '&';
		}
		$url .= 'show_sql_stat=Y&show_page_exec_time=Y&show_sql_stat_immediate=Y';

		switch ($arRes['SERVER_PORT'])
		{
		case '443':
			$url = 'https://' . $arRes['SERVER_NAME'] . $url;
			break;
		case '80':
			$url = 'http://' . $arRes['SERVER_NAME'] . $url;
			break;
		default:
			$url = 'http://' . $arRes['SERVER_NAME'] . ':' . $arRes['SERVER_PORT'] . $url;
		}

		return '<a href="' . htmlspecialcharsbx($url) . '" title="' . htmlspecialcharsbx($url) . '">&gt;&gt;</a>&nbsp;<a href="perfmon_sql_list.php?lang=' . LANGUAGE_ID . '&amp;set_filter=Y&amp;find_hit_id=' . urlencode($arRes['ID']) . '" title="' . urlencode($arRes['REQUEST_URI']) . '">' . (mb_strlen($arRes['REQUEST_URI']) > $this->max_display_url ? mb_substr($arRes['REQUEST_URI'], 0, $this->max_display_url) . '...' : $arRes['REQUEST_URI']) . '</a> ';
	}
}

class CPerfmonListColumnTemplate extends CAdminListColumnNumber
{
	public $template = '';

	public function __construct($id, $info, $precision, $template)
	{
		parent::__construct($id, $info, $precision);
		$this->template = $template;
	}

	public function getRowView($arRes)
	{
		if ($arRes[$this->id] > 0)
		{
			$this->arRes = $arRes;
			$this->arRes[$this->id] = parent::getRowView($arRes);
			return preg_replace_callback('/(#)([A-Za-z0-9_]+)(#)/', [$this, 'replace'], $this->template);
		}
		else
		{
			return false;
		}
	}

	public function replace($match)
	{
		return $this->arRes[$match[2]];
	}
}

$page = new CPerfmonHitList(GetMessage('PERFMON_HIT_TITLE'), 'tbl_perfmon_hit_list', ['PAGE_TIME' => 'desc'], GetMessage('PERFMON_HIT_PAGE'));

$page->addColumn(new CAdminListColumn('ID', [
	'content' => GetMessage('PERFMON_HIT_ID'),
	'align' => 'right',
	'sort' => 'ID',
	'filter' => 'find_id',
	'find_type' => 'id',
	'filter_key' => '=ID',
]));

$page->addColumn(new CPerfmonHitListColumnFullDate('DATE_HIT', [
	'content' => GetMessage('PERFMON_HIT_DATE_HIT'),
	'align' => 'right',
	'sort' => 'DATE_HIT',
]));

$page->addColumn(new CAdminListColumnList(
	'IS_ADMIN',
	[
		'content' => GetMessage('PERFMON_HIT_IS_ADMIN'),
		'sort' => 'IS_ADMIN',
		'filter' => 'find_is_admin',
		'filter_key' => '=IS_ADMIN',
	],
	[
		'Y' => GetMessage('MAIN_YES'),
		'N' => GetMessage('MAIN_NO'),
	]
));

$methods = [];
$rsMethods = CPerfomanceHit::GetList(
	['REQUEST_METHOD' => 'ASC'],
	[],
	true,
	false,
	['REQUEST_METHOD']
);
while ($arMethod = $rsMethods->Fetch())
{
	$methods[$arMethod['REQUEST_METHOD']] = $arMethod['REQUEST_METHOD'];
}

$page->addColumn(new CAdminListColumnList('REQUEST_METHOD', [
	'content' => GetMessage('PERFMON_HIT_REQUEST_METHOD'),
	'sort' => 'REQUEST_METHOD',
	'filter' => 'find_request_method',
	'filter_key' => '=REQUEST_METHOD',
], $methods));

$page->addColumn(new CAdminListColumn('SERVER_NAME', [
	'content' => GetMessage('PERFMON_HIT_SERVER_NAME'),
	'sort' => 'SERVER_NAME',
]));

$page->addColumn(new CAdminListColumn('SERVER_PORT', [
	'content' => GetMessage('PERFMON_HIT_SERVER_PORT'),
	'sort' => 'SERVER_PORT',
]));

$page->addColumn(new CAdminListColumn('SCRIPT_NAME', [
	'content' => GetMessage('PERFMON_HIT_SCRIPT_NAME'),
	'sort' => 'SCRIPT_NAME',
	'filter' => 'find_script_name',
	'find_type' => 'script_name',
	'filter_key' => '=SCRIPT_NAME',
]));

$page->addColumn(new CPerfmonListColumnRequestUri('REQUEST_URI', [
	'content' => GetMessage('PERFMON_HIT_REQUEST_URI2'),
	'sort' => 'REQUEST_URI',
	'default' => true,
], COption::GetOptionInt('perfmon', 'max_display_url')));

$page->addColumn(new CAdminListColumnNumber('PAGE_TIME', [
	'content' => GetMessage('PERFMON_HIT_PAGE_TIME'),
	'sort' => 'PAGE_TIME',
	'default' => true,
], 4));

$page->addColumn(new CPerfmonListColumnTemplate('COMPONENTS', [
	'content' => GetMessage('PERFMON_HIT_COMPONENTS'),
	'sort' => 'COMPONENTS',
	'default' => true,
], 0, '<a href="perfmon_comp_list.php?lang=' . LANGUAGE_ID . '&amp;set_filter=Y&amp;find_hit_id=#ID#">#COMPONENTS#</a>'));

$page->addColumn(new CAdminListColumnNumber('COMPONENTS_TIME', [
	'content' => GetMessage('PERFMON_HIT_COMPONENTS_TIME'),
	'sort' => 'COMPONENTS_TIME',
	'default' => true,
], 4));

$page->addColumn(new CAdminListColumnNumber('INCLUDED_FILES', [
	'content' => GetMessage('PERFMON_HIT_INCLUDED_FILES'),
	'sort' => 'INCLUDED_FILES',
], 0));

$page->addColumn(new CAdminListColumnNumber('MEMORY_PEAK_USAGE', [
	'content' => GetMessage('PERFMON_HIT_MEMORY_PEAK_USAGE'),
	'sort' => 'MEMORY_PEAK_USAGE',
], 0));

$page->addColumn(new CAdminListColumnNumber('CACHE_SIZE', [
	'content' => GetMessage('PERFMON_HIT_CACHE_SIZE'),
	'sort' => 'CACHE_SIZE',
], 0));

$page->addColumn(new CPerfmonListColumnTemplate('CACHE_COUNT', [
	'content' => GetMessage('PERFMON_HIT_CACHE_COUNT'),
	'sort' => 'CACHE_COUNT',
], 0, '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;set_filter=Y&amp;find_hit_id=#ID#">#CACHE_COUNT#</a>'));

$page->addColumn(new CPerfmonListColumnTemplate('CACHE_COUNT_R', [
	'content' => GetMessage('PERFMON_HIT_CACHE_COUNT_R'),
	'sort' => 'CACHE_COUNT_R',
], 0, '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;set_filter=Y&amp;find_hit_id=#ID#&amp;find_op_mode=R">#CACHE_COUNT_R#</a>'));

$page->addColumn(new CPerfmonListColumnTemplate('CACHE_COUNT_W', [
	'content' => GetMessage('PERFMON_HIT_CACHE_COUNT_W'),
	'sort' => 'CACHE_COUNT_W',
], 0, '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;set_filter=Y&amp;find_hit_id=#ID#&amp;find_op_mode=W">#CACHE_COUNT_W#</a>'));

$page->addColumn(new CPerfmonListColumnTemplate('CACHE_COUNT_C', [
	'content' => GetMessage('PERFMON_HIT_CACHE_COUNT_C'),
	'sort' => 'CACHE_COUNT_C',
], 0, '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;set_filter=Y&amp;find_hit_id=#ID#&amp;find_op_mode=C">#CACHE_COUNT_C#</a>'));

$page->addColumn(new CPerfmonListColumnTemplate('QUERIES', [
	'content' => GetMessage('PERFMON_HIT_QUERIES'),
	'sort' => 'QUERIES',
	'default' => true,
], 0, '<a href="perfmon_sql_list.php?lang=' . LANGUAGE_ID . '&amp;set_filter=Y&amp;find_hit_id=#ID#">#QUERIES#</a>'));

$page->addColumn(new CAdminListColumnNumber('QUERIES_TIME', [
	'content' => GetMessage('PERFMON_HIT_QUERIES_TIME'),
	'sort' => 'QUERIES_TIME',
	'default' => true,
], 4));

$page->addColumn(new CAdminListColumnNumber('PROLOG_TIME', [
	'content' => GetMessage('PERFMON_HIT_PROLOG_TIME'),
	'sort' => 'PROLOG_TIME',
], 4));

$page->addColumn(new CAdminListColumnNumber('AGENTS_TIME', [
	'content' => GetMessage('PERFMON_HIT_AGENTS_TIME'),
	'sort' => 'AGENTS_TIME',
], 4));

$page->addColumn(new CAdminListColumnNumber('WORK_AREA_TIME', [
	'content' => GetMessage('PERFMON_HIT_WORK_AREA_TIME'),
	'sort' => 'WORK_AREA_TIME',
], 4));

$page->addColumn(new CAdminListColumnNumber('EPILOG_TIME', [
	'content' => GetMessage('PERFMON_HIT_EPILOG_TIME'),
	'sort' => 'EPILOG_TIME',
], 4));

$page->addColumn(new CAdminListColumnNumber('EVENTS_TIME', [
	'content' => GetMessage('PERFMON_HIT_EVENTS_TIME'),
	'sort' => 'EVENTS_TIME',
], 4));

$page->show();

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
