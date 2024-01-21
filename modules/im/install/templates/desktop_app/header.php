<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<!DOCTYPE html>
<?php
\CModule::IncludeModule('im');

if (isset($_GET['IM_BACKGROUND']))
{
	$isDark = $_GET['IM_BACKGROUND'] === 'dark';
}
else
{
	$isDark = \CIMSettings::GetSetting(CIMSettings::SETTINGS, 'isCurrentThemeDark');
}
$styleFilePath = \Bitrix\Im\Settings::isLegacyChatActivated() ? '/template_styles_v1.css' : "/template_styles_v2.css";

$preloadExtensions = [
	'ui.design-tokens',
	'sidepanel',
	'intranet.sidepanel.bindings',
	'intranet.sidepanel.external',
];

if (\Bitrix\Im\Settings::isLegacyChatActivated())
{
	$preloadExtensions[] = 'socialnetwork.slider';
}

?>
<html <?=$darkClass?>>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=<?=SITE_CHARSET?>"/>
	<link href="<?=CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH . $styleFilePath)?>" type="text/css" rel="stylesheet" />
	<?
	\Bitrix\Main\UI\Extension::load($preloadExtensions);
	?>
	<?$APPLICATION->ShowCSS(true, true);?>
	<?$APPLICATION->ShowHeadStrings();?>
	<?$APPLICATION->ShowHeadScripts();?>
<title><?$APPLICATION->ShowTitle()?></title>
</head>
<body class="<?=$APPLICATION->ShowProperty("BodyClass");?>">