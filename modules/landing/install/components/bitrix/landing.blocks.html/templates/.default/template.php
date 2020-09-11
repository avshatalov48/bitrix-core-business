<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */
/** @var LandingBlocksHtmlComponent $component */
/** @var \Bitrix\Landing\Landing $landing */
/** @var \CMain $APPLICATION */

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$domainId = 0;

if (class_exists('\LandingPubComponent'))
{
	$landingInstance = \LandingPubComponent::getMainInstance();
	if (isset($landingInstance['LANDING_INSTANCE']))
	{
		$landing = $landingInstance['LANDING_INSTANCE'];
		$domainId = $landing->getDomainId();
	}
}

if ($arParams['ENABLED'] != 'Y' && $arParams['EDIT_MODE'] == 'Y')
{
	$APPLICATION->IncludeComponent(
		'bitrix:landing.blocks.message',
		'locked',
		array(
			'HEADER' => Loc::getMessage('LANDING_TPL_NOT_ENABLE_TITLE'),
			'MESSAGE' => Loc::getMessage('LANDING_TPL_NOT_ENABLE_TEXT'),
			'BUTTON' => Loc::getMessage('LANDING_TPL_NOT_ENABLE_LINK'),
			'LINK' => \Bitrix\Landing\Manager::BUY_LICENSE_PATH
		),
		$component
	);
}
else if ($arParams['EDIT_MODE'] == 'Y')
{
	?>
	<div class="g-min-height-200 g-flex-centered g-height-100">
		<div class="g-landing-alert">
			<?= Loc::getMessage('LANDING_TPL_NOT_IN_PREVIEW_MODE');?>
		</div>
	</div>
	<?
}
else if ($arParams['ENABLED'] == 'Y' || $arParams['PREVIEW_MODE'] == 'Y')
{
	$content = $component->htmlspecialcharsback($arParams['~HTML_CODE']);
	if (!$domainId)
	{
		$content = $component->sanitize($content);
	}
	echo $content;
}