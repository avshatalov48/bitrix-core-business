<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Localization\Loc;

/** @var CMain $APPLICATION */

global $APPLICATION;

$saveButtonId = 'vote-im-edit-slider-button-create';
$cancelButtonId = 'vote-im-edit-slider-button-cancel';

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:voting.im.edit',
		'POPUP_COMPONENT_PARAMS' => [
			'SAVE_BUTTON_ID' => $saveButtonId,
		],
		'USE_UI_TOOLBAR' => 'Y',
		'BUTTONS' => [
			'save' => [
				'type' => 'save',
				'ID' => $saveButtonId,
				'CAPTION' => Loc::getMessage('VOTE_IM_EDIT_COMPONENT_CREATE_VOTE'),
			],
			'close' => [
				'type' => 'close',
				'ID' => $cancelButtonId,
				'CAPTION' => Loc::getMessage('VOTE_IM_EDIT_COMPONENT_CANCEL_VOTE'),
			],
		],
	],
);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");