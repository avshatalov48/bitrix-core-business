<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("advertising"))
	return;

$arTypeFields = Array("-" =>GetMessage("ADV_SELECT_DEFAULT"));
$res = CAdvType::GetList($by, $order, Array("ACTIVE" => "Y"),$is_filtered, "Y");
while (is_object($res) && $ar = $res->GetNext())
{
	$arTypeFields[$ar["SID"]] = "[".$ar["SID"]."] ".$ar["NAME"];
}

$arComponentParameters = array(
	"GROUPS" => array(
		"SLIDE_SETTINGS" => array("NAME" => GetMessage("ADV_SLIDE_SETTINGS"), "SORT" => "150"),
		"NAV_SETTINGS" => array("NAME" => GetMessage("ADV_NAV_SETTINGS"), "SORT" => "250")
		),
	"PARAMETERS" => array(
		"TYPE" => Array(
			"NAME" => GetMessage("ADV_TYPE"),
			"PARENT" => "BASE",
			"TYPE" => "LIST",
			"DEFAULT" => "", 
			"VALUES" => $arTypeFields,
			"ADDITIONAL_VALUES" => "N"
		),
		"NOINDEX" => array(
			"NAME" => GetMessage("adv_banner_params_noindex"),
			"PARENT" => "BASE",
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",	
		),
		"QUANTITY" => array(
			"NAME" => GetMessage("ADV_QUANTITY"),
			"PARENT" => "BASE",
			"TYPE" => "STRING",
			"DEFAULT" => "1"
		),
		"CACHE_TIME" => Array("DEFAULT"=>"0"),
	)
);

if ($templateProperties['NEED_TEMPLATE'] == 'Y')
{
	$templates = array('-' => GetMessage("ADV_NOT_SELECTED"));
	$arTemplates = CComponentUtil::GetTemplatesList('bitrix:advertising.banner.view');
	if (is_array($arTemplates) && !empty($arTemplates))
	{
		foreach ($arTemplates as $template)
			$templates[$template['NAME']] = $template['NAME'];
	}

	$arComponentParameters['PARAMETERS']['DEFAULT_TEMPLATE'] = array(
		"NAME" => GetMessage("ADV_DEFAULT_TEMPLATE"),
		"PARENT" => "BASE",
		"TYPE" => "LIST",
		"VALUES" => $templates,
		"DEFAULT" => '',
		"ADDITIONAL_VALUES" => "N"
	);

	unset($templateProperties['NEED_TEMPLATE']);
}
