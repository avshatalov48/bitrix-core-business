<?php
/** @var CMain $APPLICATION */
/** @var CDatabase $DB */
/** @var CUser $USER */

use Bitrix\Main\Loader;

const ADMIN_MODULE_NAME = 'perfmon';
const PERFMON_STOP = true;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

Loader::includeModule('perfmon');
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/prolog.php';

IncludeModuleLangFile(__FILE__);

/** @var \Bitrix\Main\HttpRequest $request */
$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$connectionName = $request['connection'] ?: 'default';
$connection = \Bitrix\Main\Application::getConnection($connectionName);
$sqlHelper = $connection->getSqlHelper();

$table_name = $request['table_name'];
$obTable = new CPerfomanceTable;
$obTable->Init($table_name, $connection);

$RIGHT = CMain::GetGroupRight('perfmon');
if ($RIGHT == 'D' || !$obTable->IsExists())
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

if (
	$request->getRequestMethod() === 'GET'
	&& $request->get('ajax_tooltip') === 'y'
	&& check_bitrix_sessid()
)
{
	require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_js.php';

	$filter = [];
	foreach ($obTable->GetTableFields() as $FIELD_NAME => $FIELD_INFO)
	{
		if ($request->get('f_' . $FIELD_NAME) !== null)
		{
			$filter['=' . $FIELD_NAME] = $request->get('f_' . $FIELD_NAME);
		}
	}

	$rsData = $obTable->GetList(['*'], $filter);
	$arData = $rsData->fetch();
	if ($arData)
	{
		?><table class="list"><?php
			?><tr><?php
				?><td align="left" colspan="2"><b><?php echo htmlspecialcharsEx($table_name) ?></b></td></tr><?php
				foreach ($arData as $key => $value)
				{
					?><tr><?php
						?><td align="left"><?php echo htmlspecialcharsEx($key) ?></td><?php
						?><td align="left">&nbsp;<?php echo htmlspecialcharsEx($value) ?></td></tr><?php
				}
		?></table><?php
	}
	else
	{
		?>no data found<?php
	}

	require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin_js.php';
}

$arFields = [];
$arFieldsEx = $obTable->GetTableFields(false, true);

foreach ($arFieldsEx as $FIELD_NAME => $FIELD_INFO)
{
	$arFields[$FIELD_NAME] = $FIELD_INFO['type'];
}

$arUniqueIndexes = $obTable->GetUniqueIndexes();
$sTableID = 'tbl_perfmon_table' . md5($table_name);
$oSort = new CAdminUiSorting($sTableID, 'ID', 'asc');
$by = $oSort->getField();
$order = mb_strtoupper($oSort->getOrder());
$lAdmin = new CAdminUiList($sTableID, $oSort);

$arID = $lAdmin->GroupAction();
if (isset($_REQUEST['action']) && $arID && $RIGHT >= 'W')
{
	foreach ($arID as $ID)
	{
		if ($ID == '')
		{
			continue;
		}

		//Gather columns from request
		$arRowPK = unserialize(base64_decode($ID), ['allowed_classes' => false]);
		if (!is_array($arRowPK) || count($arRowPK) < 1)
		{
			continue;
		}

		if ($_REQUEST['action'] === 'delete')
		{
			foreach ($arUniqueIndexes as $arIndexColumns)
			{
				$arMissed = array_diff($arIndexColumns, array_keys($arRowPK));
				if (!$arMissed)
				{
					$strSql = 'delete from ' . $table_name . ' WHERE 1=1 ';
					foreach ($arRowPK as $column => $value)
					{
						if ($value <> '')
						{
							$strSql .= ' AND ' . $sqlHelper->quote($column) . " = '" . $sqlHelper->forSql($value) . "'";
						}
						else
						{
							$strSql .= ' AND (' . $sqlHelper->quote($column) . " = '" . $sqlHelper->forSql($value) . "' OR " . $sqlHelper->quote($column) . ' is null)';
						}
					}
					$connection->query($strSql);
				}
			}
		}
		else
		{
			$obSchema = new CPerfomanceSchema;
			$arRowActions = $obSchema->GetRowActions($table_name);
			if (
				array_key_exists($_REQUEST['action'], $arRowActions)
				&& is_callable($arRowActions[$_REQUEST['action']]['callback'])
			)
			{
				foreach ($arUniqueIndexes as $arIndexColumns)
				{
					$arMissed = array_diff($arIndexColumns, array_keys($arRowPK));
					if (!$arMissed)
					{
						$callbackArgs = [];
						foreach ($arRowPK as $column => $value)
						{
							$callbackArgs[] = $value;
						}
						/** @var \Bitrix\Main\Result $callbackResult */
						$callbackResult = call_user_func_array($arRowActions[$_REQUEST['action']]['callback'], $callbackArgs);
						if (!$callbackResult->isSuccess())
						{
							$lAdmin->AddGroupError(implode('</br>', $callbackResult->getErrorMessages()), $ID);
						}
						break;
					}
				}
			}
		}
	}
}

