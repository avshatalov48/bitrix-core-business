<?php
/**
 * Bitrix vars
 * @global array $iblockElementInfo
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @global CUserTypeManager $USER_FIELD_MANAGER
 * @global CCacheManager $CACHE_MANAGER
 * @global array $iblockElementInfo
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

global $APPLICATION;

use Bitrix\Main;
use Bitrix\Main\Text\Converter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Seo\Engine;
use Bitrix\Seo\Adv;

Loc::loadMessages(__DIR__.'/../seo_adv.php');

$engine = new Engine\YandexDirect();
$currentUser = $engine->getCurrentUser();
$bNeedAuth = !is_array($currentUser);

if($bNeedAuth)
{
	if(!defined('BX_PUBLIC_MODE') || !BX_PUBLIC_MODE)
	{
		$message = new CAdminMessage(array(
			"TYPE" => "OK",
			"DETAILS" => Loc::getMessage("SEO_ERROR_NO_YANDEX_AUTH", array(
				"#LANGUAGE_ID#" => LANGUAGE_ID,
			)),
			"HTML" => true
		));
		echo $message->Show();
	}
	else
	{
		echo BeginNote().Loc::getMessage("SEO_ERROR_NO_YANDEX_AUTH", array(
				"#LANGUAGE_ID#" => LANGUAGE_ID,
			)).EndNote();
	}

	return;
}

$request = Main\Context::getCurrent()->getRequest();

$dbRes = Adv\YandexCampaignTable::getList(array(
	"order" => array("NAME" => "asc"),
	"filter" => array(
		'=ACTIVE' => Adv\YandexCampaignTable::ACTIVE,
		'=ENGINE_ID' => $engine->getId(),
	),
	'select' => array(
		"ID", "NAME", "XML_ID", "OWNER_ID", "SETTINGS"
	)
));
$campaignList = array();

while($campaign = $dbRes->fetch())
{
	if($campaign['OWNER_ID'] == $currentUser['id'])
	{
		$campaignList[$campaign['ID']] = $campaign;
	}
}

if(count($campaignList) <= 0)
{
	if(!defined('BX_PUBLIC_MODE') || !BX_PUBLIC_MODE)
	{
		$message = new CAdminMessage(array(
			"TYPE" => "OK",
			"DETAILS" => Loc::getMessage("SEO_ERROR_NO_CAMPAIGNS", array(
				"#LANGUAGE_ID#" => LANGUAGE_ID,
			)),
			"HTML" => true
		));
		echo $message->Show();
	}
	else
	{
		echo BeginNote().Loc::getMessage("SEO_ERROR_NO_CAMPAIGNS", array(
				"#LANGUAGE_ID#" => LANGUAGE_ID,
			)).EndNote();
	}

?>
<a href="/bitrix/admin/seo_search_yandex_direct_edit.php?lang=<?=LANGUAGE_ID?>&back_url=<?=urlencode($APPLICATION->GetCurPageParam('form_element_'.$iblockElementInfo["IBLOCK"]["ID"].'_active_tab=seo_adv_seo_adv', array('form_element_'.$iblockElementInfo["IBLOCK"]["ID"].'_active_tab')))?>"><?=Loc::getMessage("SEO_CREATE_NEW_CAMPAIGN")?></a>
<?
}
else
{
	$dbRes = Adv\LinkTable::getList(array(
		"filter" => array(
			'LINK_TYPE' => Adv\LinkTable::TYPE_IBLOCK_ELEMENT,
			'LINK_ID' => $iblockElementInfo['ID'],
			"BANNER.ENGINE_ID" => $engine->getId(),
		),
		"select" => array(
			"BANNER_ID", "BANNER_NAME" => "BANNER.NAME", "BANNER_XML_ID" => "BANNER.XML_ID",
			"BANNER_CAMPAIGN_ID" => "BANNER.CAMPAIGN_ID",
		)
	));

	$arBanners = array();
	while($banner = $dbRes->fetch())
	{
		if(!isset($arBanners[$banner['BANNER_CAMPAIGN_ID']]))
		{
			$arBanners[$banner['BANNER_CAMPAIGN_ID']] = array();
		}

		$arBanners[$banner['BANNER_CAMPAIGN_ID']][] = $banner;
	}

?>
<style type="text/css">

span.yandex-delete {
	display: inline-block;
	height: 20px;
	padding-left: 20px;
	line-height: 20px;
	cursor: pointer;
	background: url("/bitrix/panel/main/images/bx-admin-sprite-small-1.png") no-repeat scroll 5px -2446px rgba(0, 0, 0, 0);
}


.seo-adv-block
{
	border-radius: 3px;
	position: relative;
	height: 67px;
	width: 100%;
	min-width: 300px;
	margin-bottom: 10px;
}

.seo-adv-block-icon
{
	top: 12px;
	left: 12px;
	background: url('/bitrix/panel/seo/images/faces.png') no-repeat scroll 0 0;
	display: block;
	position: absolute;
	height: 42px;
	width: 42px;
}

.seo-adv-block-text,
.seo-adv-block-title
{
	margin-left: 66px;
	position: absolute;
}

.seo-adv-block-title
{
	font-size: 19px;
	font-weight: bold;
	top: 12px;
}

.seo-adv-block-text
{
	font-size: 12px;
	top: 40px;
}

.seo-adv-block-no-title .seo-adv-block-text
{
	top: 25px;
}

.seo-adv-block-green
{
	background-color: #def2c0;
}

.seo-adv-block-red
{
	background-color: #ffd7d6;
}

.seo-adv-block-gray
{
	background-color: #ecf2f3;
}

.seo-adv-block-green .seo-adv-block-icon
{
	background-position: 0 0;
}

.seo-adv-block-red .seo-adv-block-icon
{
	background-position: 0 -42px;
}

.seo-adv-block-gray .seo-adv-block-icon
{
	background-position: 0 -84px;
}

.seo-adv-block-green .seo-adv-block-title
{
	color: #3e7400;
	text-shadow: 0.5px 1px 0px rgba(195, 255, 211, 0.004);
}

.seo-adv-block-red .seo-adv-block-title
{
	color: #d30d00;
	text-shadow: 0.5px 1px 0px rgba(255, 194, 195, 0.004);
}

.seo-adv-block-gray .seo-adv-block-title
{
	color: #464f5c;
	text-shadow: 0.5px 1px 0px rgba(255, 255, 255, 0.004);
}


</style>
<table class="internal" width="100%">
	<thead>
	<tr class="heading">
		<td colspan="2"><?=Loc::getMessage('SEO_YANDEX_LINK_TITLE')?></td>
	</tr>
	</thead>
	<tbody id="adv_banner_selector">
	<tr>
		<td width="40%"><?=Loc::getMessage("SEO_CAMPAIGN_CHOOSE")?>:</td>
		<td width="60%">
			<select id="seo_adv_campaign" style="width:400px" onchange="updateNewBannerLink()">
				<option value="0"><?=Loc::getMessage("SEO_CAMPAIGN_CHOOSE_OPTION")?></option>
				<?
				foreach($campaignList as $campaign)
				{
					$canAdd = in_array(
						$campaign["SETTINGS"]['Strategy']['StrategyName'],
						Adv\YandexCampaignTable::$supportedStrategy
					);
					?>
					<option value="<?=$campaign["ID"]?>"
						data-add="<?=$canAdd ? 1 : 0?>"><?=Converter::getHtmlConverter()->encode($campaign["NAME"])?></option>
				<?
				}
				?>
			</select>&nbsp;&nbsp;<a
				href="/bitrix/admin/seo_search_yandex_direct_edit.php?lang=<?=LANGUAGE_ID?>&back_url=<?=urlencode($APPLICATION->GetCurPageParam('form_element_'.$iblockElementInfo["IBLOCK"]["ID"].'_active_tab=seo_adv_seo_adv', array('form_element_'.$iblockElementInfo["IBLOCK"]["ID"].'_active_tab')))?>"><?=Loc::getMessage("SEO_CREATE_NEW_CAMPAIGN")?></a>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage("SEO_BANNER_CHOOSE")?>:</td>
		<td>
			<select id="seo_adv_banner" style="width:400px" disabled="disabled"
				onchange="BX('seo_adv_link_btn').disabled=this.value<=0">
				<option value="0"><?=Loc::getMessage("SEO_CAMPAIGN_CHOOSE_OPTION")?></option>
			</select>&nbsp;&nbsp;<a id="adv_banner_link"
					href="/bitrix/admin/seo_search_yandex_direct_banner_edit.php?lang=<?=LANGUAGE_ID?>&element=<?=$iblockElementInfo['ID']?>&back_url=<?=urlencode($APPLICATION->GetCurPageParam('form_element_'.$iblockElementInfo["IBLOCK"]["ID"].'_active_tab=seo_adv_seo_adv', array('form_element_'.$iblockElementInfo["IBLOCK"]["ID"].'_active_tab')));?>" style="display: none;"><?=Loc::getMessage('SEO_CREATE_NEW_BANNER')?></a>
			<small id="adv_banner_strategy_warning"
				style="display: none;"><?=Loc::getMessage('SEO_YANDEX_STRATEGY_WARNING')?></small>
		</td>
	</tr>
	<tr>
		<td></td>
		<td><input type="button" class="adm-btn-save" value="<?=Loc::getMessage("SEO_BANNER_LINK_CREATE")?>"
				id="seo_adv_link_btn" disabled="disabled" onclick="createLink()"></td>
	</tr>
	</tbody>
</table>
<script>
	var bannerList = {};

	function updateNewBannerLink()
	{
		var campaignSelect = BX('seo_adv_campaign');
		var bannerSelect = BX('seo_adv_banner');
		var bannerWarning = BX('adv_banner_strategy_warning');
		var submitBtn = BX('seo_adv_link_btn');

		var campaign = campaignSelect.value;
		var link = BX('adv_banner_link');

		submitBtn.disabled = true;
		bannerWarning.style.display = 'none';

		if (campaign > 0 && typeof bannerList[campaign] == 'undefined')
		{
			campaignSelect.disabled = true;
			bannerSelect.disabled = true;

			BX.ajax.loadJSON('/bitrix/tools/seo_yandex_direct.php?action=banners_get&campaign=' + campaign + '&sessid=' + BX.bitrix_sessid(), function (result)
			{
				bannerList[campaign] = result;
				updateNewBannerLink();
			});
		}
		else
		{
			campaignSelect.disabled = false;

			bannerSelect.disabled = true;
			link.style.display = 'none';

			bannerSelect[0].text = '<?=Loc::getMessage('SEO_CAMPAIGN_CHOOSE_OPTION')?>';

			if (campaign > 0)
			{
				link.style.display = 'inline';
				bannerSelect.disabled = false;

				var option = campaignSelect.options[campaignSelect.selectedIndex];
				if (option.getAttribute('data-add') == '0')
				{
					link.style.display = 'none';
					bannerWarning.style.display = 'inline';
				}

				if (!link._href)
				{
					link._href = link.href;
				}

				link.href = link._href + '&campaign=' + campaign;

				while(bannerSelect.length > 1)
				{
					bannerSelect.remove(1);
				}

				if (!bannerList[campaign].error)
				{
					bannerSelect[0].text = '<?=Loc::getMessage('SEO_BANNER_CHOOSE_OPTION')?>';

					for (var i = 0; i < bannerList[campaign].length; i++)
					{
						bannerSelect.add(BX.create('OPTION', {
							props: {
								text: bannerList[campaign][i].NAME,
								value: bannerList[campaign][i].ID
							}
						}));
					}
				}
			}
			else
			{
				bannerSelect.selectedIndex = 0;
			}
		}
	}

	function createLink()
	{
		var campaignSelect = BX('seo_adv_campaign');
		var bannerSelect = BX('seo_adv_banner');
		var submitBtn = BX('seo_adv_link_btn');

		if (bannerSelect.value > 0)
		{
			campaignSelect.disabled = true;
			bannerSelect.disabled = true;

			BX.adminPanel.showWait(submitBtn);

			BX.ajax.loadJSON('/bitrix/tools/seo_yandex_direct.php', {
				action: 'link_create',
				banner: bannerSelect.value,
				link: <?=$iblockElementInfo['ID']?>,
				link_type: '<?=Adv\LinkTable::TYPE_IBLOCK_ELEMENT?>',
				get_list_html: 1,
				sessid: BX.bitrix_sessid()
			}, function (res)
			{
				campaignSelect.disabled = false;
				bannerSelect.disabled = false;

				if(res.result)
				{
					//BX('adv_banner_selector').style.display = 'none';
					BX('adv_banner_list').innerHTML = res.list_html;
				}
			})
		}
	}

	function deleteLink(bannerId, el)
	{
		if(!el._loading)
		{
			el._loading = true;
			el.style.background = 'url("/bitrix/panel/main/images/waiter-small-white.gif") no-repeat scroll 3px center';

			BX.ajax.loadJSON('/bitrix/tools/seo_yandex_direct.php?action=link_delete&banner='+bannerId+'&link=<?=$iblockElementInfo["ID"]?>&link_type=<?=\Bitrix\Seo\Adv\LinkTable::TYPE_IBLOCK_ELEMENT?>&get_list_html=1&sessid='+BX.bitrix_sessid(), function(res)
			{
				if(res.result)
				{
					BX('adv_banner_list').innerHTML = res.list_html;
				}
			});
		}
	}

	function deleteLinkStat(bannerId, el)
	{
		if(!el._loading)
		{
			el._loading = true;
			el.style.background = 'url("/bitrix/panel/main/images/waiter-small-white.gif") no-repeat scroll 3px center';

			BX.ajax.loadJSON('/bitrix/tools/seo_yandex_direct.php?action=link_delete&banner='+bannerId+'&link=<?=$iblockElementInfo["ID"]?>&link_type=<?=\Bitrix\Seo\Adv\LinkTable::TYPE_IBLOCK_ELEMENT?>&get_list_html=0&sessid='+BX.bitrix_sessid(), function(res)
			{
				BX.remove(BX('yandex_direct_banner_' + bannerId));
			});
		}
	}

	function loadStat(bannerId)
	{
		var btn = BX('adv_banner_stat_button');
		var banners;
		var singleBanner = false;

		if(!!btn)
		{
			btn.disabled = true;
		}

		if(!bannerId)
		{
			var input = BX('seo_adv_campaign').form['seo_yandex_banner_id[]'];
			if (!!input)
			{
				if (!!input.tagName)
				{
					input = [input];
				}

				banners = [];
				for (var i = 0; i < input.length; i++)
				{
					banners.push(input[i].value);
				}

				if(banners.length <= 0)
				{
					banners = false;
				}
			}
		}
		else
		{
			banners = bannerId;
			singleBanner = true;
		}

		var statCont = BX(singleBanner ? 'yandex_direct_banner_' + bannerId : 'adv_banner_list');

		if(!!banners)
		{
			BX.ajax.loadJSON('/bitrix/tools/seo_yandex_direct.php', {
					action: 'banner_stat_detail',
					banner: banners,
					sessid: BX.bitrix_sessid()
				}, function(res)
				{
					if(!!btn)
					{
						btn.disabled = false;
					}

					if(!!res.html)
					{
						statCont.innerHTML = res.html;
					}
				}
			);
		}
	}

	function loadBannerStats(bannerId, dateStart, session)
	{
		var queryData;

		if(!!session)
		{
			queryData = {
				action: 'banner_stats',
				banner: bannerId,
				loading_session: session,
				sessid: BX.bitrix_sessid()
			};
		}
		else
		{
			BX('yandex_banner_message_' + bannerId).innerHTML = '<?=Loc::getMessage('SEO_YANDEX_STATS_WAIT')?>';

			queryData = {
				action: 'banner_stats',
				banner: bannerId,
				type: 'interval',
				date_from: dateStart,
				date_to: '<?=new Main\Type\Date();?>',
				sessid: BX.bitrix_sessid()
			};
		}

		BX.ajax.loadJSON('/bitrix/tools/seo_yandex_direct.php', queryData, function(res)
		{

			if (!!res.session)
			{
				BX.defer(loadBannerStats)(bannerId, dateStart, res.session);
				BX('yandex_banner_message_' + bannerId).innerHTML = '<?=Loc::getMessage('SEO_YANDEX_STATS_WAIT')?>: ' + Math.floor(100-(res.left/res.amount)*100) + '%';
			}
			else if(!!res.data)
			{
				loadStat(bannerId);

				if(!!res.error && res.error.code == '<?=Engine\YandexDirect::ERROR_NO_STATS?>')
				{
					res.error = null;
				}
			}

			if(!!res.error && (!!res.error.message || !!res.error.code))
			{
				alert(res.error.message||res.error.code);
			}
		});
	}

	function stopBanner(campaignId, bannerId)
	{
		BX('yandex_direct_action_button_' + bannerId).disabled = true;
		BX.ajax({
			url: '/bitrix/admin/seo_search_yandex_direct_banner.php?ID='+bannerId+'&action_button=stop&sessid='+BX.bitrix_sessid()+'&campaign='+campaignId+'&mode=list&table_id=tbl_yandex_direct_banner',
			processData: false,
			onsuccess: function()
			{
				loadStat(bannerId);
			}
		});
	}

	function resumeBanner(campaignId, bannerId)
	{
		BX('yandex_direct_action_button_' + bannerId).disabled = true;
		BX.ajax({
			url: '/bitrix/admin/seo_search_yandex_direct_banner.php?ID='+bannerId+'&action_button=resume&sessid='+BX.bitrix_sessid()+'&campaign='+campaignId+'&mode=list&table_id=tbl_yandex_direct_banner',
			processData: false,
			onsuccess: function()
			{
				loadStat(bannerId);
			}
		});
	}

</script>

<div id="adv_banner_list">
<?
	require(__DIR__."/seo_search_yandex_direct_list_link.php");
?>
</div>

<?
}

