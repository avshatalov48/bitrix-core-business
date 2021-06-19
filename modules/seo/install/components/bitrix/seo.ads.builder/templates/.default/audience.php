<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "") . "no-all-paddings no-background");
\Bitrix\Main\UI\Extension::load([
	"ui.buttons",
	"ui.icons",
	"ui.forms",
	"ui.progressbar",
	"seo.seoadbuilder",
	'ui.entity-selector'
]);
\CJSCore::Init("loader");

\Bitrix\Main\Page\Asset::getInstance()->addCss($this->GetFolder().'/configurator.css');
$accountId = $arParams['ACCOUNT_ID'];
$clientId = $arParams['CLIENT_ID'];
$type = $arParams['TYPE'];

?>

<div class="crm-ads-new-campaign">
	<div class="crm-ads-new-campaign-expert-header"><?php echo Loc::getMessage('CRM_ADS_RTG_AUDIENCE_TITLE')?></div>
	<div class="crm-ads-new-campaign-expert" id="crm-ads-new-campaign-expert">
		<div class="crm-ads-new-campaign-item-cost">
			<div class="crm-ads-new-campaign-item-cost-value"><?php echo Loc::getMessage('CRM_ADS_RTG_NOT_AVAILABLE')?></div>
			<div class="crm-ads-new-campaign-item-cost-desc"><?php echo Loc::getMessage('CRM_ADS_RTG_POTENTIAL_AUDIENCE')?></div>
		</div>
		<div class="crm-ads-new-campaign-expert-field">
			<div class="crm-ads-new-campaign-expert-name"><?=Loc::getMessage('CRM_ADS_RTG_GENDER')?></div>
			<div class="crm-ads-new-campaign-expert-field-inner">
				<label for="male" class="crm-ads-new-campaign-expert-item">
					<input class="crm-ads-new-campaign-expert-input" name="gender" type="checkbox" id="male" checked="">
					<span><?=Loc::getMessage('CRM_ADS_RTG_GENDER_MALE')?></span>
				</label>
				<label for="female" class="crm-ads-new-campaign-expert-item">
					<input class="crm-ads-new-campaign-expert-input" name="gender" type="checkbox" id="female" checked="">
					<span><?=Loc::getMessage('CRM_ADS_RTG_GENDER_FEMALE')?></span>
				</label>
			</div>
		</div>
		<div class="crm-ads-new-campaign-expert-field">
			<div class="crm-ads-new-campaign-expert-name"><?=Loc::getMessage('CRM_ADS_RTG_AGE')?></div>
			<div class="crm-ads-new-campaign-item-runner-block crm-ads-new-campaign-item-runner-block--double">
				<div class="crm-ads-new-campaign-item-runner-inner">
					<div class="crm-ads-new-campaign-item-runner-fill"></div>
					<div class="crm-ads-new-campaign-item-runner-value"></div>
					<label for="min" class="crm-ads-new-campaign-item-runner-label" id="label-min">
						<span class="crm-ads-new-campaign-item-runner-label-value">18</span>
						<span><?=Loc::getMessage('CRM_ADS_RTG_YEARS_OLD')?></span>
					</label>
					<input class="crm-ads-new-campaign-item-runner-input" type="range" min="13"
						   value="18" max="65" id="min">
					<label for="max" class="crm-ads-new-campaign-item-runner-label" id="label-max">
						<span class="crm-ads-new-campaign-item-runner-label-value">60</span>
						<span><?=Loc::getMessage('CRM_ADS_RTG_YEARS_OLD')?></span>
					</label>
					<input class="crm-ads-new-campaign-item-runner-input" type="range" min="13"
						   value="60" max="65" id="max">
				</div>
			</div>
		</div>
		<div class="crm-ads-new-campaign-expert-field">
			<div class="crm-ads-new-campaign-expert-name"><?=Loc::getMessage('CRM_ADS_RTG_INTEREST')?></div>
			<div class="crm-ads-new-campaign-expert-desc">
				<?=Loc::getMessage('CRM_ADS_RTG_INTEREST_RECOMMENDATION')?>
			</div>
			<div id="seo-ads-interests"></div>
		</div>
	</div>
</div>
<script type="text/javascript">
	window.seoAudience = new BX.Seo.SeoAudience( {
		accountId: '<?=$accountId?>',
		clientId: '<?=$clientId?>',
		type:'<?=$type?>',
		signedParameters: <?=\Bitrix\Main\Web\Json::encode($this->getComponent()->getSignedParameters())?>
	});
</script>

<?php
$buttons = [];
$buttons[] = ['TYPE' => 'apply', 'ONCLICK' => 'window.seoAudience.apply()'];
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