$indexedColumns = [];
foreach ($obTable->GetIndexes() as $columns)
{
	ksort($columns);
	foreach ($columns as $columnName)
	{
		$indexedColumns[$columnName] = $columnName;
		break;
	}
}

$filterFields = [];
foreach ($arFields as $FIELD_NAME => $FIELD_TYPE)
{
	if ($FIELD_TYPE != 'unknown')
	{
		$filterFields[] = [
			'id' => 'f_' . $FIELD_NAME,
			'name' => $FIELD_NAME . (isset($indexedColumns[$FIELD_NAME]) ? ' (Ind)' : ''),
			'filterable' => '%=',
		];
	}
}

$arFilterForm = [];
$lAdmin->AddFilter($filterFields, $arFilterForm);

$where = new CSQLWhere();
$arFilter = [];
foreach ($arFilterForm as $key => $filterValue)
{
	$FIELD_NAME = substr($key, 4);
	if (!array_key_exists($FIELD_NAME, $arFields))
	{
		continue;
	}

	$op = $where->MakeOperation($filterValue);

	if ($filterValue === $op['FIELD'])
	{
		$op['OPERATOR'] = '%=';
	}
	else
	{
		$op['OPERATOR'] = mb_substr($filterValue, 0, mb_strlen($filterValue) - mb_strlen($op['FIELD']));
	}

	if ($op['OPERATION'] === 'B' || $op['OPERATION'] === 'NB')
	{
		$op['FIELD'] = array_map('trim', explode(',', $op['FIELD'], 2));
	}
	elseif ($op['OPERATION'] === 'IN' || $op['OPERATION'] === 'NIN')
	{
		$op['FIELD'] = array_map('trim', explode(',', $op['FIELD']));
	}

	$arFilter[$op['OPERATOR'] . $FIELD_NAME] = $op['FIELD'] === 'NULL' ? false : $op['FIELD'];
}

$filterOption = new Bitrix\Main\UI\Filter\Options($sTableID);
$filterData = $filterOption->getFilter($filterFields);
$find = trim($filterData['FIND'] ?? '', " \t\n\r");
if ($find)
{
	$c = count($filterFields);
	for ($i = 0; $i < $c; $i++)
	{
		$field = $filterFields[$i];
		if (preg_match('/^\s*' . $field['name'] . '\s*:\s*(.+)\s*$/i', $find, $match))
		{
			$filterValue = $match[1];

			$op = $where->MakeOperation($filterValue);

			if ($filterValue === $op['FIELD'])
			{
				$op['OPERATOR'] = '%=';
			}
			else
			{
				$op['OPERATOR'] = mb_substr($filterValue, 0, mb_strlen($filterValue) - mb_strlen($op['FIELD']));
			}

			$arFilter[$op['OPERATOR'] . $field['name']] = $op['FIELD'] === 'NULL' ? false : $op['FIELD'];
			break;
		}
	}
	if ($i == $c)
	{
		$field = $filterFields[0];
		$filterValue = $find;

		$op = $where->MakeOperation($filterValue);

		if ($filterValue === $op['FIELD'])
		{
			$op['OPERATOR'] = '%=';
		}
		else
		{
			$op['OPERATOR'] = mb_substr($filterValue, 0, mb_strlen($filterValue) - mb_strlen($op['FIELD']));
		}

		$arFilter[$op['OPERATOR'] . $field['name']] = $op['FIELD'] === 'NULL' ? false : $op['FIELD'];
	}
}

