<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
\Bitrix\Main\UI\Extension::load("ui.alerts");
\Bitrix\Main\UI\Extension::load("ui.forms");
\Bitrix\Main\UI\Extension::load('ui.buttons');
\Bitrix\Main\UI\Extension::load("ui.notification");
\CJSCore::init("sidepanel");
$isIframe = isset($arResult["IFRAME"]) && $arResult["IFRAME"] === "Y";

$bodyClass = $APPLICATION->getPageProperty('BodyClass', false);
$APPLICATION->setPageProperty('BodyClass', trim(sprintf('%s %s', $bodyClass, 'pagetitle-toolbar-field-view pagetitle-mail-view')));

if ($isIframe):?>
	<div class="mail-signature-is-iframe">
<?endif;

if (SITE_TEMPLATE_ID == 'bitrix24' || $isIframe)
{
	$this->setViewTarget('inside_pagetitle'); ?>

	<div class="pagetitle-container mail-pagetitle-flexible-space">
		<? $APPLICATION->includeComponent(
			'bitrix:main.ui.filter', '',
			$arResult['FILTER']
		); ?>
	</div>

	<button class="ui-btn ui-btn-primary mail-signature-create-btn" onclick="BX.Mail.UserSignature.List.openUrl('<?=CUtil::JSEscape($arResult['addUrl']);?>');">
		<?= Loc::getMessage('MAIL_USERSIGNATURE_ADD_BUTTON') ?>
	</button>

	<? $this->endViewTarget();
}
else
{
	$APPLICATION->includeComponent(
		'bitrix:main.ui.filter', '',
		$arResult['FILTER']
	); ?>

	<button class="ui-btn ui-btn-primary mail-signature-create-btn" onclick="BX.Mail.UserSignature.List.openUrl('<?=CUtil::JSEscape($arResult['addUrl']);?>');">
		<?= Loc::getMessage('MAIL_USERSIGNATURE_ADD_BUTTON') ?>
	</button>

	<?
}

$APPLICATION->SetTitle(Loc::getMessage('MAIL_USERSIGNATURE_LIST_TITLE'));

?><div id="signature-alert-container">
</div><?

$APPLICATION->IncludeComponent(
	"bitrix:main.ui.grid",
	"",
	$arResult['GRID']
);?>

<?if ($isIframe)
{?>
	</div>
<?}?>
<script>
	BX.ready(function() {
		<?='BX.message('.\CUtil::PhpToJSObject(\Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__)).');'?>
		BX.Mail.UserSignature.List.init();
	});
</script>
