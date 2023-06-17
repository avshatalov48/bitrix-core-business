<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

/** @var array $arParams */

Extension::load(
	[
		'ui.design-tokens',
		'ui.fonts.opensans',
		"ui.hint",
		"ui.icons",
		"ui.forms",
		"seo.ads.client_selector",
		"ui.buttons",
		"ui.buttons.icons",
		"ui.notification",
		"ui.switcher",
		"ui.fonts.ruble",
		"ui.sidepanel-content",
		'catalog.product-selector',
		"seo.seoadbuilder",
		'ui.entity-selector',
		'seo.ads.login',
	]
);
$provider = $arParams['PROVIDER'];
$isCloud = $arResult['IS_CLOUD'];
$containerNodeId = $arParams['CONTAINER_NODE_ID'];
$destroyEventName = $arParams['JS_DESTROY_EVENT_NAME'];
$accountId = $arParams['ACCOUNT_ID'];
$clientId = $arParams['CLIENT_ID'];
$targetUrl = $arParams['TARGET_URL'] ?? '';
$postListUrl = $arResult['POST_LIST_URL'] ?? '';
$audienceUrl = $arResult['AUDIENCE_URL'] ?? '';
$crmAudienceUrl = $arResult['CRM_AUDIENCE_URL'] ?? '';
$productSelectorUrl = $arResult['PRODUCT_SELECTOR_URL'] ?? '';
$pageConfigurationUrl = $arResult['PAGE_CONFIGURATION_URL'] ?? '';
$baseCurrency = $arResult['BASE_CURRENCY'] ?? '';
$basePriceId = $arResult['BASE_PRICE_ID'] ?? '';
$iBlockId = $arResult['IBLOCK_ID'] ?? '';
$autoRemoveDayNumber = $arParams['AUTO_REMOVE_DAY_NUMBER'];
$titleNodeSelector = $arParams['~TITLE_NODE_SELECTOR'];
$type = htmlspecialcharsbx($arParams['SUBTYPE']);
$typeUpped = mb_strtoupper($type);

$namePrefix = htmlspecialcharsbx($arParams['INPUT_NAME_PREFIX']);

$multiClients = array_key_exists('CLIENTS', $arParams['PROVIDER']);

$APPLICATION->IncludeComponent('bitrix:ui.image.input', '', [
	'upload' => true,
	'medialib' => false,
	'fileDialog' => true,
	'cloud' => true
	]
);

?>
<script>
	var loginObject = BX.Seo.Ads.LoginFactory.getLoginObject(<?=\Bitrix\Main\Web\Json::encode($provider)?>);
