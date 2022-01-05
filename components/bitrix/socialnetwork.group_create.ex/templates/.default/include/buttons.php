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

?><script>
	BX.message({
		SONET_GCE_T_DO_CREATE: '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_DO_CREATE')) ?>',
		SONET_GCE_T_DO_CREATE_PROJECT: '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_DO_CREATE_PROJECT')) ?>',
	});
</script><?php

$strSubmitButtonTitle = '';
$actionType = '';

if (empty($arResult['TAB']))
{
	$strSubmitButtonTitle = Loc::getMessage('SONET_GCE_T_DO_NEXT');
	$actionType = 'create';

}
elseif ($arResult['TAB'] === 'edit')
{
	$strSubmitButtonTitle = Loc::getMessage('SONET_GCE_T_DO_EDIT_2');
	$actionType = 'edit';
}
elseif ($arResult['TAB'] === 'invite')
{
	$strSubmitButtonTitle = Loc::getMessage('SONET_GCE_T_DO_INVITE');
	$actionType = "invite";
}

$buttons = [];

if (
	$arResult['USE_PRESETS'] === 'Y'
	&& empty($arResult['TAB'])
)
{
	$buttons[] = [
		'TYPE' => 'custom',
		'LAYOUT' => '<button class="ui-btn ui-btn-link socialnetwork-group-create-ex__button-invisible" id="sonet_group_create_popup_form_button_step_2_back">' . Loc::getMessage('SONET_GCE_T_T_BACK') . '</button>',
	];
}

$classList = [
	'ui-btn',
	'ui-btn-success',
	'ui-btn-md',
];

if ($arResult['IS_IFRAME'])
{
	$classList[] = 'ui-btn-round';
}

$buttons[] = [
	'TYPE' => 'custom',
	'LAYOUT' => '<button class="' . implode(' ', $classList) . '" id="sonet_group_create_popup_form_button_submit" bx-action-type="' . ($actionType ?? 'none') . '">' . $strSubmitButtonTitle . '</button>'
];

if (
	!empty($arResult['TAB'])
	&& !$arResult['IS_IFRAME']
)
{
	$buttons[] = [
		'TYPE' => 'custom',
		'LAYOUT' => '<button class="ui-btn ui-btn-link" id="sonet_group_create_popup_form_button_step_2_cancel" bx-url="' . htmlspecialcharsbx($arResult['Urls']['Group']) . '">' . Loc::getMessage('SONET_GCE_T_T_CANCEL') . '</button>',
	];
}

$APPLICATION->IncludeComponent('bitrix:ui.button.panel', '', [
	'BUTTONS' => $buttons,
]);
