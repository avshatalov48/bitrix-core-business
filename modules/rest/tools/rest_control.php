<?php
require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");

/**
 * Bitrix vars
 *
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

$result = array();
$request = Bitrix\Main\Context::getCurrent()->getRequest();

if($request->isPost() && check_bitrix_sessid())
{
	$APPLICATION->RestartBuffer();
	$APPLICATION->ShowCSS();
	$APPLICATION->ShowHeadScripts();

	switch($request['control'])
	{
		case 'user_selector':

			$APPLICATION->IncludeComponent(
				"bitrix:intranet.user.selector.new",
				".default",
				array(
					"MULTIPLE" => isset($request['mult']) ? 'Y' : 'N',
					"NAME" => $request['name'],
					"VALUE" => array(),
					"POPUP" => "Y",
					"ON_CHANGE" => $request['onchange'],
					"SITE_ID" => $request['site_id'],
					"SHOW_EXTRANET_USERS" => "NONE",
				),
				null,
				array("HIDE_ICONS" => "Y")
			);

		break;

		case 'access_selector':

			echo \CJSCore::Init(array('access'), true);

		break;

		case 'crm_selector':

			$APPLICATION->IncludeComponent(
				'bitrix:crm.entity.selector.ajax',
				'.default',
				array(
					"MULTIPLE" => $request['multiple'] == 'Y' ? 'Y' : 'N',
					'VALUE' => $request['value'],
					'ENTITY_TYPE' => $request['entityType'],
					'NAME' => 'restCrmSelector',
				),
				null,
				array('HIDE_ICONS' => 'Y')
			);

		break;
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
die();
