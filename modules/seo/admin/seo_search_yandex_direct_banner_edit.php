<?php
require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_admin_before.php");

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @global CUserTypeManager $USER_FIELD_MANAGER
 * @global CCacheManager $CACHE_MANAGER
 */

define('ADMIN_MODULE_NAME', 'seo');

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Seo\AdvSession;
use Bitrix\Seo\Engine;
use Bitrix\Seo\Adv;
use Bitrix\Main\Text\HtmlFilter;

Loc::loadMessages(dirname(__FILE__).'/../../main/tools.php');
Loc::loadMessages(dirname(__FILE__).'/seo_search.php');
Loc::loadMessages(dirname(__FILE__).'/seo_adv.php');

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

$engine = new Engine\YandexDirect();
$currentUser = $engine->getCurrentUser();
$bNeedAuth = !is_array($currentUser);

//get string of campaign CURRENCY name
try
{
	$clientsSettings = $engine->getClientsSettings();
	$clientCurrency = current($clientsSettings);
	$clientCurrency = Loc::getMessage('SEO_YANDEX_CURRENCY__'.$clientCurrency['Currency']);
}
catch(Engine\YandexDirectException $e)
{
	$seoproxyAuthError = new CAdminMessage(array(
		"TYPE" => "ERROR",
		"MESSAGE" => Loc::getMessage('SEO_YANDEX_SEOPROXY_AUTH_ERROR'),
		"DETAILS" => $e->getMessage(),
	));
}


$bReadOnly = $bNeedAuth;
$bAllowUpdate = !$bNeedAuth;
$bSale = Main\ModuleManager::isModuleInstalled('sale')
	&& Main\ModuleManager::isModuleInstalled('catalog')
	&& Main\Loader::includeModule('currency');

$request = Main\Context::getCurrent()->getRequest();

$back_url = isset($request["back_url"]) ? $request["back_url"] : '';

$campaignId = intval($request["campaign"]);
$elementId = intval($request["element"]);
$ID = intval($request["ID"]);

$message = null;

if($ID > 0)
{
	$dbRes = Adv\YandexBannerTable::getByPrimary($ID);
	$banner = $dbRes->fetch();
	if($banner)
	{
		$campaignId = $banner["CAMPAIGN_ID"];
	}
	else
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

		$message = new CAdminMessage(array(
			"TYPE" => "ERROR",
			"DETAILS" => Loc::getMessage("SEO_ERROR_NO_BANNER"),
			"HTML" => true
		));
		echo $message->Show();

		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	}
}

$campaign = false;
if($campaignId > 0)
{
	$dbRes = Adv\YandexCampaignTable::getByPrimary($campaignId);
	$campaign = $dbRes->fetch();
}

if(!$campaign)
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$message = new CAdminMessage(array(
		"TYPE" => "ERROR",
		"DETAILS" => Loc::getMessage("SEO_ERROR_NO_CAMPAIGN"),
	));
	echo $message->Show();

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}

if($campaign['OWNER_ID'] != $currentUser['id'])
{
	$bReadOnly = true;
	$bAllowUpdate = false;

	$message = new CAdminMessage(array(
		"TYPE" => "ERROR",
		"MESSAGE" => Loc::getMessage('SEO_CAMPAIGN_WRONG_OWNER', array("#USERINFO#" => "(".$campaign["OWNER_ID"].") ".$campaign["OWNER_NAME"]))
	));
}
elseif(!in_array($campaign["SETTINGS"]['Strategy']['StrategyName'], Adv\YandexCampaignTable::$supportedStrategy))
{
	$bReadOnly = true;

	$message = new CAdminMessage(array(
		"TYPE" => "ERROR",
		"MESSAGE" => Loc::getMessage("SEO_CAMPAIGN_STRATEGY_NOT_SUPPORTED", array(
			"#STRATEGY#" => Loc::getMessage('SEO_CAMPAIGN_STRATEGY_'.$campaign["SETTINGS"]['Strategy']['StrategyName']),
		))
	));
}

$bShowStats = $ID > 0 && $bAllowUpdate;
$bShowAuto = $ID > 0 && $bAllowUpdate && IsModuleInstalled("catalog");

if($ID <= 0)
{
	$host = str_replace(array(':80', ':443'), '', $request->getHttpHost());
	$errors = null;
	$host = \CBXPunycode::ToUnicode($host, $errors);

	$banner = array(
		"SETTINGS" => array(
			"CampaignID" => $campaign["SETTINGS"]["CampaignID"],
			"Title" => "",
			"Text" => "",
			"Href" => 'http'.($request->isHttps() ? 's' : '').'://'.$host,
			"Geo" => Main\Config\Option::get('seo', 'yandex_direct_region_last_list', ''),
			"Phrases" => array(),
			"MinusKeywords" => array(),
		)
	);

	if($elementId > 0 && Main\Loader::includeModule('iblock'))
	{
		$dbElement = \CIBlockElement::getByID($elementId);
		if($element = $dbElement->fetch())
		{
			$banner['SETTINGS']['Href'] .= \CIBlock::replaceDetailUrl($element['DETAIL_PAGE_URL'], $element, false, "E");
		}
	}
	else
	{
		$banner['SETTINGS']['Href'] .= '/';
	}

	$banner['SETTINGS']['Href'] .= strpos($banner['SETTINGS']['Href'], "?") >= 0 ? '?' : '&';
	$banner['SETTINGS']['Href'] .= AdvSession::URL_PARAM_CAMPAIGN.'='.AdvSession::URL_PARAM_CAMPAIGN_VALUE.'&'.AdvSession::URL_PARAM_BANNER.'='.AdvSession::URL_PARAM_BANNER_VALUE;
}

$banner["SETTINGS"]["Geo"] = explode(",", $banner["SETTINGS"]["Geo"]);
$banner["SETTINGS"]["MinusKeywords"] = !empty($banner["SETTINGS"]["MinusKeywords"]) ?
	implode(", ", $banner["SETTINGS"]["MinusKeywords"]) :
	'';

$aTabs = array(
	array(
		"DIV" => "edit_main",
		"TAB" => Loc::getMessage("SEO_BANNER_TAB_MAIN"),
		"TITLE" => Loc::getMessage("SEO_BANNER_TAB_MAIN_TITLE"),
	),
	array(
		"DIV" => "edit_geo",
		"TAB" => Loc::getMessage("SEO_BANNER_TAB_GEO"),
		"TITLE" => Loc::getMessage("SEO_BANNER_TAB_GEO_TITLE"),
	),
	array(
		"DIV" => "edit_keywords",
		"TAB" => Loc::getMessage("SEO_BANNER_TAB_KEYWORDS"),
		"TITLE" => Loc::getMessage("SEO_BANNER_TAB_KEYWORDS_TITLE"),
	),
);

if($ID > 0 && $bShowAuto)
{
	$aTabs[] = array(
		"DIV" => "edit_auto",
		"TAB" => Loc::getMessage("SEO_BANNER_TAB_AUTO"),
		"TITLE" => Loc::getMessage("SEO_BANNER_TAB_AUTO_TITLE"),
	);
}

if($ID > 0 && $bShowStats)
{
	$aTabs[] = array(
		"DIV" => "edit_stats",
		"TAB" => Loc::getMessage("SEO_BANNER_TAB_STATS"),
		"TITLE" => Loc::getMessage("SEO_BANNER_TAB_STATS_TITLE"),
		"ONSELECT" => "showStats()"
	);
}

$tabControl = new CAdminTabControl("tabControl", $aTabs);


