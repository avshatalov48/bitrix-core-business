<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

use Bitrix\Main\Loader;

if(!Loader::includeModule('rest'))
{
	return;
}

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$clientId = $request['client_id'];
if(!$clientId)
{
	ShowError(\Bitrix\Main\Localization\Loc::getMessage('REST_APP_NOT_FOUND'));
	return;
}

if($USER->IsAuthorized())
{
	if(isset($request['state']))
	{
		$state = $request['state'];
	}
	else
	{
		$state = '';
	}

	$authResult = \Bitrix\Rest\Application::getAuthProvider()->authorizeClient($clientId, $USER->GetID(), $state);

	if($authResult['error'])
	{
		ShowError($authResult['error'].': '.$authResult['error_description']);
	}
	elseif($authResult['redirect_uri'])
	{
		$redirectUri = $authResult['redirect_uri'];

		unset($authResult['redirect_uri']);

		$fragment = '';
		if(array_key_exists('fragment', $authResult))
		{
			$fragment = $authResult['fragment'];
			unset($authResult['fragment']);
		}

		$authResult['server_domain'] = $authResult['domain'];
		$authResult['domain'] = $request->getHttpHost();

		$redirectUri .= (mb_strpos($redirectUri, '?') !== false) ? '&' : '?';
		$redirectUri .= http_build_query($authResult);

		if($fragment <> '')
		{
			$redirectUri .= '#'.$fragment;
		}

		LocalRedirect($redirectUri, true);
	}
	else
	{
		$arResult['OAUTH_PARAMS'] = $authResult;
		$this->includeComponentTemplate();
	}
}
else
{
	if(isset($request['client_id']))
	{
		$appInfo = \Bitrix\Rest\AppTable::getByClientId($request['client_id']);
		if($appInfo && $appInfo['ACTIVE'] === \Bitrix\Rest\AppTable::ACTIVE)
		{
			$APPLICATION->AuthForm(\Bitrix\Main\Localization\Loc::getMessage('REST_NEED_AUTHORIZE_A', array(
				'#APP_ID#' => $appInfo['CODE']
			)));
			return;
		}
	}

	ShowError(\Bitrix\Main\Localization\Loc::getMessage('REST_APP_NOT_FOUND'));
}
