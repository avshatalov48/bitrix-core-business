<?php
/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @global CUserTypeManager $USER_FIELD_MANAGER
 * @global CCacheManager $CACHE_MANAGER
 * @global boolean $bSale
 * @global boolean $bSingle
 * @global array $campaignList
 * @global array $bannerList
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Currency\CurrencyManager;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Seo\Engine;

foreach($bannerList as $bannerId => $banner)
{
	$campaign = $bannerList[$banner['CAMPAIGN_ID']];

	if(!$bSingle):
?>
<div id="yandex_direct_banner_<?=$bannerId?>">
<?
	endif;
?>

<table class="internal" width="100%" style="margin-top: 15px;">
	<tr class="heading">
		<td width="50%"><?=Loc::getMessage('SEO_YANDEX_STATS_PAYBACK')?></td>
		<td width="50%"><?=Loc::getMessage('SEO_YANDEX_STATS_BANNER')?></td>
	</tr>
	<tr>
		<td valign="top">
<?
	if(!$banner['DATE_START']):
?>
		<div class="seo-adv-block seo-adv-block-gray seo-adv-block-no-title">
			<div class="seo-adv-block-icon"></div>
			<div class="seo-adv-block-text"><?=Loc::getMessage('SEO_YANDEX_STATS_NO_START_DATE')?> <a href="/bitrix/admin/seo_yandex_direct_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$banner["CAMPAIGN_ID"]?>"><?=Loc::getMessage('SEO_YANDEX_STATS_NO_START_DATE_SET')?></a></div>
		</div>
<?
	elseif($banner['LOADING_NEEDED']):
?>
		<div class="seo-adv-block seo-adv-block-gray seo-adv-block-no-title">
			<div class="seo-adv-block-icon"></div>
			<div class="seo-adv-block-text" id="yandex_banner_message_<?=$banner['ID']?>"><?=Loc::getMessage('SEO_YANDEX_STATS_NO_STATS')?> <a href="javascript:void(0)" onclick="loadBannerStats('<?=$banner['ID']?>', '<?=$banner['DATE_START']->toString()?>')" class="bx-action-href" hidefocus="true"><?=Loc::getMessage('SEO_YANDEX_STATS_NO_STATS_LOAD')?></a></div>
		</div>
<?
	else:
		$expences = 0;
		$currency = '';

		foreach($banner['STATS_DATA'] as $date => $statData)
		{
			$expences += doubleval($statData['SUM']);
			$currency = $statData['CURRENCY'];
		}

		if($bSale)
		{
			$localCurrency = CurrencyManager::getBaseCurrency();
			if($localCurrency == 'RUR')
			{
				$localCurrency = 'RUB';
			}

			if($localCurrency == $currency)
			{
				$profit = doubleval($banner['PROFIT']) - $expences;
				$color = 'gray';
				$prefix = '';
				if($profit < 0)
				{
					$color = 'red';
					$prefix = '&ndash;&nbsp;';
					$profit = -$profit;
				}
				elseif($profit > 0)
				{
					$color = 'green';
				}
?>
			<div class="seo-adv-block seo-adv-block-<?=$color?>">
				<div class="seo-adv-block-icon"></div>
				<div class="seo-adv-block-title"><?=$prefix?><?=\CCurrencyLang::CurrencyFormat($profit, CurrencyManager::getBaseCurrency(), true)?></div>
				<div class="seo-adv-block-text"><?=Loc::getMessage('SEO_YANDEX_STATS_HINT_BOTH')?></div>
			</div>
<?
			}
			else
			{
				$profit = doubleval($banner['PROFIT']);
				$color = $profit > 0 ? 'green' : 'gray';
				$prefix = '';
?>
			<div class="seo-adv-block seo-adv-block-<?=$color?>">
				<div class="seo-adv-block-icon"></div>
				<div class="seo-adv-block-title"><?=$prefix?><?=\CCurrencyLang::CurrencyFormat($profit, CurrencyManager::getBaseCurrency(), true)?></div>
				<div class="seo-adv-block-text"><?=Loc::getMessage('SEO_YANDEX_STATS_HINT_GROSS')?></div>
			</div>
<?
				$color = $expences > 0 ? 'red' : 'gray';
				$prefix = $expences > 0 ? '&ndash;&nbsp;' : '';
				$text = CCurrencyLang::CurrencyFormat($expences, CurrencyManager::getBaseCurrency(), false);
				if($currency == '')
				{
					$text .= ' '.Loc::getMessage('SEO_YANDEX_CURRENCY');
				}
				elseif(CurrencyManager::checkCurrencyID($currency))
				{
					$text = CCurrencyLang::CurrencyFormat($expences, $currency, true);
				}
				else
				{
					$text .= ' '.$currency;
				}
?>
			<div class="seo-adv-block seo-adv-block-<?=$color?>">
				<div class="seo-adv-block-icon"></div>
				<div class="seo-adv-block-title"><?=$prefix?><?=$text?></div>
				<div class="seo-adv-block-text"><?=Loc::getMessage('SEO_YANDEX_STATS_HINT_EXPENCES')?></div>
			</div>
<?
			}
		}
	endif;
?>
		</td>
		<td valign="top">

<?
	$bannerInfo = $banner;
	require("seo_search_yandex_direct_banner.php");
?>
		</td>
	</tr>
	<tr>
		<td valign="top" style="position: relative;">
<?
if($banner['SETTINGS']['StatusBannerModerate'] != Engine\YandexDirect::STATUS_NEW)
{
	if($banner['SETTINGS']['StatusShow'] == Engine\YandexDirect::BOOL_YES)
	{
?>
			<input type="button" value="<?=Loc::getMessage("SEO_BANNER_STOP")?>" onclick="stopBanner(<?=$campaign['ID']?>, <?=$banner['ID']?>)" id="yandex_direct_action_button_<?=$banner['ID']?>"><br /><br />
<?
	}
	else
	{
?>
			<input type="button" value="<?=Loc::getMessage("SEO_BANNER_RESUME")?>" onclick="resumeBanner(<?=$campaign['ID']?>, <?=$banner['ID']?>)" id="yandex_direct_action_button_<?=$banner['ID']?>"><br /><br />
<?
	}
}
?>
			<div style="position: absolute; bottom: 10px;">
				<span class="yandex-delete" onclick="deleteLinkStat('<?=$banner['ID']?>', this)"><?=Loc::getMessage('SEO_YANDEX_STATS_DELETE_LINK')?></span>
			</div>
		</td>
		<td valign="top">
<?
	$activeCampaign = 'grey';
	$activeCampaignTitle = $campaign['SETTINGS']['Status'];

	if($campaign['SETTINGS']['IsActive'] == Engine\YandexDirect::BOOL_YES)
	{
		$activeCampaign = 'green';
	}
	elseif(
		$campaign['SETTINGS']['StatusModerate'] == Engine\YandexDirect::BOOL_YES
		&& $campaign['SETTINGS']['StatusShow'] == Engine\YandexDirect::BOOL_YES
	)
	{
		if($campaign['SETTINGS']['StatusActivating'] == Engine\YandexDirect::BOOL_YES)
		{
			$activeCampaign = 'red';
		}
		else
		{
			$activeCampaign = 'yellow';
		}
	}
	elseif($campaign['SETTINGS']['StatusModerate'] == Engine\YandexDirect::BOOL_NO)
	{
		$activeCampaign = 'red';
	}


	$activeBanner = '';
	$activeBannerTitle = '';

	if($banner['SETTINGS']['StatusBannerModerate'] == Engine\YandexDirect::STATUS_NEW)
	{
		$activeBanner = 'grey';
		$activeBannerTitle = Loc::getMessage('SEO_STATUS_MODERATE_NEW');
	}
	else
	{
		if($banner['SETTINGS']['IsActive'] == Engine\YandexDirect::BOOL_YES)
		{
			if($banner['SETTINGS']['StatusActivating'] == Engine\YandexDirect::STATUS_PENDING)
			{
				$activeBanner = 'yellow';
				$activeBannerTitle = Loc::getMessage('SEO_STATUS_PENDING');
			}
			else
			{
				$activeBanner = 'green';
				$activeBannerTitle = Loc::getMessage('SEO_STATUS_SHOW_YES');
			}
		}
		else
		{
			if($banner['SETTINGS']['StatusActivating'] == Engine\YandexDirect::STATUS_PENDING)
			{
				if($banner['SETTINGS']['StatusBannerModerate'] == Engine\YandexDirect::STATUS_PENDING)
				{
					$activeBanner = 'grey';
					$activeBannerTitle = Loc::getMessage('SEO_STATUS_MODERATE_PENDING');
				}
				elseif($banner['SETTINGS']['StatusShow'] == Engine\YandexDirect::BOOL_YES)
				{
					$activeBanner = 'grey';
					$activeBannerTitle = Loc::getMessage('SEO_STATUS_SHOW_PENDING');
				}
				else
				{
					$activeBanner = 'grey';
					$activeBannerTitle = Loc::getMessage('SEO_STATUS_SHOW_OFF');
				}
			}
			else
			{
				$activeBanner = 'red';
				$activeBannerTitle = Loc::getMessage('SEO_ISACTIVE_NO');
			}
		}
	}

?>
			<span style="display: inline-block;width: 140px;"><?=Loc::getMessage('SEO_YANDEX_STATS_BANNER_STATUS')?>:</span>
			<span style="white-space:nowrap;"><div class="lamp-<?=$activeBanner?>" style="display:inline-block;"></div>&nbsp;<?=$activeBannerTitle;?></span><br />
			<span style="display: inline-block;width: 140px;"><?=Loc::getMessage('SEO_YANDEX_STATS_CAMPAIGN_STATUS')?>:</span>
			<span style="white-space:nowrap;"><div class="lamp-<?=$activeCampaign?>" style="display:inline-block;"></div>&nbsp;<?=$activeCampaignTitle;?></span>

			<div style="margin-top: 15px;">
				<a href="/bitrix/admin/seo_search_yandex_direct_banner_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$banner['ID']?>&tabControl_active_tab=edit_stats"><?=Loc::getMessage('SEO_YANDEX_STATS_BANNER_STATS')?></a><br />
				<a href="/bitrix/admin/seo_search_yandex_direct_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$banner['CAMPAIGN_ID']?>&tabControl_active_tab=edit_stats"><?=Loc::getMessage('SEO_YANDEX_STATS_CAMPAIGN_STATS')?></a><br />
			</div>
		</td>
	</tr>
</table>
<?
	if(!$bSingle):
?>
</div>
<?
	endif;
}