<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

Extension::load('ui.buttons');

$popupId = 'log_entry_stub_'.(int)$arParams['EVENT']['ID'];

?>
<div class="feed-post-stub-block">
	<div
		class="feed-post-stub-icon feed-post-stub-icon-<?= str_replace('_', '-', $arResult['EVENT_ID']) ?>"
	></div>
	<div class="feed-post-stub-message"><?= $arResult['MESSAGE'] ?></div>
	<div class="feed-post-stub-more">
		<a
			href="javascript:void(0)"
			onclick="if(top.BX.Helper)top.BX.Helper.show('redirect=detail&code=7258193'); event.preventDefault();"
		><?= Loc::getMessage('SLEB_TEMPLATE_MORE') ?></a></div>
</div>
<div class="feed-post-stub-buttons">
	<a
		href="<?= \CBitrix24::PATH_LICENSE_ALL ?>"
		class="ui-btn ui-btn-md ui-btn-primary"
	><?= Loc::getMessage('SLEB_TEMPLATE_BUTTON') ?></a>
</div>