if(!$bReadOnly && $request->isPost() && ($request["save"]<>'' || $request["apply"]<>'') && check_bitrix_sessid())
{
	$bannerSettings = $request["SETTINGS"];

	$phraseList = $request['Phrase'];
	$phrasePriority = $request['PhrasePriority'];
	if(!is_array($phraseList))
	{
		$phraseList = array();
		$phrasePriority = array();
	}

	$bannerSettings['Phrases'] = array();

	foreach($phraseList as $key => $phrase)
	{
		$bannerSettings['Phrases'][] = array(
			'PhraseID' => '0',
			'Phrase' => trim($phrase),
			'AutoBudgetPriority' => $phrasePriority[$key]
		);
	}

	$bannerSettings['MinusKeywords'] = preg_split("/[\\r\\n,;]+\\s*/", $bannerSettings['MinusKeywords']);

	$bannerFields = array(
		"CAMPAIGN_ID" => $campaignId,
		"SETTINGS" => $bannerSettings
	);

	if($bShowAuto && $banner["AUTO_QUANTITY_OFF"] != Adv\YandexBannerTable::MARKED)
	{
		$bannerFields["AUTO_QUANTITY_OFF"] = $request["AUTO_QUANTITY_OFF"] == Adv\YandexBannerTable::ACTIVE ? Adv\YandexBannerTable::ACTIVE : Adv\YandexBannerTable::INACTIVE;
	}

	if($bShowAuto && $banner["AUTO_QUANTITY_ON"] != Adv\YandexBannerTable::MARKED)
	{
		$bannerFields["AUTO_QUANTITY_ON"] = $request["AUTO_QUANTITY_ON"] == Adv\YandexBannerTable::ACTIVE ? Adv\YandexBannerTable::ACTIVE : Adv\YandexBannerTable::INACTIVE;
	}

	if($ID > 0)
	{
		$result = Adv\YandexBannerTable::update($ID, $bannerFields);
	}
	else
	{
		$result = Adv\YandexBannerTable::add($bannerFields);
	}

	if($result->isSuccess())
	{
		if($ID <= 0 && $elementId > 0)
		{
			Adv\LinkTable::add(array(
				'LINK_TYPE' => Adv\LinkTable::TYPE_IBLOCK_ELEMENT,
				'LINK_ID' => $elementId,
				'BANNER_ID' => $result->getId()
			));
		}

		if($ID <= 0)
		{
			Main\Config\Option::set('seo', 'yandex_direct_region_last_list', $bannerSettings["Geo"]);
		}

		$ID = $result->getId();

		if($request["apply"]<>'')
		{
			LocalRedirect('/bitrix/admin/seo_search_yandex_direct_banner_edit.php?lang='.LANGUAGE_ID.'&ID='.$ID.'&'.$tabControl->ActiveTabParam());
		}
		else
		{
			if($back_url == '')
			{
				LocalRedirect("/bitrix/admin/seo_search_yandex_direct_banner.php?lang=".LANGUAGE_ID.'&campaign='.$campaign['ID']);
			}
			else
			{
				LocalRedirect($back_url);
			}
		}
	}
	else
	{
		$oldSettings = $banner['SETTINGS'];

		$banner["SETTINGS"] = $request["SETTINGS"];
		$banner["SETTINGS"]["Geo"] = explode(",", $banner["SETTINGS"]["Geo"]);
		$banner["SETTINGS"]["Phrases"] = array();

		if(is_array($request['Phrase']))
		{
			foreach($request["Phrase"] as $key => $phrase)
			{
				$phraseStatus = Engine\YandexDirect::STATUS_NEW;
				if(is_array($oldSettings["Phrases"]))
				{
					foreach($oldSettings["Phrases"] as $phraseInfo)
					{
						if($phraseInfo['Phrase'] == $phrase)
						{
							$phraseStatus = $phraseInfo['StatusPhraseModerate'];
							break;
						}
					}
				}

				$banner["SETTINGS"]["Phrases"][] = array(
					'Phrase' => $phrase,
					'AutoBudgetPriority' => $request["PhrasePriority"][$key],
					'StatusPhraseModerate' => $phraseStatus,
				);
			}
		}

		$message = new CAdminMessage(array(
			"TYPE" => "ERROR",
			"MESSAGE" => Loc::getMessage('SEO_BANNER_ERROR'),
			"DETAILS" => implode('<br>', $result->getErrorMessages()),
		));
	}
}

$APPLICATION->SetTitle(
	$ID > 0
		? Loc::getMessage("SEO_BANNER_EDIT_TITLE", array(
			"#ID#" => $ID,
			"#XML_ID#" => $banner["XML_ID"],
		))
		: Loc::getMessage("SEO_BANNER_NEW_TITLE")
);

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"ICON" => "btn_list",
		"TEXT" => Loc::getMessage("SEO_BANNER_LIST"),
		"LINK" => "seo_search_yandex_direct_banner.php?lang=".LANGUAGE_ID."&campaign=".$campaignId
	)
);

if($ID > 0)
{
	if(!$bReadOnly)
	{
		$aMenu[] = array(
			"ICON" => "btn_new",
			"TEXT" => Loc::getMessage("MAIN_ADD"),
			"LINK" => "seo_search_yandex_direct_banner_edit.php?lang=".LANGUAGE_ID."&campaign=".$campaignId
		);

		$aMenu[] = array(
			"TEXT" => Loc::getMessage("MAIN_DELETE"),
			"ICON" => "btn_delete",
			"LINK" => "seo_search_yandex_direct_banner.php?lang=".LANGUAGE_ID."&campaign=".$campaignId."&ID=".$ID."&action=delete&".bitrix_sessid_get()
		);
	}
}

if(!defined('BX_PUBLIC_MODE') || !BX_PUBLIC_MODE)
{
	require_once("tab/seo_search_yandex_direct_auth.php");
}

if(isset($seoproxyAuthError))
	echo $seoproxyAuthError->Show();

$context = new CAdminContextMenu($aMenu);
$context->Show();

if(!$message && $bShowAuto)
{
	if($banner["AUTO_QUANTITY_OFF"] == Adv\YandexBannerTable::MARKED)
	{
		$message = new CAdminMessage(array(
			"TYPE" => "ERROR",
			"MESSAGE" => Loc::getMessage("SEO_BANNER_AUTO_QUANTITY_OFF_D"),
			"DETAILS" => Loc::getMessage("SEO_BANNER_AUTO_QUANTITY_OFF_D_DETAILS"),
		));
	}
	elseif($banner["AUTO_QUANTITY_ON"] == Adv\YandexBannerTable::MARKED)
	{
		$message = new CAdminMessage(array(
			"TYPE" => "OK",
			"MESSAGE" => Loc::getMessage("SEO_BANNER_AUTO_QUANTITY_ON_D"),
			"DETAILS" => Loc::getMessage("SEO_BANNER_AUTO_QUANTITY_ON_D_DETAILS"),
		));
	}
}

if($message)
{
	echo $message->Show();
}

if($bReadOnly && $ID <= 0)
{
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
}

// draw form even in readonly mode
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>&amp;ID=<?=$ID?>&amp;campaign=<?=$campaignId?>" name="form1" id="form1"<?=$bReadOnly?' onsubmit="return false;' : ''?>>
<?

$tabControl->Begin();

// main settings tab
$tabControl->BeginNextTab();
?>
<style>

	.yandex-adv-stats
	{
		display: block;
		white-space: nowrap;
		padding: 9px 5px 3px 5px;
		width: 15px;
		text-align: right;
	}

</style>

<?
if($ID > 0):

	$active = '';
	$active_title = '';

	if($banner['SETTINGS']['StatusBannerModerate'] == Engine\YandexDirect::STATUS_NEW)
	{
		$active = 'grey';
		$active_title = Loc::getMessage('SEO_STATUS_MODERATE_NEW');
	}
	else
	{
		if($banner['SETTINGS']['IsActive'] == Engine\YandexDirect::BOOL_YES)
		{
			if($banner['SETTINGS']['StatusActivating'] == Engine\YandexDirect::STATUS_PENDING)
			{
				$active = 'yellow';
				$active_title = Loc::getMessage('SEO_STATUS_PENDING');
			}
			else
			{
				$active = 'green';
				$active_title = Loc::getMessage('SEO_STATUS_SHOW_YES');
			}
		}
		else
		{
			if($banner['SETTINGS']['StatusActivating'] == Engine\YandexDirect::STATUS_PENDING)
			{
				if($banner['SETTINGS']['StatusBannerModerate'] == Engine\YandexDirect::STATUS_PENDING)
				{
					$active = 'grey';
					$active_title = Loc::getMessage('SEO_STATUS_MODERATE_PENDING');
				}
				elseif($banner['SETTINGS']['StatusShow'] == Engine\YandexDirect::BOOL_YES)
				{
					$active = 'grey';
					$active_title = Loc::getMessage('SEO_STATUS_SHOW_PENDING');
				}
				else
				{
					$active = 'grey';
					$active_title = Loc::getMessage('SEO_STATUS_SHOW_OFF');
				}
			}
			else
			{
				$active = 'red';
				$active_title = Loc::getMessage('SEO_ISACTIVE_NO');
			}
		}
	}
?>
	<tr>
		<td><?=Loc::getMessage("SEO_ISACTIVE")?>:</td>
		<td colspan="3">
			<div class="lamp-<?=$active?>" style="display:inline-block;"></div>&nbsp;<?=$active_title?>
<?
	if($bAllowUpdate)
	{
?>
		&nbsp;&nbsp;<a href="javascript:void(0)" onclick="updateBanner(this, '<?=$ID?>')"><?=Loc::getMessage("SEO_BANNER_LIST_UPDATE");?></a>
<?
	}
?>
		</td>
	</tr>
<?
endif;
?>

	<tr class="adm-detail-required-field">
		<td width="40%" valign="top"><?=Loc::getMessage("SEO_BANNER_DATA")?>:</td>
		<td width="0" valign="top">
			<input type="text" name="SETTINGS[Title]" placeholder="<?=Loc::getMessage('SEO_BANNER_DATA_TITLE')?>" value="<?=HtmlFilter::encode($banner["SETTINGS"]["Title"])?>" id="title_content" style="width: 250px;" maxlength="<?=Adv\YandexBannerTable::MAX_TITLE_LENGTH;?>" onkeyup="updateAdv()" onchange="updateAdv()" onpaste="updateAdv()" tabindex="1">
		</td>
		<td width="0" valign="top">
			<span id="title_stats" class="yandex-adv-stats"><?=Adv\YandexBannerTable::MAX_TITLE_LENGTH-strlen($banner["SETTINGS"]["Title"])?></span>
		</td>
		<td width="60%" valign="top" rowspan="2">
