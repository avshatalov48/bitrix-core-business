<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile(__FILE__);
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=LANGUAGE_ID?>" lang="<?=LANGUAGE_ID?>">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link rel="shortcut icon" type="image/x-icon" href="<?=SITE_TEMPLATE_PATH?>/favicon.ico" />
<link rel="stylesheet" type="text/css" href="<?=SITE_TEMPLATE_PATH?>/common.css" />
<?$APPLICATION->ShowHead();?>
<link rel="stylesheet" type="text/css" href="<?=SITE_TEMPLATE_PATH?>/colors.css" />
<title><?$APPLICATION->ShowTitle()?></title>
</head>
<body>
	<div id="panel"><?$APPLICATION->ShowPanel();?></div>
	<div id="page-wrapper">
	<?if(CModule::IncludeModule('advertising')):?>
	<?$APPLICATION->IncludeComponent("bitrix:advertising.banner", "top", Array(
	"TYPE" => "TOP",
	"NOINDEX" => "N",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "0",
	),
	false
	);?>
	<?endif;?>
	<div id="header">
	<div id="header-title"><a href="<?=SITE_DIR?>"><?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/infoportal_name.php"), false);?></a></div>
	<div id="header-auth">
		<?$APPLICATION->IncludeComponent("bitrix:system.auth.form", "info", array(
			"REGISTER_URL" => SITE_DIR."login/",
			"PROFILE_URL" => SITE_DIR."personal/profile/",
			"SHOW_ERRORS" => "N"
			),
			false,
			Array()
		);?>
	</div>
	<div id="main-menu">
	<?$APPLICATION->IncludeComponent("bitrix:menu", "horizontal_multilevel", array(
	"ROOT_MENU_TYPE" => "top",
	"MENU_CACHE_TYPE" => "A",
	"MENU_CACHE_TIME" => "36000000",
	"MENU_CACHE_USE_GROUPS" => "N",
	"MENU_CACHE_GET_VARS" => array(
	),
	"MAX_LEVEL" => "1",
	"CHILD_MENU_TYPE" => "top",
	"USE_EXT" => "Y",
	"DELAY" => "N",
	"ALLOW_MULTI_SELECT" => "N"
	),
	false
	);?>
	</div>
	</div>
	<div id="page-body">
	<table width="100%" cellspacing="0" cellpadding="0" >
		<tr>
		<?$arCurDir = explode("/", $APPLICATION->GetCurDir());?>
		<td <?if(!array_search('forum', $arCurDir)):?>width="60%"<?endif;?> class="page-left">
		<?if($APPLICATION->GetCurDir() != SITE_DIR):?>
		<h1><?$APPLICATION->ShowTitle()?></h1>
		<?endif;?>