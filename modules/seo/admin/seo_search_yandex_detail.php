<?
require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_admin_before.php");

define('ADMIN_MODULE_NAME', 'seo');

use Bitrix\Main;
use Bitrix\Main\Text\Converter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Seo\Engine;

Loc::loadMessages(__DIR__.'/../../main/tools.php');
Loc::loadMessages(__DIR__.'/seo_search.php');

if (!$USER->CanDoOperation('seo_tools'))
{
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

if(!Main\Loader::includeModule('seo'))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(Loc::getMessage("SEO_ERROR_NO_MODULE"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}

if(!Main\Loader::includeModule('socialservices'))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(Loc::getMessage("SEO_ERROR_NO_MODULE_SOCSERV"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}

$domain = mb_strtolower($_REQUEST['domain']);

if($domain)
{
	$bFound = false;
	$arDomains = \CSeoUtils::getDomainsList();
	foreach ($arDomains as $arDomain)
	{
		$arDomain['DOMAIN'] = mb_strtolower($arDomain['DOMAIN']);
		if($domain == $arDomain['DOMAIN'])
		{
			$bFound = true;
			break;
		}
	}

	if(!$bFound)
		$domain = false;
}

if(!$domain)
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(Loc::getMessage("SEO_ERROR_NO_DOMAIN"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}

$APPLICATION->SetAdditionalCSS('/bitrix/panel/seo/seo.css');

$engine = new Engine\Yandex();

$siteDomainEnc = Converter::getHtmlConverter()->encode($arDomain['DOMAIN']);
$e = [];
$siteDomainEncView = Converter::getHtmlConverter()->encode(\CBXPunycode::ToUnicode($arDomain['DOMAIN'], $e));
$siteDirEnc = Converter::getHtmlConverter()->encode($arDomain['SITE_DIR']);
try
{
	$arSiteInfo = $engine->getSiteInfo($arDomain['DOMAIN'], $arDomain['SITE_DIR']);
}
catch(Exception $e)
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError($e->getMessage());
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}

$aTabs = array(
	array("DIV" => "seo_info1", "TAB" => Loc::getMessage('SEO_DETAIL_INFO'), "ICON" => "main_settings", "TITLE" => Loc::getMessage('SEO_DETAIL_INFO_TITLE', array('#DOMAIN#' => $siteDomainEncView))),
	array("DIV" => "seo_info2", "TAB" => Loc::getMessage('SEO_DETAIL_TOP_QUERIES'), "ICON" => "main_settings", "TITLE" => Loc::getMessage('SEO_DETAIL_TOP_QUERIES_TITLE', array('#DOMAIN#' => $siteDomainEncView)), 'ONSELECT' => 'window.BXLoadInfo(\'top-queries\')'),
//	array("DIV" => "seo_info3", "TAB" => Loc::getMessage('SEO_DETAIL_CRAWLING'), "ICON" => "main_settings", "TITLE" => Loc::getMessage('SEO_DETAIL_CRAWLING_TITLE', array('#DOMAIN#' => $siteDomainEncView)), 'ONSELECT' => 'window.BXLoadInfo(\'crawling\')'),
	array("DIV" => "seo_info4", "TAB" => Loc::getMessage('SEO_DETAIL_ORIGINAL'), "ICON" => "main_settings", "TITLE" => Loc::getMessage('SEO_DETAIL_ORIGINAL_TITLE', array('#DOMAIN#' => $siteDomainEncView)), 'ONSELECT' => 'window.BXLoadInfo(\'original_texts\')'),
);

$tabControl = new CAdminTabControl("seoYandexTabControl", $aTabs, true, true);

$APPLICATION->SetTitle(Loc::getMessage("SEO_YANDEX_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array();

$aMenu[] = array(
	"TEXT"	=> Loc::getMessage("SEO_DOMAIN_LIST"),
	"LINK"	=> "/bitrix/admin/seo_search_yandex.php?lang=".LANGUAGE_ID,
	"ICON"	=> "btn_list",
);

$context = new CAdminContextMenu($aMenu);
$context->Show();

$tabControl->Begin();
$tabControl->BeginNextTab();

$siteIdEnc = Converter::getHtmlConverter()->encode($arDomain['LID']);
$siteNameEnc = Converter::getHtmlConverter()->encode($arDomain['SITE_NAME']);
?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?=Loc::getMessage('SEO_DOMAIN')?>:</td>
		<td width="60%"><?=$siteDomainEncView?></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('SEO_SITE')?>:</td>
		<td>[<a href="site_edit.php?lang=<?=LANGUAGE_ID?>&amp;LID=<?=urlencode($siteIdEnc)?>"><?=$siteIdEnc?></a>] <?=$siteNameEnc?></td>
	</tr>
<?
if(is_array($arSiteInfo[$domain]))
{
	if(isset($arSiteInfo[$domain]['tic']))
	{
?>
	
	<tr>
		<td><?=Loc::getMessage('SEO_YANDEX_TCY')?>:</td>
		<td><b><?=$arSiteInfo[$domain]['tic']?></b></td>
	</tr>
<?
	}
?>
<!--	indexing status-->
	<tr>
		<td valign="top"><?=Loc::getMessage('SEO_YANDEX_CRAWLING')?>:</td>
		<td><?=$arSiteInfo[$domain]['host_data_status'] == Engine\Yandex::INDEXING_STATE_OK ?
			Loc::getMessage('SEO_YANDEX_CRAWLING_INDEXED') :
			Loc::getMessage('SEO_YANDEX_CRAWLING_'.$arSiteInfo[$domain]['host_data_status']) ?>
		</td>
	</tr>
<?
	if(isset($arSiteInfo[$domain]['downloaded_pages_count']))
	{
?>
	<tr>
		<td><?=Loc::getMessage('SEO_YANDEX_URL_COUNT')?>:</td>
		<td><?=$arSiteInfo[$domain]['downloaded_pages_count']?></td>
	</tr>
<?
	}
	if(isset($arSiteInfo[$domain]['excluded_pages_count']))
	{
?>
	<tr>
		<td><?=Loc::getMessage('SEO_YANDEX_URL_ERRORS')?>:</td>
		<td><?=$arSiteInfo[$domain]['excluded_pages_count']?></td>
	</tr>
<?
	}
	if(isset($arSiteInfo[$domain]['searchable_pages_count']))
	{
?>
	<tr>
		<td><?=Loc::getMessage('SEO_YANDEX_INDEX_COUNT')?>:</td>
		<td><?=$arSiteInfo[$domain]['searchable_pages_count']?></td>
	</tr>
<?
	}
	
	if(isset($arSiteInfo[$domain]['site_problems']))
	{
?>
	<tr><td><?=Loc::getMessage('SEO_YANDEX_SITE_PROBLEMS')?>:</td><td>
	<?foreach($arSiteInfo[$domain]['site_problems'] as $problem => $count):?>
		<?=Loc::getMessage('SEO_YANDEX_SITE_PROBLEMS_TYPE_'.$problem)?> - <?=$count?><br>
	<?endforeach;?>
	</td></tr>
<?
	}
?>
	<tr>
		<td colspan="2" align="center"><?=BeginNote()?><a href="http://webmaster.yandex.ru/" target="_blank"><?=Loc::getMessage('SEO_YANDEX_WEBMASTER_TOOLS_LINK')?></a><?=EndNote()?></td>
	</tr>
<?
}
else
{
?>
<tr>
	<td></td><td><?=BeginNote(),Loc::getMessage('SEO_ERROR_NO_INFO', array("#DOMAIN#" => $siteDomainEncView)),EndNote()?></td>
</tr>
<?
}

$tabControl->BeginNextTab();
?>
<tr>
	<td><div id="seo_yandex_top-queries" align="center"><?=BeginNote(),Loc::getMessage('SEO_LOADING'),EndNote();?></div></td>
</tr>
<?
$tabControl->BeginNextTab();
?>
<tr>
	<td><div id="seo_yandex_original_texts" align="center"><?=BeginNote(),Loc::getMessage('SEO_LOADING'),EndNote();?></div></td>
</tr><tr>
	<td align="center"><?=BeginNote(),Loc::getMessage('SEO_DETAIL_ORIGINAL_HINT'),EndNote();?></td>
</tr>
<?
$tabControl->End();
?>
<script>

function BXLoadInfo(action)
{
	BX.ajax.loadJSON(
		'/bitrix/tools/seo_yandex.php?action='+action+'&domain=<?=urlencode($arDomain['DOMAIN'])?>&dir=<?=urlencode($arDomain['SITE_DIR'])?>&<?=bitrix_sessid_get()?>',
		function(res)
		{
			var node = BX('seo_yandex_' + action);
			if(!!node)
			{
				node.innerHTML = '';
				if(res.error)
				{
					node.innerHTML = res.error;
				}
				else
				{
					var s = '', i = 0;
					switch(action)
					{
						case 'original_texts':
							if(res.count > 0)
							{
								s += '<table class="internal" width="70%"><tr><td width="50%" align="right"><?=CUtil::JSEscape(Loc::getMessage('SEO_DETAIL_ORIGINAL_TOTAL'))?>:</td><td width="50%"><b>'+res.count+'</b></td></tr><tr><td align="right"><?=CUtil::JSEscape(Loc::getMessage('SEO_DETAIL_ORIGINAL_CAN_ADD'))?>:</td><td><b>'+(res['can-add'] ? '<?=CUtil::JSEscape(Loc::getMessage('MAIN_YES'))?> (' + res['quota_remainder'] + ')' : '<?=CUtil::JSEscape(Loc::getMessage('MAIN_NO'))?>')+'</b></td></tr><tr><td valign="top" colspan="2"><ol>';

								for(i = 0; i < res.original_texts.length; i++)
								{
									s += '<li>'+BX.util.htmlspecialchars(res.original_texts[i]['content_snippet'])+'</li>';
								}

								s += '</ol></td></tr></table>';
							}
							else
							{
								s += '<b><?=CUtil::JSEscape(Loc::getMessage('SEO_DETAIL_ORIGINAL_EMPTY'))?></b>';
							}
						break;
						case 'top-queries':
							if(res['QUERIES'].length > 0)
							{
//								dates
								s += '<i><?=CUtil::JSEscape(Loc::getMessage('SEO_DETAIL_QUERIES_PERIOD_FROM'))?> ' + res['DATE_FROM']
									+ ' <?=CUtil::JSEscape(Loc::getMessage('SEO_DETAIL_QUERIES_PERIOD_TO'))?> ' + res['DATE_TO'] + '</i><br><br>';
									
//								header
								s += '<table class="internal" width="70%"><tr class="heading">';
								s += '<td><?=CUtil::JSEscape(Loc::getMessage('SEO_DETAIL_QUERY_TEXT'))?></td>';
								s += '<td><?=CUtil::JSEscape(Loc::getMessage('SEO_DETAIL_QUERY_SHOWS'))?></td>';
								s += '<td><?=CUtil::JSEscape(Loc::getMessage('SEO_DETAIL_QUERY_AVG_SHOW_POSITION'))?></td>';
								s += '<td><?=CUtil::JSEscape(Loc::getMessage('SEO_DETAIL_QUERY_CLICKS'))?></td>';
								s += '<td><?=CUtil::JSEscape(Loc::getMessage('SEO_DETAIL_QUERY_AVG_CLICK_POSITION'))?></td>';
								s += '</tr>';
//								all count
								s += '<tr>';
								s += '<td><b><?=CUtil::JSEscape(Loc::getMessage('SEO_DETAIL_QUERY_COUNT_ALL'))?></b></td>';
								s += '<td align="right"><b>' + res['TOTAL_SHOWS'] + '</b></td>';
								s += '<td align="right"></td>';
								s += '<td align="right"><b>' + res['TOTAL_CLICKS'] + '</b></td>';
								s += '<td align="right"></td>';
								s += '</tr>';
								
								for(i = 0; i < res['QUERIES'].length; i++)
								{
									s += '<tr>';
									s += '<td>' + BX.util.htmlspecialchars(res['QUERIES'][i]['TEXT']) + '</td>';
									s += '<td align="right">' + res['QUERIES'][i]['TOTAL_SHOWS'] + '</td>';
									s += '<td align="right">' + res['QUERIES'][i]['AVG_SHOW_POSITION'] + '</td>';
									s += '<td align="right">' + res['QUERIES'][i]['TOTAL_CLICKS'] + '</td>';
									s += '<td align="right">' + res['QUERIES'][i]['AVG_CLICK_POSITION'] + '</td>';
									s += '</tr>';
								}
								s += '</table>';
							}
							else
							{
								s += '<b><?=CUtil::JSEscape(Loc::getMessage('MAIN_NO'))?></b>';
							}

						break;
					}

					node.innerHTML = s;
				}
			}
		}
	);
}
</script>
<?
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>