<?
$bannerInfo = $ID > 0 ? $banner : null;
require("tab/seo_search_yandex_direct_banner.php");
?>
		</td>
	</tr>
	<tr>
		<td></td>
		<td valign="top">
			<textarea name="SETTINGS[Text]" placeholder="<?=Loc::getMessage('SEO_BANNER_DATA_TEXT')?>"  id="text_content"  style="width: 250px; height: 100px;" maxlength="<?=Adv\YandexBannerTable::MAX_TEXT_LENGTH;?>" onkeyup="updateAdv()" onchange="updateAdv()" onpaste="updateAdv()" tabindex="2"><?=HtmlFilter::encode($banner["SETTINGS"]["Text"])?></textarea>
		</td>
		<td valign="top">
			<span id="text_stats" class="yandex-adv-stats"><?=Adv\YandexBannerTable::MAX_TEXT_LENGTH-strlen($banner["SETTINGS"]["Text"])?></span>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td width="40%" valign="top"><?=Loc::getMessage("SEO_BANNER_HREF")?>:</td>
		<td colspan="3">
			<input type="text" name="SETTINGS[Href]" value="<?=HtmlFilter::encode($banner["SETTINGS"]["Href"])?>" id="link_content" style="width: 500px;" onkeyup="updateAdv()" onchange="updateAdv()" onpaste="updateAdv()" tabindex="3">
			<div id="link_hint" style="display: none;">
<?
echo BeginNote();
echo Loc::getMessage('SEO_LINK_HINT', array(
	"#PARAMS_HINT#" => "<ul><li>".AdvSession::URL_PARAM_CAMPAIGN."=".AdvSession::URL_PARAM_CAMPAIGN_VALUE."</li><li>".AdvSession::URL_PARAM_BANNER."=".AdvSession::URL_PARAM_BANNER_VALUE."</li></ul>"
));
?>
				<a class="bx-action-href" hidefocus="true" onclick="addUrlParams();" href="javascript:void(0)"><?=Loc::getMessage('SEO_LINK_HINT_ACTION')?></a>
<?
echo EndNote();
?>
			</div>
		</td>
	</tr>
<?

if(($ID > 0 || $elementId > 0) && Main\Loader::includeModule('iblock'))
{
?>
	<tr class="heading"><td colspan="4"><?=Loc::getMessage("SEO_BANNER_SALE_SECTION")?></td></tr>
	<tr>
		<td valign="top"><?=Loc::getMessage("SEO_BANNER_LINKS")?>:</td>
		<td valign="top" colspan="3">
<?
	if($ID > 0)
	{
		$dbRes = Adv\LinkTable::getList(array(
			"filter" => array(
				'=BANNER_ID' => $ID,
			),
			"select" => array(
				"LINK_TYPE", "LINK_ID",
				"ELEMENT_NAME" => "IBLOCK_ELEMENT.NAME",
				"ELEMENT_IBLOCK_ID" => "IBLOCK_ELEMENT.IBLOCK_ID",
				"ELEMENT_IBLOCK_TYPE_ID" => "IBLOCK_ELEMENT.IBLOCK.IBLOCK_TYPE_ID",
				'ELEMENT_IBLOCK_SECTION_ID' => 'IBLOCK_ELEMENT.IBLOCK_SECTION_ID',
			)
		));
	}
	else
	{
		$dbRes = \Bitrix\Iblock\ElementTable::getList(array(
			'filter' => array(
				"=ID" => $elementId,
			),
			'select' => array(
				'LINK_ID' => 'ID',
				'ELEMENT_NAME' => 'NAME',
				'ELEMENT_IBLOCK_ID' => 'IBLOCK_ID',
				'ELEMENT_IBLOCK_TYPE_ID' => 'IBLOCK.IBLOCK_TYPE_ID',
				'ELEMENT_IBLOCK_SECTION_ID' => 'IBLOCK_SECTION_ID',
			)
		));
	}

	$arLinks = array();
	while($link = $dbRes->fetch())
	{
		if(!isset($link['LINK_TYPE']) && $elementId > 0)
		{
			$link['LINK_TYPE'] = Adv\LinkTable::TYPE_IBLOCK_ELEMENT;
		}

		$arLinks[] = $link;
	}

?>
			<div id="adv_link_list">
<?
	require_once("tab/seo_search_yandex_direct_list_banner.php");
?>
			</div>
		</td>
	</tr>
<?
	if($ID > 0)
	{
?>
	<tr>
		<td></td>
		<td colspan="3">
			<input type="hidden" id="new_link_container[]" onchange="createLink(this.value, '<?=Adv\LinkTable::TYPE_IBLOCK_ELEMENT?>')">
			<a href="javascript:void(0)" onclick="BX.util.popup('/bitrix/admin/iblock_element_search.php?lang=ru&n=new_link_container', 1000, 700);"><?=Loc::getMessage('SEO_BANNER_LINK_CREATE_ITEM')?></a><br />
		</td>
	</tr>
<?
if($bShowAuto):
?>
	<tr>
		<td colspan=4" align="center">

			<?=BeginNote().Loc::getMessage("SEO_BANNER_AUTO_HINT").EndNote();?>

		</td>
	</tr>
<?
endif;
?>
	<script>
		function createLink(linkId, linkType)
		{
			BX.ajax.loadJSON('/bitrix/tools/seo_yandex_direct.php?action=link_create&banner=<?=$ID?>&link='+linkId+'&link_type='+linkType+'&get_list_html=2&sessid='+BX.bitrix_sessid(), function(res)
			{
				BX('adv_link_list').innerHTML = res.list_html;
				BX.onCustomEvent("OnSeoYandexDirectLinksChange", [BX('adv_link_list')]);
			});
		}
	</script>
<?
	}

}
?>
<script>
<?
if($bAllowUpdate):
?>
function updateBanner(btn, bannerId)
{
	if(!!btn._innerHTML)
	{
		return;
	}

	//BX.addClass(btn, 'adm-btn-active');
	btn._innerHTML = btn.innerHTML;
	btn.innerHTML = '<?=Loc::getMessage('SEO_YANDEX_DIRECT_LOADING')?>';

	var url = '/bitrix/tools/seo_yandex_direct.php?action=banner_update&campaign=<?=$campaignId?>&banner=' + BX.util.urlencode(bannerId);

	BX.ajax.loadJSON(url + '&sessid=' + BX.bitrix_sessid(), function(res){
		if(BX.hasClass(btn, 'adm-btn-active'))
		{
			//BX.removeClass(btn, 'adm-btn-active');
			btn.innerHTML = btn._innerHTML;
			delete btn._innerHTML;
		}

		if(!!res.error && (!!res.error.message || !!res.error.code))
		{
			alert(res.error.message||res.error.code);
		}
		else
		{
			BX.reload();
		}
	});
}
<?
endif;
?>

var urlParams = [
	['<?=AdvSession::URL_PARAM_CAMPAIGN?>', '<?=AdvSession::URL_PARAM_CAMPAIGN_VALUE?>'],
	['<?=AdvSession::URL_PARAM_BANNER?>', '<?=AdvSession::URL_PARAM_BANNER_VALUE?>']
];

function checkUrl(url)
{
	return url.indexOf(urlParams[0].join('=')) >= 0 && url.indexOf(urlParams[1].join('=')) >= 0;
}

function addUrlParams()
{
	var s = BX('link_content').value;

	if(s.indexOf('?') < 0 && s[s.length-1] != '/')
		s += '/';

	var p = {};
	p[urlParams[0][0]] = urlParams[0][1];
	p[urlParams[1][0]] = urlParams[1][1];

	BX('link_content').value = BX.util.add_url_param(s, p);
	updateAdv();
}

function showUrlHint(link)
{
	BX('link_hint').style.display = checkUrl(link) ? 'none' : 'block';
}

function updateAdv()
{
	var title = BX('title_content').value,
		text = BX('text_content').value,
		link = BX('link_content').value,
		domain = window.location.host;

	showUrlHint(link)

	var r = link.match(/\/\/([^\/]+)/i);
	if(r)
	{
		domain = r[1];
	}

	BX('title_stats').innerHTML = <?=Adv\YandexBannerTable::MAX_TITLE_LENGTH;?>-title.length;
	BX('text_stats').innerHTML = <?=Adv\YandexBannerTable::MAX_TEXT_LENGTH;?>-text.length;

	BX('yandex_title_content').innerHTML = BX.util.htmlspecialchars(title||BX('title_content').placeholder);
	BX('yandex_text_content').innerHTML = BX.util.htmlspecialchars(text||BX('text_content').placeholder).replace(/\n+/g, ' ');
	BX('yandex_link_content').innerHTML = BX.util.htmlspecialchars(domain);
	BX('yandex_link_content_link').innerHTML = BX.util.htmlspecialchars(domain);
	BX('yandex_link').href = link;
	BX('yandex_link_content_link').href = link;

}
BX.ready(updateAdv);
</script>
<?
// geo tab
$tabControl->BeginNextTab();

