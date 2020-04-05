<?php
if (!defined ('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!\Bitrix\Main\Loader::includeModule('landing'))
{
	return;
}
?><!DOCTYPE html>
<html xml:lang="<?= LANGUAGE_ID;?>" lang="<?= LANGUAGE_ID;?>" class="<?$APPLICATION->ShowProperty('HtmlClass');?>">
<head>
	<?$APPLICATION->ShowProperty('AfterHeadOpen');?>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title><? $APPLICATION->ShowTitle(); ?></title>

	<?if (\Bitrix\Landing\Config::get('google_font')):?>
    <!-- Google Fonts -->
	<link rel="preload" as="style" onload="this.removeAttribute('onload');this.rel='stylesheet'" data-font="g-font-open-sans" data-protected="true" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&subset=cyrillic">
	<link rel="preload" as="style" onload="this.removeAttribute('onload');this.rel='stylesheet'" data-font="g-font-roboto" data-protected="true" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&subset=cyrillic,cyrillic-ext,latin-ext">
	<link rel="preload" as="style" onload="this.removeAttribute('onload');this.rel='stylesheet'" data-font="g-font-roboto-slab" data-protected="true" href="https://fonts.googleapis.com/css?family=Roboto+Slab:300,400,700&subset=cyrillic,cyrillic-ext,latin-ext">
	<link rel="preload" as="style" onload="this.removeAttribute('onload');this.rel='stylesheet'" data-font="g-font-ek-mukta" data-protected="true" href="https://fonts.googleapis.com/css?family=Ek+Mukta:400,600,700">
	<link rel="preload" as="style" onload="this.removeAttribute('onload');this.rel='stylesheet'" data-font="g-font-montserrat" data-protected="true" href="https://fonts.googleapis.com/css?family=Montserrat:300,400,600,700,900&subset=cyrillic">
	<link rel="preload" as="style" onload="this.removeAttribute('onload');this.rel='stylesheet'" data-font="g-font-alegreya-sans" data-protected="true" href="https://fonts.googleapis.com/css?family=Alegreya+Sans:400,700,900&subset=cyrillic-ext,latin-ext">
	<link rel="preload" as="style" onload="this.removeAttribute('onload');this.rel='stylesheet'" data-font="g-font-cormorant-infant" data-protected="true" href="https://fonts.googleapis.com/css?family=Cormorant+Infant:400,400i,600,600i,700,700i&subset=cyrillic-ext,latin-ext">
	<link rel="preload" as="style" onload="this.removeAttribute('onload');this.rel='stylesheet'" data-font="g-font-pt-sans-caption" data-protected="true" href="https://fonts.googleapis.com/css?family=PT+Sans+Caption:400,700&subset=cyrillic-ext,latin-ext">
	<link rel="preload" as="style" onload="this.removeAttribute('onload');this.rel='stylesheet'" data-font="g-font-pt-sans-narrow" data-protected="true" href="https://fonts.googleapis.com/css?family=PT+Sans+Narrow:400,700|PT+Sans:400,700&subset=cyrillic-ext,latin-ext">
	<link rel="preload" as="style" onload="this.removeAttribute('onload');this.rel='stylesheet'" data-font="g-font-pt-sans" data-protected="true" href="https://fonts.googleapis.com/css?family=PT+Sans:400,700&subset=cyrillic-ext,latin-ext">
	<link rel="preload" as="style" onload="this.removeAttribute('onload');this.rel='stylesheet'" data-font="g-font-lobster" data-protected="true" href="https://fonts.googleapis.com/css?family=Lobster&subset=cyrillic-ext,latin-ext">
	<noscript>
		<link data-font="g-font-open-sans" data-protected="true" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&subset=cyrillic" rel="stylesheet">
		<link data-font="g-font-roboto" data-protected="true" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&subset=cyrillic,cyrillic-ext,latin-ext" rel="stylesheet">
		<link data-font="g-font-roboto-slab" data-protected="true" href="https://fonts.googleapis.com/css?family=Roboto+Slab:300,400,700&subset=cyrillic,cyrillic-ext,latin-ext" rel="stylesheet">
		<link data-font="g-font-ek-mukta" data-protected="true" href="https://fonts.googleapis.com/css?family=Ek+Mukta:400,600,700" rel="stylesheet">
		<link data-font="g-font-montserrat" data-protected="true" href="https://fonts.googleapis.com/css?family=Montserrat:300,400,600,700,900&subset=cyrillic" rel="stylesheet">
		<link data-font="g-font-alegreya-sans" data-protected="true" href="https://fonts.googleapis.com/css?family=Alegreya+Sans:400,700,900&subset=cyrillic-ext,latin-ext" rel="stylesheet">
		<link data-font="g-font-cormorant-infant" data-protected="true" href="https://fonts.googleapis.com/css?family=Cormorant+Infant:400,400i,600,600i,700,700i&subset=cyrillic-ext,latin-ext" rel="stylesheet">
		<link data-font="g-font-pt-sans-caption" data-protected="true" href="https://fonts.googleapis.com/css?family=PT+Sans+Caption:400,700&subset=cyrillic-ext,latin-ext" rel="stylesheet">
		<link data-font="g-font-pt-sans-narrow" data-protected="true" href="https://fonts.googleapis.com/css?family=PT+Sans+Narrow:400,700|PT+Sans:400,700&subset=cyrillic-ext,latin-ext" rel="stylesheet">
		<link data-font="g-font-pt-sans" data-protected="true" href="https://fonts.googleapis.com/css?family=PT+Sans:400,700&subset=cyrillic-ext,latin-ext" rel="stylesheet">
		<link data-font="g-font-lobster" data-protected="true" href="https://fonts.googleapis.com/css?family=Lobster&subset=cyrillic-ext,latin-ext" rel="stylesheet">
	</noscript>
	<?endif;?>

	<?
	$APPLICATION->ShowHead();
	$APPLICATION->ShowProperty('MetaOG');
	IncludeTemplateLangFile(__FILE__);
	$APPLICATION->ShowProperty('BeforeHeadClose');
	?>
</head>

<body class="<?$APPLICATION->ShowProperty('BodyClass');?>" <?
	?><?$APPLICATION->ShowProperty('BodyTag');?><?
	?><?= isset($_REQUEST['theme']) ? ' style="pointer-events: none; user-select: none;"' : '';?>>
<?
/*
This is commented to avoid Project Quality Control warning
$APPLICATION->ShowPanel();
*/
?>
<?$APPLICATION->ShowProperty('AfterBodyOpen');?>
<main class="<?$APPLICATION->ShowProperty('MainClass');?>">