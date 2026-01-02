<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\UI\Buttons\Color;
use Bitrix\UI\Toolbar\ButtonLocation;
use Bitrix\UI\Toolbar\Facade\Toolbar;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \CMain $APPLICATION */
/** @var array $arResult */

\Bitrix\Main\UI\Extension::load("ui.alerts");
\Bitrix\Main\UI\Extension::load("ui.forms");
\Bitrix\Main\UI\Extension::load('ui.buttons');
\Bitrix\Main\UI\Extension::load("ui.notification");
\Bitrix\Main\Loader::includeModule('ui');
\CJSCore::init("sidepanel");

$bodyClass = $APPLICATION->getPageProperty('BodyClass', false);
$APPLICATION->setPageProperty('BodyClass', trim(sprintf('%s %s', $bodyClass, 'pagetitle-toolbar-field-view pagetitle-mail-view')));

$button = new Bitrix\UI\Buttons\Button([
	'text' => Loc::getMessage('MAIL_USERSIGNATURE_ADD_BUTTON'),
	'classList' => ['mail-signature-create-btn'],
	'color' => Color::PRIMARY,
]);
$button->addAttribute('onclick', sprintf('BX.Mail.UserSignature.List.openUrl("%s")', CUtil::JSEscape($arResult['addUrl'])));

Toolbar::addFilter($arResult['FILTER']);
Toolbar::addButton($button, ButtonLocation::AFTER_TITLE);

$APPLICATION->SetTitle(Loc::getMessage('MAIL_USERSIGNATURE_LIST_TITLE'));

?><div id="signature-alert-container">
</div><?

$APPLICATION->IncludeComponent(
	"bitrix:main.ui.grid",
	"",
	$arResult['GRID']
);?>

<script>
	BX.ready(function() {
		<?='BX.message('.\CUtil::PhpToJSObject(\Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__)).');'?>
		BX.Mail.UserSignature.List.init();
	});
</script>
