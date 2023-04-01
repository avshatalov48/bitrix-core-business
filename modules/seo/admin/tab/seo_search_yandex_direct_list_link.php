<?php
/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @global CUserTypeManager $USER_FIELD_MANAGER
 * @global CCacheManager $CACHE_MANAGER
 * @global array $iblockElementInfo
 * @global array $campaignList
 * @global array $arBanners
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Text\Converter;

Loc::loadMessages(__DIR__.'/../seo_adv.php');

if (
	!is_array($campaignList) || empty($campaignList)
	|| !is_array($arBanners) || empty($arBanners)
)
{
	return;
}
?>

<input id="adv_banner_stat_button" type="button" value="<?=Loc::getMessage('SEO_YANDEX_STATS_LOAD')?>" onclick="loadStat()">

<style>
.adv-links-list
{
	margin-top: 20px;
}

.adv-campaign-list
{
	padding-left: 25px;
}

.adv-banner-link,
.adv-campaign-link
{
	display: inline-block;
	height: 20px;
	vertical-align: top;
	margin-top: 2px;
}
</style>
<div id="adv_links_list" class="adv-links-list">
	<b><?= Loc::getMessage('SEO_YANDEX_DIRECT_BANNER_LINKS') ?></b>
	<?php foreach($arBanners as $campaignId => $campaignBanners): ?>
		<?php if (isset($campaignList[$campaignId])): ?>
			<div class="adv-campaign-item">
				<a
					href="/bitrix/admin/seo_search_yandex_direct_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$campaignId?>&back_url=<?=Converter::getHtmlConverter()->encode(urlencode($APPLICATION->GetCurPageParam('form_element_'.$iblockElementInfo["IBLOCK"]["ID"].'_active_tab=seo_adv_seo_adv', ['form_element_'.$iblockElementInfo["IBLOCK"]["ID"].'_active_tab'])))?>"
					class="adv-campaign-link"
				>
					<?=Converter::getHtmlConverter()->encode($campaignList[$campaignId]['NAME']);?>
				</a>
				<div class="adv-campaign-list">
					<?php foreach($campaignBanners as $banner): ?>
						<div class="adv-banner-item">
							<input type="hidden" name="seo_yandex_banner_id[]" value="<?=$banner['BANNER_ID']?>" />
							<a
								href="/bitrix/admin/seo_search_yandex_direct_banner_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$banner['BANNER_ID']?>&element=<?=$iblockElementInfo['ID']?>&back_url=<?=Converter::getHtmlConverter()->encode(urlencode($APPLICATION->GetCurPageParam('form_element_'.$iblockElementInfo["IBLOCK"]["ID"].'_active_tab=seo_adv_seo_adv', ['form_element_'.$iblockElementInfo["IBLOCK"]["ID"].'_active_tab'])))?>"
								class="adv-banner-link"
							>
								<?= Loc::getMessage('SEO_YANDEX_DIRECT_BANNER_LINK_TPL', [
									"#XML_ID#" => $banner['BANNER_XML_ID'],
									'#NAME#' => $banner['BANNER_NAME'],
								]) ?>
							</a>
							&nbsp;
							<span class="yandex-delete" onclick="deleteLink('<?=$banner['BANNER_ID']?>', this)"></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>
</div>