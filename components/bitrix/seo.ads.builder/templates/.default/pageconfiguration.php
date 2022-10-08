<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "") . "no-all-paddings no-background");
\Bitrix\Main\UI\Extension::load([
	"ui.design-tokens",
	"ui.fonts.opensans",
	"ui.buttons",
	"ui.icons",
	"ui.forms",
	"ui.buttons.icons",
	"seo.seoadbuilder",
]);

\CJSCore::Init("loader");

\Bitrix\Main\Page\Asset::getInstance()->addCss($this->GetFolder().'/configurator.css');

?>

<div class="crm-ads-new-campaign">
	<div class="crm-ads-new-campaign-expert-header"><?php echo Loc::getMessage('CRM_ADS_RTG_PAGE_CONFIGURATION_TITLE')?></div>
	<div class="crm-ads-new-campaign-expert">
		<div class="crm-ads-new-campaign-item-subtitle">
			<?php echo Loc::getMessage('CRM_ADS_RTG_PAGE_CONFIGURATION_EXPERT_MODE')?>
		</div>
		<div class="crm-ads-new-campaign-expert-field">
			<div class="crm-ads-new-campaign-expert-name">
				<?php echo Loc::getMessage('CRM_ADS_RTG_PAGE_CONFIGURATION_TARGET_PAGE')?>
			</div>
			<label for="target" class="crm-ads-new-campaign-expert-label">
				<?php echo Loc::getMessage('CRM_ADS_RTG_PAGE_CONFIGURATION_YOUR_URL')?>
			</label>
			<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
				<input type="text" class="ui-ctl-element seo-ads-target-url"
				<?php if(!empty($arParams['TARGET_URL'])):?>
				value="<?=htmlspecialcharsbx($arParams['TARGET_URL'])?>"
				<?php endif;?>
					placeholder="https://">
			</div>
		</div>
<!--		<div class="crm-ads-new-campaign-expert-field">-->
<!--			<div class="crm-ads-new-campaign-expert-name">-->
<!--				--><?php //echo Loc::getMessage('CRM_ADS_RTG_PAGE_CONFIGURATION_CALL_TO_ACTION')?>
<!--			</div>-->
<!--			<div class="crm-ads-new-campaign-expert-btn">-->
<!--				<div class="crm-ads-new-campaign-expert-select">-->
<!--					<label for="button" class="crm-ads-new-campaign-expert-label">-->
<!--						--><?php //echo Loc::getMessage('CRM_ADS_RTG_PAGE_CONFIGURATION_CALL_TO_ACTION')?>
<!--					</label>-->
<!--					<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100">-->
<!--						<div class="ui-ctl-after ui-ctl-icon-angle"></div>-->
<!--						<div class="ui-ctl-element" id="button"></div>-->
<!--					</div>-->
<!--				</div>-->
<!--				<div class="crm-ads-new-campaign-expert-preview">-->
<!--					<span class="crm-ads-new-campaign-expert-label">-->
<!--							--><?php //echo Loc::getMessage('CRM_ADS_RTG_PAGE_CONFIGURATION_PREVIEW')?>
<!--					</span>-->
<!--					<div class="crm-ads-new-campaign-expert-btn-value"></div>-->
<!--				</div>-->
<!--			</div>-->
<!--		</div>-->
	</div>
<script type="text/javascript">
	window.pageConfiguration = new BX.Seo.PageConfiguration();
</script>

<?php
	$buttons = [];
	$buttons[] = ['TYPE' => 'apply', 'ONCLICK' => 'window.pageConfiguration.apply(this)', 'WAIT' => false];
	$buttons[] = ['TYPE' => 'cancel'];
	$APPLICATION->IncludeComponent(
		"bitrix:ui.button.panel",
		"",
		array(
			'BUTTONS' => $buttons
		),
		false
	);
?>
