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
$connection = \Bitrix\Main\Application::getConnection();
if ($RIGHT === 'D' || $connection->getType() !== 'mysql')
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

$sTableID = 'tbl_perfmon_index_list';
$oSort = new CAdminSorting($sTableID, 'TABLE_NAME', 'asc');
$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());
$lAdmin = new CAdminList($sTableID, $oSort);
$go = false;
$last_id = 0;

if ($lAdmin->GroupAction())
{
	switch ($_REQUEST['action'])
	{
	case 'analyze_start':
		CPerfomanceIndexSuggest::Clear();
		$last_id = 0;
		$go = true;
		$_SESSION['queries'] = 0;
		break;
	case 'analyze_cont':
		$etime = time() + 5;
		$last_id = intval($_REQUEST['last_id']);
		$sql_cache = [];

		while (time() < $etime)
		{
			$rsSQL = CPerfomanceSQL::GetList(
				['ID', 'SQL_TEXT', 'QUERY_TIME'],
				['>ID' => $last_id],
				['ID' => 'ASC'],
				false,
				['nTopCount' => 100]
			);
			while ($arSQL = $rsSQL->Fetch())
			{
				$_SESSION['queries']++;
				$go = true;
				$sql_md5 = md5(CPerfQuery::remove_literals($arSQL['SQL_TEXT']));

				//Check if did it already on previous steps
				if (!array_key_exists($sql_md5, $sql_cache))
				{
					$sql_cache[$sql_md5] = true;

					$rsInd = CPerfomanceIndexSuggest::GetList(['SQL_MD5'], ['=SQL_MD5' => $sql_md5], []);
					if ($rsInd->Fetch())
					{
						CPerfomanceIndexSuggest::UpdateStat($sql_md5, 1, $arSQL['QUERY_TIME'], $arSQL['ID']);
					}
					else
					{
						$arMissedKeys = [];
						$arExplain = [];
						$q = new CPerfQuery;
						$strSQL = CPerfQuery::transform2select($arSQL['SQL_TEXT']);
						if ($strSQL && $q->parse($strSQL))
						{
							$i = 0;
							$rsData = $DB->Query('explain ' . $strSQL, true);
							if (is_object($rsData))
							{
								while ($arRes = $rsData->Fetch())
								{
									$i++;
									$arExplain[] = $arRes;
									if (
										$arRes['type'] === 'ALL'
										&& $arRes['key'] == ''
										&& is_object($q)
										&& ($i > 1 || $q->has_where($arRes['table']))
									)
									{
										$missed_keys = $q->suggest_index($arRes['table']);
										if ($missed_keys)
										{
											$arMissedKeys = array_merge($arMissedKeys, $missed_keys);
										}
										elseif ($q->has_where())
										{
											//Check if it is possible to find missed keys on joined tables
											foreach ($q->table_joins($arRes['table']) as $alias => $join_columns)
											{
												$missed_keys = $q->suggest_index($alias);
												if ($missed_keys)
												{
													$arMissedKeys = array_merge($arMissedKeys, $missed_keys);
												}
											}
										}
									}
								}
							}
						}

						if (!empty($arMissedKeys))
						{
							foreach (array_unique($arMissedKeys) as $suggest)
							{
								list($alias, $table, $columns) = explode(':', $suggest);
								if (
									!CPerfQueryStat::IsBanned($table, $columns)
									&& !CPerfomanceIndexComplete::IsBanned($table, $columns)
								)
								{
									if (
										CPerfQueryStat::GatherExpressStat($table, $columns, $q)
										&& !CPerfQueryStat::IsSelective($table, $columns, $q)
									)
									{
										CPerfQueryStat::Ban($table, $columns);
									}
									else
									{
										CPerfomanceIndexSuggest::Add([
											'TABLE_NAME' => $table,
											'TABLE_ALIAS' => $alias,
											'COLUMN_NAMES' => $columns,
											'SQL_TEXT' => $arSQL['SQL_TEXT'],
											'SQL_MD5' => $sql_md5,
											'SQL_COUNT' => 0,
											'SQL_TIME' => 0,
											'SQL_EXPLAIN' => serialize($arExplain),
										]);
									}
								}
							}
							CPerfomanceIndexSuggest::UpdateStat($sql_md5, 1, $arSQL['QUERY_TIME'], $arSQL['ID']);
						}
					}
				}
				else
				{
					CPerfomanceIndexSuggest::UpdateStat($sql_md5, 1, $arSQL['QUERY_TIME'], $arSQL['ID']);
				}

				$last_id = $arSQL['ID'];
			}
		}
		break;
	}

	if ($go)
	{
		$lAdmin->BeginPrologContent();
		$message = new CAdminMessage([
			'MESSAGE' => GetMessage('PERFMON_INDEX_IN_PROGRESS'),
			'DETAILS' => GetMessage('PERFMON_INDEX_QUERIES_ANALYZED', ['#QUERIES#' => '<b>' . intval($_SESSION['queries']) . '</b>']) . '<br>',
			'HTML' => true,
			'TYPE' => 'PROGRESS',
		]);
		echo $message->Show();
		?>
		<script>
			<?php echo $lAdmin->ActionDoGroup(0, 'analyze_cont', 'last_id=' . $last_id);?>
		</script>
		<?php
		$lAdmin->EndPrologContent();
	}
	else
	{
		$lAdmin->BeginPrologContent();
		$message = new CAdminMessage([
			'MESSAGE' => GetMessage('PERFMON_INDEX_COMPLETE'),
			'DETAILS' => GetMessage('PERFMON_INDEX_QUERIES_ANALYZED', ['#QUERIES#' => '<b>' . intval($_SESSION['queries']) . '</b>']) . '<br>',
			'HTML' => true,
			'TYPE' => 'OK',
		]);
		echo $message->Show();
		$lAdmin->EndPrologContent();
	}
}

