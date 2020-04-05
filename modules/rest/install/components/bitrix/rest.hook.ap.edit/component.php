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

if(!\Bitrix\Main\Loader::includeModule('rest') || !$USER->IsAuthorized())
{
	return;
}

$arParams['ID'] = intval($arParams['ID']);

InitBVar($arParams['SET_TITLE']);

if($arParams['ID'] > 0)
{
	$dbRes = \Bitrix\Rest\APAuth\PasswordTable::getById($arParams['ID']);
	$password = $dbRes->fetch();

	if(is_array($password))
	{
		if($password['USER_ID'] != $USER->GetID())
		{
			$password = false;
		}
	}

	if(!$password)
	{
		ShowError(\Bitrix\Main\Localization\Loc::getMessage('REST_HAPE_NOT_FOUND'));
		return;
	}

	$arResult['INFO'] = array(
		'ID' => $password['ID'],
		"TITLE" => $password['TITLE'],
		"COMMENT" => $password['COMMENT'],
		"DATE_CREATE" => $password['DATE_CREATE'],
		"DATE_LOGIN" => $password['DATE_LOGIN'],
		"LAST_IP" => $password['LAST_IP'],
		"PASSWORD" => $password['PASSWORD'],
		"SCOPE" => array(),
	);

	$arResult['EXAMPLE'] = \CRestUtil::getWebhookEndpoint($arResult['INFO']['PASSWORD'], $password['USER_ID'], 'profile');

	$dbRes = \Bitrix\Rest\APAuth\PermissionTable::getList(
		array(
			'filter' => array(
				'=PASSWORD_ID' => $password['ID'],
			),
			'select' => array('PERM')
		)
	);
	while($perm = $dbRes->fetch())
	{
		$arResult['INFO']['SCOPE'][] = $perm['PERM'];
	}

	$arResult['INFO']['SCOPE'] = \Bitrix\Rest\APAuth\PermissionTable::cleanPermissionList($arResult['INFO']['SCOPE']);
}
else
{
	$arResult['INFO'] = array(
		'ID' => 0,
		'TITLE' => \Bitrix\Main\Localization\Loc::getMessage('REST_HAPE_TITLE_DEFAULT'),
		'SCOPE' => array(),
	);
}

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

if($request->isPost() && check_bitrix_sessid())
{
	$arResult['INFO']['TITLE'] = trim($request['TITLE']);
	$arResult['INFO']['COMMENT'] = trim($request['COMMENT']);
	$arResult['INFO']['SCOPE'] = is_array($request['SCOPE']) ? $request['SCOPE'] : array();

	$arResult['INFO']['SCOPE'] = \Bitrix\Rest\APAuth\PermissionTable::cleanPermissionList($arResult['INFO']['SCOPE']);

	$justCreated = false;

	if($arResult['INFO']['ID'] > 0)
	{
		$result = \Bitrix\Rest\APAuth\PasswordTable::update(
			$arResult['INFO']['ID'],
			array(
				'TITLE' => $arResult['INFO']['TITLE'],
				'COMMENT' => $arResult['INFO']['COMMENT'],
			)
		);
	}
	else
	{
		$arResult['INFO']['PASSWORD'] = \Bitrix\Rest\APAuth\PasswordTable::generatePassword();

		$result = \Bitrix\Rest\APAuth\PasswordTable::add(
			array(
				'USER_ID' => $USER->getId(),
				'PASSWORD' => $arResult['INFO']['PASSWORD'],
				'DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
				'TITLE' => $arResult['INFO']['TITLE'],
				'COMMENT' => $arResult['INFO']['COMMENT'],
			)
		);

		$justCreated = true;
	}

	if($result->isSuccess())
	{
		if($arResult['INFO']['ID'] > 0)
		{
			\Bitrix\Rest\APAuth\PermissionTable::deleteByPasswordId($arResult['INFO']['ID']);
		}
		else
		{
			$arResult['INFO']['ID'] = $result->getId();
		}

		foreach($arResult['INFO']['SCOPE'] as $scope)
		{
			\Bitrix\Rest\APAuth\PermissionTable::add(array(
				'PASSWORD_ID' => $arResult['INFO']['ID'],
				'PERM' => $scope,
			));
		}

		if($justCreated)
		{
			$url = new \Bitrix\Main\Web\Uri(str_replace(
				'#id#', $arResult['INFO']['ID'], $arParams['EDIT_URL_TPL']
			));

			LocalRedirect(
				$url->addParams(array('success' => 1))
					->getLocator()
			);
		}
		else
		{
			LocalRedirect($arParams['LIST_URL']);
		}
	}
	else
	{
		$arResult['ERROR'] = implode('<br />', $result->getErrorMessages());
	}
}

$arResult["SCOPE"] = \CRestUtil::getScopeList();
$arResult["SCOPE"] = \Bitrix\Rest\APAuth\PermissionTable::cleanPermissionList($arResult['SCOPE']);

$arResult['HTTPS'] = $request->isHttps();

if($arParams['SET_TITLE'] == 'Y')
{
	if($arResult['INFO']['ID'] > 0)
	{
		$APPLICATION->SetTitle(\Bitrix\Main\Localization\Loc::getMessage('REST_HAPE_EDIT_TITLE'));
	}
	else
	{
		$APPLICATION->SetTitle(\Bitrix\Main\Localization\Loc::getMessage('REST_HAPE_ADD_TITLE'));
	}
}

$this->IncludeComponentTemplate();