$arHeaders = [];
foreach ($arFields as $FIELD_NAME => $FIELD_TYPE)
{
	$arHeaders[$FIELD_NAME] = [
		'id' => $FIELD_NAME,
		'content' => $FIELD_NAME,
		'sort' => $arFieldsEx[$FIELD_NAME]['sortable'] ? $FIELD_NAME : '',
		'default' => true,
		'prevent_default' => false,
	];
	if ($FIELD_TYPE == 'int' || $FIELD_TYPE == 'datetime' || $FIELD_TYPE == 'date' || $FIELD_TYPE == 'double')
	{
		$arHeaders[$FIELD_NAME]['align'] = 'right';
	}
}

$lAdmin->AddHeaders($arHeaders);

$bDelete = false;
$arPKColumns = [];
$arSelectedFields = $lAdmin->GetVisibleHeaderColumns();
if (!is_array($arSelectedFields) || (count($arSelectedFields) < 1))
{
	$arSelectedFields = ['*',];
	$bDelete = count($arUniqueIndexes) > 0;
	$arPKColumns = array_shift($arUniqueIndexes);
}
else
{
	foreach ($arUniqueIndexes as $arIndexColumns)
	{
		$arMissed = array_diff($arIndexColumns, $arSelectedFields);
		if (count($arMissed) == 0)
		{
			$bDelete = true;
			$arPKColumns = $arIndexColumns;
			break;
		}
	}
}

$bDelete = $bDelete && $RIGHT >= 'W';

$obSchema = new CPerfomanceSchema;
$arChildren = $obSchema->GetChildren($table_name);
$arParents = $obSchema->GetParents($table_name);
$arRowActions = $obSchema->GetRowActions($table_name);

$nav = $lAdmin->getPageNavigation('nav-perfmon-table');
if ($lAdmin->isTotalCountRequest())
{
	CTimeZone::Disable();
	$count = $obTable->GetList(
		['ID'],
		$arFilter,
		[],
		['bOnlyCount' => true]
	);
	CTimeZone::Enable();
	$lAdmin->sendTotalCountResponse($count);
}

if ($request['mode'] === 'excel')
{
	$arNavParams = false;
}
else
{
	$arNavParams = [
		'nTopCount' => $nav->getLimit() + 1,
		'nOffset' => $nav->getOffset(),
	];
}

CTimeZone::Disable();
$rsData = $obTable->GetList(
	$arSelectedFields,
	$arFilter,
	[$by => $order],
	$arNavParams
);
CTimeZone::Enable();

function TableExists($tableName)
{
	global $connection;
	static $cache = [];

	if (!isset($cache[$tableName]))
	{
		$cache[$tableName] = $connection->isTableExists($tableName);
	}

	return $tableName;
}

$precision = ini_get('precision') >= 0 ? ini_get('precision') : 2;
$max_display_url = COption::GetOptionInt('perfmon', 'max_display_url');

