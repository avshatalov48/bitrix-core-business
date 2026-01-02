<?php

use Bitrix\Intranet\Integration\Templates\Air\ChatMenu;
use Bitrix\Intranet\Site\Sections\CollaborationSection;
use Bitrix\Main\Application;
use Bitrix\Main\Web\Json;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

CModule::IncludeModule('voximplant');

/** @var \CMain $APPLICATION */

\Bitrix\Main\UI\Extension::load([
	'ls',
	'ui.design-tokens',
	'im.v2.application.messenger',
	'im.v2.application.launch'
]);

\Bitrix\UI\Toolbar\Facade\Toolbar::disable();

$bodyClass = $APPLICATION->getPageProperty('BodyClass');
$APPLICATION->setPageProperty(
	'BodyClass',
	($bodyClass ? $bodyClass . ' ' : '') . 'no-all-paddings no-background no-page-header no-footer-endless'
);

$application = \Bitrix\Im\V2\Service\Locator::getMessenger()->getApplication();
$config = Json::encode($application->getConfig());
?>
<div id="messenger-embedded-application"></div>
<script>
	BX.Messenger.v2.Application.Launch('messenger', <?=$config?>)
		.then(application => {
			application.initComponent('#messenger-embedded-application');
		})
	;
</script>

<?php
$this->setViewTarget("above_pagetitle", 100);

if (\Bitrix\Main\Loader::includeModule('intranet'))
{
	$showCollaborationMenu = CollaborationSection::shouldShowNewStructure();
	$APPLICATION->includeComponent(
		'bitrix:main.interface.buttons',
		'',
		[
			'ID' => $showCollaborationMenu ? 'top_menu_id_collaboration' : 'chat-menu',
			'ITEMS' => $showCollaborationMenu ? CollaborationSection::getMenuItems() : ChatMenu::getMenuItems(),
			'THEME' => 'air',
		]
	);
}

$this->endViewTarget();
