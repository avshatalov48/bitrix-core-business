<?
define("ADMIN_MODULE_NAME", "perfmon");
define("PERFMON_STOP", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/prolog.php");

IncludeModuleLangFile(__FILE__);

$RIGHT = $APPLICATION->GetGroupRight("perfmon");
if ($RIGHT == "D" || $DB->type !== "MYSQL")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$sTableID = "tbl_perfmon_index_list";
$oSort = new CAdminSorting($sTableID, "TABLE_NAME", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);
$go = false;

if ($lAdmin->GroupAction())
{
	switch ($_REQUEST['action'])
	{
	case "analyze_start":
		CPerfomanceIndexSuggest::Clear();
		$last_id = 0;
		$go = true;
		$_SESSION["queries"] = 0;
		break;
	case "analyze_cont":
		$etime = time() + 5;
		$last_id = intval($_REQUEST["last_id"]);
		$sql_cache = array();

		while (time() < $etime)
		{
			$rsSQL = CPerfomanceSQL::GetList(
				array("ID", "SQL_TEXT", "QUERY_TIME"),
				array(">ID" => $last_id),
				array("ID" => "ASC"),
				false,
				array("nTopCount" => 100)
			);
			while ($arSQL = $rsSQL->Fetch())
			{
				$_SESSION["queries"]++;
				$go = true;
				$sql_md5 = md5(CPerfQuery::remove_literals($arSQL["SQL_TEXT"]));

				//Check if did it already on previous steps
				if (!array_key_exists($sql_md5, $sql_cache))
				{
					$sql_cache[$sql_md5] = true;

					$rsInd = CPerfomanceIndexSuggest::GetList(array("SQL_MD5"), array("=SQL_MD5" => $sql_md5), array());
					if ($rsInd->Fetch())
					{
						CPerfomanceIndexSuggest::UpdateStat($sql_md5, 1, $arSQL["QUERY_TIME"], $arSQL["ID"]);
					}
					else
					{
						$arMissedKeys = array();
						$q = new CPerfQuery;
						$strSQL = $q->transform2select($arSQL["SQL_TEXT"]);
						if ($strSQL && $q->parse($strSQL))
						{
							$i = 0;
							$arExplain = array();
							$rsData = $DB->Query("explain ".$strSQL, true);
							if (is_object($rsData))
							{
								while ($arRes = $rsData->Fetch())
								{
									$i++;
									$arExplain[] = $arRes;
									if (
										$arRes["type"] === "ALL"
										&& strlen($arRes["key"]) == 0
										&& is_object($q)
										&& ($i > 1 || $q->has_where($arRes["table"]))
									)
									{
										$missed_keys = $q->suggest_index($arRes["table"]);
										if ($missed_keys)
											$arMissedKeys = array_merge($arMissedKeys, $missed_keys);
										elseif ($q->has_where())
										{
											//Check if it is possible to find missed keys on joined tables
											foreach ($q->table_joins($arRes["table"]) as $alias => $join_columns)
											{
												$missed_keys = $q->suggest_index($alias);
												if ($missed_keys)
													$arMissedKeys = array_merge($arMissedKeys, $missed_keys);
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
								list($alias, $table, $columns) = explode(":", $suggest);
								if (
									!CPerfQueryStat::IsBanned($table, $columns)
									&& !CPerfomanceIndexComplete::IsBanned($table, $columns)
								)
								{
									if (
										CPerfQueryStat::GatherExpressStat($table, $columns, $q)
										&& !CPerfQueryStat::IsSelective($table, $columns, $q)
									)
										CPerfQueryStat::Ban($table, $columns);
									else
									{
										CPerfomanceIndexSuggest::Add(array(
											"TABLE_NAME" => $table,
											"TABLE_ALIAS" => $alias,
											"COLUMN_NAMES" => $columns,
											"SQL_TEXT" => $arSQL["SQL_TEXT"],
											"SQL_MD5" => $sql_md5,
											"SQL_COUNT" => 0,
											"SQL_TIME" => 0,
											"SQL_EXPLAIN" => serialize($arExplain),
										));
									}
								}
							}
							CPerfomanceIndexSuggest::UpdateStat($sql_md5, 1, $arSQL["QUERY_TIME"], $arSQL["ID"]);
						}
					}
				}
				else
				{
					CPerfomanceIndexSuggest::UpdateStat($sql_md5, 1, $arSQL["QUERY_TIME"], $arSQL["ID"]);
				}

				$last_id = $arSQL["ID"];
			}
		}
		break;
	}

	if ($go)
	{
		$lAdmin->BeginPrologContent();
		$message = new CAdminMessage(array(
			"MESSAGE" => GetMessage("PERFMON_INDEX_IN_PROGRESS"),
			"DETAILS" => GetMessage("PERFMON_INDEX_QUERIES_ANALYZED", array("#QUERIES#" => "<b>".intval($_SESSION["queries"])."</b>"))."<br>",
			"HTML" => true,
			"TYPE" => "PROGRESS",
		));
		echo $message->Show();
		?>
		<script>
			<?echo $lAdmin->ActionDoGroup(0, "analyze_cont", "last_id=".$last_id);?>
		</script>
		<?
		$lAdmin->EndPrologContent();
	}
	else
	{
		$lAdmin->BeginPrologContent();
		$message = new CAdminMessage(array(
			"MESSAGE" => GetMessage("PERFMON_INDEX_COMPLETE"),
			"DETAILS" => GetMessage("PERFMON_INDEX_QUERIES_ANALYZED", array("#QUERIES#" => "<b>".intval($_SESSION["queries"])."</b>"))."<br>",
			"HTML" => true,
			"TYPE" => "OK",
		));
		echo $message->Show();
		$lAdmin->EndPrologContent();
	}
}

if (!$go && CPerfomanceKeeper::IsActive())
{
	$lAdmin->BeginPrologContent();
	$message = new CAdminMessage(array(
		"MESSAGE" => GetMessage("PERFMON_INDEX_KEEPER_NOTE_IS_ACTIVE"),
		"DETAILS" => GetMessage("PERFMON_INDEX_KEEPER_NOTE_ANALYZE")."<br>",
		"HTML" => true,
		"TYPE" => "OK",
	));
	echo $message->Show();
	$lAdmin->EndPrologContent();
}

$lAdmin->AddHeaders(array(
	array(
		"id" => "BANNED",
		"content" => GetMessage("PERFMON_INDEX_BANNED"),
		"align" => "center",
		"default" => true,
	),
	array(
		"id" => "TABLE_NAME",
		"content" => GetMessage("PERFMON_INDEX_TABLE_NAME"),
		"default" => true,
		"sort" => "TABLE_NAME",
	),
	array(
		"id" => "COLUMN_NAMES",
		"content" => GetMessage("PERFMON_INDEX_COLUMN_NAMES"),
		"default" => true,
	),
	array(
		"id" => "SQL_COUNT",
		"content" => GetMessage("PERFMON_INDEX_SQL_COUNT"),
		"align" => "right",
		"default" => true,
		"sort" => "SQL_COUNT",
	),
	array(
		"id" => "SQL_TIME_AVG",
		"content" => GetMessage("PERFMON_INDEX_SQL_TIME_AVG"),
		"align" => "right",
		"default" => true,
	),
	array(
		"id" => "SQL_TIME",
		"content" => GetMessage("PERFMON_INDEX_SQL_TIME"),
		"align" => "right",
		"default" => true,
		"sort" => "SQL_TIME",
	),
	array(
		"id" => "SQL_TEXT",
		"content" => GetMessage("PERFMON_INDEX_SQL_TEXT"),
		"default" => true,
	),
));

$arSelectedFields = $lAdmin->GetVisibleHeaderColumns();
if (!is_array($arSelectedFields) || (count($arSelectedFields) < 1))
	$arSelectedFields = array(
		"TABLE_NAME",
		"COLUMN_NAMES",
		"SQL_COUNT",
		"SQL_TIME",
		"SQL_TEXT",
	);
$arSelectedFields[] = "ID";

$cData = new CPerfomanceIndexSuggest;
$rsData = $cData->GetList($arSelectedFields, array("!=BANNED" => "Y"), array($by => $order));

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("PERFMON_INDEX_PAGE")));

while ($arRes = $rsData->NavNext(true, "f_"))
{
	$arRes["SQL_TEXT"] = CPerfomanceSQL::Format($arRes["SQL_TEXT"]);
	$row =& $lAdmin->AddRow($f_NAME, $arRes);

	$row->AddViewField("SQL_TIME", perfmon_NumberFormat($f_SQL_TIME, 6));

	if ($f_SQL_COUNT > 0)
	{
		$row->AddViewField("SQL_TIME_AVG", perfmon_NumberFormat($f_SQL_TIME / $f_SQL_COUNT, 6));
	}

	$row->AddViewField("SQL_COUNT", '<a href="perfmon_sql_list.php?lang='.LANGUAGE_ID.'&amp;set_filter=Y&amp;find_suggest_id='.$f_ID.'">'.$f_SQL_COUNT.'</a>');
	$row->AddViewField("COLUMN_NAMES", str_replace(",", "<br>", $f_COLUMN_NAMES));
	if ($f_BANNED == "N")
		$row->AddViewField("BANNED", '<span class="adm-lamp adm-lamp-in-list adm-lamp-green" title="'.htmlspecialcharsbx(GetMessage("PERFMON_INDEX_GREEN_ALT")).'"></span>');
	elseif ($f_BANNED == "Y")
		$row->AddViewField("BANNED", '<span class="adm-lamp adm-lamp-in-list adm-lamp-red" title="'.htmlspecialcharsbx(GetMessage("PERFMON_INDEX_RED_ALT")).'"></span>');
	else
		$row->AddViewField("BANNED", '<span class="adm-lamp adm-lamp-in-list adm-lamp-yellow" title="'.htmlspecialcharsbx(GetMessage("PERFMON_INDEX_YELLOW_ALT")).'"></span>');

	$rsQueries = CPerfomanceSQL::GetList(
		array("ID"),
		array("=SUGGEST_ID" => $f_ID),
		array("ID" => "ASC"),
		false,
		array("nTopCount" => 1)
	);
	if ($arQuery = $rsQueries->GetNext())
		$f_SQL_ID = $arQuery["ID"];
	else
		$f_SQL_ID = "";

	if (class_exists("geshi") && $f_SQL_TEXT)
	{
		$obGeSHi = new GeSHi($arRes["SQL_TEXT"], 'sql');
		$html = $obGeSHi->parse_code();
	}
	else
	{
		$html = str_replace(
			array(" ", "\n"),
			array(" &nbsp;", "<br>"),
			htmlspecialcharsbx($arRes["SQL_TEXT"])
		);
	}
	$html = '<span onmouseover="addTimer(this)" onmouseout="removeTimer(this)" id="'.$f_SQL_ID.'_sql_backtrace">'.$html.'</span>';
	$row->AddViewField("SQL_TEXT", $html);

	$arActions = array(
		array(
			"DEFAULT" => "Y",
			"TEXT" => GetMessage("PERFMON_INDEX_DETAILS"),
			"ACTION" => $lAdmin->ActionRedirect('perfmon_index_detail.php?lang='.LANG.'&ID='.$f_ID),
		),
	);

	if ($f_SQL_ID)
	{
		$arActions[] = array(
			"TEXT" => GetMessage("PERFMON_INDEX_EXPLAIN"),
			"ACTION" => 'jsUtils.OpenWindow(\'perfmon_explain.php?lang='.LANG.'&ID='.$arQuery["ID"].'\', 600, 500);',
		);
	}

	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $rsData->SelectedRowsCount(),
		),
	)
);

$aContext = array();

if ($go || !CPerfomanceKeeper::IsActive())
{
	$aContext[] = array(
		"TEXT" => GetMessage("PERFMON_INDEX_ANALYZE"),
		"LINK" => "javascript:".$lAdmin->ActionDoGroup(0, "analyze_start"),
	);
}

$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("PERFMON_INDEX_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

CJSCore::Init(array("ajax", "popup"));
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
			var url = 'perfmon_index_list.php?lang=<?echo LANGUAGE_ID?>&<?echo bitrix_sessid_get()?>&action=analyze';
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
<?
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>