$n = 0;
$pageSize = $lAdmin->getNavSize();
while ($arRes = $rsData->fetch())
{
	$n++;
	if (($n > $pageSize) && !($request['mode'] === 'excel'))
	{
		break;
	}

	$ID = $arRes['ID'];
	if ($arPKColumns)
	{
		$arRowPK = [];
		foreach ($arPKColumns as $FIELD_NAME)
		{
			$arRowPK[$FIELD_NAME] = $arRes[$FIELD_NAME];
		}
		$ID = base64_encode(serialize($arRowPK));
	}

	$arRowPK = [];
	foreach ($arFields as $FIELD_NAME => $FIELD_TYPE)
	{
		if ($bDelete && in_array($FIELD_NAME, $arPKColumns))
		{
			$arRowPK[] = urlencode('pk[' . $FIELD_NAME . ']') . '=' . urlencode($arRes[$FIELD_NAME]);
		}
	}

	$editUrl = '';
	if ($bDelete && (count($arPKColumns) == count($arRowPK)))
	{
		$editUrl = 'perfmon_row_edit.php?lang=' . LANGUAGE_ID . '&table_name=' . urlencode($table_name) . (isset($request->getQueryList()['connection']) ? '&connection=' . urlencode($connectionName) : '') . '&' . implode('&', $arRowPK);
	}

	$row = $lAdmin->AddRow($ID, $arRes, $editUrl);

	foreach ($arFields as $FIELD_NAME => $FIELD_TYPE)
	{
		if ($arRes[$FIELD_NAME] === null)
		{
			$val = '<i>[NULL]</i>';
			$row->AddViewField($FIELD_NAME, $val);
		}
		elseif ($arRes[$FIELD_NAME] !== '')
		{
			if ($FIELD_TYPE == 'int')
			{
				$val = perfmon_NumberFormat($arRes[$FIELD_NAME], 0);
			}
			elseif ($FIELD_TYPE == 'double')
			{
				$val = htmlspecialcharsEx($arRes[$FIELD_NAME]);
			}
			elseif ($FIELD_TYPE == 'datetime')
			{
				$val = str_replace(' ', '&nbsp;', $arRes['FULL_' . $FIELD_NAME]);
			}
			elseif ($FIELD_TYPE == 'date')
			{
				$val = str_replace(' ', '&nbsp;', $arRes['SHORT_' . $FIELD_NAME]);
			}
			else
			{
				$val = htmlspecialcharsbx($arRes[$FIELD_NAME]);
			}

			if (array_key_exists($FIELD_NAME, $arParents) && TableExists($arParents[$FIELD_NAME]['PARENT_TABLE']))
			{
				$href = 'perfmon_table.php?lang=' . LANGUAGE_ID . '&table_name=' . $arParents[$FIELD_NAME]['PARENT_TABLE'] . (isset($request->getQueryList()['connection']) ? '&connection=' . urlencode($connectionName) : '') . '&apply_filter=Y&' . urlencode('f_' . $arParents[$FIELD_NAME]['PARENT_COLUMN']) . '=' . urlencode($arRes[$FIELD_NAME]);
				$val = '<a onmouseover="addTimer(this)" onmouseout="removeTimer(this)" href="' . htmlspecialcharsbx($href) . '" onclick="' . htmlspecialcharsbx('window.location=\'' . CUtil::JSEscape($href) . '\'') . '	">' . $val . '</a>';
			}

			$row->AddViewField($FIELD_NAME, $val);
		}
	}

	$arActions = [];
	if ($editUrl)
	{
		$arActions[] = [
			'ICON' => 'edit',
			'DEFAULT' => true,
			'TEXT' => GetMessage('MAIN_EDIT'),
			'ACTION' => $lAdmin->ActionRedirect($editUrl),
		];
		$arActions[] = [
			'ICON' => 'delete',
			'DEFAULT' => false,
			'TEXT' => GetMessage('MAIN_DELETE'),
			'ACTION' => $lAdmin->ActionDoGroup($ID, 'delete', 'table_name=' . urlencode($table_name) . (isset($request->getQueryList()['connection']) ? '&connection=' . urlencode($connectionName) : '')),
		];
		if ($arRowActions)
		{
			$arActions[] = ['SEPARATOR' => true];
			foreach ($arRowActions as $rowActionId => $rowAction)
			{
				$confirm = isset($rowAction['confirm']) ? "if(confirm('" . CUtil::JSEscape($rowAction['confirm']) . "')) " : '';
				$arActions[] = [
					'TEXT' => $rowAction['title'],
					'ACTION' => $confirm . $lAdmin->ActionDoGroup($ID, $rowActionId, 'table_name=' . urlencode($table_name) . (isset($request->getQueryList()['connection']) ? '&connection=' . urlencode($connectionName) : '')),
				];
			}
		}
	}

	if (count($arChildren))
	{
		$arActions[] = ['SEPARATOR' => true];
		foreach ($arChildren as $arChild)
		{
			if (TableExists($arChild['CHILD_TABLE']))
			{
				$href = 'perfmon_table.php?lang=' . LANGUAGE_ID . '&table_name=' . urlencode($arChild['CHILD_TABLE']) . (isset($request->getQueryList()['connection']) ? '&connection=' . urlencode($connectionName) : '') . '&apply_filter=Y&' . urlencode('f_' . $arChild['CHILD_COLUMN']) . '=' . urlencode($arRes[$arChild['PARENT_COLUMN']]);
				$arActions[] = [
					'ICON' => '',
					'DEFAULT' => false,
					'TEXT' => $arChild['CHILD_TABLE'] . '.' . $arChild['CHILD_COLUMN'] . ' = ' . $arChild['PARENT_COLUMN'],
					'ACTION' => $lAdmin->ActionRedirect($href),
				];
			}
		}
	}

	if (count($arActions))
	{
		$row->AddActions($arActions);
	}
}

