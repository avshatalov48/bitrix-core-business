<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \CMain $APPLICATION */
/** @var LandingSiteMasterComponent $component */
/** @var array $arResult */

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$APPLICATION->setPageProperty('BodyClass', 'no-background');

\Bitrix\Main\Loader::includeModule('ui');
\Bitrix\Main\UI\Extension::load([
	'ui.progressbar',
	'ui.fonts.opensans',
]);

$uriSelect = new \Bitrix\Main\Web\Uri($arResult['CUR_URI']);
$uriSelect->deleteParams([
	'super',
	'IFRAME',
	'IFRAME_TYPE',
]);
$uriSelect->addParams([
	'stepper' => 'store',
	'param' => 'store_v3',
	'showcaseId' => 'fashion',
	'sessid' => bitrix_sessid(),
]);

$teaserParams = [
	'ajaxUrl' => $uriSelect->getUri(),
	'texts' => [
		Loc::getMessage('LANDING_TPL_RANDOM_LOAD_TEXT_1'),
		Loc::getMessage('LANDING_TPL_RANDOM_LOAD_TEXT_2'),
		Loc::getMessage('LANDING_TPL_RANDOM_LOAD_TEXT_3'),
		Loc::getMessage('LANDING_TPL_RANDOM_LOAD_TEXT_4'),
	]
];

?>

<div class="landing-sm-teaser<?if (!\Bitrix\Landing\Manager::availableOnlyForZone('ru')){?> landing-sm-teaser--en<?}?>">
	<div class="landing-sm-teaser-head">
		<div class="landing-sm-teaser-title">
			<?= Loc::getMessage('LANDING_TPL_HOT_HEADER_NEW', [
				'#SPAN_HOT#' => '<span class="landing-sm-teaser-title--hot">',
				'#SPAN_GRAY#' => '<span class="landing-sm-teaser-title--gray">',
				'#SPAN_END#' => '</span>'
			]);?>
		</div>
		<div class="landing-sm-teaser-title landing-sm-teaser-title--sub">
			<?= Loc::getMessage('LANDING_TPL_HOT_SUBHEADER');?>
		</div>
		<div class="landing-sm-teaser-control">
			<div class="landing-sm-teaser-button">
				<form class="landing-sm-teaser-inline" data-role="landing-sm-form" action="<?= \htmlspecialcharsbx($component->getUri(['action' => 'create']))?>" method="post">
					<input type="hidden" name="param" value="store_v3" />
					<?= bitrix_sessid_post();?>
					<?if (\Bitrix\Landing\Domain::canRegisterInBitrix24()):?>
						<input class="ui-btn ui-btn-lg ui-btn-primary" data-role="landing-sm-create" type="button" value="<?= Loc::getMessage('LANDING_TPL_SUBMIT');?>" />
					<?else:?>
						<span style="color: red;"><?= Loc::getMessage('LANDING_TPL_ALERT_DISABLE')?></span>
					<?endif;?>
				</form>
			</div>
			<div class="landing-sm-teaser-loader-wrapper">
				<div class="landing-sm-teaser-loader" data-role="landing-sm-teaser-loader"></div>
				<div class="landing-sm-teaser-loader-ext" data-role="landing-sm-teaser-loader-ext"></div>
			</div>
		</div>
	</div>
	<div class="landing-sm-teaser-bottom"></div>
</div>

<script>
	BX.Landing.TemplateTeaserInstance = BX.Landing.TemplateTeaser.getInstance(<?=CUtil::PhpToJSObject(
		$teaserParams,
		false,
		false,
		true
	);?>);
</script>