if (!$go && CPerfomanceKeeper::IsActive())
{
	$lAdmin->BeginPrologContent();
	$message = new CAdminMessage([
		'MESSAGE' => GetMessage('PERFMON_INDEX_KEEPER_NOTE_IS_ACTIVE'),
		'DETAILS' => GetMessage('PERFMON_INDEX_KEEPER_NOTE_ANALYZE') . '<br>',
		'HTML' => true,
		'TYPE' => 'OK',
	]);
	echo $message->Show();
	$lAdmin->EndPrologContent();
}

$lAdmin->AddHeaders([
	[
		'id' => 'BANNED',
		'content' => GetMessage('PERFMON_INDEX_BANNED'),
		'align' => 'center',
		'default' => true,
	],
	[
		'id' => 'TABLE_NAME',
		'content' => GetMessage('PERFMON_INDEX_TABLE_NAME'),
		'default' => true,
		'sort' => 'TABLE_NAME',
	],
	[
		'id' => 'COLUMN_NAMES',
		'content' => GetMessage('PERFMON_INDEX_COLUMN_NAMES'),
		'default' => true,
	],
	[
		'id' => 'SQL_COUNT',
		'content' => GetMessage('PERFMON_INDEX_SQL_COUNT'),
		'align' => 'right',
		'default' => true,
		'sort' => 'SQL_COUNT',
	],
	[
		'id' => 'SQL_TIME_AVG',
		'content' => GetMessage('PERFMON_INDEX_SQL_TIME_AVG'),
		'align' => 'right',
		'default' => true,
	],
	[
		'id' => 'SQL_TIME',
		'content' => GetMessage('PERFMON_INDEX_SQL_TIME'),
		'align' => 'right',
		'default' => true,
		'sort' => 'SQL_TIME',
	],
	[
		'id' => 'SQL_TEXT',
		'content' => GetMessage('PERFMON_INDEX_SQL_TEXT'),
		'default' => true,
	],
]);

$arSelectedFields = $lAdmin->GetVisibleHeaderColumns();
if (!is_array($arSelectedFields) || (count($arSelectedFields) < 1))
{
	$arSelectedFields = [
		'TABLE_NAME',
		'COLUMN_NAMES',
		'SQL_COUNT',
		'SQL_TIME',
		'SQL_TEXT',
	];
}
if (!in_array('ID', $arSelectedFields, true))
{
	$arSelectedFields[] = 'ID';
}
if (!in_array('TABLE_NAME', $arSelectedFields, true))
{
	$arSelectedFields[] = 'TABLE_NAME';
}

$rsData = CPerfomanceIndexSuggest::GetList($arSelectedFields, ['!=BANNED' => 'Y'], [$by => $order]);

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage('PERFMON_INDEX_PAGE')));