</script>
<div class="crm-ads-new-campaign" id="crm-ads-new-campaign">
	<div class="crm-ads-new-campaign-item">
		<div class="crm-ads-new-campaign-item-counter" data-stage="1">
			<div class="crm-ads-new-campaign-item-line"></div>
			<div class="crm-ads-new-campaign-item-number">
				<div class="crm-ads-new-campaign-item-number-checker"></div>
				<div class="crm-ads-new-campaign-item-number-text">1</div>
			</div>
		</div>
		<div class="crm-ads-new-campaign-item-block">
			<div class="crm-ads-new-campaign-item-header">
				<span class="crm-ads-new-campaign-item-title"><?=Loc::getMessage('CRM_ADS_RTG_LOG_ON')?></span>
				<a href="#" onclick="top.BX.Helper.show('redirect=detail&code=13065408')"
					class="ui-link ui-link-dashed crm-ads-new-campaign-item-link">
					<?=Loc::getMessage('CRM_ADS_RTG_NEED_HELP')?>
				</a>
				<span class="crm-ads-new-campaign-item-arrow"></span>
			</div>
			<div class="crm-ads-new-campaign-item-content">
				<div class="crm-ads-new-campaign-item-content-inner">
					<div class="ui-slider-section ui-slider-section-icon">
						<div class="ui-icon ui-slider-icon crm-ads-new-campaign-item-<?=$type?>">
							<i></i>
						</div>
						<div class="ui-slider-content-box">
							<div class="ui-slider-heading-3"><?=Loc::getMessage('CRM_ADS_RTG_TITLE_'.mb_strtoupper($type))?></div>
							<p class="ui-slider-paragraph-2"></p>
							<button
								type="button"
								class="ui-btn ui-btn-light-border"
								onclick="loginObject.login();"
								data-bx-ads-block="login"
							>
								<?=Loc::getMessage('CRM_ADS_RTG_LOGIN')?>
							</button>
							<div data-bx-ads-block="auth" style="display: none;">
								<div class="crm-ads-rtg-popup-settings">
									<div class="crm-ads-rtg-popup-social crm-ads-rtg-popup-social-<?=$type?>">
										<div data-bx-ads-client="" class="crm-ads-rtg-popup-client"></div>
										<input type="hidden" data-bx-ads-client-input=""
											name="<?=$namePrefix?>CLIENT_ID" value="<?=$clientId?>"
										>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="crm-ads-new-campaign-item-content-account" data-bx-ads-block="auth" data-flex="true">
						<div class="crm-ads-new-campaign-item-content-account-detail">
							<div class="crm-ads-new-campaign-item-content-account-label"><?=Loc::getMessage('CRM_ADS_RTG_AD_CABINET');?></div>
							<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">
								<div class="ui-ctl-after ui-ctl-icon-angle"></div>
								<select class="ui-ctl-element" disabled name="<?=$namePrefix?>ACCOUNT_ID"
										data-bx-ads-account=""></select>
							</div>
						</div>
						<div class="crm-ads-new-campaign-item-content-account-notice seo-ads-no-ad-account"
							style="display: none">
							<?=Loc::getMessage('CRM_ADS_RTG_AD_CABINET_NONE');?></div>
					</div>
					<div class="crm-ads-new-campaign-item-content-account" data-bx-ads-block="auth" data-flex="true">
						<div class="crm-ads-new-campaign-item-content-account-detail">
							<div class="crm-ads-new-campaign-item-content-account-label"><?=Loc::getMessage('CRM_ADS_RTG_AD_INSTAGRAM_PAGE');?></div>
							<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">
								<div class="ui-ctl-after ui-ctl-icon-angle"></div>
								<select class="ui-ctl-element" disabled name="<?=$namePrefix?>INSTAGRAM_ACCOUNT_ID"
										data-bx-ads-instagram-account=""></select>
							</div>
						</div>
						<div class="crm-ads-new-campaign-item-content-account-notice seo-ads-no-ad-account-instagram"
							style="display: none">
							<?=Loc::getMessage('CRM_ADS_RTG_AD_INSTAGRAM_NONE');?></div>
					</div>
					<div class="crm-ads-new-campaign-item-content-currency seo-ads-currency-block"
						style="display: none">
						<div class="crm-ads-new-campaign-item-content-currency-title">
							<?=Loc::getMessage('CRM_ADS_RTG_CURRENCY_CONFIGURATION');?>
						</div>
						<div class="crm-ads-new-campaign-item-content-currency-desc">
							<?=Loc::getMessage('CRM_ADS_RTG_CURRENCY_CONFIGURATION_DESC');?>
						</div>
						<div class="crm-ads-new-campaign-item-content-currency-subtitle">
							<?=Loc::getMessage('CRM_ADS_RTG_CURRENCY_CONFIGURATION_CURRENT');?>
						</div>
						<span class="crm-ads-new-campaign-item-content-currency-value seo-ads-current-currency">
						</span>
						<div class="crm-ads-new-campaign-item-content-currency-rate">
							<div class="crm-ads-new-campaign-item-content-currency-subtitle">
								<?=Loc::getMessage('CRM_ADS_RTG_CURRENCY_CONFIGURATION_COURSE');?>(
								<?=Loc::getMessage('CRM_ADS_RTG_CURRENCY_CONFIGURATION_BASE');?>
								<?=$baseCurrency?>)
							</div>
							<div class="crm-ads-new-campaign-item-content-currency-block">
								<div class="ui-ctl ui-ctl-textbox">
									<input type="text" class="ui-ctl-element seo-ads-currency-count" value="1">
								</div>
								<span class="crm-ads-new-campaign-item-content-currency-name seo-ads-current-currency"></span>
								<span class="crm-ads-new-campaign-item-content-currency-equal">=</span>
								<div class="ui-ctl ui-ctl-textbox">
									<input type="text" class="ui-ctl-element seo-ads-currency-course">
								</div>
								<span class="crm-ads-new-campaign-item-content-currency-name seo-ads-base-currency"><?=$baseCurrency?></span>
							</div>
						</div>
						<button type="button" class="ui-btn ui-btn-sm ui-btn-primary seo-ads-currency-apply-btn">
							<?=Loc::getMessage('CRM_ADS_RTG_APPLY');?>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="crm-ads-new-campaign-item">
		<div class="crm-ads-new-campaign-item-counter" data-stage="2">
			<div class="crm-ads-new-campaign-item-line"></div>
			<div class="crm-ads-new-campaign-item-number">
				<div class="crm-ads-new-campaign-item-number-checker"></div>
				<div class="crm-ads-new-campaign-item-number-text">2</div>
			</div>
		</div>
		<div class="crm-ads-new-campaign-item-block">
			<div class="crm-ads-new-campaign-item-header">
				<span class="crm-ads-new-campaign-item-title"><?=Loc::getMessage('CRM_ADS_RTG_SELECT_POST');?></span>
			</div>
			<div class="crm-ads-new-campaign-item-content">
				<div class="crm-ads-new-campaign-item-content-inner">
					<div class="crm-ads-new-campaign-item-desc"><?=Loc::getMessage(
							'CRM_ADS_RTG_SELECT_POST_DESCRIPTION'
						);?></div>
					<div class="crm-ads-new-campaign-item-posts">
						<div class="crm-ads-new-campaign-item-post crm-ads-new-campaign-item-post-new">
							<div class="crm-ads-new-campaign-item-post-img"></div>
							<div class="crm-ads-new-campaign-item-post-new-text"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="crm-ads-new-campaign-item">
		<div class="crm-ads-new-campaign-item-counter" data-stage="3">
			<div class="crm-ads-new-campaign-item-line"></div>
			<div class="crm-ads-new-campaign-item-number">
				<div class="crm-ads-new-campaign-item-number-checker"></div>
				<div class="crm-ads-new-campaign-item-number-text">3</div>
			</div>
		</div>
		<div class="crm-ads-new-campaign-item-block">
			<div class="crm-ads-new-campaign-item-header">
				<span class="crm-ads-new-campaign-item-title"><?=Loc::getMessage('CRM_ADS_RTG_PRODUCT_PAGE');?></span>
