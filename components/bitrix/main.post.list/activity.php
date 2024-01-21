<?php

use Bitrix\Main\Loader;

const PUBLIC_AJAX_MODE = true;
const NO_KEEP_STATISTIC = 'Y';
const NO_AGENT_STATISTIC = 'Y';
const NO_AGENT_CHECK = true;
const DisableEventsCheck = true;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

/**
 * Bitrix vars
 * @global CUser $GLOBALS["USER"]
 * @global CMain $APPLICATION
 * @var array $arParams
 */

$arParams = [];
$arParams["AVATAR_SIZE"] = (int)$_REQUEST["AVATAR_SIZE"];
$arParams["AVATAR_SIZE"] = ($arParams["AVATAR_SIZE"] > 0 ? $arParams["AVATAR_SIZE"] : 42);
$arParams["NAME_TEMPLATE"] = (!empty($_REQUEST["NAME_TEMPLATE"]) ? $_REQUEST["NAME_TEMPLATE"] : CSite::GetNameFormat());
$arParams["SHOW_LOGIN"] = ($_REQUEST["SHOW_LOGIN"] === "Y" ? "Y" : "N");

global $USER;

$arParams["SIGN"] = null;
if (isset($_REQUEST["sign"]) && is_string($_REQUEST["sign"]) && !empty($_REQUEST["sign"]))
{
	try
	{
		$sign = (new \Bitrix\Main\Security\Sign\Signer());
		$arParams["SIGN"] = $sign->unsign($_REQUEST["sign"], "main.post.list");
	}
	catch (Exception $e)
	{
		$arParams["SIGN"] = null;
	}
}

if (!isset($_SESSION["UC_LAST_ACTIVITY"]) || !is_array($_SESSION["UC_LAST_ACTIVITY"]))
{
	$_SESSION["UC_LAST_ACTIVITY"] = [
		'TIME' => 0,
		'ENTITY_XML_ID' => $_REQUEST['ENTITY_XML_ID'],
	];
}

if (
	$_REQUEST['MODE'] === 'PUSH&PULL'
	&& is_string($arParams['SIGN'])
	&& $arParams['SIGN'] === $_REQUEST['ENTITY_XML_ID']
	&& check_bitrix_sessid()
 	&& $USER->IsAuthorized()
 	&& (
		!isset($_SESSION["UC_ACTIVITY"])
		|| $_SESSION["UC_ACTIVITY"]["ENTITY_XML_ID"] !== $_REQUEST["ENTITY_XML_ID"]
		|| (time() - $_SESSION["UC_ACTIVITY"]["TIME"]) > 10
	)
	&& Loader::includeModule('pull')
	&& CPullOptions::GetNginxStatus()
)
{
	$request = \Bitrix\Main\Context::getCurrent()->getRequest();

	$_SESSION["UC_ACTIVITY"]["TIME"] = time();
	$_SESSION["UC_ACTIVITY"]["ENTITY_XML_ID"] = $_REQUEST["ENTITY_XML_ID"];

	$dbUser = CUser::GetList(
		[ 'ID' => 'desc' ],
		'',
		[ 'ID' => $USER->GetId() ],
		[
			'FIELDS' => [ 'ID', 'LAST_NAME', 'NAME', 'SECOND_NAME', 'LOGIN', 'PERSONAL_PHOTO', 'PERSONAL_GENDER' ]
		]
	);

	$arUser = [];
	if ($dbUser && ($arUser = $dbUser->GetNext()) && ((int)$arUser["PERSONAL_PHOTO"] > 0))
	{
		$arUser["PERSONAL_PHOTO_file"] = CFile::GetFileArray($arUser["PERSONAL_PHOTO"]);
		$arUser["PERSONAL_PHOTO_resized_30"] = CFile::ResizeImageGet(
			$arUser["PERSONAL_PHOTO_file"],
			[
				'width' => $arParams['AVATAR_SIZE'],
				'height' => $arParams['AVATAR_SIZE'],
			],
			BX_RESIZE_IMAGE_EXACT,
			false,
			false,
			true
		);
	}

	$arUserInfo = (
		!empty($arUser)
			? $arUser
			: [ 'PERSONAL_PHOTO_resized_30' => [ 'src' => '' ] ]
	);
	$arUserInfo["NAME_FORMATED"] = CUser::FormatName(
		$arParams["NAME_TEMPLATE"],
		[
			"NAME" => $arUserInfo["~NAME"],
			"LAST_NAME" => $arUserInfo["~LAST_NAME"],
			"SECOND_NAME" => $arUserInfo["~SECOND_NAME"],
			"LOGIN" => $arUserInfo["~LOGIN"],
			"NAME_LIST_FORMATTED" => "",
		],
		$arParams["SHOW_LOGIN"] !== "N",
		false
	);
	CPullWatch::AddToStack('UNICOMMENTS' . $_REQUEST["ENTITY_XML_ID"],
		[
			'module_id' => 'unicomments',
			'command' => 'answer',
			'expiry' => 60,
			'params' => [
				"USER_ID" => $USER->GetId(),
				"ENTITY_XML_ID" => $_REQUEST["ENTITY_XML_ID"],
				"TS" => time(),
				"NAME" => $arUserInfo["NAME_FORMATED"],
				"AVATAR" => $arUserInfo["PERSONAL_PHOTO_resized_30"]["src"] ?? ''
			] + (
				$request->getPost("COMMENT_EXEMPLAR_ID") === null
					? []
					: [ 'COMMENT_EXEMPLAR_ID' => $request->getPost('COMMENT_EXEMPLAR_ID') ]
			)
		]
	);

	CPullWatch::AddToStack('UNICOMMENTSEXTENDED'.$_REQUEST["ENTITY_XML_ID"],
		[
			'module_id' => 'unicomments',
			'command' => 'answer',
			'expiry' => 60,
			'params' => [
				"USER_ID" => $USER->GetId(),
				"ENTITY_XML_ID" => $_REQUEST["ENTITY_XML_ID"],
				"TS" => time(),
				"NAME" => $arUserInfo["NAME_FORMATED"],
				"AVATAR" => $arUserInfo["PERSONAL_PHOTO_resized_30"]["src"]
			] + (
				$request->getPost("COMMENT_EXEMPLAR_ID") === null
					? []
					: [ 'COMMENT_EXEMPLAR_ID' => $request->getPost('COMMENT_EXEMPLAR_ID') ]
			)
		]
	);

	CMain::FinalActions();
	die();
}
