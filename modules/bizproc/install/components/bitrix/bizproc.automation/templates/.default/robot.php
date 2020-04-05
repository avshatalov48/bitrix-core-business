<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

\Bitrix\Main\Loader::includeModule('socialnetwork');
CUtil::InitJSCore(
	['tooltip', 'date', 'bp_user_selector', 'bp_field_type']
);

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
$dialog = $arResult['dialog'];

$dialog->setDialogFileName('robot_properties_dialog');

echo $dialog;
