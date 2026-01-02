<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\UI\Filter\Options;

define('ADMIN_MODULE_NAME', 'perfmon');
define('PERFMON_STOP', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
/** @var CMain $APPLICATION */
Loader::includeModule('perfmon');
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/prolog.php';

$RIGHT = CMain::GetGroupRight('perfmon');
if (!Bitrix\Main\Engine\CurrentUser::get()->isAdmin() || ($RIGHT === 'D'))
{
	$APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

$bCluster = Loader::includeModule('cluster');

/** @var \Bitrix\Main\HttpRequest $request */
$request = \Bitrix\Main\Context::getCurrent()->getRequest();

if (
	$request->getRequestMethod() === 'GET'
	&& $request->get('ajax_tooltip') === 'y'
	&& $request->get('sql_id') !== null
	&& check_bitrix_sessid()
)
{
	require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_js.php';

	$rsData = CPerfomanceSQL::GetBacktraceList($request->get('sql_id'));
	$arData = $rsData->Fetch();
	if ($arData)
	{
		?>
		<style>
			.left-align {
				text-align: left;
			}
			.right-align {
				text-align: right;
			}
		</style>
		<table class="list"><?php
		?>
		<tr>
		<td class="left-align"><b><?php echo Loc::getMessage('PERFMON_SQL_FILE') ?></b></td>
		<td class="left-align"><b><?php echo Loc::getMessage('PERFMON_SQL_LINE_NUMBER'); ?></b></td>
		<td class="left-align"><b><?php echo Loc::getMessage('PERFMON_SQL_FUNCTION'); ?></b></td>
		</tr><?php
		do
		{
			?>
			<tr>
			<td class="left-align">&nbsp;<?php echo htmlspecialcharsEx($arData['FILE_NAME']) ?></td>
			<td class="right-align">&nbsp;<?php echo htmlspecialcharsEx($arData['LINE_NO']) ?></td>
			<?php
			if ($arData['CLASS_NAME']):?>
				<td class="left-align">
					&nbsp;<?php echo htmlspecialcharsEx($arData['CLASS_NAME'] . '::' . $arData['FUNCTION_NAME']) ?></td>
			<?php else: ?>
				<td class="left-align">&nbsp;<?php echo htmlspecialcharsEx($arData['FUNCTION_NAME']) ?></td>
			<?php endif; ?>
			</tr><?php
		}
		while ($arData = $rsData->Fetch());
		?></table><?php
	}
	else
	{
		?>no backtrace found<?php
	}
	require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin_js.php';
}

Extension::load(['sidepanel']);

$adminListTableID = 'tbl_perfmon_sql_list';
$oSort = new CAdminUiSorting($adminListTableID, 'NN', 'asc');
$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());
$lAdmin = new CAdminUiList($adminListTableID, $oSort);

$arHeaders = [
	[
		'id' => 'ID',
		'content' => Loc::getMessage('PERFMON_SQL_ID'),
		'sort' => 'ID',
		'align' => 'right',
		'default' => true,
	],
	[
		'id' => 'HIT_ID',
		'content' => Loc::getMessage('PERFMON_SQL_HIT_ID'),
		'sort' => 'HIT_ID',
		'align' => 'right',
		'default' => true,
	],
	[
		'id' => 'NN',
		'content' => Loc::getMessage('PERFMON_SQL_NN'),
		'sort' => 'NN',
		'align' => 'right',
		'default' => true,
	],
	[
		'id' => 'QUERY_TIME',
		'content' => Loc::getMessage('PERFMON_SQL_QUERY_TIME'),
		'sort' => 'QUERY_TIME',
		'align' => 'right',
		'default' => true,
	],
	[
		'id' => 'MODULE_NAME',
		'content' => Loc::getMessage('PERFMON_SQL_MODULE_NAME'),
		'sort' => 'MODULE_NAME',
	],
	[
		'id' => 'COMPONENT_NAME',
		'content' => Loc::getMessage('PERFMON_SQL_COMPONENT_NAME'),
		'sort' => 'COMPONENT_NAME',
	],
	[
		'id' => 'QUERY_STAT',
		'content' => Loc::getMessage('PERFMON_SQL_QUERY_STAT'),
		'default' => true,
	],
	[
		'id' => 'SQL_TEXT',
		'content' => Loc::getMessage('PERFMON_SQL_SQL_TEXT'),
		'default' => true,
	],
];

$filterFields = [
	[
		'id' => 'ID',
		'name' => 'ID',
		'field' => '=ID',
	],
	[
		'id' => 'find_hit_id',
		'name' => Loc::getMessage('PERFMON_SQL_HIT_ID'),
		'default' => true,
		'field' => '=HIT_ID',
	],
	[
		'id' => 'find_component_id',
		'name' => Loc::getMessage('PERFMON_SQL_COMPONENT_NAME'),
		'field' => '=COMPONENT_ID',
	],
	[
		'id' => 'find_query_time',
		'name' => Loc::getMessage('PERFMON_SQL_QUERY_TIME'),
		'field' => '>=QUERY_TIME',
	],
	[
		'id' => 'SELECTED_ROWS',
		'name' => Loc::getMessage('PERFMON_SQL_FIELD_SELECTED_ROWS'),
		'field' => '>=SELECTED_ROWS',
	],
	[
		'id' => 'SELECTED_FIELDS',
		'name' => Loc::getMessage('PERFMON_SQL_FIELD_SELECTED_FIELDS'),
		'field' => '>=SELECTED_FIELDS',
	],
	[
		'id' => 'FETCHED_ROWS',
		'name' => Loc::getMessage('PERFMON_SQL_FIELD_FETCHED_ROWS'),
		'field' => '>=FETCHED_ROWS',
	],
	[
		'id' => 'FETCHED_LENGTH',
		'name' => Loc::getMessage('PERFMON_SQL_FIELD_FETCHED_LENGTH'),
		'field' => '>=FETCHED_LENGTH',
	],
	[
		'id' => 'HAS_BIG_FIELDS',
		'name' => Loc::getMessage('PERFMON_SQL_FIELD_HAS_BIG_FIELDS'),
		'field' => '=HAS_BIG_FIELDS',
		'type' => 'list',
		'items' => [
			'Y' => Loc::getMessage('PERFMON_SQL_BIG_FIELDS_YES'),
			'N' => Loc::getMessage('PERFMON_SQL_BIG_FIELDS_NO'),
		],
	],
];

$arClusterNodes = [];
if ($bCluster)
{
	$arHeaders[] = [
		'id' => 'NODE_ID',
		'content' => Loc::getMessage('PERFMON_SQL_NODE_ID'),
	];
	$arClusterNodes[''] = Loc::getMessage('MAIN_ALL');
	$rsNodes = CClusterDBNode::GetList();
	while ($node = $rsNodes->fetch())
	{
		$arClusterNodes[$node['ID']] = htmlspecialcharsEx($node['NAME']);
	}

	$filterFields[] = [
		'id' => 'find_node_id',
		'name' => Loc::getMessage('PERFMON_SQL_NODE_ID'),
		'filterable' => '',
		'type' => 'list',
		'items' => $arClusterNodes,
		'field' => 'NODE_ID',
	];
}

$lAdmin->AddHeaders($arHeaders);

$arSelectedFields = $lAdmin->GetVisibleHeaderColumns();
if (!is_array($arSelectedFields) || (empty($arSelectedFields)))
{
	$arSelectedFields = [
		'ID',
		'HIT_ID',
		'NN',
		'QUERY_TIME',
		'SQL_TEXT',
		'SELECTED_ROWS',
		'SELECTED_FIELDS',
		'FETCHED_ROWS',
		'FETCHED_LENGTH',
		'HAS_BIG_FIELDS',
	];
}

if ($bCluster && !in_array('NODE_ID', $arSelectedFields, true))
{
	$arSelectedFields[] = 'NODE_ID';
}

$statFields = [
	'SELECTED_ROWS' => [
		'warnValue' => 20,
		'dangerValue' => 50,
	],
	'SELECTED_FIELDS' => [
		'warnValue' => 10,
		'dangerValue' => 25,
	],
	'FETCHED_ROWS' => [
		'warnValue' => 20,
		'dangerValue' => 50,
	],
	'FETCHED_LENGTH' => [
		'warnValue' => 100000,
		'dangerValue' => 500000,
		'size' => true,
	],
	'HAS_BIG_FIELDS' => [],
];
if (in_array('QUERY_STAT', $arSelectedFields, true))
{
	$arSelectedFields = array_merge($arSelectedFields, array_keys($statFields));
}
if (!in_array('ID', $arSelectedFields, true))
{
	$arSelectedFields[] = 'ID';
}

$currentFilter = $lAdmin->InitFilter(array_column($filterFields, 'id'));
$filterOption = new \Bitrix\Main\UI\Filter\Options($adminListTableID);

if (!empty($currentFilter) && !$request->get('grid_action'))
{
	$settings = \Bitrix\Main\UI\Filter\Options::fetchSettingsFromQuery($filterFields, $request);

	if ($settings !== null)
	{
		$filterOption->setCurrentFilterPresetId(Options::TMP_FILTER);
		$filterOption->setFilterSettings(Options::TMP_FILTER, $settings, true, false);
		$filterOption->save();
	}
}

$filterData = $filterOption->getFilter($filterFields);
$filter = [];
$lAdmin->AddFilter($filterFields, $filter);

foreach ($filterFields as $field)
{
	if (isset($filter[$field['id']]) && $field['id'] !== 'find_node_id')
	{
		$filter[$field['field']] = $filter[$field['id']];

		unset($filter[$field['id']]);
	}
}

if (isset($filter['find_node_id']))
{
	if ($filter['find_node_id'] > 1)
	{
		$filter['=NODE_ID'] = (int) $filter['find_node_id'];
	}
	else
	{
		$filter['0'] = [
			'LOGIC' => 'OR',
			'0' => [
				'=NODE_ID' => 1,
			],
			'1' => [
				'=NODE_ID' => false,
			],
		];
	}
	unset($filter['find_node_id']);
}

$nav = $lAdmin->getPageNavigation('nav-perfmon-sql-list');
$arNavParams = [
	'nTopCount' => $nav->getLimit(),
	'nOffset' => $nav->getOffset(),
];

$rsData = CPerfomanceSQL::GetList($arSelectedFields, $filter, [$by => $order], false, $arNavParams);
$totalCount = CPerfomanceSQL::GetList(['COUNT'], $filter, ['COUNT' => 'ASC'], false)->Fetch();

if ($totalCount)
{
	$totalCount = (int) $totalCount['COUNT'];
}

$queryIterator = new CAdminUiResult($rsData, $adminListTableID);

$nav->setRecordCount($totalCount);
$lAdmin->setNavigation($nav, GetMessage('MAIN_USER_ADMIN_PAGES'), false);

while ($query = $queryIterator->Fetch())
{
	$explainUrl = 'perfmon_explain.php?lang=' . LANG . '&ID=' . $query['ID'];
	$query['SQL_TEXT'] = CPerfomanceSQL::Format($query['SQL_TEXT']);
	$row = $lAdmin->AddRow($query['ID'], $query, $explainUrl);

	$row->AddViewField('QUERY_TIME', perfmon_NumberFormat($query['QUERY_TIME'], 6));

	$html = str_replace(
		[' ', "\t", "\n"],
		[' ', '&nbsp;&nbsp;&nbsp;', '<br>'],
		htmlspecialcharsbx(CSqlFormat::reformatSql($query['SQL_TEXT'])),
	);

	$html = '<span onmouseover="addTimer(this)" onmouseout="removeTimer(this)" id="' . $query['ID'] . '_sql_backtrace">' . $html . '</span>';

	$row->AddViewField('SQL_TEXT', $html);
	$row->AddViewField('HIT_ID', '<a href="perfmon_hit_list.php?lang=' . LANGUAGE_ID . '&amp;set_filter=Y&amp;find_id=' . $query['HIT_ID'] . '">' . $query['HIT_ID'] . '</a>');
	if ($bCluster && $query['NODE_ID'] != '')
	{
		if ($query['NODE_ID'] < 0)
		{
			$html = '<div class="lamp-red" style="display:inline-block"></div>';
		}
		else
		{
			$html = '';
		}

		if ($query['NODE_ID'] > 1)
		{
			$html .= $arClusterNodes[$query['NODE_ID']];
		}
		else
		{
			$html .= $arClusterNodes[1];
		}

		$row->AddViewField('NODE_ID', $html);
	}

	if (isset($query['SELECTED_ROWS']) && $query['SELECTED_FIELDS'] > 0)
	{
		$fieldContent = '';
		foreach ($statFields as $field => $params)
		{
			if ($field === 'HAS_BIG_FIELDS')
			{
				continue;
			}

			$color = 'ui-label-tag-light';

			if ($query[$field] > $params['dangerValue'])
			{
				$color = 'ui-label-lightred';
			}
			elseif ($query[$field] > $params['warnValue'])
			{
				$color = 'ui-label-lightorange';
			}

			if (isset($params['size']) && $params['size'])
			{
				$query[$field] = CFile::FormatSize($query[$field]);
			}

			$text = Loc::getMessage('PERFMON_SQL_FIELD_' . $field) . ': ' . $query[$field];

			$fieldContent .= '<span class="ui-label ' . $color . ' ui-label-fill perf-label">
				<span class="ui-label-inner">
					' . $text . '
				</span>
			</span>';
		}

		if (isset($query['HAS_BIG_FIELDS']) && $query['HAS_BIG_FIELDS'] === 'Y')
		{
			$text = Loc::getMessage('PERFMON_SQL_FIELD_HAS_BIG_FIELDS');
			$fieldContent .= '<span class="ui-label ui-label-lightorange ui-label-fill perf-label">
				<span class="ui-label-inner">
					' . $text . '
				</span>
			</span>';
		}

		if ($query['SELECTED_FIELDS'] > 0 && $query['SELECTED_ROWS'] > $query['FETCHED_ROWS'])
		{
			$text = Loc::getMessage('PERFMON_SQL_WRONG_LIMIT');
			$fieldContent .= '<span class="ui-label ui-label-danger ui-label-fill perf-label">
				<span class="ui-label-inner">
					' . $text . '
				</span>
			</span>';
		}

		$row->AddViewField('QUERY_STAT', $fieldContent);
	}

	$arActions = [];
	$arActions[] = [
		'DEFAULT' => 'Y',
		'TEXT' => Loc::getMessage('PERFMON_SQL_EXPLAIN'),
		'ACTION' => 'BX.adminSidePanel.onOpenPage("' . CUtil::JSEscape($explainUrl) . '");',
	];
	$row->AddActions($arActions);
	$row->setConfig([
		CAdminUiListRow::DEFAULT_ACTION_TYPE_FIELD => CAdminUiListRow::LINK_TYPE_SLIDER,
	]);
}

$APPLICATION->SetTitle(Loc::getMessage('PERFMON_SQL_TITLE'));

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

CJSCore::Init(['ajax', 'popup']);
?>
	<script>
		let toolTipCache = [];

		function drawTooltip(result, _this)
		{
			if (!_this) _this = this;

			if (result !== 'no backtrace found')
			{
				_this.toolTip = BX.PopupWindowManager.create(
					'table_tooltip_' + (parseInt(Math.random() * 100000)), _this,
					{
						autoHide: true,
						closeIcon: true,
						closeByEsc: true,
						content: result
					}
				);

				_this.toolTip.show();
			}

			toolTipCache[_this.id] = result;
		}

		function sendRequest()
		{
			if (this.toolTip)
			{
				this.toolTip.show();
			}
			else if (toolTipCache[this.id])
			{
				drawTooltip(toolTipCache[this.id], this);
			}
			else
			{
				BX.ajax.get(
					'perfmon_sql_list.php?ajax_tooltip=y' + '&sessid=' + BX.message('bitrix_sessid') + '&sql_id=' + this.id,
					BX.proxy(drawTooltip, this)
				);
			}
		}

		function addTimer(p_href)
		{
			p_href.timerID = setTimeout(BX.proxy(sendRequest, p_href), 1000);
		}

		function removeTimer(p_href)
		{
			if (p_href.timerID)
			{
				clearTimeout(p_href.timerID);
				p_href.timerID = null;
			}
		}

		BX.ready(() => {
			let pagination = '';

			BX.addCustomEvent('Grid::beforeRequest', (grid, eventArgs) => {
				pagination = '';
				if (eventArgs.data && eventArgs.data.apply_filter === 'Y' && eventArgs.data.clear_nav === 'Y')
				{
					let current = new URL(window.location.origin + eventArgs.url);
					current.searchParams.delete('nav-perfmon-sql-list');

					eventArgs.url = current.href;
				}
				else if (BX.type.isNotEmptyString(eventArgs.url) && eventArgs.method === 'GET')
				{
					let requestURL;
					try
					{
						requestURL = new URL(eventArgs.url);
					}
					catch
					{
						return
					}

					let current = new URL(window.location.href);

					if (requestURL.searchParams.has('nav-perfmon-sql-list'))
					{
						const newPage = requestURL.searchParams.get('nav-perfmon-sql-list');
						const currentPage = current.searchParams.get('nav-perfmon-sql-list');

						if (currentPage !== newPage)
						{
							requestURL.searchParams.set('nav-perfmon-sql-list', newPage);
							eventArgs.url = requestURL.href;
							pagination = newPage;
						}
						else
						{
							requestURL.searchParams.delete('nav-perfmon-sql-list');
							eventArgs.url = requestURL.href;
						}
					}
				}
			});

			BX.addCustomEvent('BX.Main.Filter:apply', (id,data,ctx) => {
				const url = new URL(location.href);
				for (const [field, value] of Object.entries(ctx.getFilterFieldsValues()))
				{
					const valuestr = String(value);
					if (valuestr === '')
					{
						url.searchParams.delete(field);
					}
					else
					{
						url.searchParams.set(field, valuestr);
					}
				}
				url.searchParams.delete('nav-perfmon-sql-list');
				url.searchParams.set('apply_filter', 'Y');
				window.history.replaceState(null, null, url.href);
			});

			BX.addCustomEvent('Grid::updated', () => {
				const url = new URL(location.href);
				if (pagination)
				{
					url.searchParams.set('nav-perfmon-sql-list', pagination);
					window.history.replaceState(null, null, url.href);
				}
				else
				{
					url.searchParams.delete('nav-perfmon-sql-list');
					window.history.replaceState(null, null, url.href);
				}
			});
		});

	</script>
	<style>
		.perf-label {
			margin-bottom: 7px;
			--ui-font-size-5xs: 12px;
		}
	</style>

<?php
$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList([
	'ACTION_PANEL' => false,
]);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