$dbRes = Adv\YandexRegionTable::getList(array(
	'order' => array('PARENT_XML_ID' => 'ASC'),
	'select' => array('ID', 'NAME', 'XML_ID', 'PARENT_ID', 'PARENT_XML_ID' => 'PARENT.XML_ID')
));

$regions = array();
$regionsOutput = array();

while($region = $dbRes->fetch())
{
	$regionsOutput[$region['PARENT_XML_ID']] = '';
	$regions[] = $region;
}

foreach($regions as $region)
{
	$bHasChildren = isset($regionsOutput[$region["XML_ID"]]);
	$bHasParent = $region['PARENT_XML_ID'] !== null;

	if($regionsOutput[$region["PARENT_XML_ID"]] === '')
	{
		$regionsOutput[$region["PARENT_XML_ID"]] = '<div id="regions_'.$region["PARENT_XML_ID"].'" style="display:'.($bHasParent ? 'none' : 'block').'">';
	}

	if($bHasChildren)
	{
		$button = '<span onclick="showRegions(\''.$region["XML_ID"].'\', this.parentNode)" class="openbutton"></span>';
	}
	else
	{
		$button = '<span class="openbutton empty"></span>';
	}

	$regionsOutput[$region["PARENT_XML_ID"]] .= '<div class="region-closed">'.$button.'<input type="checkbox" name="region[]" id="region_'.$region['XML_ID'].'" value="'.$region['XML_ID'].'" data-parent="'.$region['PARENT_XML_ID'].'"  data-title="'.HtmlFilter::encode($region['NAME']).'">&nbsp;<label for="region_'.$region['XML_ID'].'">'.HtmlFilter::encode($region['NAME']).'</label></div>';
}
?>
<style>
.openbutton
{
	display: inline-block;
	height: 20px;
	width: 20px;
	cursor: pointer;
}

.region-opened, .region-closed
{
	cursor: pointer;
}

.region-closed .openbutton
{
	background: url("/bitrix/panel/main/images/bx-admin-sprite.png") no-repeat scroll -10px -233px rgba(0, 0, 0, 0);
}

.region-opened > .openbutton
{
	background: url("/bitrix/panel/main/images/bx-admin-sprite.png") no-repeat scroll -10px -211px rgba(0, 0, 0, 0);
}

.openbutton.empty
{
	background: url("/bitrix/panel/main/images/bx-admin-sprite-small-1.png") no-repeat scroll -3px -1132px rgba(0, 0, 0, 0) !important;
}

span.loading-message-text
{
	background: url('/bitrix/panel/main/images/waiter-small-white.gif') no-repeat left center;
	padding-left: 20px;
	margin: 0 0 0 15px;
	line-height: 15px;
	font-size: 11px;
	display: inline-block;
}

span.yandex-delete
{
	display: inline-block;
	height: 20px;
	width: 20px;
	cursor: pointer;
	background: url("/bitrix/panel/main/images/bx-admin-sprite-small-1.png") no-repeat scroll 5px -2446px rgba(0, 0, 0, 0);
}
</style>
<input type="hidden" id="geo_settings" name="SETTINGS[Geo]" value="<?=implode(',',$banner["SETTINGS"]["Geo"])?>" />
<tr>
	<td colspan="2" align="center">
		<table class="internal" width="80%">
			<tr class="heading">
				<td width="50%"><?=Loc::getMessage('SEO_YANDEX_DIRECT_CHOOSE_REGIONS')?></td>
				<td width="50%"><?=Loc::getMessage('SEO_YANDEX_DIRECT_CHOSEN_REGIONS')?></td>
			</tr>
			<tr>
				<td valign="top">
					<div class="regions-list" id="regions-list">
<?
echo implode('</div>', $regionsOutput).'</div>';
?>

					</div>
				</td>
				<td valign="top"><ul id="regions-selected">
				</ul></td>
			</tr>
		</table>
	</td>
</tr>
<script>
	function showRegions(id, el)
	{
		var r = BX('regions_' + id);
		if(r)
		{
			if(BX.hasClass(el, 'region-opened'))
			{
				el.className = 'region-closed';
				r.style.display = 'none';
			}
			else
			{
				el.appendChild(r);
				r.style.display = 'block';
				r.style.paddingLeft = '20px';

				el.className = 'region-opened';
			}
		}
	}

	BX.ready(function()
	{
		var listCont = BX('regions-list'), resultCont = BX('regions-selected');

		function checkAll(id, bChecked)
		{
			var r = BX('regions_' + id);
			if(r)
			{
				var subReg = BX.findChildren(r, {tag: 'INPUT', property: {type: 'checkbox', name: 'region[]'}}, true);
				if (subReg)
				{
					for (var i = 0; i < subReg.length; i++)
					{
						subReg[i].checked = bChecked;
						checkAll(subReg[i].value, bChecked);
					}
				}
			}
		}


		function drawAll(id, bParentChecked, drawRes, valueRes)
		{
			var c = BX('region_' + id);
			if(c.checked != bParentChecked)
			{
				if(c.checked)
				{
					drawRes.push('<b>' + BX.util.htmlspecialchars(c.getAttribute('data-title')) + '</b>');
				}
				else
				{
					drawRes.push('<?=Loc::getMessage('SEO_YANDEX_REGIONS_BESIDES', array("#NAME#" => "' + BX.util.htmlspecialchars(c.getAttribute('data-title')) + '"))?>');
				}

				valueRes.push((c.checked ? '' : '-') + c.value);
			}

			var r = BX('regions_' + id);
			if(r)
			{
				var subReg = BX.findChildren(r, {tag: 'INPUT', property: {type: 'checkbox', name: 'region[]'}, attribute: {'data-parent': id}}, true);
				if (subReg)
				{
					for (var i = 0; i < subReg.length; i++)
					{
						drawAll(subReg[i].value, c.checked, drawRes, valueRes);
					}
				}
			}
		}

		var h = function()
		{
			var i, id = this.value,drawRes = [], valueRes = [];
			checkAll(id, this.checked);
			drawAll('0', false, drawRes, valueRes);

			if(drawRes.length > 0)
				resultCont.innerHTML = '<li>' + drawRes.join('</li><li>') + '</li>';
			else
				resultCont.innerHTML = '<li><b><?=Loc::getMessage('SEO_YANDEX_REGIONS_ALL')?></b></li>';

			document.forms.form1['SETTINGS[Geo]'].value = valueRes.join(',');
		};

		var startValue = document.forms.form1['SETTINGS[Geo]'].value;

		BX.bindDelegate(listCont, 'click', {tag: 'INPUT', props: {type: 'checkbox', name: 'region[]'}}, h)

		if(startValue == '')
		{
			startValue = '0';
		}

		startValue = startValue.split(',');
		for(var i = 0; i < startValue.length; i++)
		{
			var bChecked = true;
			if(startValue[i].charAt(0) == '-')
			{
				bChecked = false;
				startValue[i] = startValue[i].substring(1);
			}

			var checkbox = BX('region_' + startValue[i]);
			checkbox.checked = bChecked;

			h.apply(checkbox, []);
		}

		showRegions("0", BX('region_0').parentNode);
	})
</script>

<?
// keywords tab
$tabControl->BeginNextTab();

$phraseString = "";
$phrasePriority = array();
$phraseStatus = array();

foreach($banner["SETTINGS"]["Phrases"] as $phraseData)
{
	$phraseString .= $phraseData["Phrase"]."\n";
	$phrasePriority[$phraseData["Phrase"]] = $phraseData["AutoBudgetPriority"];
	$phraseStatus[$phraseData["Phrase"]] = $phraseData["StatusPhraseModerate"];
}

?>

<tr class="heading">
	<td colspan="2"><?=Loc::getMessage('SEO_YANDEX_WORDSTAT')?></td>
</tr>

<tr>
	<td colspan="2">
		<textarea id="phrase_text" style="width: 99%; margin-bottom: 15px;" rows="3"><?=HtmlFilter::encode($phraseString)?></textarea><br />
		<input type="button" value="<?=Loc::getMessage('SEO_YANDEX_WORDSTAT_STAT')?>" onclick="showWordstatReport();" name="template_preview" id="wordstat_button"><span id="wordstat_wait" class="loading-message-text" style="display: none"><?=Loc::getMessage('SEO_YANDEX_WORDSTAT_WAIT')?></span><span id="wordstat_wait_more" class="loading-message-text" style="display: none"><?=Loc::getMessage('SEO_YANDEX_WORDSTAT_WAIT_MORE')?></span>
		<div id="worstat_report" style="text-align: center;"></div>
	</td>
</tr>

<tr class="heading">
	<td colspan="2"><?=Loc::getMessage('SEO_YANDEX_FORECAST')?></td>
</tr>

<tr id="forecast_block">
	<td colspan="2">
		<input type="button" name="template_preview" id="forecast_button" value="<?=Loc::getMessage('SEO_YANDEX_FORECAST_GET');?>" onclick="showForecast();" /><span id="forecast_wait" class="loading-message-text" style="display: none;"><?=Loc::getMessage('SEO_YANDEX_FORECAST_WAIT');?></span><span id="forecast_wait_more" class="loading-message-text" style="display: none;"><?=Loc::getMessage('SEO_YANDEX_FORECAST_WAIT_MORE')?></span>
		<div id="forecast_block_content" style="text-align: center; margin-top: 15px;"></div>
	</td>