$nav->setRecordCount($nav->getOffset() + $n);
$lAdmin->setNavigation($nav, GetMessage('PERFMON_TABLE_PAGE'), false);

$lAdmin->AddFooter([
	[
		'title' => GetMessage('MAIN_ADMIN_LIST_SELECTED'),
		'value' => $rsData->getSelectedRowsCount(),
	],
]);

$aContext = [];

if ($arPKColumns)
{
	$aContext[] = [
		'TEXT' => GetMessage('MAIN_ADD'),
		'LINK' => '/bitrix/admin/perfmon_row_edit.php?lang=' . LANGUAGE_ID . '&table_name=' . urlencode($table_name),
		'ICON' => 'btn_new',
	];
}

$lAdmin->AddAdminContextMenu($aContext);

$sLastTables = CUserOptions::GetOption('perfmon', 'last_tables' . (isset($request->getQueryList()['connection']) ? '_' . $connectionName : ''), '');
if ($sLastTables <> '')
{
	$arLastTables = array_flip(explode(',', $sLastTables));
}
else
{
	$arLastTables = [];
}
unset($arLastTables[$table_name]);
$arLastTables[$table_name] = true;
while (count($arLastTables) > 10)
{
	array_shift($arLastTables);
}
CUserOptions::SetOption('perfmon', 'last_tables' . (isset($request->getQueryList()['connection']) ? '_' . $connectionName : ''), implode(',', array_keys($arLastTables)));

