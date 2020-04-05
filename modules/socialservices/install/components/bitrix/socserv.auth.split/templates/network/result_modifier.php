<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

if(\Bitrix\Main\Loader::includeModule('socialservices'))
{
	$dbRes = \Bitrix\SocialServices\UserTable::getList(array(
		'filter' => array(
			'USER_ID' => $arParams['USER_ID'],
			'EXTERNAL_AUTH_ID' => CSocServBitrix24Net::ID
		),
		'select' => array(
			'NAME', 'LAST_NAME', 'LOGIN', 'PERSONAL_WWW', 'XML_ID'
		),
	));

	$arResult['NETWORK_ACCOUNT'] = $dbRes->fetch();

	if(is_array($arResult['NETWORK_ACCOUNT']) && strlen($arResult['NETWORK_ACCOUNT']['PERSONAL_WWW']) <= 0)
	{
		$arResult['NETWORK_ACCOUNT']['PERSONAL_WWW'] = CSocServBitrix24Net::NETWORK_URL.'/id'.$arResult['NETWORK_ACCOUNT']['XML_ID'].'/';
	}
}
?>