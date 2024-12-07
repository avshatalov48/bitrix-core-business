<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

use Bitrix\Main\Text\HtmlFilter;
/********************** Check user access rights ***********************/

if (!$USER->CanDoOperation('seo_tools'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$io = CBXVirtualIo::GetInstance();

$path = "/";
if (isset($_REQUEST["path"]) && $_REQUEST["path"] <> '')
{
	$path = $_REQUEST["path"];
	$path = $io->CombinePath("/", $path);
}

//Page path
$documentRoot = CSite::GetSiteDocRoot($_REQUEST['site']);
$absoluteFilePath = $documentRoot.$path;

if (false !== ($pos = mb_strrpos($absoluteFilePath, '/')))
{
	$absoluteDirPath = mb_substr($absoluteFilePath, 0, $pos);
}

$bReadOnly = false;

// this rights check is temporary disabled. it's fileman rights, we don't have to take a look on'em...
// if (IsModuleInstalled("fileman"))
// {
	// if (!$USER->CanDoOperation('fileman_admin_files') && !$USER->CanDoOperation('fileman_edit_existent_files'))
		// $bReadOnly = true;
// }

IncludeModuleLangFile(__FILE__);

/** @var $DB CDatabase */
global $DB;

//Check permissions
if (!$io->FileExists($absoluteFilePath))
{
	CAdminMessage::ShowMessage(GetMessage('SEO_TOOLS_ERROR_FILE_NOT_FOUND')." (".HtmlFilter::encode($path).")");
	die();
}
elseif (!$USER->CanDoFileOperation('fm_edit_existent_file',array($_REQUEST['site'], $path)))
{
	$bReadOnly = true;
}

function SeoShowHelp($topic)
{
	$msg = GetMessage('SEO_HELP_'.$topic);
	if ($msg <> '')
	{
		$msg = ShowJSHint($msg, array('return' => true));
	}

	return $msg;
}

CModule::IncludeModule('seo');

$bStatsIncluded = CModule::IncludeModule('statistic');
$bStatsRights = $APPLICATION->GetGroupRight("statistic") >= 'M'; // view stats w/o finance data


//get site settings
$site = SITE_ID;
if (isset($_REQUEST["site"]) && $_REQUEST["site"] <> '')
{
	$obSite = CSite::GetByID($_REQUEST["site"]);
	if ($arSite = $obSite->Fetch())
	{
		$site = HtmlFilter::encode($_REQUEST["site"]);
		$serverName = HtmlFilter::encode($arSite['SERVER_NAME']);
	}
}
//find server name in other places
if ($serverName == '')
	$serverName = COption::GetOptionString('main', 'server_name', '');
if ($serverName == '')
	$serverName = $_SERVER['SERVER_NAME'];

$serverName = str_replace(array("https://", "http://"), '', $serverName);
$protocol = \CMain::IsHTTPS() ? "https://" : "http://";

$serverPort = intval($_SERVER['SERVER_PORT']);
$serverHost = $_SERVER['HTTP_HOST'];
if($serverPort <> '' && $serverHost <> '')
	if(mb_strpos($serverHost, $serverPort) !== false || ($serverPort!=80 && $serverPort!=443))
		$serverName .= ":".$serverPort;

//lang
if (!isset($_REQUEST["lang"]) || $_REQUEST["lang"] == '')
	$lang = LANGUAGE_ID;

// title changers
if($_REQUEST['title_changer_name'] <> '')
{
	$titleChangerName = $_REQUEST['title_changer_name'];
}

if ($_REQUEST['title_changer_link'] <> '')
	$titleChangerLink = base64_decode($_REQUEST['title_changer_link']);

if($_REQUEST['title_final'] <> '')
{
	$titleFinal = base64_decode($_REQUEST['title_final']);
}

// browser title changers
if($_REQUEST['title_win_changer_name'] <> '')
{
	$titleWinChangerName = $_REQUEST['title_win_changer_name'];
}

if ($_REQUEST['title_win_changer_link'] <> '')
	$titleWinChangerLink = base64_decode($_REQUEST['title_win_changer_link']);

if($_REQUEST['title_win_final'] <> '')
{
	$titleWinFinal = base64_decode($_REQUEST['title_win_final']);
}

//back url processing
$back_url = (isset($_REQUEST["back_url"]) ? $_REQUEST["back_url"] : "");
$original_backurl = $back_url;

$back_url = CSeoUtils::CleanURL($back_url);

/**************** ajax tabs processing ************************/
if (isset($_REQUEST['loadtab']))
{
	define('ADMIN_AJAX_MODE', 1);

	$searchers = COption::GetOptionString('seo', 'searchers_list', '');
	$arSearchers = array();
	if ($searchers <> '')
	{
		$arSearchers = explode(',', $searchers);
		$arSearcherHits = array();
		if (count($arSearchers) > 0)
		{
			$dbRes = CSearcher::GetList('s_name', 'asc', array('ID' => implode('|', $arSearchers)));
			$arSearchers = array();
			while ($arRes = $dbRes->Fetch())
			{
				$arSearchers[$arRes['ID']] = $arRes;
				$arSearcherHits[$arRes['ID']] = 0;
			}
		}
	}

	switch($_REQUEST['loadtab'])
	{
		case 'indexing':

			if (count($arSearchers) <= 0):
?>
<table width="100%">
	<tr>
		<td><?echo BeginNote(),GetMessage('SEO_PAGE_ERROR_NO_SEARCHERS'),EndNote()?></td>
	</tr>
</table>
<?
			else:
				$url = $protocol.$serverName.$back_url;
				$arFilter = array(
					'SEARCHER_ID' => implode('|', array_keys($arSearchers)),
					'DATE1' => ConvertTimeStamp(strtotime('-3 month'), false, $site_id),
					'DATE2' => ConvertTimeStamp(time(), false, $site_id),
					'URL' => $url,
					'URL_EXACT_MATCH' => 'Y',
					'SITE_ID' => $site,
				);

				$last_ts = strtotime('-'.COption::GetOptionInt('statistic', 'SEARCHER_HIT_DAYS', 3).' days');
				$total = 0;
				$dbRes = CSearcherHit::GetList('s_searcher_id', 'asc', $arFilter);
				while ($arRes = $dbRes->Fetch())
				{
					$ts = MakeTimeStamp($arRes['DATE_HIT']);
					$total++;
					if ($ts < $last_ts) $last_ts = $ts;
					$arSearcherHits[$arRes['SEARCHER_ID']]++;
				}

				$days_count = floor((time() - $last_ts)/86400);
?>
<table width="100%">
	<tr class="heading">
		<td colspan="2"><?echo str_replace('#COUNT#', $days_count, GetMessage('SEO_PAGE_STATS_INDEX'))?></td>
	</tr>
<?
				if ($total > 0):
					foreach ($arSearcherHits as $key => $count):
						if ($count > 0):
?>
	<tr>
		<td width="50%" align="right"><?echo HtmlFilter::encode($arSearchers[$key]['NAME'])?>:&nbsp;&nbsp;</td>
		<td width="50%"><?echo $count;?></td>
	</tr>
<?
						endif;
					endforeach;
				else:
?>
	<tr>
		<td colspan="2" align="center">
<?
					echo BeginNote(),GetMessage('SEO_PAGE_STATS_ERROR_NO_DATA'),EndNote();
?>
		</td>
	</tr>
<?
				endif;

				$arrDays = CSearcher::GetGraphArray(array(
						"SEARCHER_ID"	=> $arFilter['SEARCHER_ID'],
						"DATE1"			=> $arFilter['DATE1'],
						"DATE2"			=> $arFilter['DATE2'],
						"SUMMA"			=> 'N'
					), $arrLegend
				);


?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage('SEO_PAGE_STATS_SITE_INDEX')?></td>
	</tr>
<?
				if (count($arrLegend) > 0 && count($arrDays) > 1):
?>
	<tr>
		<td colspan="2"><img src="/bitrix/admin/searcher_graph.php?&lang=<?echo LANGUAGE_ID?>&find_date1_DAYS_TO_BACK=90<?foreach ($arSearchers as $key => $ar) echo '&find_searchers[]='.$key;?>&mode=list&find_summa=N&width=576&height=300" border="0" width="576" height="300" border="0" /><br /><br />
		<table border="0" cellspacing="0" cellpadding="0" class="legend">
<?
					foreach ($arrLegend as $keyL => $arrL):
						$color = $arrL["COLOR"];
?>
			<tr>
				<td><img src="/bitrix/admin/graph_legend.php?color=<?=$color?>" width="45" height="2"></td>
				<td nowrap="nowrap">[<a href="/bitrix/admin/searcher_list.php?lang=<?=LANGUAGE_ID?>&amp;find_id=<?=$keyL?>&amp;set_filter=Y"><?=$keyL?></a>]&nbsp;<a  href="/bitrix/admin/searcher_dynamic_list.php?lang=<?=LANGUAGE_ID?>&amp;find_searcher_id=<?=$keyL?>&amp;find_date1=<?echo $arFilter["DATE1"]?>&amp;find_date2=<?=$arFilter["DATE2"]?>&amp;set_filter=Y"><?=$arrL["NAME"]?></a></td>
			</tr>
<?
					endforeach;
?>
		</table></td>
	</tr></table>
<?
		else:
?>
<table width="100%">
	<tr>
		<td colspan="2" align="center">
<?
					echo BeginNote(),GetMessage('SEO_PAGE_STATS_SITE_INDEX_ERROR_NO_DATA'),EndNote();
?>
		</td>
	</tr>
</table>
<?
				endif;
			endif;


		break;


		/****************** words tab **************************/
		case 'words':

			if (count($arSearchers) <= 0):
?>
<table width="100%">
	<tr>
		<td><?echo BeginNote(),GetMessage('SEO_PAGE_ERROR_NO_SEARCHERS'),EndNote()?></td>
	</tr>
</table>
<?
			else:
				$url = $protocol.$serverName.$back_url;
				$arFilter = array(
					'SEARCHER_ID' => implode('|', array_keys($arSearchers)),
					'TO' => $url,
					'TO_EXACT_MATCH' => 'Y',
					'GROUP' => 'P'
				);

				$dbRes = CPhrase::GetList('s_quantity', 'desc', $arFilter, null, $total);
				$dbRes->NavStart(20, false, 0);

				$arWords = array();
				while ($arRes = $dbRes->Fetch())
				{
					$arWords[$arRes['PHRASE']] = array(
						'TOTAL' => $arRes['QUANTITY'],
						'PERCENT' => $arRes['C_PERCENT'],
						'SEARCHERS' => array(),
					);
				}

				//unset($arFilter['GROUP']);
				$arFilter['GROUP'] = 'S';

				foreach ($arWords as $phrase => $arWord)
				{
					$arFilter['PHRASE'] = '"'.$phrase.'"';
					$arFilter['PHRASE_EXACT_MATCH'] = 'Y';
					$dbRes = CPhrase::GetList('s_quantity', 'desc', $arFilter);
					$dbRes->NavStart(50, false, 0);
					while ($arRes = $dbRes->Fetch())
					{
						$arWords[$phrase]['SEARCHERS'][$arRes['SEARCHER_ID']] = array(
							'SEARCHER_NAME' => $arRes['SEARCHER_NAME'],
							'COUNT' => $arRes['QUANTITY'],
						);
					}
				}

				if ($total > 0)
				{
					$cnt = count($arWords);
?>
					<table width="100%" class="referers-table bx-seo-words-table internal" id="bx_seo_words_table">
						<thead>
							<tr class="heading">
								<td width="30%" style="text-align:left !important;"><?=GetMessage('SEO_PAGE_REFERERS_PHRASE');?></td>
								<td width="70%" style="text-align:left !important;"><?=GetMessage('SEO_PAGE_REFERERS_SEARCHERS_COUNT');?></td>
							</tr>
						</thead>
						<tbody>
						<?
						$color = null;
						foreach ($arWords as $word => $arData)
						{
							$arData['ID'] = rand(0, 65535);
							$percent = number_format($arData['PERCENT'], 2);
							?>
							<tr>
								<td style="text-align:left !important;"><?=HtmlFilter::encode($word)?></td>
								<td style="text-align:left !important;">
									<div style="height: 15px; border: solid 1px #<?echo $color = GetNextRGB($color, $cnt)?> !important; width: 100%; position: relative; text-align: left !important;" class="bx-seo-words-table-link-element">
										<div style="float: left; height: 15px; width: <?echo $percent?>%; background-color: #<?echo $color; ?>; white-space: nowrap; position: absolute;">
											<?echo intval($arData['TOTAL'])?> (<?echo $percent?>%)
										</div>
										<?echo intval($arData['TOTAL'])?> (<?echo $percent?>%)
									</div>
									<? foreach ($arData['SEARCHERS'] as $searcher_id => $arSearcherData): ?>
										<div style="text-align: left !important;" class="bx-seo-words-table-link-element">
											<? echo HtmlFilter::encode($arSearcherData['SEARCHER_NAME']);?> &mdash;
											<?echo intval($arSearcherData['COUNT']);?>
										</div>
									<?endforeach;?>
								</td>
							</tr>
							<?
						}
						?>
						</tbody>
					</table>
					<div>
						<a href="/bitrix/admin/phrase_list.php?lang=<?echo LANGUAGE_ID?>"><?echo GetMessage('SEO_PAGE_GOTO_CP')?></a>
					</div>
<?
				}
				else
				{
?>
<table width="100%">
	<tr>
		<td colspan="2" align="center">
<?
					echo BeginNote(),GetMessage('SEO_PAGE_PHRASES_ERROR_NO_DATA'),EndNote();
?>
		</td>
	</tr>
</table>
<?
				}


			endif;

		break;



		/******************* referers tab *********************/
		case 'referers':
			$url = $protocol.$serverName.$back_url;
			$arFilter = array(
				'TO' => $url,
				'TO_EXACT_MATCH' => 'Y',
				'GROUP' => 'S'
			);
			

			$dbRes = CReferer::GetList('s_quantity', 'desc', $arFilter, null, $total);
			$dbRes->NavStart(20, false, 0);

			$arReferers = array();
			while ($arRes = $dbRes->Fetch())
			{
				if ($arRes['URL_FROM'] <> '')
				{
					if (!is_array($arReferers[$arRes['URL_FROM']]))
					{
						$arReferers[$arRes['URL_FROM']] = array(
							'TOTAL' => $arRes['QUANTITY'],
							'PERCENT' => $arRes['C_PERCENT'],
							'URL_FROM' => array(),
						);
					}
				}
			}

			//unset($arFilter['GROUP']);
			$arFilter['GROUP'] = 'U';

			// damn ineffectively but there's no other way
			foreach ($arReferers as $key => $arData)
			{
				$arFilter['FROM_DOMAIN'] = $key;
				$arFilter['FROM_DOMAIN_EXACT_MATCH'] = 'Y';
				$dbRes = CReferer::GetList('s_quantity', 'desc', $arFilter, null, $total);
				$dbRes->NavStart(50, false, 0);
				while ($arRes = $dbRes->Fetch())
				{
					if ($arRes['URL_FROM'] <> '' && ($arUrl = parse_url($arRes['URL_FROM'])))
					{
						if ($arUrl['port'] != '' && $arUrl['port'] != 80)
							$arUrl['host'] .= ':'.$arUrl['port'];

						if (isset($arReferers[$arUrl['host']]))
							$arReferers[$arUrl['host']]['URL_FROM'][$arRes['URL_FROM']] = $arRes['QUANTITY'];
					}
				}
			}

			if ($total > 0):?>
			
			<table width="100%" class="referers-table bx-seo-words-table internal" id="bx_seo_words_table">
				<thead>
					<tr class="heading">
						<td width="30%" style="text-align:left !important;"><?=GetMessage('SEO_PAGE_REFERERS_SITE');?></td>
						<td width="70%" style="text-align:left !important; text-transform: capitalize;"><?=GetMessage('SEO_PAGE_REFERERS_COUNT');?></td>
					</tr>
				</thead>
				<tbody>
				<?
					$cnt = count($arReferers);
					$color = null;
					foreach ($arReferers as $domain => $arData)
					{
						$percent = number_format($arData['PERCENT'], 2);
						$domainEnc = \Bitrix\Main\Text\Converter::getHtmlConverter()->encode($domain);
						?>
						<tr>
							<td style="text-align:left !important;"><?=$domainEnc?></td>
							<td style="text-align:left !important;">
								<div style="height: 15px; border: solid 1px #<?echo $color = GetNextRGB($color, $cnt)?> !important; width: 100%; position: relative; text-align: left !important;" class="bx-seo-words-table-link-element">
									<div style="float: left; height: 15px; width: <?echo $percent?>%; background-color: #<?echo $color; ?>; white-space: nowrap; position: absolute;">
										<?echo intval($arData['TOTAL'])?> (<?echo $percent?>%)
									</div>
									<?echo intval($arData['TOTAL'])?> (<?echo $percent?>%)
								</div>
								<? foreach ($arData['URL_FROM'] as $url => $count): ?>
									<div style="text-align: left !important;" class="bx-seo-words-table-link-element">
										<a href="<?echo HtmlFilter::encode($url)?>"><? echo HtmlFilter::encode(TruncateText($url, 100));?></a> &mdash;
										<?echo intval($count);?>
									</div>
								<? endforeach;?>
							</td>
						</tr>
						<?
					}
				?>
				</tbody>
			</table>
			<div>
				<a href="/bitrix/admin/referer_list.php?lang=<?echo LANGUAGE_ID?>"><?echo GetMessage('SEO_PAGE_GOTO_CP')?></a>
			</div>
<?
//			have not references
			else:
?>
	<table width="100%"><tr>
		<td colspan="2" align="center">
<?
				echo BeginNote(),GetMessage('SEO_PAGE_REFERERS_ERROR_NO_DATA'),EndNote();
?>
		</td>
	</tr></table>
<?
			endif;

		break;
	}


	// one more little hack to avoid warning from compression module
	ob_start();

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$fileContent = $APPLICATION->GetFileContent($absoluteFilePath);


/************************** GET/POST processing ***************************************/
$strWarning = '';
$success = true;

if (!check_bitrix_sessid())
{
	$strWarning = GetMessage("MAIN_SESSION_EXPIRED");
}
elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_REQUEST["save"]))
{
	if(!$bReadOnly)
	{
		//Title
		if (isset($_POST["pageTitle"]) && $_POST["pageTitle"] <> '')
		{
			$fileContent = SetPrologTitle($fileContent, $_POST["pageTitle"]);
		}
		
		//Title
		$propertyCode = COption::GetOptionString('seo', 'property_window_title', 'title');
		if (isset($_POST["property_" . $propertyCode]))
		{
			$fileContent = SetPrologProperty($fileContent, $propertyCode, $_POST["property_" . $propertyCode]);
		}
		
		//Properties
		if (isset($_POST["PROPERTY"]) && is_array($_POST["PROPERTY"]))
		{
			foreach ($_POST["PROPERTY"] as $arProperty)
			{
				$arProperty["CODE"] = (isset($arProperty["CODE"]) ? trim($arProperty["CODE"]) : "");
				$arProperty["VALUE"] = (isset($arProperty["VALUE"]) ? trim($arProperty["VALUE"]) : "");
				
				if (preg_match("/[a-zA-Z_-~]+/i", $arProperty["CODE"]))
				{
					$fileContent = SetPrologProperty($fileContent, $arProperty["CODE"], $arProperty["VALUE"]);
				}
			}
		}
		
		$success = $APPLICATION->SaveFileContent($absoluteFilePath, $fileContent);
		
		if ($success === false && ($exception = $APPLICATION->GetException()))
			$strWarning = $exception->msg;
	}
	if (isset($_POST['internal_keywords']) && $success !== false)
	{
		CSeoKeywords::Update(array(
			'URL' => $back_url,
			'SITE_ID' => $site,
			'KEYWORDS' => $_POST['internal_keywords'],
		));
	}
	
	LocalRedirect("/" . ltrim($original_backurl, "/"));
	die();
}

if ($strWarning != "")
{
	CAdminMessage::ShowMessage($strWarning);
	die();
}


//Properties from fileman settings
$arFilemanProperties = array();
if (CModule::IncludeModule("fileman") && is_callable(array("CFileMan", "GetPropstypes")))
	$arFilemanProperties = CFileMan::GetPropstypes($site);

//Properties from page
$arPageSlice = ParseFileContent($fileContent);
$arDirProperties = $arPageSlice["PROPERTIES"];
$pageTitle = $arPageSlice["TITLE"];

//All properties for file. Includes properties from root folders
$arInheritProperties = $APPLICATION->GetDirPropertyList(array($site, $path));
if ($arInheritProperties === false)
	$arInheritProperties = array();

//Delete equal properties
$arGlobalProperties = array();

if(is_array($arFilemanProperties))
{
	foreach ($arFilemanProperties as $propertyCode => $propertyDesc)
	{
		if (array_key_exists($propertyCode, $arDirProperties))
			$arGlobalProperties[$propertyCode] = $arDirProperties[$propertyCode];
		else
			$arGlobalProperties[$propertyCode] = "";

		unset($arDirProperties[$propertyCode]);
		unset($arInheritProperties[mb_strtoupper($propertyCode)]);
	}
}

foreach ($arDirProperties as $propertyCode => $propertyValue)
{
	unset($arInheritProperties[mb_strtoupper($propertyCode)]);
}

$counters = COption::GetOptionString('seo', 'counters', SEO_COUNTERS_DEFAULT);

//HTML output
$aTabs = [
	["DIV" => "seo_edit1", "TAB" => GetMessage('SEO_TOOLS_TAB_PAGE'), "ICON" => "main_settings", "TITLE" => GetMessage('SEO_TOOLS_TAB_PAGE_TITLE').' '.HtmlFilter::encode($back_url, ENT_QUOTES)],
	["DIV" => "seo_edit2", "TAB" => GetMessage('SEO_TOOLS_INTERNAL_KEYWORDS'), "ICON" => "main_settings", "TITLE" => GetMessage('SEO_TOOLS_INTERNAL_KEYWORDS_TITLE')],
	["DIV" => "seo_edit3", "TAB" => GetMessage('SEO_TOOLS_TAB_EDIT'), "ICON" => "main_settings", "TITLE" => GetMessage('SEO_TOOLS_TAB_EDIT_TITLE')],
];
if ($DB->type !== "PGSQL")
{
	$aTabs[] = ["DIV" => "seo_edit4", "TAB" => GetMessage('SEO_TOOLS_TAB_INDEX'), "ICON" => "main_settings", "TITLE" => GetMessage('SEO_TOOLS_TAB_INDEX_TITLE'), 'ONSELECT' => ($bStatsIncluded && $bStatsRights ? 'window.BXLoadTab(\'indexing\')' : '')];
	$aTabs[] = ["DIV" => "seo_edit5", "TAB" => GetMessage('SEO_TOOLS_TAB_WORDS'), "ICON" => "main_settings", "TITLE" => GetMessage('SEO_TOOLS_TAB_WORDS_TITLE'), 'ONSELECT' => ($bStatsIncluded && $bStatsRights ? 'window.BXLoadTab(\'words\')' : '')];
	$aTabs[] = ["DIV" => "seo_edit6", "TAB" => GetMessage('SEO_TOOLS_TAB_REFERERS'), "ICON" => "main_settings", "TITLE" => GetMessage('SEO_TOOLS_TAB_REFERERS_TITLE'), 'ONSELECT' => ($bStatsIncluded && $bStatsRights ? 'window.BXLoadTab(\'referers\')' : '')];
}
$tabControl = new CAdminTabControl("seoTabControl", $aTabs, true, true);

$APPLICATION->SetTitle(GetMessage('SEO_TOOLS_TITLE'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="seo_form" method="POST" action="/bitrix/admin/public_seo_tools.php" enctype="multipart/form-data">
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>" />
<input type="hidden" name="site" value="<?=$site?>" />
<input type="hidden" name="path" value="<?echo htmlspecialcharsEx($path)?>" />
<input type="hidden" name="back_url" value="<?echo htmlspecialcharsEx($original_backurl)?>" />
<?=bitrix_sessid_post()?>
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
<style type="text/css">
div#bx_admin_form table.edit-table tr#bx_keywords_stats table.bx-seo-words-table tr td {padding: 3px !important; text-align: center; border-collapse: separate !important; border-spacing: 1px !important;}

div#bx_page_extended_data a {text-decoration: none;}

div#bx_admin_form table.edit-table table tr td {padding: 4px;}

table.legend {margin:0px 0px 7px 10px; font-size:70%;}
table.legend td {padding:3px 10px 3px 0px;}

table.phrases-table, table.referers-table {width: 85%;}
table.phrases-table tr.bx-th td, table.referers-table tr.bx-th td {text-align: center; font-weight: bold !important;}
table.phrases-table td div, table.referers-table td div {color: black !important; white-space: nowrap;}
table.phrases-table td div div, table.referers-table td div div {color: white !important; white-space: nowrap; overflow: hidden; text-shadow: unset;}
table.phrases-table td table, table.referers-table  td table {width: 90%;}
.bx-seo-words-table-link-element{margin-bottom:6px;}

div#bx_page_extended_data div {height: 140px; width: 99%; overflow: auto; margin-right: 5px; border: solid 1px #E0E4F1;}
#bx_page_errors ol li {margin: 5px 0px;}

</style>
<?
if ($counters <> ''):
	$counters = str_replace(array('#DOMAIN#'), array($serverName), $counters);

foreach(GetModuleEvents("seo", "OnSeoCountersGetList", true) as $arEvent)
{
	if ($str = ExecuteModuleEventEx($arEvent, $arParams = array()))
	{
		$counters .= "\r\n\r\n".$str;
	}
}

?>
<tr>
	<td width="40%" valign="top"><?echo GetMessage('SEO_TOOLS_COUNTERS')?>: <?echo SeoShowHelp('counters')?></td>
	<td width="60%"><?echo $counters;?></td>
</tr>
<?endif;?>
</table>
<table id="bx_stats_loading_notify" class="edit-table"><tr><td align="center"><?echo BeginNote(),GetMessage('SEO_TOOLS_LOADING'),EndNote();?></td></tr></table>
<table id="bx_stats_loading_error" class="edit-table"><tr><td align="center" id="bx_seo_error_text"></td></tr></table>
<table id="bx_stats_table" class="edit-table" cellspacing="0" cellpadding="0" border="0" style="display: none;">
<tbody>
<tr height="0"><td width="50%" nowrap="nowrap"></td><td width="50%" nowrap="nowrap"></td></tr>
<tr class="heading" id="bx_page_stats_row">
	<td colspan="2" align="center"><?echo GetMessage('SEO_TOOLS_STATS')?></td>
</tr>
<tr class="heading">
	<td colspan="2" align="center"><?echo GetMessage('SEO_TOOLS_ANALYSIS')?></td>
</tr>
<tr>
	<td colspan="2">
		<div id="bx_page_extended_data"><table width="100%">
			<tr>
				<td width="67%">
					<div id="bx_ex_out"></div>
				</td>
				<td width="33%">
					<a href="javascript: void(0)" id="bx_seo_link_HEADERS" onclick="BXShowExtendedStat('HEADERS');">HTTP HEADERS</a><br />
					<a href="javascript: void(0)" id="bx_seo_link_TITLE" onclick="BXShowExtendedStat('TITLE')">HTML TITLE (&lt;TITLE&gt;)</a><br />
					<a href="javascript: void(0)" id="bx_seo_link_H" onclick="BXShowExtendedStat('H')">HTML HEADING(&lt;H1&gt;-&lt;H6&gt;)</a><br />
					<a href="javascript: void(0)" id="bx_seo_link_META_DESCRIPTION" onclick="BXShowExtendedStat('META_DESCRIPTION')">META DESCRIPTION</a><br />
					<a href="javascript: void(0)" id="bx_seo_link_META_KEYWORDS" onclick="BXShowExtendedStat('META_KEYWORDS')">META KEYWORDS</a><br />
					<a href="javascript: void(0)" id="bx_seo_link_BOLD" onclick="BXShowExtendedStat('BOLD')">BOLD (&lt;B&gt;, &lt;STRONG&gt;)</a><br />
					<a href="javascript: void(0)" id="bx_seo_link_ITALIC" onclick="BXShowExtendedStat('ITALIC')">ITALIC (&lt;I&gt;, &lt;EM&gt;)</a><br />
					<a href="javascript: void(0)" id="bx_seo_link_LINK" onclick="BXShowExtendedStat('LINK')">LINK</a><br />
					<a href="javascript: void(0)" id="bx_seo_link_LINK_EXTERNAL" onclick="BXShowExtendedStat('LINK_EXTERNAL')">LINK (EXT)</a><br />
					<a href="javascript: void(0)" id="bx_seo_link_NOINDEX" onclick="BXShowExtendedStat('NOINDEX')">NOINDEX</a><br />
					<a href="javascript: void(0)" id="bx_seo_link_NOFOLLOW" onclick="BXShowExtendedStat('NOFOLLOW')">NOFOLLOW</a><br />
				</td>
			</tr>
		</table></div>
	</td>
</tr>
<tr class="heading">
	<td colspan="2" align="center"><?echo GetMessage('SEO_PAGE_RECOMMEMDATIONS')?></td>
</tr>
<tr>
	<td colspan="2" id="bx_page_errors"></td>
</tr>
</tbody>
<?
/*************************************/
/* internal keywords tab */
/*************************************/
$tabControl->BeginNextTab();


if ($propertyCode = COption::GetOptionString('seo', 'property_internal_keywords', 'keywords_inner')):

	$savedKeywords = CSeoKeywords::GetByURL($back_url, $site);
	if (empty($savedKeywords))
	{
		$keywords = $arGlobalProperties[$propertyCode] ?: $arDirProperties[$propertyCode];
		$keywords = explode(',', $keywords);
		CSeoKeywords::Add([
			'URL' => $back_url,
			'SITE_ID' => $site,
			'KEYWORDS' => $keywords,
		]);
	}
	else
	{
		$keywords = [];
		foreach ($savedKeywords as $key => $value)
		{
			$keywords = array_merge($keywords, explode(',', $value['KEYWORDS']));
		}
	}
	TrimArr($keywords, true);
?>
<tr id="bx_keywords_stats_loading_notify"><td align="center"><?echo BeginNote(),GetMessage('SEO_TOOLS_LOADING'),EndNote();?></td></tr>
<tr id="bx_keywords_stats" style="display: none;">
	<td colspan="2">
		<div><input type="text" id="internal_keywords" rows="5" name="internal_keywords" style="width: 80%;" value="<?echo htmlspecialcharsEx(implode(', ', $keywords))?>" /><button onclick="BXCallUpdateKeywordsStats(document.getElementById('internal_keywords').value); return false;" title="<?echo GetMessage('SEO_TOOLS_INTERNAL_KEYWORDS_RELOAD_TITLE')?>"><?echo GetMessage('SEO_TOOLS_INTERNAL_KEYWORDS_RELOAD')?></button></div>
		<br />
		<table width="100%" class="bx-seo-words-table" id="bx_seo_words_table">
			<thead>
				<tr class="heading">
					<td style="text-align:left !important;"><?echo GetMessage('SEO_TOOLS_INTERNAL_KEYWORDS_WORD');?></td>
					<td><?echo GetMessage('SEO_TOOLS_INTERNAL_KEYWORDS_ALL_CONTRAST');?></td>
					<td><?echo GetMessage('SEO_TOOLS_INTERNAL_KEYWORDS_HEADERS');?></td>
					<td><?echo GetMessage('SEO_TOOLS_INTERNAL_KEYWORDS_BOLD_ITALIC');?></td>
					<td><?echo GetMessage('SEO_TOOLS_INTERNAL_KEYWORDS_DESCR_KEYWORDS');?></td>
					<td><?echo GetMessage('SEO_TOOLS_INTERNAL_KEYWORDS_LINKS');?></td>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
		<?echo BeginNote(),GetMessage('SEO_TOOLS_INTERNAL_KEYWORDS_HINT'),EndNote();?>
	</td>
</tr>

<?
endif;

/*************************************/
/* file and directory properties tab */
/*************************************/
$tabControl->BeginNextTab();
?>
<?if ($bReadOnly):?>
	<tr>
		<td>
			<?echo
			BeginNote(),GetMessage('SEO_PAGE_ERROR_FOLDER_ACCESS', array(
				'#F'=> HtmlFilter::encode($path))
			), EndNote()
			?>
		</td>
	</tr>
<?else:?>
	
	<tr>
		<td><?echo GetMessage('SEO_PAGE_BASE_TITLE')?> (&lt;H1&gt;): <?echo SeoShowHelp('base_title')?></td>
		<td><input type="text" name="pageTitle" value="<?=htmlspecialcharsEx($pageTitle)?>"  size="50" /></td>
	</tr>
	<?
	if ($titleFinal != $pageTitle):
	?>
	<tr>
		<td valign="top"><?echo GetMessage('SEO_PAGE_CURRENT_TITLE')?>: <?echo SeoShowHelp('current_title')?></td>
		<td valign="top">
	<script>
	window.jsPopup_subdialog = new JCPopup({'suffix':'subdialog', 'zIndex':parseInt(jsPopup.zIndex)+20});
	</script>
	<?
	if ($titleChangerLink)
	{
		$titleChangerLink = preg_replace(
			"/jsPopup.ShowDialog\('(.*?)',([^\)]*)\)/is",
			"jsPopup_subdialog.ShowDialog('\\1&subdialog=Y&suffix=subdialog',\\2)",
			$titleChangerLink
		);
	
		$titleChangerLink = preg_replace(
			"/BX.CAdminDialog[\s]*\([\s]*\{(.*?)'content_url'[\s]*:[\s]*'(.*?)'/is",
			"BX.CAdminDialog({\\1'content_url':'\\2&subdialog=Y'",
			$titleChangerLink
		);
	}
	?>
			<b><?echo htmlspecialcharsEx($titleFinal)?></b>&nbsp;<?if ($titleChangerName != ''):?>(<?echo HtmlFilter::encode($titleChangerName)?>)&nbsp;<?endif;?><?if ($titleChangerLink):?><br /><a href="<?echo HtmlFilter::encode($titleChangerLink, ENT_QUOTES);?>"><?echo GetMessage('SEO_PAGE_CURRENT_TITLE_EDIT')?></a><?endif;?>
	</tr>
	<?
	endif;
	
	if ($propertyCode = COption::GetOptionString('seo', 'property_window_title', 'title')):
		$value = $arGlobalProperties[$propertyCode] ? $arGlobalProperties[$propertyCode] : $arDirProperties[$propertyCode];
		if ($value == '')
		{
			$value = $APPLICATION->GetDirProperty($propertyCode, array($site, $path));
		}
	?>
	<tr>
		<td><?echo $arFilemanProperties[$propertyCode] ? $arFilemanProperties[$propertyCode] : GetMessage('SEO_PAGE_PROPERTY_WINDOW_TITLE')?> (&lt;TITLE&gt;): <?echo SeoShowHelp('property_window_title')?></td>
		<td><input type="text" name="property_<?echo HtmlFilter::encode($propertyCode)?>" value="<?=HtmlFilter::encode($value)?>" size="50" /></td>
	</tr>
	<?
		if ($value != $titleWinFinal):
		?>
	<tr>
		<td valign="top"><?echo $arFilemanProperties[$propertyCode] ? $arFilemanProperties[$propertyCode] : GetMessage('SEO_PAGE_PROPERTY_WINDOW_TITLE')?> (<?echo GetMessage('SEO_PAGE_WINDOW_TITLE_CURRENT')?>): <?echo SeoShowHelp('current_window_title')?></td>
		<td valign="top">
		<?
			if ($titleWinChangerLink)
			{
				$titleWinChangerLink = preg_replace(
					"/jsPopup.ShowDialog\('(.*?)',([^\)]*)\)/is",
					"jsPopup_subdialog.ShowDialog('\\1&subdialog=Y&suffix=subdialog',\\2)",
					$titleWinChangerLink
				);
	
				$titleWinChangerLink = preg_replace(
					"/BX.CAdminDialog[\s]*\([\s]*\{(.*?)'content_url'[\s]*:[\s]*'(.*?)'/is",
					"BX.CAdminDialog({\\1'content_url':'\\2&subdialog=Y'",
					$titleWinChangerLink
				);
			}
		?>
			<b><?echo htmlspecialcharsEx($titleWinFinal)?></b>&nbsp;<?if ($titleWinChangerName != ''):?>(<?echo HtmlFilter::encode($titleWinChangerName)?>)&nbsp;<?endif;?><?if ($titleWinChangerLink):?><br /><a href="<?echo HtmlFilter::encode($titleWinChangerLink, ENT_QUOTES)?>"><?echo GetMessage('SEO_PAGE_CURRENT_TITLE_EDIT')?></a><?endif;?>
		</tr>
		<?
		endif;
	endif;
	
	$arEditProperties = array();
	if ($propertyCode = COption::GetOptionString('seo', 'property_keywords', 'keywords'))
	{
		$arEditProperties['keywords'] = HtmlFilter::encode($propertyCode);
	}
	if ($propertyCode = COption::GetOptionString('seo', 'property_description', 'description'))
	{
		$arEditProperties['description'] = HtmlFilter::encode($propertyCode);
	}
	
	foreach ($arEditProperties as $propertyCode):
		$value = $arGlobalProperties[$propertyCode];
	?>
	<tr>
		<td><?echo $arFilemanProperties[$propertyCode]?>: <?echo SeoShowHelp('property_'.$key)?></td>
		<td><input type="hidden" name="PROPERTY[<?=HtmlFilter::encode($propertyCode)?>][CODE]" value="<?=HtmlFilter::encode($propertyCode)?>" />
		<?
		if ($value == ''):
			$value = $APPLICATION->GetDirProperty($propertyCode, array($site, $path));
		?>
			<div id="bx_view_property_<?=HtmlFilter::encode($propertyCode)?>" style="overflow:hidden;padding:2px 12px 2px 2px; border:1px solid #F8F9FC; width:90%; cursor:text; box-sizing:border-box; -moz-box-sizing:border-box;background-color:transparent; background-position:right; background-repeat:no-repeat; height: 22px;" onclick="BXEditProperty('<?=$propertyCode?>')" onmouseover="this.style.borderColor = '#434B50 #ADC0CF #ADC0CF #434B50';" onmouseout="this.style.borderColor = '#F8F9FC'" class="edit-field"><?=htmlspecialcharsEx($value)?></div>
	
			<div id="bx_edit_property_<?=HtmlFilter::encode($propertyCode)?>" style="display:none;"></div>
		<?
		else:
		?>
			<input type="text" name="PROPERTY[<?=HtmlFilter::encode($propertyCode)?>][VALUE]" value="<?=HtmlFilter::encode($value)?>" size="50" /></td>
		<?
		endif;
		?>
	</tr>
	<?
	endforeach;
	?>
<?endif;?>
<?php
if ($DB->type !== "PGSQL")
{
	/********************************/
	/* searchers indexing stats tab */
	/********************************/
	$tabControl->BeginNextTab();
	if (!$bStatsIncluded):
		?>
		<tr>
			<td><? ShowError(GetMessage('SEO_PAGE_ERROR_NO_STATS')); ?></td>
		</tr>
	<?php
	elseif (!$bStatsRights):
		?>
		<tr>
			<td><?php ShowError(GetMessage('SEO_PAGE_ERROR_NO_STATS_RIGHTS')); ?></td>
		</tr>
	<?php
	else:
		?>
		<tr>
			<td align="center">
				<div id="bx_seo_tab_indexing"><? echo BeginNote(), GetMessage('SEO_TOOLS_LOADING'), EndNote() ?></div>
			</td>
		</tr>
	<?
	endif;

	/********************/
	/* search words tab */
	/********************/
	$tabControl->BeginNextTab();
	if (!$bStatsIncluded):
	?>
		<tr>
			<td><?ShowError(GetMessage('SEO_PAGE_ERROR_NO_STATS'));?></td>
		</tr>
	<?
	elseif (!$bStatsRights):
	?>
		<tr>
			<td><?ShowError(GetMessage('SEO_PAGE_ERROR_NO_STATS_RIGHTS'));?></td>
		</tr>
	<?
	else:
	?>
		<tr>
			<td align="center"><div id="bx_seo_tab_words"><?echo BeginNote(),GetMessage('SEO_TOOLS_LOADING'),EndNote()?></div></td>
		</tr>
	<?
	endif;

	/****************/
	/* referers tab */
	/****************/
	$tabControl->BeginNextTab();

	if (!$bStatsIncluded):
	?>
		<tr>
			<td><?ShowError(GetMessage('SEO_PAGE_ERROR_NO_STATS'));?></td>
		</tr>
	<?
	elseif (!$bStatsRights):
	?>
		<tr>
			<td><?ShowError(GetMessage('SEO_PAGE_ERROR_NO_STATS_RIGHTS'));?></td>
		</tr>
	<?
	else:
	?>
		<tr>
			<td align="center"><div id="bx_seo_tab_referers" align="center"><?echo BeginNote(),GetMessage('SEO_TOOLS_LOADING'),EndNote()?></td>
		</tr>
	<?
	endif;
}

$tabControl->Buttons(array("disabled"=>$bReadOnly));
$tabControl->End();
?>
</form>
<script>
window.BXToggle = function(id)
{
	with(document.getElementById(id)){if (style.display=='none')style.display='';else style.display='none';};
}

window.BXUpdateKeywordsStats = function(data)
{
	BX.closeWait();

	var obTable = document.getElementById('bx_seo_words_table');
	var newKeywords = [];

	if (null != obTable)
	{
		obTable = obTable.tBodies[0];

		while (obTable.firstChild) obTable.removeChild(obTable.firstChild);

		for (var i = 0; i < data.length; i++)
		{
//			collect new keywords string
			newKeywords.push(data[i][0]);
			var obRow = obTable.insertRow(-1);

			obRow.insertCell(-1).appendChild(document.createTextNode(data[i][0]));
			obRow.insertCell(-1).appendChild(document.createTextNode(null != data[i][1].TOTAL ? data[i][1].TOTAL + '/' + data[i][1].CONTRAST : '-'));
			obRow.insertCell(-1).appendChild(document.createTextNode(null != data[i][1].TITLE ? data[i][1].TITLE + '/' + data[i][1].H1 : '-'));
			obRow.insertCell(-1).appendChild(document.createTextNode(null != data[i][1].BOLD ? data[i][1].BOLD + '/' + data[i][1].ITALIC : '-'));
			obRow.insertCell(-1).appendChild(document.createTextNode(null != data[i][1].TOTAL ? data[i][1].DESCRIPTION + '/' + data[i][1].KEYWORDS : '-'));
			obRow.insertCell(-1).appendChild(document.createTextNode(null != data[i][1].LINK ? data[i][1].LINK + '/' + data[i][1].LINK_EXTERNAL : '-'));

			obRow.cells[0].style.textAlign = 'left';

		}
		
//		print new keywords string
		if(newKeywords.length > 0)
		{
			BX('internal_keywords').value = newKeywords.join(', ');
		}
	}
}
<?
?>
window.BXCallUpdateKeywordsStats = function(keywords)
{
	BX.showWait();
	BX.ajax({
		url: '/bitrix/tools/seo_page_parser.php?lang=<?=LANGUAGE_ID?>&site=<?=$site?>&url=<?echo CUtil::JSEScape(urlencode($back_url))?>&callback=set_keywords_stats&sessid=' + BX.bitrix_sessid(),
		data: 'keywords=' + BX.util.urlencode(keywords),
		method: 'POST',
		dataType: 'script',
		emulateOnload: false
	});
}
<?
?>
window.arTabsLoaded = {};
window.BXLoadTab = function(tab)
{
	if (!window.arTabsLoaded[tab])
	{
		var request = new JCHttpRequest();
		request.Action = function(result)
		{
			BX.closeWait();
			document.getElementById('bx_seo_tab_' + tab).innerHTML = result;
			window.arTabsLoaded[tab] = true;
		}
		BX.showWait();

		var url = '<?echo $APPLICATION->GetCurPageParam('', array('loadtab'))?>&loadtab=' + tab;
		request.Send(url);
	}
}

window.BXCallPageStats = function()
{
	BX.showWait();

	var keywords = '<?echo CUtil::JSEscape(implode(', ', $keywords));?>';

	BX.ajax({
		url: '/bitrix/tools/seo_page_parser.php?lang=<?=LANGUAGE_ID?>&first=Y&site=<?=$site?>&url=<?echo CUtil::JSEScape(urlencode($back_url))?>&callback=set_stats&sessid=' + BX.bitrix_sessid(),
		data: 'keywords=' + BX.util.urlencode(keywords),
		method: 'POST',
		dataType: 'script',
		emulateOnload: false
	});
}

window.BXSetStats = function(data, stats, errors, extended)
{
	BXUpdateKeywordsStats(data);
	BXUpdatePageStats(stats);
	BXUpdatePageErrors(errors);

	BXUpdatePageExtendedData(extended);

	document.getElementById('bx_stats_loading_notify').style.display = 'none';
	document.getElementById('bx_stats_table').style.display = '';
	document.getElementById('bx_keywords_stats_loading_notify').style.display = 'none';
	document.getElementById('bx_keywords_stats').style.display = '';

}

window.BXSetStatsError = function(error)
{
	BX.closeWait();
	document.getElementById('bx_stats_loading_notify').style.display = 'none';
	document.getElementById('bx_stats_loading_error').style.display = '';
	var err_str = '<?echo CUtil::JSEscape(BeginNote())?>';
	err_str +=  error;
<?
if (false === mb_strpos(COption::GetOptionString('main', 'server_name', ''), ':')):
?>
	if (window.location.port && window.location.port != '80')
		err_str += '<br /><br /><?echo CUtil::JSEscape(GetMessage('SEO_PAGE_CONNECTION_ERROR_HINT'))?>';
<?
endif;
?>

	err_str += '<?echo CUtil::JSEscape(EndNote())?>';
	document.getElementById('bx_seo_error_text').innerHTML = err_str;
}

window.BXUpdatePageExtendedData = function(extended)
{
	window.__BXExtendedPageStat = extended;

	var arList = ['HEADERS', 'TITLE', 'H', 'META_DESCRIPTION', 'META_KEYWORDS', 'BOLD', 'ITALIC', 'LINK', 'LINK_EXTERNAL', 'NOINDEX', 'NOFOLLOW'];
	for (var i = 0; i < arList.length; i++)
	{
		if (null != extended[arList[i]] && extended[arList[i]].length > 0)
			document.getElementById('bx_seo_link_' + arList[i]).innerHTML += ' [' + extended[arList[i]].length + ']';
	}

	BXShowExtendedStat(arList[0]);
}

window.BXSeoCurStat = '';
window.BXShowExtendedStat = function(stat)
{
	var out = document.getElementById('bx_ex_out');
	out.innerHTML = '';

	if (window.BXSeoCurStat != '')
	{
		document.getElementById('bx_seo_link_' + window.BXSeoCurStat).style.fontWeight = 'normal';
	}

	window.BXSeoCurStat = stat;

	document.getElementById('bx_seo_link_' + window.BXSeoCurStat).style.fontWeight = 'bold';

	if (null != window.__BXExtendedPageStat[stat])
	{
		for (var i = 0; i < window.__BXExtendedPageStat[stat].length; i++)
		{
			out.appendChild(document.createElement('P')).appendChild(document.createTextNode(window.__BXExtendedPageStat[stat][i]));
		}
	}
}

window.BXUpdatePageStats = function(stats)
{
	var obTable = document.getElementById('bx_stats_table').tBodies[0];
	var index = document.getElementById('bx_page_stats_row').sectionRowIndex;

<?
$arStats = array('URL', 'TOTAL_LENGTH', 'TOTAL_WORDS_COUNT', 'UNIQUE_WORDS_COUNT', 'META_DESCRIPTION', 'META_KEYWORDS');
foreach ($arStats as $stat):
?>
	var obRow = obTable.insertRow(++index);
	obRow.insertCell(-1).appendChild(document.createTextNode('<?echo CUtil::JSEscape(GetMessage('SEO_PAGE_STAT_'.$stat))?>: '));
	obRow.insertCell(-1).appendChild(document.createTextNode(stats.<?echo $stat?>));
	obRow.cells[0].className = 'field-name';
<?
endforeach;
?>
}

window.BXUpdatePageErrors = function(errors)
{
	var obCell = document.getElementById('bx_page_errors');
	obCell.innerHTML = '';

	if (errors.length > 0)
	{
		var str = '<ol style="padding: 0px 0px 0px 25px;">';
		for (var i = 0; i < errors.length; i++)
		{
			str += '<li>' + errors[i].TEXT + '</li>';
		}
		str += '</ol>';

		obCell.innerHTML = str;
	}
}

window.BXBlurProperty = function(element, propertyIndex)
{
	var viewProperty = document.getElementById("bx_view_property_" + propertyIndex);

	if (element.value == "" || element.value == viewProperty.innerHTML)
	{
		var editProperty = document.getElementById("bx_edit_property_" + propertyIndex);

		viewProperty.style.display = "block";
		editProperty.style.display = "none";

		while (editProperty.firstChild)
			editProperty.removeChild(editProperty.firstChild);
	}
}

window.BXEditProperty = function(propertyIndex)
{
	if (document.getElementById("bx_property_input_" + propertyIndex))
		return;

	var editProperty = document.getElementById("bx_edit_property_" + propertyIndex);
	var viewProperty = document.getElementById("bx_view_property_" + propertyIndex);

	viewProperty.style.display = "none";
	editProperty.style.display = "block";

	var input = document.createElement("INPUT");

	input.type = "text";
	input.name = "PROPERTY["+propertyIndex+"][VALUE]";

	input.style.width = "90%";
	input.style.padding = "2px";
	input.id = "bx_property_input_" + propertyIndex;
	input.onblur = function () {BXBlurProperty(input,propertyIndex)};
	input.value = viewProperty.innerHTML;

	editProperty.appendChild(input);
	input.focus();
	input.select();

	return input;
}
if (BX)
	BX.ready(BXCallPageStats);
else
	window.onload = BXCallPageStats;
</script>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>