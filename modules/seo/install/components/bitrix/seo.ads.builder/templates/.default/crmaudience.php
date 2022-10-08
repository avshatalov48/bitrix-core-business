<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$APPLICATION->SetPageProperty("BodyClass", ($bodyClass? $bodyClass." " : "")."no-all-paddings no-background");
\Bitrix\Main\UI\Extension::load(
	[
		"ui.design-tokens",
		"ui.fonts.opensans",
		"ui.buttons",
		"ui.icons",
		"ui.forms",
		"ui.progressbar",
		"seo.seoadbuilder",
		"ui.entity-selector",
	]
);
\CJSCore::Init("loader");

\Bitrix\Main\Page\Asset::getInstance()
	->addCss($this->GetFolder().'/configurator.css');

$accountId = $arParams['ACCOUNT_ID'];
$clientId = $arParams['CLIENT_ID'];
$type = $arParams['TYPE'];

?>

	<div class="crm-ads-new-campaign">
		<div class="crm-ads-new-campaign-expert-header"><?php echo Loc::getMessage(
				'CRM_ADS_RTG_AUDIENCE_TITLE'
			) ?></div>
		<div class="crm-ads-new-campaign-expert">
			<div class="sender-letter-edit-row">
				<?
				$APPLICATION->IncludeComponent(
					"bitrix:sender.segment.selector",
					"",
					[
						'PATH_TO_ADD'        => "/marketing/segment/edit/0/",
						'PATH_TO_EDIT'       => "/marketing/segment/edit/#id#/",
						'DURATION_FORMATTED' => true,
						'SHOW_COUNTERS'      => true,
						'MESS'               => $arParams['MESS'],
					],
					false
				);
				?>
			</div>
		</div>
	</div>
<?php
$buttons = [];
$buttons[] = ['TYPE' => 'apply', 'ONCLICK' => 'BX.Seo.SeoCrmAudience.apply(this)'];
$buttons[] = ['TYPE' => 'cancel'];

$APPLICATION->IncludeComponent(
	"bitrix:ui.button.panel",
	"",
	[
		'BUTTONS' => $buttons
	],
	false
);
?>
