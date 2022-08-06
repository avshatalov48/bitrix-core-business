<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

Loc::loadMessages(__FILE__);

if ($arResult['showThemePicker'])
{
	?><div class="socialnetwork-group-create-ex__project-background" id="GROUP_THEME_container">
		<div class="socialnetwork-group-create-ex__project-background--title"><?= Loc::getMessage('SONET_GCE_T_THEME') ?></div>
		<div class="socialnetwork-group-create-ex__project-background--area" bx-group-edit-theme-node="image">
			<div class="socialnetwork-group-create-ex__project-background--change">
				<div class="ui-btn ui-btn-xs ui-btn-primary ui-btn-round"><?= Loc::getMessage('SONET_GCE_T_THEME_CHANGE') ?></div>
			</div>
			<div class="socialnetwork-group-create-ex__project-background--title --background" bx-group-edit-theme-node="title"></div>
			<input type="hidden" bx-group-edit-theme-node="id" name="GROUP_THEME_ID" />
		</div>
	</div><?php
}
