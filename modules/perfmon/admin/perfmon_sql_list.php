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

$bCluster = CModule::IncludeModule('cluster');

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
		<table class="list"><?php
		?>
		<tr>
		<td align="left"><b><?php echo GetMessage('PERFMON_SQL_FILE') ?></b></td>
		<td align="left"><b><?php echo GetMessage('PERFMON_SQL_LINE_NUMBER'); ?></b></td>
		<td align="left"><b><?php echo GetMessage('PERFMON_SQL_FUNCTION'); ?></b></td>
		</tr><?php
		do
		{
			?>
			<tr>
			<td align="left">&nbsp;<?php echo htmlspecialcharsEx($arData['FILE_NAME']) ?></td>
			<td align="right">&nbsp;<?php echo htmlspecialcharsEx($arData['LINE_NO']) ?></td>
			<?php
			if ($arData['CLASS_NAME']):?>
				<td align="left">
					&nbsp;<?php echo htmlspecialcharsEx($arData['CLASS_NAME'] . '::' . $arData['FUNCTION_NAME']) ?></td>
			<?php else: ?>
				<td align="left">&nbsp;<?php echo htmlspecialcharsEx($arData['FUNCTION_NAME']) ?></td>
			<?php endif; ?>
			</tr><?php
		} while ($arData = $rsData->Fetch());
		?></table><?php
	}
	else
	{
		?>no backtrace found<?php
	}
	require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin_js.php';
}

$sTableID = 'tbl_perfmon_sql_list';
$oSort = new CAdminSorting($sTableID, 'NN', 'asc');
$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());
$lAdmin = new CAdminList($sTableID, $oSort);

$FilterArr = [
	'find',
	'find_type',
	'find_hit_id',
	'find_component_id',
	'find_query_time',
	'find_suggest_id',
	'find_node_id',
];

$currentFilter = $lAdmin->InitFilter($FilterArr);
foreach ($FilterArr as $fieldName)
{
	$currentFilter[$fieldName] = ($currentFilter[$fieldName] ?? '');
}

$arFilter = [
	'=HIT_ID' => ($currentFilter['find'] != '' && $currentFilter['find_type'] == 'hit_id' ? $currentFilter['find'] : $currentFilter['find_hit_id']),
	'=COMPONENT_ID' => ($currentFilter['find'] != '' && $currentFilter['find_type'] == 'component_id' ? $currentFilter['find'] : $currentFilter['find_component_id']),
	'>=QUERY_TIME' => floatval($currentFilter['find_query_time']),
	'=SUGGEST_ID' => intval($currentFilter['find_suggest_id']),
];
foreach ($arFilter as $key => $value)
{
	if (!$value)
	{
		unset($arFilter[$key]);
	}
}

if ($currentFilter['find_node_id'] != '')
{
	if ($currentFilter['find_node_id'] > 1)
	{
		$arFilter['=NODE_ID'] = $currentFilter['find_node_id'];
	}
	else
	{
		$arFilter['0'] = [
			'LOGIC' => 'OR',
			'0' => [
				'=NODE_ID' => 1,
			],
			'1' => [
				'=NODE_ID' => false,
			],
		];
	}
}

$arHeaders = [
	[
		'id' => 'ID',
		'content' => GetMessage('PERFMON_SQL_ID'),
		'sort' => 'ID',
		'align' => 'right',
		'default' => true,
	],
	[
		'id' => 'HIT_ID',
		'content' => GetMessage('PERFMON_SQL_HIT_ID'),
		'sort' => 'HIT_ID',
		'align' => 'right',
		'default' => true,
	],
	[
		'id' => 'NN',
		'content' => GetMessage('PERFMON_SQL_NN'),
		'sort' => 'NN',
		'align' => 'right',
		'default' => true,
	],
	[
		'id' => 'QUERY_TIME',
		'content' => GetMessage('PERFMON_SQL_QUERY_TIME'),
		'sort' => 'QUERY_TIME',
		'align' => 'right',
		'default' => true,
	],
	[
		'id' => 'MODULE_NAME',
		'content' => GetMessage('PERFMON_SQL_MODULE_NAME'),
		'sort' => 'MODULE_NAME',
	],
	[
		'id' => 'COMPONENT_NAME',
		'content' => GetMessage('PERFMON_SQL_COMPONENT_NAME'),
		'sort' => 'COMPONENT_NAME',
	],
	[
		'id' => 'SQL_TEXT',
		'content' => GetMessage('PERFMON_SQL_SQL_TEXT'),
		//"sort" => "SQL_TEXT",
		'default' => true,
	],
];

