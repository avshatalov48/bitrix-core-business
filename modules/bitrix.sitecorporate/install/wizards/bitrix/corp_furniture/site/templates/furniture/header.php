<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
IncludeTemplateLangFile(__FILE__);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<?$APPLICATION->ShowHead();?>
<link href="<?=SITE_TEMPLATE_PATH?>/common.css" type="text/css" rel="stylesheet" />
<link href="<?=SITE_TEMPLATE_PATH?>/colors.css" type="text/css" rel="stylesheet" />

	<!--[if lte IE 6]>
	<style type="text/css">
		
		#banner-overlay { 
			background-image: none;
			filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>images/overlay.png', sizingMethod = 'crop'); 
		}
		
		div.product-overlay {
			background-image: none;
			filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>images/product-overlay.png', sizingMethod = 'crop');
		}
		
	</style>
	<![endif]-->

	<title><?$APPLICATION->ShowTitle()?></title>
</head>
<body>
	<div id="page-wrapper">
	<div id="panel"><?$APPLICATION->ShowPanel();?></div>
		<div id="header">
			
			<table id="logo">
				<tr>
					<td><a href="<?=SITE_DIR?>" title="<?=GetMessage('CFT_MAIN')?>"><?
$APPLICATION->IncludeFile(
	SITE_DIR."include/company_name.php",
	Array(),
	Array("MODE"=>"html")
);
?></a></td>
				</tr>
			</table>
			
			<div id="top-menu">
				<div id="top-menu-inner">
<?$APPLICATION->IncludeComponent("bitrix:menu", "horizontal_multilevel", array(
	"ROOT_MENU_TYPE" => "top",
	"MAX_LEVEL" => "2",
	"CHILD_MENU_TYPE" => "left",
	"USE_EXT" => "Y",
	"MENU_CACHE_TYPE" => "A",
	"MENU_CACHE_TIME" => "36000000",
	"MENU_CACHE_USE_GROUPS" => "Y",
	"MENU_CACHE_GET_VARS" => ""
	),
	false,
	array(
	"ACTIVE_COMPONENT" => "Y"
	)
);?>
				</div>
			</div>
			
			<div id="top-icons">
				<a href="<?=SITE_DIR?>" class="home-icon" title="<?=GetMessage('CFT_MAIN')?>"></a>
				<a href="<?=SITE_DIR?>search/" class="search-icon" title="<?=GetMessage('CFT_SEARCH')?>"></a>
				<a href="<?=SITE_DIR?>contacts/" class="feedback-icon" title="<?=GetMessage('CFT_FEEDBACK')?>"></a>
			</div>
		
		</div>
		
		<div id="banner">		
			<table id="banner-layout">
				<tr>
					<td id="banner-image"><div><img src="<?=SITE_TEMPLATE_PATH?>/images/head.jpg" alt=""/></div></td>
					<td id="banner-slogan">
<?
$APPLICATION->IncludeFile(
	SITE_DIR."include/motto.php",
	Array(),
	Array("MODE"=>"html")
);
?>
					</td>
				</tr>
			</table>
			<div id="banner-overlay"></div>	
		</div>
		
		<div id="content">
		
			<div id="sidebar">
<?$APPLICATION->IncludeComponent("bitrix:menu", "left", array(
	"ROOT_MENU_TYPE" => "left",
	"MENU_CACHE_TYPE" => "A",
	"MENU_CACHE_TIME" => "36000000",
	"MENU_CACHE_USE_GROUPS" => "Y",
	"MENU_CACHE_GET_VARS" => array(
	),
	"MAX_LEVEL" => "1",
	"CHILD_MENU_TYPE" => "left",
	"USE_EXT" => "Y",
	"ALLOW_MULTI_SELECT" => "N"
	),
	false,
	array(
		"ACTIVE_COMPONENT" => "Y"
	)
);?>
				<div class="content-block">
					<div class="content-block-inner">
						<h3><?=GetMessage('CFT_NEWS')?></h3>
<?
$APPLICATION->IncludeFile(
	SITE_DIR."include/news.php",
	Array(),
	Array("MODE"=>"html")
);
?>
					</div>
				</div>
				
				<div class="content-block">
					<div class="content-block-inner">
						
<?
$APPLICATION->IncludeComponent("bitrix:search.form", "flat", Array(
	"PAGE" => "#SITE_DIR#search/",
),
	false
);
?>
					</div>
				</div>

				<div class="information-block">
					<div class="top"></div>
					<div class="information-block-inner">
						<h3><?=GetMessage('CFT_FEATURED')?></h3>
<?
$APPLICATION->IncludeFile(
	SITE_DIR."include/random.php",
	Array(),
	Array("MODE"=>"html")
);
?>						
					</div>
					<div class="bottom"></div>
				</div>
			</div>
		
			<div id="workarea">
				<h1 id="pagetitle"><?$APPLICATION->ShowTitle(false);?></h1>