$lAdmin->BeginPrologContent();
?>
<script>
	var toolTipCache = new Array;

	function drawTooltip(result, _this)
	{
		if (!_this) _this = this;

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
		toolTipCache[_this.href] = result;
	}

	function sendRequest()
	{
		if (this.toolTip)
			this.toolTip.show();
		else if (toolTipCache[this.href])
			drawTooltip(toolTipCache[this.href], this);
		else
			BX.ajax.get(
				this.href + '&sessid=' + BX.message('bitrix_sessid') + '&ajax_tooltip=y',
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

	function makeFilterUrl(filter)
	{
		const url = new URL(location.href);
		Object.entries(filter.getFilterFieldsValues()).forEach(([name, value]) => {
			const valueStr = String(value);
			if (valueStr === '')
			{
				url.searchParams.delete(name);
			}
			else
			{
				url.searchParams.set(name, valueStr);
			}
		});
		url.searchParams.set('apply_filter', 'Y');
		return url;
	}

	BX.addCustomEvent('BX.Main.Filter:show', (filter) => {
		const addContainer = filter.getAddField().parentElement;
		const findContainer = filter.getFindButton().parentElement;
		if (addContainer.parentElement != findContainer)
		{
			findContainer.appendChild(addContainer);
		}
	});

	function deleteLastUrlParameter(url, name)
	{
		const request = url.split('?');
		const params = request[1].split('&');
		for (var i = params.length - 1; i >= 0; i--)
		{
			if (params[i].split('=')[0] === name)
			{
				params.splice(i, 1);
				break;
			}
		}
		request[1] = params.join('&');
		return request.join('?');
	}

	function getFirstUrlParameter(url, name)
	{
		const request = url.split('?');
		const params = request[1].split('&');
		for (var i = 0; i < params.length; i++)
		{
			if (params[i].split('=')[0] == name)
			{
				return params[i].split('=')[1];
			}
		}
		return '';
	}

	var pagination = '';
	var by = '';
	var order = '';

	BX.ready(() => { //BX.ready to bind after core_admin_interface.js handler
		BX.addCustomEvent('Grid::beforeRequest', (grid, eventArgs) => {
			if (BX.type.isNotEmptyString(eventArgs.url))
			{
				// fix pagination by removing nav parameter
				// (see. BX.adminUiList.prototype.onBeforeRequest)
				// from window.location
				const current = new URL(window.location);
				if (eventArgs.url.indexOf('?') > 0)
				{
					if (current.searchParams.has('<?=$nav->getId()?>'))
					{
						eventArgs.url = deleteLastUrlParameter(eventArgs.url, '<?=$nav->getId()?>');
					}
					pagination = getFirstUrlParameter(eventArgs.url, '<?=$nav->getId()?>');

					if (current.searchParams.has('by'))
					{
						eventArgs.url = deleteLastUrlParameter(eventArgs.url, 'by');
					}
					by = getFirstUrlParameter(eventArgs.url, 'by');

					if (current.searchParams.has('order'))
					{
						eventArgs.url = deleteLastUrlParameter(eventArgs.url, 'order');
					}
					order = getFirstUrlParameter(eventArgs.url, 'order');
				}
			}
		});
	});

	var prevFilter = '';
	BX.addCustomEvent('Grid::updated', (grid) => {
		const filter = BX.Main.filterManager.getById(grid.getId());
		const newUrl = makeFilterUrl(filter);

		if (pagination)
		{
			newUrl.searchParams.set('<?=$nav->getId()?>', pagination);
		}
		else
		{
			newUrl.searchParams.delete('<?=$nav->getId()?>');
		}

		if (by)
		{
			newUrl.searchParams.set('by', by);
		}
		else
		{
			newUrl.searchParams.delete('by');
		}

		if (order)
		{
			newUrl.searchParams.set('order', order);
		}
		else
		{
			newUrl.searchParams.delete('order');
		}

		if (newUrl.search != prevFilter)
		{
			history.pushState({}, null, newUrl.toString());
			prevFilter = newUrl.search;
		}
	});
</script>
<style>
.main-ui-filter-field-button-container,
.main-ui-filter-field-preset-button-container {
	text-align: left;
}
.main-ui-filter-field-add {
	float: right;
}
.main-ui-filter-field-add-item {
	display: block;
}
.main-ui-filter-field-restore-items {
	margin-left: 0;
}
.main-grid-cell-content, .main-grid-editor-container {
	margin: 10px 16px 10px;
}
</style>
<?php
$lAdmin->EndPrologContent();

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage('PERFMON_TABLE_ALT_TITLE', ['#TABLE_NAME#' => $table_name]));

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

CJSCore::Init(['ajax', 'popup']);

$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList([
	'SHOW_COUNT_HTML' => true,
	'SERVICE_URL' => 'perfmon_table.php?lang=' . LANGUAGE_ID . '&table_name=' . urlencode($table_name) . (isset($request->getQueryList()['connection']) ? '&connection=' . urlencode($connectionName) : ''),
]);

echo BeginNote();
echo '
	<ul>
	<li>= Identical</li>
	<li>&gt; Greater</li>
	<li>&gt;= Greater or Equal</li>
	<li>&lt; Less</li>
	<li>&lt;= Less or Equal</li>
	<li>% Substring</li>
	<li>? Logic</li>
	<li>&gt;&lt;MIN,MAX Between</li>
	<li>@N1,N2,...,NN IN</li>
	<li>NULL Empty</li>
	<li>! Negate any of above</li>
	</ul>
';
echo EndNote();

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