$arClusterNodes = [];
if ($bCluster)
{
	$arHeaders[] = [
		'id' => 'NODE_ID',
		'content' => GetMessage('PERFMON_SQL_NODE_ID'),
	];
	$arClusterNodes[''] = GetMessage('MAIN_ALL');
	$rsNodes = CClusterDBNode::GetList();
	while ($node = $rsNodes->fetch())
	{
		$arClusterNodes[$node['ID']] = htmlspecialcharsEx($node['NAME']);
	}
}

$lAdmin->AddHeaders($arHeaders);

$arSelectedFields = $lAdmin->GetVisibleHeaderColumns();
if (!is_array($arSelectedFields) || (count($arSelectedFields) < 1))
{
	$arSelectedFields = [
		'ID',
		'HIT_ID',
		'NN',
		'QUERY_TIME',
		'SQL_TEXT',
	];
}

if ($bCluster && !in_array('NODE_ID', $arSelectedFields, true))
{
	$arSelectedFields[] = 'NODE_ID';
}

$rsData = CPerfomanceSQL::GetList($arSelectedFields, $arFilter, [$by => $order], false, ['nPageSize' => CAdminResult::GetNavSize($sTableID)]);

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage('PERFMON_SQL_PAGE')));

while ($arRes = $rsData->GetNext()):
	$arRes['SQL_TEXT'] = CPerfomanceSQL::Format($arRes['~SQL_TEXT']);
	$row =& $lAdmin->AddRow($arRes['ID'], $arRes);

	$row->AddViewField('QUERY_TIME', perfmon_NumberFormat($arRes['QUERY_TIME'], 6));

	$html = str_replace(
		[' ', "\t", "\n"],
		[' ', '&nbsp;&nbsp;&nbsp;', '<br>'],
		htmlspecialcharsbx(CSqlFormat::reformatSql($arRes['SQL_TEXT']))
	);

	$html = '<span onmouseover="addTimer(this)" onmouseout="removeTimer(this)" id="' . $arRes['ID'] . '_sql_backtrace">' . $html . '</span>';

	$row->AddViewField('SQL_TEXT', $html);
	$row->AddViewField('HIT_ID', '<a href="perfmon_hit_list.php?lang=' . LANGUAGE_ID . '&amp;set_filter=Y&amp;find_id=' . $arRes['HIT_ID'] . '">' . $arRes['HIT_ID'] . '</a>');
	if ($bCluster && $arRes['NODE_ID'] != '')
	{
		if ($arRes['NODE_ID'] < 0)
		{
			$html = '<div class="lamp-red" style="display:inline-block"></div>';
		}
		else
		{
			$html = '';
		}

		if ($arRes['NODE_ID'] > 1)
		{
			$html .= $arClusterNodes[$arRes['NODE_ID']];
		}
		else
		{
			$html .= $arClusterNodes[1];
		}

		$row->AddViewField('NODE_ID', $html);
	}

	$arActions = [];
	$arActions[] = [
		'DEFAULT' => 'Y',
		'TEXT' => GetMessage('PERFMON_SQL_EXPLAIN'),
		'ACTION' => 'jsUtils.OpenWindow(\'perfmon_explain.php?lang=' . LANG . '&ID=' . $arRes['ID'] . '\', 600, 500);',
	];
	$row->AddActions($arActions);