<!--				<div class="crm-ads-new-campaign-item-expert">-->
<!--					<span class="crm-ads-new-campaign-item-expert-text">--><?//=Loc::getMessage('CRM_ADS_RTG_EXPERT_MODE');?><!--</span>-->
<!--					<span class="crm-ads-new-campaign-item-expert-switcher"-->
<!--						id="crm-ads-new-campaign-item-expert-product"></span>-->
<!--				</div>-->
			</div>
			<div class="crm-ads-new-campaign-item-content">
				<div class="crm-ads-new-campaign-item-content-inner">
					<div class="crm-ads-new-campaign-item-desc"><?=Loc::getMessage(
							'CRM_ADS_RTG_PRODUCT_PAGE_ADD_OR_CREATE'
						);?></div>
					<div class="crm-ads-new-campaign-item-options">
						<?php if ($isCloud): ?>
							<div class="crm-ads-new-campaign-item-option seo-ads-product-item-block
							crm-ads-new-campaign-item-option--selected"
								data-type="auto">
								<span class="crm-ads-new-campaign-item-option-label"><?=Loc::getMessage(
										'CRM_ADS_RTG_PRODUCT_AD'
									)?></span>
								<div class="crm-ads-new-campaign-item-option-subtitle"><?=Loc::getMessage(
										'CRM_ADS_RTG_PRODUCT_SELL'
									)?></div>
								<div class="crm-ads-new-campaign-item-option-text"><?=Loc::getMessage(
										'CRM_ADS_RTG_PRODUCT_SELL_CREATE_AUTO'
									)?></div>
							</div>
						<?php endif ?>

						<div class="crm-ads-new-campaign-item-option seo-ads-product-item-block" data-type="expert">
							<span class="crm-ads-new-campaign-item-option-label"><?=Loc::getMessage(
									'CRM_ADS_RTG_PRODUCT_EXPERT'
								)?></span>
							<div class="crm-ads-new-campaign-item-option-subtitle"><? Loc::getMessage(
									'CRM_ADS_RTG_PRODUCT_EXPERT_SUBTITLE'
								) ?></div>
							<div class="crm-ads-new-campaign-item-option-text"><?=Loc::getMessage(
									'CRM_ADS_RTG_PRODUCT_EXPERT_DESCRIPTION'
								)?></div>
						</div>
					</div>

					<?php if ($isCloud): ?>
						<div class="crm-ads-new-campaign-item-container seo-ads-store"
						data-type="store-not-created" style="display:none">
							<div class="crm-ads-new-campaign-item-subtitle"><?=Loc::getMessage(
									'CRM_ADS_RTG_PRODUCT_FOR_SELL'
								)?></div>
							<button type="button" class="ui-btn ui-btn-primary seo-ads-add-product-btn">
								<?=Loc::getMessage('CRM_ADS_RTG_ADD_PRODUCT_FOR_SELL')?>
							</button>
						</div>
					<?php endif; ?>

					<div class="crm-ads-new-campaign-item-container seo-ads-store" data-type="store-created" style="display:none">
						<div class="crm-ads-new-campaign-item-subtitle"><?=Loc::getMessage(
								'CRM_ADS_RTG_PRODUCT_FOR_SELL'
							)?></div>
						<div id="facebook-product-selector"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="crm-ads-new-campaign-item crm-ads-new-campaign-item--ads">
		<div class="crm-ads-new-campaign-item-counter" data-stage="4">
			<div class="crm-ads-new-campaign-item-line"></div>
			<div class="crm-ads-new-campaign-item-number">
				<div class="crm-ads-new-campaign-item-number-checker"></div>
				<div class="crm-ads-new-campaign-item-number-text">4</div>
			</div>
		</div>
		<div class="crm-ads-new-campaign-item-block">
			<div class="crm-ads-new-campaign-item-header">
				<span class="crm-ads-new-campaign-item-title"><?=Loc::getMessage('CRM_ADS_RTG_CAMPAIGN')?></span>
			</div>
			<div class="crm-ads-new-campaign-item-content">
				<div class="crm-ads-new-campaign-item-content-inner">
					<div class="crm-ads-new-campaign-item-desc"><?=Loc::getMessage('CRM_ADS_RTG_AUDIENCE');?></div>
					<div class="crm-ads-new-campaign-item-geo">
						<div class="crm-ads-new-campaign-item-geo-title"><?=Loc::getMessage(
								'CRM_ADS_RTG_PRODUCT_LOCATION'
							)?></div>
						<div class="crm-ads-new-campaign-item-geo-subject"><?=Loc::getMessage(
								'CRM_ADS_RTG_LOCATION_LIVING_PLACE'
							)?></div>
						<div id="seo-ads-regions"></div>
					</div>
					<div class="crm-ads-new-campaign-item-options">
						<div class="crm-ads-new-campaign-item-option seo-ads-audience-item-block
						crm-ads-new-campaign-item-option--selected" data-type="auto">
							<span class="crm-ads-new-campaign-item-option-label"><?=Loc::getMessage(
									'CRM_ADS_RTG_AUDIENCE_AD'
								);?></span>
							<div class="crm-ads-new-campaign-item-option-subtitle"><?=Loc::getMessage(
									'CRM_ADS_RTG_AUDIENCE_AD'
								);?></div>
							<div class="crm-ads-new-campaign-item-option-text"><?=Loc::getMessage(
									'CRM_ADS_RTG_AUDIENCE_NEW'
								);?></div>
							<div class="crm-ads-new-campaign-item-option-notice"><?=Loc::getMessage(
									'CRM_ADS_RTG_AUDIENCE_MEN_WOMAN_25_45'
								);?></div>
						</div>
						<div class="crm-ads-new-campaign-item-option seo-ads-audience-item-block"  data-type="crm">
							<span class="crm-ads-new-campaign-item-option-label"><?=Loc::getMessage(
									'CRM_ADS_RTG_AUDIENCE_AUTO'
								);?></span>
							<div class="crm-ads-new-campaign-item-option-subtitle"><?=Loc::getMessage(
									'CRM_ADS_RTG_AUDIENCE_AUTO_TITLE'
								);?></div>
							<div class="crm-ads-new-campaign-item-option-text"><?=Loc::getMessage(
									'CRM_ADS_RTG_AUDIENCE_AUTO_DESCRIPTION'
								)?></div>
						</div>
						<div class="crm-ads-new-campaign-item-option seo-ads-audience-item-block" data-type="expert">
							<span class="crm-ads-new-campaign-item-option-label"><?=Loc::getMessage(
									'CRM_ADS_RTG_PRODUCT_EXPERT'
								)?></span>
							<div class="crm-ads-new-campaign-item-option-subtitle"><?=Loc::getMessage(
									'CRM_ADS_RTG_PRODUCT_EXPERT_SUBTITLE'
								)?></div>
							<div class="crm-ads-new-campaign-item-option-text"><?=Loc::getMessage(
									'CRM_ADS_RTG_PRODUCT_EXPERT_DESCRIPTION'
								)?></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="crm-ads-new-campaign-item crm-ads-new-campaign-item--cost">
		<div class="crm-ads-new-campaign-item-counter" data-stage="5">
			<div class="crm-ads-new-campaign-item-line"></div>
			<div class="crm-ads-new-campaign-item-number">
				<div class="crm-ads-new-campaign-item-number-checker"></div>
				<div class="crm-ads-new-campaign-item-number-text">5</div>
			</div>
		</div>
		<div class="crm-ads-new-campaign-item-block">
			<div class="crm-ads-new-campaign-item-header">
				<span class="crm-ads-new-campaign-item-title"><?=Loc::getMessage('CRM_ADS_RTG_BUDGET');?></span>
			</div>
			<div class="crm-ads-new-campaign-item-content">
				<div class="crm-ads-new-campaign-item-content-inner">
					<div class="crm-ads-new-campaign-item-cost" style="display: none">
						<div class="crm-ads-new-campaign-item-cost-value"><span
								class="seo-ads-budget-total-value"></span>
							<span class="seo-ads-budget-total-currency"></span>
							<?=Loc::getMessage('CRM_ADS_RTG_BUDGET_FOR');?>
							<span class="seo-ads-budget-total-duration"></span>
							<?=Loc::getMessage(
								'CRM_ADS_RTG_BUDGET_TOTAL_DAYS'
							);?>
						</div>
						<div class="crm-ads-new-campaign-item-cost-desc">
							<?=Loc::getMessage('CRM_ADS_RTG_BUDGET_TOTAL');?>
						</div>
					</div>
					<div class="crm-ads-new-campaign-item-desc">
						<?=Loc::getMessage('CRM_ADS_RTG_BUDGET_OPTIONS')?>
					</div>
					<div class="crm-ads-new-campaign-item-options">
						<div
							class="crm-ads-new-campaign-item-option seo-ads-budget-item-block"
							data-type="recommended"
						>
							<span class="crm-ads-new-campaign-item-option-label">
								<?=Loc::getMessage('CRM_ADS_RTG_BUDGET_RECOMMENDED');?></span>
							<div class="crm-ads-new-campaign-item-option-subtitle"><?=Loc::getMessage(
									'CRM_ADS_RTG_BUDGET_RECOMMENDED_SUBTITLE'
								);?></div>
							<div class="crm-ads-new-campaign-item-option-text"><?=Loc::getMessage(
									'CRM_ADS_RTG_BUDGET_RECOMMENDED_TEXT'
								);?></div>
							<div class="crm-ads-new-campaign-item-option-price">
								<span class="seo-ads-budget-recommended-duration"></span>
								<?=Loc::getMessage(
									'CRM_ADS_RTG_BUDGET_DAY'
								);?>
								<span class="seo-ads-budget-recommended-value"></span>
								<span class="seo-ads-budget-recommended-currency"></span>
							</div>
						</div>
						<div
							class="crm-ads-new-campaign-item-option seo-ads-budget-item-block"
							data-type="verified"
						>
							<span class="crm-ads-new-campaign-item-option-label"><?=Loc::getMessage(
									'CRM_ADS_RTG_BUDGET_VERIFIED'
								);?></span>
							<div class="crm-ads-new-campaign-item-option-subtitle"><?=Loc::getMessage(
									'CRM_ADS_RTG_BUDGET_VERIFIED_SUBTITLE'
								);?></div>
							<div class="crm-ads-new-campaign-item-option-text"><?=Loc::getMessage(
									'CRM_ADS_RTG_BUDGET_VERIFIED_TEXT'
								);?></div>
							<div class="crm-ads-new-campaign-item-option-price">
								<span class="seo-ads-budget-verified-duration"></span>
								<?=Loc::getMessage(
									'CRM_ADS_RTG_BUDGET_DAY'
								);?>
								<span class="seo-ads-budget-verified-value"></span>
								<span class="seo-ads-budget-verified-currency"></span>
							</div>
						</div>
						<div
							class="crm-ads-new-campaign-item-option seo-ads-budget-item-block"
							data-type="boost"
						>
							<span class="crm-ads-new-campaign-item-option-label"><?=Loc::getMessage(
									'CRM_ADS_RTG_BUDGET_BOOST'
								);?></span>
							<div class="crm-ads-new-campaign-item-option-subtitle"><?=Loc::getMessage(
									'CRM_ADS_RTG_BUDGET_BOOST_SUBTITLE'
								);?></div>
							<div class="crm-ads-new-campaign-item-option-text"><?=Loc::getMessage(
									'CRM_ADS_RTG_BUDGET_BOOST_TEXT'
								);?></div>
							<div class="crm-ads-new-campaign-item-option-price">
								<span class="seo-ads-budget-boost-duration"></span>
								<?=Loc::getMessage(
									'CRM_ADS_RTG_BUDGET_DAY'
								);?>
								<span class="seo-ads-budget-boost-value"></span>
								<span class="seo-ads-budget-boost-currency"></span>
							</div>
						</div>
						<div
							class="crm-ads-new-campaign-item-option seo-ads-budget-item-block"
							data-type="confident"
						>
							<span class="crm-ads-new-campaign-item-option-label"><?=Loc::getMessage(
									'CRM_ADS_RTG_BUDGET_CONFIDENT'
								);?></span>
							<div class="crm-ads-new-campaign-item-option-subtitle"><?=Loc::getMessage(
									'CRM_ADS_RTG_BUDGET_CONFIDENT_SUBTITLE'
								);?></div>
							<div class="crm-ads-new-campaign-item-option-text"><?=Loc::getMessage(
									'CRM_ADS_RTG_BUDGET_CONFIDENT_TEXT'
								);?></div>
							<div class="crm-ads-new-campaign-item-option-price">
								<span class="seo-ads-budget-confident-duration"></span>
								<?=Loc::getMessage(
									'CRM_ADS_RTG_BUDGET_DAYS'
								);?>
								<span class="seo-ads-budget-confident-value"></span>
								<span class="seo-ads-budget-confident-currency"></span>
							</div>
						</div>
					</div>
				</div>
				<div class="crm-ads-new-campaign-item-container" style="display: none;">
					<div class="crm-ads-new-campaign-item-subtitle"><?=Loc::getMessage(
							'CRM_ADS_RTG_EXPERT_MODE'
						)?></div>
					<div class="crm-ads-new-campaign-item-expert-runner">
						<div class="crm-ads-new-campaign-item-runner-title"></div>
						<div class="crm-ads-new-campaign-item-runner-block">
							<label for="cost" class="crm-ads-new-campaign-item-runner-label">
								<span class="crm-ads-new-campaign-item-runner-label-value">5</span>
								<span> </span>
							</label>
							<div class="crm-ads-new-campaign-item-runner-inner">
								<div class="crm-ads-new-campaign-item-runner-fill"></div>
								<div class="crm-ads-new-campaign-item-runner-value" id="crm-ads-new-campaign-item-runner-value-cost"></div>
								<input class="crm-ads-new-campaign-item-runner-input" type="range" id="cost">
							</div>
							<div class="crm-ads-new-campaign-item-runner-desc"> 400</div>
						</div>
					</div>
					<div class="crm-ads-new-campaign-item-expert-runner">
						<div class="crm-ads-new-campaign-item-runner-title"></div>
						<div class="crm-ads-new-campaign-item-runner-block">
							<label for="duration" class="crm-ads-new-campaign-item-runner-label">
								<span class="crm-ads-new-campaign-item-runner-label-value">5</span>
								<span><?=Loc::getMessage('CRM_ADS_RTG_AD_DAYS')?></span>
							</label>
							<div class="crm-ads-new-campaign-item-runner-inner">
								<div class="crm-ads-new-campaign-item-runner-fill"></div>
								<div class="crm-ads-new-campaign-item-runner-value" id="crm-ads-new-campaign-item-runner-value-duration"></div>
								<input class="crm-ads-new-campaign-item-runner-input" type="range" id="duration">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="crm-ads-new-campaign-item crm-ads-new-campaign-item--total">
		<div class="crm-ads-new-campaign-item-counter">
			<div class="crm-ads-new-campaign-item-line"></div>
		</div>
		<div class="crm-ads-new-campaign-item-block">
			<div class="crm-ads-new-campaign-item-header">
				<span class="crm-ads-new-campaign-item-title"><?=Loc::getMessage('CRM_ADS_RTG_HOW_WILL_IT_LOOK');?></span>
			</div>
			<div class="crm-ads-new-campaign-item-content">
				<div class="crm-ads-new-campaign-item-content-inner">
					<div class="crm-ads-new-campaign-item-total">
						<div class="crm-ads-new-campaign-item-total-info">
							<div class="crm-ads-new-campaign-item-total-desc">
								<div class="crm-ads-new-campaign-item-total-item">
									<span class="crm-ads-new-campaign-item-total-label">
										<?=Loc::getMessage('CRM_ADS_RTG_TARGET_URL');?>
									</span>
									<div class="crm-ads-new-campaign-item-total-content seo-ads-target-url">
										<span>bitrix24.site</span>
									</div>
								</div>
								<div class="crm-ads-new-campaign-item-total-item">
									<span class="crm-ads-new-campaign-item-total-label">
										<?=Loc::getMessage('CRM_ADS_RTG_AUDIENCE');?>
									</span>
									<div class="crm-ads-new-campaign-item-total-content">
										<span class="seo-ads-audience-summary"></span>
									</div>
								</div>
								<div class="crm-ads-new-campaign-item-total-item">
									<span class="crm-ads-new-campaign-item-total-label"><?=Loc::getMessage('CRM_ADS_RTG_BUDGET_AND_DURATION');?></span>
									<div class="crm-ads-new-campaign-item-total-content">
										<span
											class="seo-ads-total-budget"></span>
										<span class="seo-ads-total-currency"></span>
										<?=Loc::getMessage('CRM_ADS_RTG_BUDGET_FOR');?>
										<span class="seo-ads-total-duration"></span>
										<?=Loc::getMessage(
											'CRM_ADS_RTG_BUDGET_TOTAL_DAYS'
										);?>
									</div>
								</div>
								<div class="crm-ads-new-campaign-item-total-item">
									<span class="crm-ads-new-campaign-item-total-label"><?=Loc::getMessage('CRM_ADS_RTG_BUDGET_AND_PAYMENT');?></span>
									<div class="crm-ads-new-campaign-item-total-content">
										<a href = "#"
										   class="ui-link ui-link-dashed"
											onclick="top.BX.Helper.show('redirect=detail&code=13065422')"
											><?=Loc::getMessage('CRM_ADS_RTG_AD_ARTICLE');?></a>
										<a class="ui-link ui-link-dashed"
											href="https://www.facebook.com/ads/manager/accounts"
											target="_blank"><?=Loc::getMessage('CRM_ADS_RTG_AD_CABINET');?></a>
									</div>
								</div>
							</div>
							<div class="crm-ads-new-campaign-item-total-cost">
								<div class="crm-ads-new-campaign-item-total-title">
									<?=Loc::getMessage('CRM_ADS_RTG_AD_SUMMARY_PRICE');?></div>
								<div class="crm-ads-new-campaign-item-total-block">
									<span class="crm-ads-new-campaign-item-total-name">
										<?=Loc::getMessage('CRM_ADS_RTG_AD_SUMMARY_BUDGET');?></span>
									<div class="crm-ads-new-campaign-item-total-value">
										<span class="seo-ads-budget-total-value"></span>
										<span class="seo-ads-current-currency"></span>
									</div>
								</div>
								<div class="crm-ads-new-campaign-item-total-block">
									<span class="crm-ads-new-campaign-item-total-name">
										<?=Loc::getMessage('CRM_ADS_RTG_AD_BUDGET_TOTAL');?></span>
									<div class="crm-ads-new-campaign-item-total-value">
										<span class="seo-ads-budget-total-value"></span>
										<span class="seo-ads-current-currency"></span>
									</div>
								</div>
							</div>
						</div>
						<div class="crm-ads-new-campaign-item-total-preview">
							<div class="crm-ads-new-campaign-item-total-preview-img">
								<div class="crm-ads-new-campaign-item-total-preview-img-value"></div>
							</div>
							<div class="crm-ads-new-campaign-item-total-preview-text"><?=Loc::getMessage('CRM_ADS_RTG_AD_MORE');?></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="crm-ads-new-campaign-item crm-ads-new-campaign-item--run">
		<div class="crm-ads-new-campaign-item-counter" data-stage="6">
			<div class="crm-ads-new-campaign-item-line"></div>
			<div class="crm-ads-new-campaign-item-number"></div>
		</div>
		<div class="crm-ads-new-campaign-item-block">
			<div class="crm-ads-new-campaign-item-run">
				<div class="crm-ads-new-campaign-item-run-btn">
					<button class="ui-btn ui-btn-lg ui-btn-success ui-btn-round seo-ads-to-moderation-btn"
						type="button">
						<?=Loc::getMessage('CRM_ADS_RTG_AD_TO_MODERATION');?>
					</button>
				</div>
				<p class="crm-ads-new-campaign-item-run-desc"><?=Loc::getMessage('CRM_ADS_RTG_AD_PRE_MODERATION_MESSAGE');?><br>
					<?=Loc::getMessage('CRM_ADS_RTG_AD_PRE_TERM_OF_USE');?>
                    <a href="https://www.facebook.com/business/ads-guide" target="_blank" class="ui-link"><?=Loc::getMessage('CRM_ADS_RTG_AD_TERM_OF_USE');?></a> Instagram.</p>
			</div>
		</div>
	</div>

	<div hidden="hidden">
		<input type="hidden" data-bx-ads-body="" name="<?=$namePrefix?>BODY">
		<input type="hidden" data-bx-ads-target-url="" name="<?=$namePrefix?>TARGET_URL">
		<input type="hidden" data-bx-ads-budget="" name="<?=$namePrefix?>BUDGET">
		<input type="hidden" data-bx-ads-duration="" name="<?=$namePrefix?>DURATION">
		<input type="hidden" data-bx-ads-permalink="" name="<?=$namePrefix?>PERMALINK">
		<input type="hidden" data-bx-ads-page-id="" name="<?=$namePrefix?>PAGE_ID">
		<input type="hidden" data-bx-ads-id="" name="<?=$namePrefix?>ADS_ID">
		<input type="hidden" data-bx-ads-creative="" name="<?=$namePrefix?>CREATIVE_ID">
		<input type="hidden" data-bx-ads-campaign="" name="<?=$namePrefix?>CAMPAIGN_ID">
		<input type="hidden" data-bx-ads-ad-set="" name="<?=$namePrefix?>AD_SET_ID">
		<input type="hidden" data-bx-ads-interests="" name="<?=$namePrefix?>INTERESTS">
		<input type="hidden" data-bx-ads-genders="" name="<?=$namePrefix?>GENDERS">
		<input type="hidden" data-bx-ads-regions="" name="<?=$namePrefix?>REGIONS">
		<input type="hidden" data-bx-ads-age-from="" name="<?=$namePrefix?>AGE_FROM">
		<input type="hidden" data-bx-ads-age-to="" name="<?=$namePrefix?>AGE_TO">
		<input type="hidden" data-bx-ads-media-id="" name="<?=$namePrefix?>MEDIA_ID">
		<input type="hidden" data-bx-ads-actor-id="" name="<?=$namePrefix?>INSTAGRAM_ACTOR_ID">
		<input type="hidden" data-bx-ads-image-url="" name="<?=$namePrefix?>IMAGE_URL">
	</div>