while ($arRes = $rsData->GetNext())
{
	$arRes['SQL_TEXT'] = CPerfomanceSQL::Format($arRes['SQL_TEXT']);
	$row =& $lAdmin->AddRow($arRes['TABLE_NAME'], $arRes);

	$row->AddViewField('SQL_TIME', perfmon_NumberFormat($arRes['SQL_TIME'], 6));

	if ($arRes['SQL_COUNT'] > 0)
	{
		$row->AddViewField('SQL_TIME_AVG', perfmon_NumberFormat($arRes['SQL_TIME'] / $arRes['SQL_COUNT'], 6));
	}

	$row->AddViewField('SQL_COUNT', '<a href="perfmon_sql_list.php?lang=' . LANGUAGE_ID . '&amp;set_filter=Y&amp;find_suggest_id=' . $arRes['ID'] . '">' . $arRes['SQL_COUNT'] . '</a>');
	$row->AddViewField('COLUMN_NAMES', str_replace(',', '<br>', $arRes['COLUMN_NAMES']));
	if ($arRes['BANNED'] == 'N')
	{
		$row->AddViewField('BANNED', '<span class="adm-lamp adm-lamp-in-list adm-lamp-green" title="' . htmlspecialcharsbx(GetMessage('PERFMON_INDEX_GREEN_ALT')) . '"></span>');
	}
	elseif ($arRes['BANNED'] == 'Y')
	{
		$row->AddViewField('BANNED', '<span class="adm-lamp adm-lamp-in-list adm-lamp-red" title="' . htmlspecialcharsbx(GetMessage('PERFMON_INDEX_RED_ALT')) . '"></span>');
	}
	else
	{
		$row->AddViewField('BANNED', '<span class="adm-lamp adm-lamp-in-list adm-lamp-yellow" title="' . htmlspecialcharsbx(GetMessage('PERFMON_INDEX_YELLOW_ALT')) . '"></span>');
	}

	$rsQueries = CPerfomanceSQL::GetList(
		['ID'],
		['=SUGGEST_ID' => $arRes['ID']],
		['ID' => 'ASC'],
		false,
		['nTopCount' => 1]
	);
	if ($arQuery = $rsQueries->GetNext())
	{
		$arRes['SQL_ID'] = $arQuery['ID'];
	}
	else
	{
		$arRes['SQL_ID'] = '';
	}

	$html = str_replace(
		[' ', "\n"],
		[' &nbsp;', '<br>'],
		htmlspecialcharsbx($arRes['SQL_TEXT'])
	);

	$html = '<span onmouseover="addTimer(this)" onmouseout="removeTimer(this)" id="' . $arRes['SQL_ID'] . '_sql_backtrace">' . $html . '</span>';
	$row->AddViewField('SQL_TEXT', $html);

	$arActions = [
		[
			'DEFAULT' => 'Y',
			'TEXT' => GetMessage('PERFMON_INDEX_DETAILS'),
			'ACTION' => $lAdmin->ActionRedirect('perfmon_index_detail.php?lang=' . LANG . '&ID=' . $arRes['ID']),
		],
	];

	if ($arRes['SQL_ID'])
	{
		$arActions[] = [
			'TEXT' => GetMessage('PERFMON_INDEX_EXPLAIN'),
			'ACTION' => 'jsUtils.OpenWindow(\'perfmon_explain.php?lang=' . LANG . '&ID=' . $arQuery['ID'] . '\', 600, 500);',
		];
	}

	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	[
		[
			'title' => GetMessage('MAIN_ADMIN_LIST_SELECTED'),
			'value' => $rsData->SelectedRowsCount(),
		],
	]
);

$aContext = [];

if ($go || !CPerfomanceKeeper::IsActive())
{
	$aContext[] = [
		'TEXT' => GetMessage('PERFMON_INDEX_ANALYZE'),
		'LINK' => 'javascript:' . $lAdmin->ActionDoGroup(0, 'analyze_start'),
	];
}

$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage('PERFMON_INDEX_TITLE'));

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

CJSCore::Init(['ajax', 'popup']);
?>
	<script>
		var toolTipCache = new Array;

		function drawTooltip(result, _this)
		{
			if (!_this)
				_this = this;

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

			_this.toolTip.show();
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

		function Analyze()
		{
			var url = 'perfmon_index_list.php?lang=<?php echo LANGUAGE_ID?>&<?php echo bitrix_sessid_get()?>&action=analyze';
			ShowWaitWindow();
			BX.ajax.post(
				url,
				null,
				function (result)
				{
					CloseWaitWindow();
					if (result.length > 0 && result.indexOf("MoveProgress") < 0)
						document.getElementById('progress_message').innerHTML = result;
				}
			);
		}
	</script>
<?php
$lAdmin->DisplayList();

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