</tr>

<tr class="heading">
	<td colspan="2"><?=Loc::getMessage('SEO_MINUS_KEYWORDS')?></td>
</tr>
<tr>
	<td colspan="2">
		<textarea id="minus_text" style="width: 99%;" rows="3" name="SETTINGS[MinusKeywords]"><?=HtmlFilter::encode($banner["SETTINGS"]["MinusKeywords"])?></textarea>
	</td>
</tr>


<script>
	var phraseList = [];
	var phrasePriority = <?=count($phrasePriority) > 0 ? CUtil::PhpToJSObject($phrasePriority) : '{}';?>;
	var phraseStatus = <?=count($phraseStatus) > 0 ? CUtil::PhpToJSObject($phraseStatus) : '{}';?>;
	var lastForecast = {};

	function parsePhraseList()
	{
		var textInput = BX('phrase_text');

		if(textInput.offsetHeight < textInput.scrollHeight)
		{
			textInput.style.height = (textInput.offsetHeight + 20) + 'px';
		}

		var v = BX.util.trim(textInput.value);

		if(v.length > 0)
		{
			phraseList = BX.util.array_unique(v.split(/[\n,;]+\s*/));
		}
		else
		{
			phraseList = [];
		}

		updatePhraseList();

		BX('wordstat_button').disabled = phraseList.length <= 0;
		BX('forecast_button').disabled = phraseList.length <= 0;
	}

	function addPhrase(phrase)
	{
		phraseList.push(phrase);
		BX('phrase_text').value = BX.util.array_unique(phraseList).join('\n');

		parsePhraseList();
	}


	function removePhrase(phrase)
	{
		var key = BX.util.array_search(phrase, phraseList);
		if(key >= 0)
		{
			phraseList = BX.util.deleteFromArray(phraseList, key);
		}
		BX('phrase_text').value = phraseList.join('\n');

		parsePhraseList();
	}


	function updatePhraseList()
	{
		var phraseStruct = {Phrases:[]};

		for(var i = 0; i < phraseList.length; i++)
		{
			if(phraseList[i].length > 0)
			{
				phraseStruct.Phrases.push({
					Phrase: phraseList[i],
					Shows: 'N/A',
					Clicks: 'N/A',
					Min: 'N/A',
					CTR: 'N/A'
				});
			}
		}

		BX('forecast_block_content').innerHTML = getForecastContent(phraseStruct, true);
	}

	function showWordstatReport()
	{
		BX('wordstat_button').disabled = true;
		BX('wordstat_wait').style.display = 'inline-block';
		BX('wordstat_wait_more').style.display = 'none';

		var yandexTimeout = null;

		var f = function (bClear)
		{
			BX.ajax.loadJSON('/bitrix/tools/seo_yandex_direct.php', {
				action: !!bClear ? 'wordstat_report_clear' : 'wordstat_report',
				phrase: phraseList,
				geo: document.forms.form1['SETTINGS[Geo]'].value,
				sessid: BX.bitrix_sessid()
			}, function (res)
			{
				if (res.error)
				{
					switch (res.error.code + '')
					{
						case '92':
							if(!yandexTimeout)
							{
								yandexTimeout = setTimeout(function(){
									BX('wordstat_wait').style.display = 'none';
									BX('wordstat_wait_more').style.display = 'inline-block';
								}, 30000);
							}

							setTimeout(f, 5000);
							break; // report not ready yet
						case '31':
							f(true);
							break; // reached max amount of wordstat reports
						default:
							clearTimeout(yandexTimeout);
							BX('wordstat_button').disabled = false;
							BX('wordstat_wait').style.display = 'none';
							BX('wordstat_wait_more').style.display = 'none';

							BX('worstat_report').innerHTML = '<b><?=Loc::getMessage('SEO_ERROR')?></b> ' + BX.util.htmlspecialchars(res.error.message);
					}
				}
				else if (res.REPORT_ID)
				{
					setTimeout(f, 2000);
				}
				else
				{
					clearTimeout(yandexTimeout);
					BX('wordstat_button').disabled = false;
					BX('wordstat_wait').style.display = 'none';
					BX('wordstat_wait_more').style.display = 'none';

					BX('worstat_report').innerHTML = getWordstatReportContent(res);
				}
			});
		};

		f();
	}

	function getWordstatReportContent(res)
	{
		var bMult = res.length > 1;
		var html = '', i, j, r = Math.random(), p, bChecked;

		html = '<table class="internal" width="100%"><tr class="heading"><td><?=Loc::getMessage('SEO_BANNER_PHRASE')?></td><td class="bx-digit-cell"><?=Loc::getMessage('SEO_BANNER_PHRASE_SHOWS')?></td></tr>';

		for(i = 0; i < res.length; i++)
		{
			var phraseEnc = BX.util.htmlspecialchars(res[i]['Phrase']);
			var cnt = 'N/A';

			var info = '';

			info += '<table width="100%" class="internal"><tr class="heading"><td width="50%"><?=Loc::getMessage('SEO_SEARCH_WITH', array("#PHRASE#" => "'+phraseEnc+'"))?></td><td width="50%"><?=Loc::getMessage('SEO_SEARCH_WHATELSE', array("#PHRASE#" => "'+phraseEnc+'"))?></td></tr>';

			info += '<tr><td valign="top" style="padding:0 !important;">';
			if(res[i]['SearchedWith'])
			{
				info += '<div style="max-height: 400px; overflow: auto;"><table class="internal" width="100%"><tr class="heading"><td width="0"></td><td width="70%"><?=Loc::getMessage('SEO_BANNER_PHRASE')?></td><td width="30%" class="bx-digit-cell"><?=Loc::getMessage('SEO_BANNER_PHRASE_SHOWS')?></td></tr>';

				for(j = 0; j < res[i]['SearchedWith'].length; j++)
				{
					if(res[i]['Phrase'] == res[i]['SearchedWith'][j]['Phrase'])
					{
						bChecked = true;
						cnt = res[i]['SearchedWith'][j]['Shows'];
					}
					else
					{
						bChecked = false;
					}

					p = BX.util.htmlspecialchars(res[i]['SearchedWith'][j]['Phrase']);

					info += '<tr><td><input name="wordstat_phrase[]" type="checkbox" id="sw_'+i+'_'+j+'_'+r+'" value="'+p+'"'
					+(bChecked?' checked="checked"':'')+'></td><td><label for="sw_'+i+'_'+j+'_'+r+'">'+p
					+'</label></td><td class="bx-digit-cell">'+res[i]['SearchedWith'][j]['Shows']+'</td></tr>';
				}

				info += '</table></div>';
			}
			else
			{
				info += '<div style="text-align: center;"><b><?=Loc::getMessage('SEO_SEARCH_EMPTY')?></b></div>';
			}

			info += '</td><td valign="top" style="padding:0 !important;">';

			if(res[i]['SearchedAlso'])
			{
				info += '<div style="max-height: 400px; overflow: auto; text-align: left;"><table class="internal" width="100%"><tr class="heading"><td width="0"></td><td width="70%"><?=Loc::getMessage('SEO_BANNER_PHRASE')?></td><td width="30%" class="bx-digit-cell"><?=Loc::getMessage('SEO_BANNER_PHRASE_SHOWS')?></td></tr>';

				for(j = 0; j < res[i]['SearchedAlso'].length; j++)
				{
					bChecked = res[i]['Phrase'] == res[i]['SearchedAlso'][j]['Phrase'];
					p = BX.util.htmlspecialchars(res[i]['SearchedAlso'][j]['Phrase']);

					info += '<tr><td><input name="wordstat_phrase[]" type="checkbox" id="sa_'+i+'_'+j+'_'+r+'" value="'+p+'"'
					+(bChecked?' checked="checked"':'')+'></td><td><label for="sa_'+i+'_'+j+'_'+r+'">'+p
					+'</label></td><td class="bx-digit-cell">'+res[i]['SearchedAlso'][j]['Shows']+'</td></tr>';
				}

				info += '</table></div>';
			}
			else
			{
				info += '<div style="text-align: center;"><b><?=Loc::getMessage('SEO_SEARCH_EMPTY')?></b></div>';
			}

			info += '</td></tr></table>';


			html += '<tr><td>';
			if(bMult)
			{
				html += '<div class="region-closed" onclick="toggleDataRow(this,\'wordstat\',\''+i+'\', \''+r+'\')"><span class="openbutton"></span>' + phraseEnc + '</div>';
			}
			else
			{
				html += '<div>' + phraseEnc + '</div>';
			}

			html += '</td><td class="bx-digit-cell">' + cnt + '</td></tr>';

			html += '<tr id="wordstat_'+i+'_0_'+r+'" style="display:'+(bMult ? 'none' : '')+'"><td colspan="2">' + info + '</td></tr>';
		}

		return '<div style="margin-top: 15px; text-align: left;">' + html + '</div>';
	}

	BX.ready(function(){
		var timeOut = null;
		
//		phrases hint binds
		var textInput = BX('phrase_text');
		var hint = new BX.PopupWindow('phrase_hint_' + Math.random(), textInput, {
			content: '<div style="max-width: 250px;"><?=Loc::getMessage('SEO_PHRASE_HINT')?></div>',
			angle: {postion: 'top', offset: 230},
			bindOptions: {position: 'bottom'}
		});

		BX.bind(textInput, 'focus', function(){
			var pos = BX.pos(textInput);
			hint.setOffset({
				offsetLeft: pos.width - 250
			});
			hint.show();
			timeOut = setTimeout(BX.proxy(hint.close, hint), 10000)
		});
		BX.bind(textInput, 'blur', function(){hint.close(); clearTimeout(timeOut);});
		BX.bind(textInput, 'keyup', parsePhraseList);

//		minus keywords hint binds
		var minusKWInput = BX('minus_text');
		var hintMinus = new BX.PopupWindow('minus_keywords_hint_' + Math.random(), minusKWInput, {
			content: '<div style="max-width: 250px;"><?=Loc::getMessage('SEO_MINUS_KW_HINT')?></div>',
			angle: {postion: 'top', offset: 230},
			bindOptions: {position: 'top'}
		});
		
		BX.bind(minusKWInput, 'focus', function(){
			var pos = BX.pos(minusKWInput);
			hintMinus.setOffset({
				offsetLeft: pos.width - 250
			});
			hintMinus.show();
			timeOut = setTimeout(BX.proxy(hintMinus.close, hintMinus), 10000)
		});
		BX.bind(minusKWInput, 'blur', function(){hintMinus.close(); clearTimeout(timeOut);});

		BX.bindDelegate(
			BX('worstat_report'),
			'click',
			{tag: 'INPUT', props: {type: 'checkbox', name: 'wordstat_phrase[]'}},
			function(){
				if(this.checked)
				{
					addPhrase(this.value);
				}
				else
				{
					removePhrase(this.value);
				}
			}
		);

		parsePhraseList();

	});

	function showForecast()
	{
		var list = phraseList,
			yandexTimeout = null;

		if(list.length > 0)
		{
			BX('forecast_button').disabled = true;
			BX('forecast_wait').style.display = 'inline-block';
			BX('forecast_wait_more').style.display = 'none';

			var f = function (bClear)
			{
				BX.ajax.loadJSON('/bitrix/tools/seo_yandex_direct.php', {
					action: !!bClear ? 'forecast_report_clear' : 'forecast_report',
					phrase: list,
					geo: document.forms.form1['SETTINGS[Geo]'].value,
					sessid: BX.bitrix_sessid()
				}, function (res)
				{
					if (res.error)
					{
						switch (res.error.code+'')
						{
							case '74':
								if(!yandexTimeout)
								{
									yandexTimeout = setTimeout(function(){
										BX('forecast_wait').style.display = 'none';
										BX('forecast_wait_more').style.display = 'inline-block';
									}, 15000);
								}

								setTimeout(f, 1000);
								break; // report not ready yet
							case '31':
								f(true);
								break; // reached max amount of forecast reports
							default:
								clearTimeout(yandexTimeout);
								BX('forecast_button').disabled = false;
								BX('forecast_wait').style.display = 'none';
								BX('forecast_wait_more').style.display = 'none';

								BX('forecast_block_content').innerHTML = '<b><?=Loc::getMessage('SEO_ERROR')?></b> ' + BX.util.htmlspecialchars(res.error.message);
						}
					}
					else if (res.REPORT_ID)
					{
						setTimeout(f, 2000);
					}
					else
					{
						clearTimeout(yandexTimeout);
						BX('forecast_button').disabled = false;
						BX('forecast_wait').style.display = 'none';
						BX('forecast_wait_more').style.display = 'none';

						BX('forecast_block_content').innerHTML = getForecastContent(res);
					}
				});
			};

			f();
		}
	}

	function getForecastContent(res, bFromForm)
	{
		var r = Math.random(),
			h = '<table width="100%" class="internal">';

		if(!bFromForm)
		{
			h += '<tr class="heading"><td></td><td><?=Loc::getMessage('SEO_FORECAST_SHOWS')?></td><td><?=Loc::getMessage('SEO_FORECAST_CLICKS')?></td><td colspan="2"><?=Loc::getMessage('SEO_FORECAST_COST')?>, <?=$clientCurrency?></td><td colspan="2"></td></tr>';

			h += '<tr>' +
				'<td style="text-align: left;"><span class="region-closed" onclick="toggleDataRow(this,\'forecast\',\'common\',\'' + r + '\');"><span class="openbutton"></span><b><?=Loc::getMessage('SEO_FORECAST_COMMON')?></b></span></td>' +
				'<td>' + res['Common']['Shows'] + '</td>' +
				'<td>' + res['Common']['Clicks'] + '</td>' +
				'<td colspan="2">' + res['Common']['Min'] + '/' + res['Common']['Max'] + '</td>' +
				'<td colspan="2"></td>' +
			'</tr>';

			h += '<tr id="forecast_common_0_' + r + '" style="display:none;">' +
				'<td style="text-align: right;"><?=Loc::getMessage('SEO_FORECAST_COMMON_PLACE')?><br /><small><?=Loc::getMessage('SEO_FORECAST_COMMON_FIRSTPLACE')?></small></td>' +
				'<td></td>' +
				'<td>' + res['Common']['Clicks'] + '<br /><small>' + res['Common']['FirstPlaceClicks'] + '</small></td>' +
				'<td colspan="2">' + res['Common']['Min'] + '<br /><small>' + res['Common']['Max'] + '</small></td>' +
				'<td colspan="2"></td>' +
			'</tr><tr id="forecast_common_1_' + r + '" style="display:none;">' +
				'<td style="text-align: right;"><?=Loc::getMessage('SEO_FORECAST_PREMIUM_PLACE')?></td>' +
				'<td></td>' +
				'<td>' + res['Common']['PremiumClicks'] + '</td>' +
				'<td colspan="2">' + res['Common']['PremiumMin'] + '</td>' +
				'<td colspan="2"></td>' +
			'</tr>';
		}

		h += '<tr class="heading"><td><?=Loc::getMessage('SEO_FORECAST_PHRASE')?></td><td><?=Loc::getMessage('SEO_FORECAST_SHOWS')?></td><td><?=Loc::getMessage('SEO_FORECAST_CLICKS')?></td><td><?=Loc::getMessage('SEO_FORECAST_PRICE')?>, <?=$clientCurrency?></td><td><?=Loc::getMessage('SEO_FORECAST_CTR')?></td><td><?=Loc::getMessage('SEO_FORECAST_PRIORITY')?></td><td></td></tr>';

		for(var i = 0; i < res['Phrases'].length; i++)
		{
			var bShowDetails = !bFromForm;

			if(!bFromForm)
			{
				lastForecast[res['Phrases'][i]['Phrase']] = res['Phrases'][i];
			}
			else if(typeof lastForecast[res['Phrases'][i]['Phrase']] != 'undefined')
			{
				res['Phrases'][i] = lastForecast[res['Phrases'][i]['Phrase']];
				bShowDetails = true;
			}

			h += '<tr>' +
				'<td style="text-align: left;"><input type="hidden" name="Phrase[]" value="'+BX.util.htmlspecialchars(res['Phrases'][i]['Phrase'])+'">'+(bShowDetails?'<span class="region-closed" onclick="toggleDataRow(this,\'forecast\',\''+i+'\',\''+r+'\');"><span class="openbutton"></span>':'<span>');

			var status = phraseStatus[res['Phrases'][i]['Phrase']]||'<?=Engine\YandexDirect::STATUS_NEW?>';

			switch(status)
			{
				case '<?=Engine\YandexDirect::STATUS_NEW?>':
					h += '<div title="<?=Loc::getMessage('SEO_FORECAST_STATUS_NEW')?>" class="lamp-grey" style="display: inline-block;"></div>';
					break;
				case '<?=Engine\YandexDirect::BOOL_YES?>':
					h += '<div title="<?=Loc::getMessage('SEO_FORECAST_STATUS_YES')?>" class="lamp-green" style="display: inline-block;"></div>';
					break;
				case '<?=Engine\YandexDirect::BOOL_NO?>':
					h += '<div title="<?=Loc::getMessage('SEO_FORECAST_STATUS_NO')?>" class="lamp-red" style="display: inline-block;"></div>';
					break;
			}

			h += '&nbsp;' + BX.util.htmlspecialchars(res['Phrases'][i]['Phrase'])+'</span></td><td>' + res['Phrases'][i]['Shows'] + '</td><td>' + res['Phrases'][i]['Clicks'] + '</td>' +
				'<td>' + res['Phrases'][i]['Min'] + (typeof res['Phrases'][i]['Max'] != 'undefined' ? ('/' + res['Phrases'][i]['Max']) : '') + '</td>' +
				'<td>' + res['Phrases'][i]['CTR'] + '</td>' +
				'<td><select name="PhrasePriority[]" onchange="phrasePriority[\''+BX.util.htmlspecialchars(res['Phrases'][i]['Phrase']).replace('\'','\\\'')+'\'] = this.value;">';

			var priority = phrasePriority[res['Phrases'][i]['Phrase']]||'<?=Engine\YandexDirect::PRIORITY_MEDIUM?>';

			h += '<option value="<?=Engine\YandexDirect::PRIORITY_LOW?>"'+(priority=='<?=Engine\YandexDirect::PRIORITY_LOW?>' ? ' selected="selected"' : '')+'><?=Loc::getMessage('SEO_FORECAST_PRIORITY_LOW')?></option><option value="<?=Engine\YandexDirect::PRIORITY_MEDIUM?>"'+(priority=='<?=Engine\YandexDirect::PRIORITY_MEDIUM?>' ? ' selected="selected"' : '')+'><?=Loc::getMessage('SEO_FORECAST_PRIORITY_MEDIUM')?></option><option value="<?=Engine\YandexDirect::PRIORITY_HIGH?>"'+(priority=='<?=Engine\YandexDirect::PRIORITY_HIGH?>' ? ' selected="selected"' : '')+'><?=Loc::getMessage('SEO_FORECAST_PRIORITY_HIGH')?></option>';

			h += '</select></td>' +
				'<td><span onclick="removePhrase(\''+BX.util.htmlspecialchars(res['Phrases'][i]['Phrase'].replace(/'/, '\\\''))+'\'); this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode);" class="yandex-delete"></span></td>' +
			'</tr>';

			if(bShowDetails)
			{
				h += '<tr id="forecast_' + i + '_0_' + r + '" style="display:none;">' +
					'<td style="text-align: right;"><?=Loc::getMessage('SEO_FORECAST_COMMON_PLACE')?><br /><small><?=Loc::getMessage('SEO_FORECAST_COMMON_FIRSTPLACE')?></small></td>' +
					'<td></td>' +
					'<td>' + res['Phrases'][i]['Clicks'] + '<br /><small>' + res['Phrases'][i]['FirstPlaceClicks'] + '</small></td>' +
					'<td>' + res['Phrases'][i]['Min'] + '<br /><small>' + res['Phrases'][i]['Max'] + '</small></td>' +
					'<td>' + res['Phrases'][i]['CTR'] + '<br /><small>' + res['Phrases'][i]['FirstPlaceCTR'] + '</small></td>' +
					'<td colspan="2"></td>' +
				'</tr><tr id="forecast_' + i + '_1_' + r + '" style="display:none;">' +
					'<td  style="text-align: right;"><?=Loc::getMessage('SEO_FORECAST_PREMIUM_PLACE')?><br /><small><?=Loc::getMessage('SEO_FORECAST_PREMIUM_FIRSTPLACE')?></small></td>' +
					'<td></td>' +
					'<td>' + res['Phrases'][i]['PremiumClicks'] + '</td>' +
					'<td>' + res['Phrases'][i]['PremiumMin'] + '<br /><small>' + res['Phrases'][i]['PremiumMax'] + '</small></td>' +
					'<td>' + res['Phrases'][i]['PremiumCTR'] + '</td>' +
					'<td colspan="2"></td>' +
				'</tr>';
			}
		}

		h += '</table>';

		return h;
	}

	function toggleDataRow(el,prefix,i,r)
	{
		if(BX.hasClass(el, 'region-closed'))
		{
			el.className = 'region-opened';
			BX(prefix+'_'+i+'_0_'+r).style.display = '';
			if(BX(prefix+'_'+i+'_1_'+r))
			{
				BX(prefix+'_'+i+'_1_'+r).style.display = '';
			}
		}
		else
		{
			el.className = 'region-closed';
			BX(prefix+'_'+i+'_0_'+r).style.display = 'none';
			if(BX(prefix+'_'+i+'_1_'+r))
			{
				BX(prefix+'_'+i+'_1_'+r).style.display = 'none';
			}
		}
	}