</div>


<script>
	BX.ready(function () {

		var r = (Date.now()/1000|0);
		BX.loadCSS('<?=$this->GetFolder()?>/configurator.css?' + r);

		var params = <?=\Bitrix\Main\Web\Json::encode(
			[
				'provider'             => $provider,
				'clientId'             => $clientId,
				'accountId'            => $accountId,
				'destroyEventName'     => $destroyEventName,
				'postListUrl'          => $postListUrl,
				'audienceUrl'          => $audienceUrl,
				'crmAudienceUrl'       => $crmAudienceUrl,
				'pageConfigurationUrl' => $pageConfigurationUrl,
				'baseCurrency'         => $baseCurrency,
				'iBlockId'             => $iBlockId,
				'isCloud'              => $isCloud !== false,
				'basePriceId'          => $basePriceId,
				'storeExists'          => $arResult['STORE_EXISTS'],
				'signedParameters'     => $this->getComponent()
					->getSignedParameters(),
				'componentName'        => $this->getComponent()
					->getName(),
				'titleNodeSelector'    => $titleNodeSelector,
				'type'                 => $type,
				'mess'                 => [
					'errorAction'           => Loc::getMessage('CRM_ADS_RTG_ERROR_ACTION'),
					'dlgBtnClose'           => Loc::getMessage('CRM_ADS_RTG_CLOSE'),
					'dlgBtnCreate'          => Loc::getMessage('CRM_ADS_RTG_CREATE'),
					'dlgBtnApply'           => Loc::getMessage('CRM_ADS_RTG_APPLY'),
					'dlgBtnCancel'          => Loc::getMessage('CRM_ADS_RTG_CANCEL_ALT')
				]
			]
		)?>;
		new BX.Seo.SeoAdBuilder(params);
	});
</script>
