<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */

if(!CModule::IncludeModule("sale"))
	return;

$providerList = array();
$groupList = array();
$fieldsList = array();
$currentProviderCode = null;
$currentGroupCode = null;
$currentFieldCode = null;

// PROVIDERS
$providerFieldsList = \Bitrix\Sale\BusinessValue::getProviders();
foreach($providerFieldsList as $providerCode => $provider)
{
	if(isset($provider['FIELDS']) && is_array($provider['FIELDS']))
	{
		$providerList[$providerCode] = $provider['NAME'];
	}
}

// CURRENT PROVIDER
foreach($providerList as $providerCode => $provider)
{
	$currentProviderCode = $providerCode;
	break;
}
if(isset($arCurrentValues['PROVIDER']) && isset($providerList[$arCurrentValues['PROVIDER']]))
{
	$currentProviderCode = $arCurrentValues['PROVIDER'];
}

// GROUPS
$currentProvider = $providerFieldsList[$currentProviderCode];
foreach($currentProvider['FIELDS_GROUPS'] as $groupCode => $group)
{
	$groupList[$groupCode] = $group['NAME'];
}

// CURRENT GROUP
foreach($groupList as $groupCode => $group)
{
	$currentGroupCode = $groupCode;
	break;
}
if(isset($arCurrentValues['GROUP']) && isset($groupList[$arCurrentValues['GROUP']]))
{
	$currentGroupCode = $arCurrentValues['GROUP'];
}

// FIELDS
foreach($currentProvider['FIELDS'] as $fieldCode => $field)
{
	$fieldCode = (isset($field['CODE']) && $field['CODE']) ? $field['CODE'] : $fieldCode;
	if(!isset($field['GROUP']) || !$field['GROUP'])
	{
		$fieldsList[$fieldCode] = $field['NAME'];
		continue;
	}

	if(!isset($field['GROUP']) || !$currentGroupCode)
	{
		continue;
	}

	if($field['GROUP'] == $currentGroupCode)
	{
		$fieldsList[$fieldCode] = $field['NAME'];
	}
}

$arComponentParameters = array(
	"GROUPS" => array(),
	"PARAMETERS" => array()
);

$arComponentParameters['PARAMETERS']['PROVIDER'] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage('SALE_BVAL_MAIL_PARAM_PROVIDER'),
	"TYPE" => "LIST",
	"VALUES" => $providerList,
	"REFRESH" => "Y",
);

if(count($groupList) > 0)
{
	$arComponentParameters['PARAMETERS']['GROUP'] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage('SALE_BVAL_MAIL_PARAM_GROUP'),
		"TYPE" => "LIST",
		"VALUES" => $groupList,
		"REFRESH" => "Y",
	);
}

$arComponentParameters['PARAMETERS']['FIELD'] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage('SALE_BVAL_MAIL_PARAM_FIELD'),
	"TYPE" => "LIST",
	"VALUES" => $fieldsList,
	"MULTIPLE" => "Y"
);
$arComponentParameters['PARAMETERS']['ORDER_ID'] = array(
	"PARENT" => "ADDITIONAL",
	"NAME" => GetMessage('SALE_BVAL_MAIL_PARAM_FIELD_ORDER_ID'),
	"TYPE" => "STRING",
	"DEFAULT" => "{#ORDER_ID#}",
);
?>