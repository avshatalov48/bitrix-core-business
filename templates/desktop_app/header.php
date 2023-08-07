<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<!DOCTYPE html>
<?php
\CModule::IncludeModule('im');
$darkClass = \CIMSettings::GetSetting(CIMSettings::SETTINGS, 'isCurrentThemeDark')? 'style="background: #313131"': '';
$styleFilePath = \Bitrix\Im\Settings::isBetaActivated() ? "/template_styles_v2.css" : '/template_styles_v1.css';
?>
<html <?=$darkClass?>>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=<?=SITE_CHARSET?>"/>
	<link href="<?=CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH . $styleFilePath)?>" type="text/css" rel="stylesheet" />
	<?
	\Bitrix\Main\UI\Extension::load([
		'ui.design-tokens',
		'sidepanel',
		'intranet.sidepanel.bindings',
		'intranet.sidepanel.external',
		'socialnetwork.slider',
	]);
	?>
	<?$APPLICATION->ShowCSS(true, true);?>
	<?$APPLICATION->ShowHeadStrings();?>
	<?$APPLICATION->ShowHeadScripts();?>
<title><?$APPLICATION->ShowTitle()?></title>
</head>
<body class="<?=$APPLICATION->ShowProperty("BodyClass");?>">