endwhile;

$lAdmin->AddFooter(
	[
		[
			'title' => GetMessage('MAIN_ADMIN_LIST_SELECTED'),
			'value' => $rsData->SelectedRowsCount(),
		],
	]
);

$aContext = [];
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage('PERFMON_SQL_TITLE'));

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$arFilter = [
	'find_hit_id' => GetMessage('PERFMON_SQL_HIT_ID'),
	'find_component_id' => GetMessage('PERFMON_SQL_COMPONENT_ID'),
	'find_query_time' => GetMessage('PERFMON_SQL_QUERY_TIME'),
];
if ($bCluster)
{
	$arFilter['find_node_id'] = GetMessage('PERFMON_SQL_NODE_ID');
}

$oFilter = new CAdminFilter($sTableID . '_filter', $arFilter);

CJSCore::Init(['ajax', 'popup']);
?>
	<script>
		var toolTipCache = new Array;

		function drawTooltip(result, _this)
		{
			if (!_this) _this = this;

			if (result != 'no backtrace found')
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
				this.toolTip.show();
			else if (toolTipCache[this.id])
				drawTooltip(toolTipCache[this.id], this);
			else
				BX.ajax.get(
					'perfmon_sql_list.php?ajax_tooltip=y' + '&sessid=' + BX.message('bitrix_sessid') + '&sql_id=' + this.id,
					BX.proxy(drawTooltip, this)
				);
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
	</script>

	<form name="find_form" method="get" action="<?php echo $APPLICATION->GetCurPage(); ?>">
		<?php $oFilter->Begin(); ?>
		<tr>
			<td><b><?=GetMessage('PERFMON_SQL_FIND')?>:</b></td>
			<td>
				<input type="text" size="25" name="find" value="<?php echo htmlspecialcharsbx($currentFilter['find']) ?>"
					title="<?=GetMessage('PERFMON_SQL_FIND')?>">
				<?php
				$arr = [
					'reference' => [
						GetMessage('PERFMON_SQL_HIT_ID'),
						GetMessage('PERFMON_SQL_COMPONENT_ID'),
					],
					'reference_id' => [
						'hit_id',
						'component_id',
					]
				];
				echo SelectBoxFromArray('find_type', $arr, $currentFilter['find_type'], '', '');
				?>
			</td>
		</tr>
		<tr>
			<td><?=GetMessage('PERFMON_SQL_HIT_ID')?></td>
			<td><input type="text" name="find_hit_id" size="47"
				value="<?php echo htmlspecialcharsbx($currentFilter['find_hit_id']) ?>"></td>
		</tr>
		<tr>
			<td><?=GetMessage('PERFMON_SQL_COMPONENT_ID')?></td>
			<td><input type="text" name="find_component_id" size="47"
				value="<?php echo htmlspecialcharsbx($currentFilter['find_component_id']) ?>"></td>
		</tr>
		<tr>
			<td><?=GetMessage('PERFMON_SQL_QUERY_TIME')?></td>
			<td><input type="text" name="find_query_time" size="7"
				value="<?php echo htmlspecialcharsbx($currentFilter['find_query_time']) ?>"></td>
		</tr>
		<?php if ($bCluster): ?>
			<tr>
				<td><?=GetMessage('PERFMON_SQL_NODE_ID')?></td>
				<td><?php
					$arr = [
						'reference' => array_values($arClusterNodes),
						'reference_id' => array_keys($arClusterNodes),
					];
					echo SelectBoxFromArray('find_node_id', $arr, $currentFilter['find_node_id'], '', '');
					?></td>
			</tr>
		<?php endif; ?>
		<?php
		$oFilter->Buttons([
			'table_id' => $sTableID,
			'url' => $APPLICATION->GetCurPage(),
			'form' => 'find_form',
		]);
		$oFilter->End();
		?>
	</form>

<?php $lAdmin->DisplayList(); ?>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
