<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
IncludeModuleLangFile(__FILE__);

// ************************************************************************
// $main_exec_time, $bShowTime, $bShowStat MUST be defined before include
// ************************************************************************

global $APPLICATION;
$application = \Bitrix\Main\Application::getInstance();
$sqlTracker  = $application->getConnection()->getTracker();

echo CJSCore::Init('admin_interface', true);
?>
<style>
div.bx-debug-content-table tr.heading td {background-color:#E1EEDA;}
div.bx-debug-content-table tr.heading-sort td {white-space:nowrap; border-bottom:solid 1px #dce7ed; cursor:pointer;}
div.bx-debug-content-table tr.cache-row {vertical-align:top;}
div.bx-debug-content-table tr.cache-row td {white-space:nowrap;}
div.bx-debug-content-table tr.heading-bottom td {padding:3px 3px 9px 3px !important;}
div.bx-debug-content-table td {padding-right:4px !important; padding-bottom:4px !important;}
div.bx-debug-content-table td.number {padding-right:4px !important; padding-bottom:4px !important; text-align:right !important; white-space:nowrap !important}
div.bx-debug-content-top {padding:12px; position:relative; top:0px; left:0px; height:120px; overflow:auto; border-bottom:1px solid #D0D0D0;}
</style>
<?
if($bShowTime || $bShowStat || $bShowCacheStat)
{
?>
<div class="bx-component-debug bx-debug-summary">
<?
}

$bShowExtTime = $bShowTime && !defined("ADMIN_SECTION") && $bShowStat;
$DOCUMENT_ROOT_LEN = strlen($_SERVER["DOCUMENT_ROOT"]);

if($bShowExtTime)
{
	$START_EXEC_CURRENT_TIME = microtime();

	list($usec, $sec) = explode(" ", START_EXEC_PROLOG_AFTER_2);
	$PROLOG_AFTER_2 = (float)$sec + (float)$usec;
	list($usec, $sec) = explode(" ", START_EXEC_PROLOG_AFTER_1);
	$PROLOG_AFTER_1 = (float)$sec + (float)$usec;
	$PROLOG_AFTER = $PROLOG_AFTER_2 - $PROLOG_AFTER_1;

	list($usec, $sec) = explode(" ", START_EXEC_AGENTS_2);
	$AGENTS_2 = (float)$sec + (float)$usec;
	list($usec, $sec) = explode(" ", START_EXEC_AGENTS_1);
	$AGENTS_1 = (float)$sec + (float)$usec;
	$AGENTS = $AGENTS_2 - $AGENTS_1;

	list($usec, $sec) = explode(" ", START_EXEC_PROLOG_BEFORE_1);
	$PROLOG_BEFORE_1 = (float)$sec + (float)$usec;
	$PROLOG_BEFORE = $PROLOG_AFTER_1 - $PROLOG_BEFORE_1 - $AGENTS;

	$PROLOG = $PROLOG_AFTER_2 - $PROLOG_BEFORE_1;

	list($usec, $sec) = explode(" ", START_EXEC_EPILOG_BEFORE_1);
	$EPILOG_BEFORE_1 = (float)$sec + (float)$usec;

	$WORK_AREA = $EPILOG_BEFORE_1 - $PROLOG_AFTER_2;

	list($usec, $sec) = explode(" ", START_EXEC_EPILOG_AFTER_1);
	$EPILOG_AFTER_1 = (float)$sec + (float)$usec;

	$EPILOG_BEFORE = $EPILOG_AFTER_1 - $EPILOG_BEFORE_1;

	list($usec, $sec) = explode(" ", $START_EXEC_CURRENT_TIME);
	$CURRENT_TIME = (float)$sec + (float)$usec;

	$EPILOG_AFTER = $CURRENT_TIME - $EPILOG_AFTER_1;

	$EPILOG = $CURRENT_TIME - $EPILOG_BEFORE_1;

	$PAGE = $CURRENT_TIME - $PROLOG_BEFORE_1;

	$arAreas = array(
		"PAGE"          => array("FLT" => array("PB", "AG", "PA", "WA", "EB", "EV", "EA"), "TIME" => $PAGE),
		"PROLOG"        => array("FLT" => array("PB", "AG", "PA"                        ), "TIME" => $PROLOG),
		"PROLOG_BEFORE" => array("FLT" => array("PB"                                    ), "TIME" => $PROLOG_BEFORE),
		"AGENTS"        => array("FLT" => array(      "AG"                              ), "TIME" => $AGENTS),
		"PROLOG_AFTER"  => array("FLT" => array(            "PA"                        ), "TIME" => $PROLOG_AFTER),
		"WORK_AREA"     => array("FLT" => array(                  "WA"                  ), "TIME" => $WORK_AREA),
		"EPILOG"        => array("FLT" => array(                        "EB", "EV", "EA"), "TIME" => $EPILOG),
		"EPILOG_BEFORE" => array("FLT" => array(                        "EB"            ), "TIME" => $EPILOG_BEFORE),
		"EPILOG_AFTER"  => array("FLT" => array(                              "EV", "EA"), "TIME" => $EPILOG_AFTER),
	);

	$j = 1;
	foreach($arAreas as $i => $arArea)
	{
		$arAreas[$i]["NUM"] = $j;
		$j++;

		$arAreas[$i]["TRACE"] = array(
			"PATH" => $APPLICATION->GetCurPage(),
			"QUERY_COUNT" => 0,
			"QUERY_TIME" => 0.0,
			"QUERIES" => array(),
			"TIME" => $arArea["TIME"],
			"COMPONENT_COUNT" => 0,
			"COMPONENT_TIME" => 0.0,
			"COMP_QUERY_COUNT" => 0,
			"COMP_QUERY_TIME" => 0.0,
			"CACHE_SIZE" => 0,
		);
	}

	$state = "PB";
	foreach($sqlTracker->getQueries() as $arQueryDebug)
	{
		if (strlen($arQueryDebug["BX_STATE"]) > 0)
			$state = $arQueryDebug["BX_STATE"];

		foreach($arAreas as $i => $arArea)
		{
			if(in_array($state, $arArea["FLT"]))
			{
				$arAreas[$i]["TRACE"]["QUERY_COUNT"]++;
				$arAreas[$i]["TRACE"]["QUERY_TIME"]+=$arQueryDebug["TIME"];
				//$arAreas[$i]["TRACE"]["QUERIES"][] = $arQueryDebug;
			}
		}
	}

	$state = "PA";
	foreach($APPLICATION->arIncludeDebug as $arIncludeDebug)
	{
		if (strlen($arIncludeDebug["BX_STATE"]) > 0)
			$state = $arIncludeDebug["BX_STATE"];

		foreach($arAreas as $i => $arArea)
		{
			if(in_array($state, $arArea["FLT"]))
			{
				$arAreas[$i]["TRACE"]["TIME"] -= $arIncludeDebug["TIME"];
				$arAreas[$i]["TRACE"]["COMPONENT_COUNT"]++;
				$arAreas[$i]["TRACE"]["COMPONENT_TIME"] += $arIncludeDebug["TIME"];
				$arAreas[$i]["TRACE"]["COMP_QUERY_COUNT"] += $arIncludeDebug["QUERY_COUNT"];
				$arAreas[$i]["TRACE"]["COMP_QUERY_TIME"] += $arIncludeDebug["QUERY_TIME"];
				$arAreas[$i]["TRACE"]["CACHE_SIZE"] += $arIncludeDebug["CACHE_SIZE"];
			}
		}
	}

	$bShowComps = count($APPLICATION->arIncludeDebug) > 0;

	foreach($arAreas as $i => $arArea)
	{
		$arAreas[$i]["IND"] = count($APPLICATION->arIncludeDebug);
		$APPLICATION->arIncludeDebug[]=$arArea["TRACE"];
	}

	echo '<a href="javascript:jsDebugTimeWindow.Show(); jsDebugTimeWindow.ShowDetails(\'BX_DEBUG_TIME_1_1\')">'.GetMessage("debug_info_cr_time").'</a> <span id="bx_main_exec_time">'.round($PAGE, 4).'</span> '.GetMessage("debug_info_sec").'<br>';
}
elseif($bShowTime)
{
	echo GetMessage("debug_info_cr_time").' <span id="bx_main_exec_time">'.round($main_exec_time, 4).'</span> '.GetMessage("debug_info_sec").'<br />';
}

$totalQueryCount = 0;
$totalQueryTime = 0.0;

if($bShowStat || $bShowCacheStat)
{
	if ($bShowStat)
	{
		$totalQueryCount = $sqlTracker->getCounter();
		$totalQueryTime = $sqlTracker->getTime();
		foreach($APPLICATION->arIncludeDebug as $i=>$arIncludeDebug)
		{
			if(array_key_exists("REL_PATH", $arIncludeDebug))
			{
				$totalQueryCount += $arIncludeDebug["QUERY_COUNT"];
				$totalQueryTime += $arIncludeDebug["QUERY_TIME"];
			}
		}
		echo '<a title="'.GetMessage("debug_info_query_title").'" href="javascript:BX_DEBUG_INFO_'.count($APPLICATION->arIncludeDebug).'.Show(); BX_DEBUG_INFO_'.count($APPLICATION->arIncludeDebug).'.ShowDetails(\'BX_DEBUG_INFO_'.count($APPLICATION->arIncludeDebug).'_1\');">'.GetMessage("debug_info_total_queries")."</a> ".intval($totalQueryCount)."<br>";
		echo GetMessage("debug_info_total_time")." ".round($totalQueryTime, 4)." ".GetMessage("debug_info_sec")."<br>";
	}

	if($GLOBALS["CACHE_STAT_BYTES"] || $bShowCacheStat)
	{
		$arCacheDebug = \Bitrix\Main\Diag\CacheTracker::getCacheTracking();
		if (!empty($arCacheDebug))
		{
			echo '<a title="'.GetMessage("debug_info_query_title").'" href="javascript:BX_DEBUG_INFO_CACHE.Show(); BX_DEBUG_INFO_CACHE.ShowDetails(\'BX_DEBUG_INFO_CACHE_m_0\');">'.GetMessage("debug_info_cache_size")."</a> "." ",CFile::FormatSize(\Bitrix\Main\Diag\CacheTracker::getCacheStatBytes(), 0)." (".count($arCacheDebug).")<br>";
		}
		else
		{
			echo GetMessage("debug_info_cache_size")." ",CFile::FormatSize(\Bitrix\Main\Diag\CacheTracker::getCacheStatBytes(), 0)."<br>";
		}
	}
}

if($bShowTime || $bShowStat)
{
	echo '</div><div class="empty"></div>';
}

if ($bShowStat || $bShowCacheStat) //2
{
	$APPLICATION->arIncludeDebug[] = array(
		"PATH" => $APPLICATION->GetCurPage(),
		"QUERY_COUNT" => $totalQueryCount,
		"QUERY_TIME" => round($totalQueryTime, 4),
		"QUERIES" => $sqlTracker,
		"TIME" => $main_exec_time,
	);

	//CJSPopup
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");

	$arCacheDebug = \Bitrix\Main\Diag\CacheTracker::getCacheTracking();
	if (!empty($arCacheDebug))
	{
		?>
		<script type="text/javascript">
			function sortTable(table_id, column_num, reverse)
			{
				var table = BX(table_id);
				var title = table.rows[0].cells[column_num].innerHTML;
				if (title.charCodeAt(0) == 8595)
					reverse = true;
				if (title.charCodeAt(0) == 8593)
					reverse = false;

				for (var i = 1; i < table.rows.length; i++)
				{
					for (var j = 1; j < table.rows.length; j++)
					{
						var a = table.rows[i].cells[column_num].getAttribute('sort')? table.rows[i].cells[column_num].getAttribute('sort'): table.rows[i].cells[column_num].innerHTML;
						var ai = parseInt(a);
						if (ai > 0) a = ai;
						var b = table.rows[j].cells[column_num].getAttribute('sort')? table.rows[j].cells[column_num].getAttribute('sort'): table.rows[j].cells[column_num].innerHTML;
						var bi = parseInt(b);
						if (bi > 0) b = bi;

						if ((!reverse && a < b) || (reverse && a > b))
						{
							table.tBodies[0].insertBefore(table.rows[i], table.rows[j]);
						}
					}
				}

				for (var i = 0; i < table.rows[0].cells.length; i++)
				{

					var title = table.rows[0].cells[i].innerHTML;
					if (
						title.charCodeAt(0) == 8595
						|| title.charCodeAt(0) == 8593
					)
						table.rows[0].cells[i].innerHTML = title.substr(1);

					if (i == column_num)
						table.rows[0].cells[i].innerHTML = (reverse? '&uarr;': '&darr;') + table.rows[0].cells[i].innerHTML;
				}
			}
			BX_DEBUG_INFO_CACHE = new BX.CDebugDialog();
		</script>
		<?
		$obJSPopup = new CJSPopupOnPage('', array());
		$obJSPopup->jsPopup = 'BX_DEBUG_INFO_CACHE';
		$obJSPopup->StartDescription('bx-debug-window');
		?>
		<p><?echo GetMessage("debug_info_cache_size")?> <?=CFile::FormatSize(\Bitrix\Main\Diag\CacheTracker::getCacheStatBytes(), 0)?></p>
		<?
		$obJSPopup->StartContent(array('buffer' => true));
		?>
		<div class="bx-debug-content bx-debug-content-table">
			<table id="cacheDebug" cellpadding="2" cellspacing="0" border="0">
				<tr class="heading-sort">
					<td onclick="sortTable('cacheDebug', 0)">&darr;&nbsp;</td>
					<td onclick="sortTable('cacheDebug', 1)"><?echo GetMessage("debug_info_cache_table_func");?></td>
					<td onclick="sortTable('cacheDebug', 2, true)"><?echo GetMessage("debug_info_cache_table_size");?></td>
					<td onclick="sortTable('cacheDebug', 3)"><?echo GetMessage("debug_info_cache_file_path");?></td>
				</tr>
				<?
				foreach($arCacheDebug as $j => $cacheDebug)
				{
					if (substr($cacheDebug["path"], 0, $DOCUMENT_ROOT_LEN) === $_SERVER["DOCUMENT_ROOT"])
						$path = '<a target="blank" href="/bitrix/admin/fileman_file_view.php?path='.urlencode(substr($cacheDebug["path"], $DOCUMENT_ROOT_LEN)).'&lang='.LANGUAGE_ID.'">'.htmlspecialcharsEx(substr($cacheDebug["path"], $DOCUMENT_ROOT_LEN)).'</a>';
					else
						$path = '&nbsp;';
				?>
				<tr class="cache-row">
					<td class="number"><?echo $j+1?></td>
					<td><a href="javascript:BX_DEBUG_INFO_CACHE.ShowDetails('BX_DEBUG_INFO_CACHE_m_<?=$j?>')"><?echo $cacheDebug["callee_func"]?></a></td>
					<td class="number" sort="<?echo $cacheDebug["cache_size"]?>"><?=CFile::FormatSize($cacheDebug["cache_size"], 0)?></td>
					<td><?=$path?></td>
				</tr>
				<?
				}
				?>
			</table>
		</div>#DIVIDER#<div class="bx-debug-content bx-debug-content-details">
			<?
				foreach($arCacheDebug as $j => $cacheDebug)
				{
					?><div id="BX_DEBUG_INFO_CACHE_m_<?=$j?>" style="display:none">
					<b><?echo GetMessage("debug_info_query_from")?></b>
					<?
					$k=1;
					foreach($cacheDebug["TRACE"] as $n => $tr)
					{
						?>
						<br /><br />
						<b>(<?echo ($n + 1)?>)</b>
						<?
						echo $tr["file"].":".$tr["line"]."<br /><nobr>".htmlspecialcharsbx($tr["func"]);
						if($n == 0)
							echo "(...)</nobr>";
						else
							echo "</nobr>(".htmlspecialcharsbx(print_r($tr["args"], true)).")";
					} //$back_trace
					?></div>
					<?
				}; // $arQueries
				?>
			</div>
			<?
		$obJSPopup->StartButtons();
		$obJSPopup->ShowStandardButtons(array('close'));
	}
?>
		<script type="text/javascript">
			var tableRows;
			function filterTable(input, table_id, column_num)
			{
				var table = BX(table_id);
				for (var i = 0; i < table.rows.length; i++)
				{
					var sql = table.rows[i].cells[column_num].innerHTML;
					if (input.value.length > 0 && sql.indexOf(input.value) == -1)
						table.rows[i].style.display = 'none';
					else
						table.rows[i].style.display = 'block';
				}
			}
			BX_DEBUG_INFO_CACHE = new BX.CDebugDialog();
		</script>
<?
	foreach($APPLICATION->arIncludeDebug as $i=>$arIncludeDebug)
	{
		?>
		<script type="text/javascript">
			BX_DEBUG_INFO_<?=$i?> = new BX.CDebugDialog();
		</script>
		<?
		$obJSPopup = new CJSPopupOnPage('', array());
		$obJSPopup->jsPopup = 'BX_DEBUG_INFO_'.$i;
		$obJSPopup->StartDescription('bx-core-debug-info');
		?>
		<p><?echo GetMessage("debug_info_path")?> <?=$arIncludeDebug["PATH"]?></p>
		<p><?echo GetMessage("debug_info_time")?> <?=$arIncludeDebug["TIME"]?> <?echo GetMessage("debug_info_sec")?></p>
		<p><?echo GetMessage("debug_info_queries")?> <?=$arIncludeDebug["QUERY_COUNT"]?>, <?echo GetMessage("debug_info_time1")?> <?=$arIncludeDebug["QUERY_TIME"]?> <?echo GetMessage("debug_info_sec")?><?if($arIncludeDebug["TIME"] > 0):?> (<?=round($arIncludeDebug["QUERY_TIME"]/$arIncludeDebug["TIME"]*100, 2)?>%)<?endif?></p>
		<p><?echo GetMessage("debug_info_search")?>: <input type="text" style="height:16px" onkeydown="filterTable(this, 'queryDebug<?echo $i?>', 1)" onpaste="filterTable(this, 'queryDebug<?echo $i?>', 1)" oninput="filterTable(this, 'queryDebug<?echo $i?>', 1)"></p>
		<?
		$obJSPopup->StartContent(array('buffer' => true));
		if(count($arIncludeDebug["QUERIES"]) > 0)
		{
			?><div class="bx-debug-content bx-debug-content-table"><?
				$arQueries = array();
				foreach($arIncludeDebug["QUERIES"] as $j=>$arQueryDebug)
				{
					$strSql = $arQueryDebug["QUERY"];
					$arQueries[$strSql]["COUNT"]++;
					$arQueries[$strSql]["CALLS"][] = array(
						"TIME"=>$arQueryDebug["TIME"],
						"TRACE"=>$arQueryDebug["TRACE"]
					);
				}
				?><table id="queryDebug<?echo $i?>" cellpadding="0" cellspacing="0" border="0"><?
					$j = 1;
					foreach($arQueries as $strSql=>$query)
					{
						?><tr>
							<td class="number" valign="top"><?echo $j?></td>
							<td><a href="javascript:BX_DEBUG_INFO_<?=$i?>.ShowDetails('BX_DEBUG_INFO_<?=$i."_".$j?>')"><?echo htmlspecialcharsbx(substr($strSql, 0, 100))."..."?></a>&nbsp;(<?echo $query["COUNT"]?>) </td>
							<td class="number" valign="top"><?
								$t = 0.0;
								foreach($query["CALLS"] as $call)
									$t += $call["TIME"];
								echo number_format($t/$query["COUNT"], 5);
							?></td>
						</tr><?
						$j++;
					} //$arQueries
				?></table>
			</div>#DIVIDER#<div class="bx-debug-content bx-debug-content-details">
			<?
				$j = 1;
				foreach($arQueries as $strSql=>$query)
				{
					?><div id="BX_DEBUG_INFO_<?=$i."_".$j?>" style="display:none">
					<b><?echo GetMessage("debug_info_query")?> <?echo $j?>:</b>
					<br /><br />
					<?
					$strSql = preg_replace("/[\\n\\r\\t\\s ]+/", " ", $strSql);
					$strSql = preg_replace("/^ +/", "", $strSql);
					$strSql = preg_replace("/ (INNER JOIN|OUTER JOIN|LEFT JOIN|SET|LIMIT) /i", "\n\\1 ", $strSql);
					$strSql = preg_replace("/(INSERT INTO [A-Z_0-1]+?)\\s/i", "\\1\n", $strSql);
					$strSql = preg_replace("/(INSERT INTO [A-Z_0-1]+?)([(])/i", "\\1\n\\2", $strSql);
					$strSql = preg_replace("/([\\s)])(VALUES)([\\s(])/i", "\\1\n\\2\n\\3", $strSql);
					$strSql = preg_replace("/ (FROM|WHERE|ORDER BY|GROUP BY|HAVING) /i", "\n\\1\n", $strSql);
						echo str_replace(
							array("\n"),
							array("<br />"),
							htmlspecialcharsbx($strSql)
						);
					?>
					<br /><br />
					<b><?echo GetMessage("debug_info_query_from")?></b>
					<?
					$k=1;
					foreach($query["CALLS"] as $call)
					{
						$back_trace = $call["TRACE"];

						if(is_array($back_trace))
						{
							foreach($back_trace as $n=>$tr)
							{
								?>
								<br /><br />
								<b>(<?echo $k.".".($n+1)?>)</b>
								<?
								echo $tr["file"].":".$tr["line"]."<br /><nobr>".htmlspecialcharsbx($tr["class"].$tr["type"].$tr["function"]);
								if($n == 0)
									echo "(...)</nobr>";
								else
									echo "</nobr>(".htmlspecialcharsbx(print_r($tr["args"], true)).")";
								if($n > 3)
									break;
							} //$back_trace
						}
						else //is_array($back_trace)
						{
							?>
							<br /><br />
							<b>(<?echo $k?>)</b> <?echo GetMessage("debug_info_query_from_unknown")?>
							<?
						} //is_array($back_trace)
						?>
						<br /><br />
						<?echo GetMessage("debug_info_query_time")?> <?echo round($call["TIME"], 5)?> <?echo GetMessage("debug_info_sec")?>
						<?
						$k++;
					} //$query["CALLS"]
					?></div>
					<?
					$j++;
				}; // $arQueries
				?>
			</div>
			<?
		} //if(count($arIncludeDebug["QUERIES"])>0)
		$obJSPopup->StartButtons();
		$obJSPopup->ShowStandardButtons(array('close'));

		/*************************************CACHE*********************************************/
		?>
		<script type="text/javascript">
			BX_DEBUG_INFO_CACHE_<?=$i?> = new BX.CDebugDialog();
		</script>
		<?
		$obJSPopup = new CJSPopupOnPage('', array());
		$obJSPopup->jsPopup = 'BX_DEBUG_INFO_CACHE_'.$i;
		$obJSPopup->StartDescription('bx-core-debug-info');
		?>
		<p><?echo GetMessage("debug_info_cache_size")?> <?=CFile::FormatSize($arIncludeDebug["CACHE_SIZE"], 0)?></p>
		<?
		$obJSPopup->StartContent(array('buffer' => true));
		if($arIncludeDebug["CACHE"] && !empty($arIncludeDebug["CACHE"]))
		{
			?>
			<div class="bx-debug-content bx-debug-content-table">
				<table id="cacheDebug<?=$i?>" cellpadding="2" cellspacing="0" border="0">
					<tr class="heading-sort">
						<td onclick="sortTable('cacheDebug<?=$i?>', 0)">&darr;&nbsp;</td>
						<td onclick="sortTable('cacheDebug<?=$i?>', 1)"><?echo GetMessage("debug_info_cache_table_func");?></td>
						<td onclick="sortTable('cacheDebug<?=$i?>', 2, true)"><?echo GetMessage("debug_info_cache_table_size");?></td>
						<td onclick="sortTable('cacheDebug', 3)"><?echo GetMessage("debug_info_cache_file_path");?></td>
					</tr>
					<?
					foreach($arIncludeDebug["CACHE"] as $j => $cacheDebug)
					{
						if (substr($cacheDebug["path"], 0, $DOCUMENT_ROOT_LEN) === $_SERVER["DOCUMENT_ROOT"])
							$path = '<a target="blank" href="/bitrix/admin/fileman_file_view.php?path='.urlencode(substr($cacheDebug["path"], $DOCUMENT_ROOT_LEN)).'&lang='.LANGUAGE_ID.'">'.htmlspecialcharsEx(substr($cacheDebug["path"], $DOCUMENT_ROOT_LEN)).'</a>';
						else
							$path = '&nbsp;';
					?>
					<tr class="cache-row">
						<td class="number"><?echo $j+1?></td>
						<td><a href="javascript:BX_DEBUG_INFO_CACHE_<?=$i?>.ShowDetails('BX_DEBUG_INFO_CACHE_<?=$i."_".$j?>')"><?echo $cacheDebug["callee_func"]?></a></td>
						<td class="number" sort="<?echo $cacheDebug["cache_size"]?>"><?=CFile::FormatSize($cacheDebug["cache_size"], 0)?></td>
						<td><?=$path?></td>
					</tr>
					<?
					}
					?>
				</table>
			</div>#DIVIDER#<div class="bx-debug-content bx-debug-content-details">
			<?
				foreach($arIncludeDebug["CACHE"] as $j => $cacheDebug)
				{
					?><div id="BX_DEBUG_INFO_CACHE_<?=$i?>_<?=$j?>" style="display:none">
					<b><?echo GetMessage("debug_info_query_from")?></b>
					<?
					$k=1;
					foreach($cacheDebug["TRACE"] as $n => $tr)
					{
						?>
						<br /><br />
						<b>(<?echo ($n + 1)?>)</b>
						<?
						echo $tr["file"].":".$tr["line"]."<br /><nobr>".htmlspecialcharsbx($tr["func"]);
						if($n == 0)
							echo "(...)</nobr>";
						else
							echo "</nobr>(".htmlspecialcharsbx(print_r($tr["args"], true)).")";
					} //$back_trace
					?></div>
					<?
				}; // $arQueries
				?>
			</div>
			<?
		} //if($arIncludeDebug["CACHE"])
		$obJSPopup->StartButtons();
		$obJSPopup->ShowStandardButtons(array('close'));
	} //$APPLICATION->arIncludeDebug
} //$bShowStat 2

if($bShowExtTime)
{
	$obJSPopup = new CJSPopupOnPage();
	$obJSPopup->jsPopup = 'jsDebugTimeWindow';
?>
<script type="text/javascript">
var jsDebugTimeWindow = new BX.CDebugDialog();
</script>
<div id="BX_DEBUG_TIME" class="bx-debug-window" style="z-index:99; width:660px !important;">
<?
	$obJSPopup->StartDescription('bx-core-debug-info');
?>
	<p><?echo GetMessage("debug_info_page")?> <?=$APPLICATION->GetCurPage()?></p>
	<p><?echo GetMessage("debug_info_comps_cache")?> <?if(COption::GetOptionString("main", "component_cache_on", "Y")=="Y") echo GetMessage("debug_info_comps_cache_on"); else echo "<a href=\"/bitrix/admin/cache.php\"><font class=\"errortext\">".GetMessage("debug_info_comps_cache_off")."</font></a>";?>.</p>
	<p><?
	if(\Bitrix\Main\Data\Cache::getShowCacheStat())
		echo GetMessage("debug_info_cache_size")." ",CFile::FormatSize(\Bitrix\Main\Diag\CacheTracker::getCacheStatBytes(), 0);
	else
		echo "&nbsp;";
	?></p>
<?
	$obJSPopup->StartContent(array('buffer' => true));
?>
	<div id="BX_DEBUG_TIME_1">
		<div class="bx-debug-content bx-debug-content-table">

<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr class="heading">
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td class="number" nowrap>
			<span><?echo GetMessage("debug_info_page_exec")?></span>
		</td>
		<td class="number" nowrap>
			<span><?echo GetMessage("debug_info_sec")?></span>
		</td>
		<?if($bShowComps):?>
			<td class="number" nowrap>
				<span><?echo GetMessage("debug_info_comps_exec")?></span>
			</td>
			<td class="number" nowrap>
				<span><?echo GetMessage("debug_info_sec")?></span>
			</td>
		<?endif;?>
		<?if($bShowStat):?>
			<td class="number" nowrap>
				<span><?echo GetMessage("debug_info_queries_exec")?></span>
			</td>
			<td class="number" nowrap>
				<span><?echo GetMessage("debug_info_sec")?></span>
			</td>
		<?endif;?>
		<td class="heading">&nbsp;</td>
	</tr>
	<tr class="heading heading-bottom">
		<td>&nbsp;</td>
		<td>
			<?if($bShowComps):?>
				<a style="font-weight:bold !important" href="javascript:jsDebugTimeWindow.ShowDetails('BX_DEBUG_TIME_1_1')"><?echo GetMessage("debug_info_whole_page")?></a>
			<?else:?>
				<b><?echo GetMessage("debug_info_whole_page")?></b>
			<?endif?>
		</td>
		<td class="number" nowrap>
			<b><?echo number_format($PAGE/$PAGE*100, 2),"%"?></b>
		</td>
		<td class="number" nowrap>
			<b><?echo number_format($PAGE, 4)?></b>
		</td>
		<?if($bShowComps):?>
			<td class="number" nowrap>
				<b><?echo intval($arAreas["PAGE"]["TRACE"]["COMPONENT_COUNT"])?></b>
			</td>
			<td class="number" nowrap>
				<b><?echo number_format($arAreas["PAGE"]["TRACE"]["COMPONENT_TIME"], 4)?></b>
			</td>
		<?endif;?>
		<?if($bShowStat):?>
			<td class="number" nowrap>
				<b><?echo $arAreas["PAGE"]["TRACE"]["QUERY_COUNT"]+$arAreas["PAGE"]["TRACE"]["COMP_QUERY_COUNT"]?></b>
			</td>
			<td class="number" nowrap>
				<b><?echo number_format($arAreas["PAGE"]["TRACE"]["QUERY_TIME"]+$arAreas["PAGE"]["TRACE"]["COMP_QUERY_TIME"], 4)?></b>
			</td>
		<?endif;?>
		<td class="heading">&nbsp;</td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td>
			<?if($bShowComps):?>
				<p><a style="font-weight:bold !important" href="javascript:jsDebugTimeWindow.ShowDetails('BX_DEBUG_TIME_1_2')"><?echo GetMessage("debug_info_prolog")?></a></p>
				<p>
					&nbsp;&nbsp;<a href="javascript:jsDebugTimeWindow.ShowDetails('BX_DEBUG_TIME_1_3')"><?echo GetMessage("debug_info_prolog_before")?></a><br>
					&nbsp;&nbsp;<a href="javascript:jsDebugTimeWindow.ShowDetails('BX_DEBUG_TIME_1_4')"><?echo GetMessage("debug_info_agents")?></a><br>
					&nbsp;&nbsp;<a href="javascript:jsDebugTimeWindow.ShowDetails('BX_DEBUG_TIME_1_5')"><?echo GetMessage("debug_info_prolog_after")?></a><br>
				</p>
			<?else:?>
				<p><b><?echo GetMessage("debug_info_prolog")?></b></p>
				<p>
					&nbsp;&nbsp;<?echo GetMessage("debug_info_prolog_before")?><br>
					&nbsp;&nbsp;<?echo GetMessage("debug_info_agents")?><br>
					&nbsp;&nbsp;<?echo GetMessage("debug_info_prolog_after")?><br>
				</p>
			<?endif?>
		</td>
		<td class="number" nowrap>
			<p><b><?echo number_format($PROLOG/$PAGE*100, 2),"%"?></b></p>
			<p>
				<?echo number_format($PROLOG_BEFORE/$PAGE*100, 2),"%"?><br>
				<?echo number_format($AGENTS/$PAGE*100, 2),"%"?><br>
				<?echo number_format($PROLOG_AFTER/$PAGE*100, 2),"%"?><br>
			</p>
		</td>
		<td class="number" nowrap>
			<p><b><?echo number_format($PROLOG, 4)?></b></p>
			<p>
				<?echo number_format($PROLOG_BEFORE, 4)?><br>
				<?echo number_format($AGENTS, 4)?><br>
				<?echo number_format($PROLOG_AFTER, 4)?><br>
			</p>
		</td>
		<?if($bShowComps):?>
			<td class="number" nowrap>
				<p><b><?echo intval($arAreas["PROLOG"]["TRACE"]["COMPONENT_COUNT"])?></b></p>
				<p>
					<?echo intval($arAreas["PROLOG_BEFORE"]["TRACE"]["COMPONENT_COUNT"])?><br>
					<?echo intval($arAreas["AGENTS"]["TRACE"]["COMPONENT_COUNT"])?><br>
					<?echo intval($arAreas["PROLOG_AFTER"]["TRACE"]["COMPONENT_COUNT"])?><br>
				</p>
			</td>
			<td class="number" nowrap>
				<p><b><?echo number_format($arAreas["PROLOG"]["TRACE"]["COMPONENT_TIME"], 4)?></b></p>
				<p>
					<?echo number_format($arAreas["PROLOG_BEFORE"]["TRACE"]["COMPONENT_TIME"], 4)?><br>
					<?echo number_format($arAreas["AGENTS"]["TRACE"]["COMPONENT_TIME"], 4)?><br>
					<?echo number_format($arAreas["PROLOG_AFTER"]["TRACE"]["COMPONENT_TIME"], 4)?><br>
				</p>
			</td>
		<?endif;?>
		<?if($bShowStat):?>
			<td class="number" nowrap>
				<p><b><?echo $arAreas["PROLOG"]["TRACE"]["QUERY_COUNT"]+$arAreas["PROLOG"]["TRACE"]["COMP_QUERY_COUNT"]?></b></p>
				<p>
					<?echo $arAreas["PROLOG_BEFORE"]["TRACE"]["QUERY_COUNT"]+$arAreas["PROLOG_BEFORE"]["TRACE"]["COMP_QUERY_COUNT"]?><br>
					<?echo $arAreas["AGENTS"]["TRACE"]["QUERY_COUNT"]+$arAreas["AGENTS"]["TRACE"]["COMP_QUERY_COUNT"]?><br>
					<?echo $arAreas["PROLOG_AFTER"]["TRACE"]["QUERY_COUNT"]+$arAreas["PROLOG_AFTER"]["TRACE"]["COMP_QUERY_COUNT"]?><br>
				</p>
			</td>
			<td class="number" nowrap>
				<p><b><?echo number_format($arAreas["PROLOG"]["TRACE"]["QUERY_TIME"]+$arAreas["PROLOG"]["TRACE"]["COMP_QUERY_TIME"], 4)?></b></p>
				<p>
					<?echo number_format($arAreas["PROLOG_BEFORE"]["TRACE"]["QUERY_TIME"]+$arAreas["PROLOG_BEFORE"]["TRACE"]["COMP_QUERY_TIME"], 4)?><br>
					<?echo number_format($arAreas["AGENTS"]["TRACE"]["QUERY_TIME"]+$arAreas["AGENTS"]["TRACE"]["COMP_QUERY_TIME"], 4)?><br>
					<?echo number_format($arAreas["PROLOG_AFTER"]["TRACE"]["QUERY_TIME"]+$arAreas["PROLOG_AFTER"]["TRACE"]["COMP_QUERY_TIME"], 4)?><br>
				</p>
			</td>
		<?endif;?>
		<td>&nbsp;</td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td>
			<?if($bShowComps):?>
				<p><a style="font-weight:bold !important" href="javascript:jsDebugTimeWindow.ShowDetails('BX_DEBUG_TIME_1_6')"><?echo GetMessage("debug_info_work_area")?></a></p>
			<?else:?>
				<p><b><?echo GetMessage("debug_info_work_area")?></b></p>
			<?endif?>
		</td>
		<td class="number" nowrap>
			<p><b><?echo number_format($WORK_AREA/$PAGE*100, 2),"%"?></b></p>
		</td>
		<td class="number" nowrap>
			<p><b><?echo number_format($WORK_AREA, 4)?></b></p>
		</td>
		<?if($bShowComps):?>
			<td class="number" nowrap>
				<b><?echo intval($arAreas["WORK_AREA"]["TRACE"]["COMPONENT_COUNT"])?></b>
			</td>
			<td class="number" nowrap>
				<b><?echo number_format($arAreas["WORK_AREA"]["TRACE"]["COMPONENT_TIME"], 4)?></b>
			</td>
		<?endif;?>
		<?if($bShowStat):?>
			<td class="number" nowrap>
				<p><b><?echo $arAreas["WORK_AREA"]["TRACE"]["QUERY_COUNT"]+$arAreas["WORK_AREA"]["TRACE"]["COMP_QUERY_COUNT"]?></b></p>
			</td>
			<td class="number" nowrap>
				<p><b><?echo number_format($arAreas["WORK_AREA"]["TRACE"]["QUERY_TIME"]+$arAreas["WORK_AREA"]["TRACE"]["COMP_QUERY_TIME"], 4)?></b></p>
			</td>
		<?endif;?>
		<td>&nbsp;</td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td>
			<?if($bShowComps):?>
				<p><a style="font-weight:bold !important" href="javascript:jsDebugTimeWindow.ShowDetails('BX_DEBUG_TIME_1_7')"><?echo GetMessage("debug_info_epilog")?></a></p>
				<p>
					&nbsp;&nbsp;<a href="javascript:jsDebugTimeWindow.ShowDetails('BX_DEBUG_TIME_1_8')"><?echo GetMessage("debug_info_epilog_before")?></a><br>
					&nbsp;&nbsp;<a href="javascript:jsDebugTimeWindow.ShowDetails('BX_DEBUG_TIME_1_9')"><?echo GetMessage("debug_info_epilog_after")?></a><br>
				</p>
			<?else:?>
				<p><b><?echo GetMessage("debug_info_epilog")?></b></p>
				<p>
					&nbsp;&nbsp;<?echo GetMessage("debug_info_epilog_before")?><br>
					&nbsp;&nbsp;<?echo GetMessage("debug_info_epilog_after")?><br>
				</p>
			<?endif?>
		</td>
		<td class="number" nowrap>
			<p><b><?echo number_format($EPILOG/$PAGE*100, 2),"%"?></b></p>
			<p>
				<?echo number_format($EPILOG_BEFORE/$PAGE*100, 2),"%"?><br>
				<?echo number_format($EPILOG_AFTER/$PAGE*100, 2),"%"?><br>
			</p>
		</td>
		<td class="number" nowrap>
			<p><b><?echo number_format($EPILOG, 4)?></b></p>
			<p>
				<?echo number_format($EPILOG_BEFORE, 4)?><br>
				<?echo number_format($EPILOG_AFTER, 4)?><br>
			</p>
		</td>
		<?if($bShowComps):?>
			<td class="number" nowrap>
				<p><b><?echo intval($arAreas["EPILOG"]["TRACE"]["COMPONENT_COUNT"])?></b></p>
				<p>
					<?echo intval($arAreas["EPILOG_BEFORE"]["TRACE"]["COMPONENT_COUNT"])?><br>
					<?echo intval($arAreas["EPILOG_AFTER"]["TRACE"]["COMPONENT_COUNT"])?><br>
				</p>
			</td>
			<td class="number" nowrap>
				<p><b><?echo number_format($arAreas["EPILOG"]["TRACE"]["COMPONENT_TIME"], 4)?></b></p>
				<p>
					<?echo number_format($arAreas["EPILOG_BEFORE"]["TRACE"]["COMPONENT_TIME"], 4)?><br>
					<?echo number_format($arAreas["EPILOG_AFTER"]["TRACE"]["COMPONENT_TIME"], 4)?><br>
				</p>
			</td>
		<?endif;?>
		<?if($bShowStat):?>
			<td class="number" nowrap>
				<p><b><?echo $arAreas["EPILOG"]["TRACE"]["QUERY_COUNT"]+$arAreas["EPILOG"]["TRACE"]["COMP_QUERY_COUNT"]?></b></p>
				<p>
					<?echo $arAreas["EPILOG_BEFORE"]["TRACE"]["QUERY_COUNT"]+$arAreas["EPILOG_BEFORE"]["TRACE"]["COMP_QUERY_COUNT"]?><br>
					<?echo $arAreas["EPILOG_AFTER"]["TRACE"]["QUERY_COUNT"]+$arAreas["EPILOG_AFTER"]["TRACE"]["COMP_QUERY_COUNT"]?><br>
				</p>
			</td>
			<td class="number" nowrap>
				<p><b><?echo number_format($arAreas["EPILOG"]["TRACE"]["QUERY_TIME"]+$arAreas["EPILOG"]["TRACE"]["COMP_QUERY_TIME"], 4)?></b></p>
				<p>
					<?echo number_format($arAreas["EPILOG_BEFORE"]["TRACE"]["QUERY_TIME"]+$arAreas["EPILOG_BEFORE"]["TRACE"]["COMP_QUERY_TIME"], 4)?><br>
					<?echo number_format($arAreas["EPILOG_AFTER"]["TRACE"]["QUERY_TIME"]+$arAreas["EPILOG_AFTER"]["TRACE"]["COMP_QUERY_TIME"], 4)?><br>
				</p>
			</td>
		<?endif;?>
		<td>&nbsp;</td>
	</tr>
</table>

		</div>
	</div>#DIVIDER#<?if($bShowComps):?><div class="bx-debug-content bx-debug-content-table">
			<?foreach($arAreas as $id => $arArea):?>
			<div id="BX_DEBUG_TIME_1_<?echo $arArea["NUM"]?>" style="display:none">
				<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<?
				$tim = 0;
				foreach($APPLICATION->arIncludeDebug as $i=>$arIncludeDebug)
				{
					if(isset($arIncludeDebug["REL_PATH"]) && in_array($arIncludeDebug["BX_STATE"], $arArea["FLT"]))
					{
						$tim += $arIncludeDebug["TIME"];
					}
				}
				if($tim > $arArea["TIME"]) $tim = $arArea["TIME"];
				?>
					<tr>
						<td class="number" valign="top">0</td>
						<td>
						<?if($bShowStat):?>
							<a title="<?echo GetMessage("debug_info_query_title")?>" href="javascript:BX_DEBUG_INFO_<?echo $arArea["IND"]?>.Show(); BX_DEBUG_INFO_<?echo $arArea['IND']?>.ShowDetails('BX_DEBUG_INFO_<?echo $arArea['IND']?>_1');"><?echo GetMessage("debug_info_raw_code")?></a>
						<?else:?>
							<?echo GetMessage("debug_info_raw_code")?>
						<?endif?>
						</td>
						<td>&nbsp;</td>
						<td class="number">&nbsp;<?
							if($arArea["TRACE"]["CACHE_SIZE"])
								echo CFile::FormatSize($arArea["TRACE"]["CACHE_SIZE"],0);
						?></td>
						<td class="number"><?if($arArea["TIME"] > 0):?><?echo number_format((1-$tim/$arArea["TIME"])*100, 2)?>%<?endif?></td>
						<td class="number"><?echo number_format($arArea["TIME"] - $tim, 4)?> <?echo GetMessage("debug_info_sec")?></td>
						<td class="number"><?echo intval($arArea["TRACE"]["QUERY_COUNT"])?> <?echo GetMessage("debug_info_query_short")?></td>
						<td class="number"><?echo number_format($arArea["TRACE"]["QUERY_TIME"], 4)?> <?echo GetMessage("debug_info_sec")?></td>
					</tr>
				<?$j=1;$k=1;foreach($APPLICATION->arIncludeDebug as $i=>$arIncludeDebug):?>
					<?if(isset($arIncludeDebug["REL_PATH"]) && in_array($arIncludeDebug["BX_STATE"], $arArea["FLT"])):?>
					<tr>
						<td class="number" valign="top"><?echo $k?></td>
						<td>
						<?if($arIncludeDebug["LEVEL"] > 0) echo str_repeat("&nbsp;&nbsp;", $arIncludeDebug["LEVEL"]);?>
						<?if($bShowStat):?>
							<a title="<?echo GetMessage("debug_info_query_title")?>" href="javascript:BX_DEBUG_INFO_<?echo $i?>.Show(); BX_DEBUG_INFO_<?echo $i?>.ShowDetails('BX_DEBUG_INFO_<?echo $i?>_1');"><?echo htmlspecialcharsbx($arIncludeDebug["REL_PATH"])?></a>
						<?else:?>
							<?echo htmlspecialcharsbx($arIncludeDebug["REL_PATH"])?>
						<?endif?>
						</td>
						<td>&nbsp;<?
							switch($arIncludeDebug["CACHE_TYPE"])
							{
								case "N": echo GetMessage("debug_info_cache_off"); break;
								case "Y": echo GetMessage("debug_info_cache_on"); break;
								default: echo GetMessage("debug_info_cache_auto"); break;
							}
						?></td>
						<td class="number" nowrap>&nbsp;<?
							if($arIncludeDebug["CACHE_SIZE"])
								echo CFile::FormatSize($arIncludeDebug["CACHE_SIZE"],0);
						?></td>
						<td class="number" nowrap><?if($arArea["TIME"] > 0):?><?echo number_format($arIncludeDebug["TIME"]/$arArea["TIME"]*100, 2)?>%<?endif?></td>
						<td class="number" nowrap><?echo number_format($arIncludeDebug["TIME"], 4)?> <?echo GetMessage("debug_info_sec")?></td>
						<td class="number" nowrap><?echo intval($arIncludeDebug["QUERY_COUNT"])?> <?echo GetMessage("debug_info_query_short")?></td>
						<td class="number" nowrap><?echo number_format($arIncludeDebug["QUERY_TIME"], 4)?> <?echo GetMessage("debug_info_sec")?></td>
					</tr>
					<?$k++;endif;?>
				<?$j++;endforeach;?>
				</table>
			</div>
			<?endforeach;?>
		</div>
		<?endif;?>
<?
	$obJSPopup->StartButtons();
	$obJSPopup->ShowStandardButtons(array('close'));
?>
</div>
<?
	if(
		$_GET["show_sql_stat"] === "Y"
		&& $_GET["show_page_exec_time"] === "Y"
		&& $_GET["show_sql_stat_immediate"] === "Y"
		&& preg_match("#/admin/perfmon_hit_list.php#", $_SERVER["HTTP_REFERER"])
	)
		echo "<script>BX.ready(function() {jsDebugTimeWindow.Show(); jsDebugTimeWindow.ShowDetails('BX_DEBUG_TIME_1_1');});</script>";
}
?>