</script>

<?
// Auto tab
if($bShowAuto)
{
	$tabControl->BeginNextTab();

?>
<tr>
	<td colspan="2" align="center"><?= BeginNote().Loc::getMessage("SEO_BANNER_AUTO_HINT").EndNote();?></td>
</tr>
<tr>
	<td width="60%">
		<label for="AUTO_QUANTITY_OFF"><?= Loc::getMessage("SEO_BANNER_AUTO_QUANTITY_OFF");?></label>
	</td>
	<td width="40%">
		<input type="hidden" name="AUTO_QUANTITY_OFF" value="<?=Adv\YandexBannerTable::INACTIVE?>">
		<input type="checkbox" name="AUTO_QUANTITY_OFF" id="AUTO_QUANTITY_OFF" value="<?=Adv\YandexBannerTable::ACTIVE?>"<?=$banner["AUTO_QUANTITY_OFF"] == Adv\YandexBannerTable::ACTIVE || $banner["AUTO_QUANTITY_OFF"] == Adv\YandexBannerTable::MARKED ? ' checked="checked"' : ''?>>
		<label for="AUTO_QUANTITY_OFF"><?= Loc::getMessage("MAIN_YES");?></label>
	</td>
</tr><tr>
	<td width="60%">
		<label for="AUTO_QUANTITY_ON"><?= Loc::getMessage("SEO_BANNER_AUTO_QUANTITY_ON");?></label>
	</td>
	<td width="40%">
		<input type="hidden" name="AUTO_QUANTITY_ON" value="<?=Adv\YandexBannerTable::INACTIVE?>">
		<input type="checkbox" name="AUTO_QUANTITY_ON" id="AUTO_QUANTITY_ON" value="<?=Adv\YandexBannerTable::ACTIVE?>"<?=$banner["AUTO_QUANTITY_ON"] == Adv\YandexBannerTable::ACTIVE || $banner["AUTO_QUANTITY_ON"] == Adv\YandexBannerTable::MARKED ? ' checked="checked"' : ''?>>
		<label for="AUTO_QUANTITY_ON"><?= Loc::getMessage("MAIN_YES");?></label>
	</td>
</tr>
<script>
BX.addCustomEvent("OnSeoYandexDirectLinksChange", BX.defer(function(el){
	var disabled = true;
	var c = BX.firstChild(el);
	if(c && c.tagName == 'DIV')
	{
		var c1 = BX.nextSibling(c);
		if(!c1 || c1.tagName != "DIV")
		{
			disabled = false;
		}
	}

	BX('AUTO_QUANTITY_OFF').disabled = disabled;
	BX('AUTO_QUANTITY_ON').disabled = disabled;

	if(disabled)
	{
		window.tabControl.DisableTab("edit_auto");
	}
	else
	{
		window.tabControl.EnableTab("edit_auto");
	}
}));
</script>
<?
}

if($bShowStats)
{
	$tabControl->BeginNextTab();

	CJSCore::Init(array('amcharts_serial'));

	$dateStart = new Main\Type\Date();
	$dateStart->add("-".Engine\YandexDirect::MAX_STAT_DAYS_DELTA." days");

	$dateFinish = new Main\Type\Date();

	$statsData = Adv\YandexStatTable::getBannerStat(
		$banner['ID'],
		$dateStart,
		$dateFinish
	);

	$gaps = Adv\YandexStatTable::getMissedPeriods($statsData, $dateStart, $dateFinish);

	$graphData = array();

	$currency = $clientCurrency;
	foreach($statsData as $date => $dayData)
	{
		if($dayData['CURRENCY'] != '')
		{
			$currency = $dayData['CURRENCY'];
		}

		unset($dayData['ID']);
		unset($dayData['CAMPAIGN_ID']);
		unset($dayData['BANNER_ID']);
		unset($dayData['DATE_DAY']);

		$dayData['date'] = $date;
		$graphData[] = $dayData;
	}

	$bLoadStats = count($gaps) > 0;
	$bShowStats = count($graphData) > 0 || $bLoadStats;

	$bannerProfit = 0;

	if(!$bLoadStats && $bSale)
	{
		$orderStats = Adv\OrderTable::getList(array(
			'filter' => array(
				'=BANNER_ID' => $banner['ID'],
				'=PROCESSED' => Adv\OrderTable::PROCESSED,
				">=TIMESTAMP_X" => $dateStart,
				"<TIMESTAMP_X" => $dateFinish,
			),
			'group' => array(
				'BANNER_ID'
			),
			'select' => array('BANNER_SUM'),
			'runtime' => array(
				new \Bitrix\Main\Entity\ExpressionField('BANNER_SUM', 'SUM(SUM)'),
			),
		));
		if($stat = $orderStats->fetch())
		{
			$bannerProfit = $stat['BANNER_SUM'];
		}
	}

?>
<tr>
	<td width="30%" class="adm-detail-required-field"><?=Loc::getMessage('SEO_YANDEX_STATS_PERIOD')?>:</td>
	<td width="70%">
			<span style="white-space: nowrap; display:inline-block;"><select name="period_sel" onchange="setGraphInterval(this.value)">
					<option value="interval"><?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_INTERVAL')?></option>
					<option value="week_ago"><?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_WEEK')?></option>
					<option value="month_ago"><?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_MONTH')?></option>
				</select>&nbsp;<span id="seo_graph_interval"><?=CalendarDate("date_from", $dateStart->toString(), 'form1', "4")?>&nbsp;&hellip;<?=CalendarDate("date_to", $dateFinish->toString(), 'form1', "4")?></span></span>&nbsp;&nbsp;<input type="button" value="<?=Loc::getMessage('SEO_YANDEX_STATS_PERIOD_APPLY')?>" onclick="loadGraphData()" id="stats_loading_button" name="template_preview"><span id="stats_wait" class="loading-message-text" style="display: none; margin-top: 5px;"><?=Loc::getMessage('SEO_YANDEX_STATS_WAIT')?></span>
	</td>
</tr>
<?
	if($bSale):
?>
<tr>
	<td><?=Loc::getMessage('SEO_YANDEX_STATS_SUM_ORDER_REPIOD')?>:</td>
	<td><span id="banner_profit"><?=\CCurrencyLang::CurrencyFormat(doubleval($bannerProfit), \Bitrix\Currency\CurrencyManager::getBaseCurrency(), true)?></span></td>
</tr>
<?
	endif;
?>
<tr>
	<td><?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TYPE')?>:</td>
	<td><select onchange="setGraph(this.value)">
		<option value="sum"><?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TYPE_SUM')?></option>
		<option value="shows"><?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TYPE_SHOWS')?></option>
		<option value="clicks"><?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TYPE_CLICKS')?></option>
	</select></td>
</tr>
<tr>
	<td colspan="2">
<?
	if($errorMessage)
	{
		echo $errorMessage->Show();
	}

	if($bShowStats):
?>
		<div style="padding: 0 50px; text-align: center;">
			<div id="statschart" style="width: 100%; height: 600px;"></div>
		</div>

<script>
	var currentGraph = 'sum';

	function setGraphInterval(val)
	{
		BX('seo_graph_interval').style.display = val == 'interval' ? 'inline' : 'none';
	}

	function getGraph(id, valueField, title)
	{
		return {
			"bullet": "square",
			"bulletBorderAlpha": 1,
			"bulletBorderThickness": 1,
			"bulletSize": 10,
			"id": id,
			"lineThickness": 3,
			"title": title,
			"valueField": valueField
		}
	}

	function setGraph(graph)
	{
		currentGraph = graph;

		window.yandexChart.graphs = yandexGraphs[currentGraph];
		window.yandexChart.valueAxes = [yandexAxis[currentGraph]];

		window.yandexChart.validateData();
	}

	function setData(data)
	{
		window.yandexChart.dataProvider = data;
		window.yandexChart.validateData();

		if(data.length > 0)
		{
			yandexAxis['sum'].title = "<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_AXIS_SUM')?>, " + (data[0].CURRENCY || '<?=$clientCurrency?>');

			if (currentGraph == 'sum')
			{
				BX.defer(setGraph)(currentGraph);
			}
		}
	}

	function loadGraphData(session)
	{
		var queryData;

		if(!!session)
		{
			queryData = {
				action: 'banner_stats',
				banner: '<?=$banner['ID']?>',
				loading_session: session,
				sessid: BX.bitrix_sessid()
			};
		}
		else
		{
			BX('stats_loading_button').disabled = true;
			BX('stats_wait').style.display = 'inline-block';
			BX('stats_wait').innerHTML = '<?=Loc::getMessage('SEO_YANDEX_STATS_WAIT')?>';

			queryData = {
				action: 'banner_stats',
				banner: '<?=$banner['ID']?>',
				type: document.forms.form1.period_sel.value,
				date_from: document.forms.form1.date_from.value,
				date_to: document.forms.form1.date_to.value,
				sessid: BX.bitrix_sessid()
			};
		}

		BX.ajax.loadJSON('/bitrix/tools/seo_yandex_direct.php', queryData, function(res)
		{

			if (!!res.session)
			{
				BX.defer(loadGraphData)(res.session);
				BX('stats_wait').innerHTML = '<?=Loc::getMessage('SEO_YANDEX_STATS_WAIT')?>: ' + Math.floor(100-(res.left/res.amount)*100) + '%';
			}
			else if(!!res.data)
			{
				BX('stats_loading_button').disabled = false;
				BX('stats_wait').style.display = 'none';

				setData(res.data);

				if(typeof res.order_sum != 'undefined' && BX('banner_profit'))
				{
					BX('banner_profit').innerHTML = res.order_sum_format;
				}

				if(!!res.error && res.error.code == '<?=Engine\YandexDirect::ERROR_NO_STATS?>')
				{
					res.error = null;
				}
			}

			if(!!res.error && (!!res.error.message || !!res.error.code))
			{
				BX('stats_loading_button').disabled = false;
				BX('stats_wait').style.display = 'none';

				alert(res.error.message||res.error.code);
				setData([]);
			}
		});

	}

	var yandexGraphs = {
		sum: [
			getGraph('yandex_sum', 'SUM', '<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TITLE_SUM')?>'),
			getGraph('yandex_sum_search', 'SUM_SEARCH', '<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TITLE_SUM_SEARCH')?>'),
			getGraph('yandex_sum_context', 'SUM_CONTEXT', '<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TITLE_SUM_CONTEXT')?>'),
		],
		shows: [
			getGraph('yandex_show', 'SHOWS', '<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TITLE_SHOWS')?>'),
			getGraph('yandex_shows_search', 'SHOWS_SEARCH', '<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TITLE_SHOWS_SEARCH')?>'),
			getGraph('yandex_show_context', 'SHOWS_CONTEXT', '<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TITLE_SHOWS_CONTEXT')?>'),
		],
		clicks: [
			getGraph('yandex_clicks', 'CLICKS', '<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TITLE_CLICKS')?>'),
			getGraph('yandex_clicks_search', 'CLICKS_SEARCH', '<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TITLE_CLICKS_SEARCH')?>'),
			getGraph('yandex_clicks_context', 'CLICKS_CONTEXT', '<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TITLE_CLICKS_CONTEXT')?>'),
		]
	};

	var yandexAxis = {
		sum: {
			id: "Sum",
			title: "<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_AXIS_SUM')?>, <?=$currency?>"
		},
		shows: {
			id: "Shows",
			title: "<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_AXIS_SHOWS')?>"
		},
		clicks: {
			id: "Clicks",
			title: "<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_AXIS_CLICKS')?>"
		}
	};

	window.yandexChart = null;

	window.showStats = function(){
		window.yandexChart = AmCharts.makeChart("statschart", {
			"type": "serial",
			"theme": "light",
			"pathToImages": "http://cdn.amcharts.com/lib/3/images/",
			"zoomOutText": "<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_SHOW_ALL')?>",
			"categoryField": "date",
			"dataDateFormat": "<?=FORMAT_DATE?>",
			"autoMarginOffset": 40,
			"marginRight": 60,
			"marginTop": 60,
			"fontSize": 13,
			"numberFormatter": {
				"decimalSeparator": ".",
				"thousandsSeparator": " ",
				"precision": 2
			},
			"categoryAxis": {
				"parseDates": true,
				"labelFunction": function(val, date)
				{
					return BX.date.format(
						BX.date.convertBitrixFormat('<?=FORMAT_DATE?>'), date
					);
				}
			},
			"chartCursor": {
				categoryBalloonFunction: function(date)
				{
					return BX.date.format(
						BX.date.convertBitrixFormat('<?=FORMAT_DATE?>'), date
					);
				}
			},
			"trendLines": [],
			"legend": {
				//"switchable": false
			},
			"graphs": yandexGraphs[currentGraph],
			"guides": [],
			"valueAxes": [
				yandexAxis[currentGraph]
			],
			"allLabels": [],
			"balloon": {},
			"titles": [],
			"dataProvider": <?=Main\Web\Json::encode($graphData)?>
		});

<?
		if($bLoadStats):
?>
		loadGraphData();
<?
		endif;
?>

		window.showStats = BX.DoNothing;
	};
<?
		if($tabControl->selectedTab == "edit_stats"):
?>
	showStats();
<?
		endif;
?>
</script>
<?
	endif;
?>
	</td>
</tr>
<?
}

$tabControl->EndTab();
if(!$bReadOnly)
{
	$tabControl->Buttons(array(
		"back_url" => $back_url ? $back_url : "seo_search_yandex_direct_banner.php?lang=".LANGUAGE_ID,
	));
}
$tabControl->End();

if(!$bReadOnly):
?>
<?=bitrix_sessid_post();?>
<?
	if($back_url!=''):
?>
	<input type="hidden" name="back_url" value="<?echo HtmlFilter::encode($back_url)?>">
<?
	endif;
?>

	<input type="hidden" name="campaign" value="<?=$campaignId?>">
	<input type="hidden" name="element" value="<?=$elementId?>">
	<input type="hidden" name="ID" value="<?=$ID?>">


<?
endif;
?>
</form>
<?
if($ID > 0):
?>
<script>
	BX.ready(function(){
		BX.onCustomEvent("OnSeoYandexDirectLinksChange", [BX('adv_link_list')]);
	});
</script>
<?
endif;

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
